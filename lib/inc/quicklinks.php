<?php

//----- INSTANTIATE -----//

global $meerkat_ql;
if ( ! $meerkat_ql ) {
	$meerkat_ql = new MeerkatQuickLinks();
}

// template redirect
add_action( 'template_redirect', array( &$meerkat_ql, 'quick_links_template' ) );

//---- MAP JAVASCRIPT AJAX CALLS TO PHP FUNCTIONS ----//

// logout
add_action( "wp_ajax_logout", array( &$meerkat_ql, 'logout' ) );
add_action( "wp_ajax_nopriv_logout", array( &$meerkat_ql, 'logout' ) );

// login
//add_action("wp_ajax_login", array (&$meerkat_ql, 'login'));
//add_action("wp_ajax_nopriv_login", array (&$meerkat_ql, 'login'));

// load default quicklinks
add_action( "wp_ajax_load_default_links", array( &$meerkat_ql, 'load_default_links' ) );
add_action( "wp_ajax_nopriv_load_default_links", array( &$meerkat_ql, 'load_default_links' ) );

// load all quicklinks
add_action( "wp_ajax_load_all", array( &$meerkat_ql, 'load_all_links' ) );
add_action( "wp_ajax_nopriv_load_all", array( &$meerkat_ql, 'load_all_links' ) );

// load custom quicklinks/feeds
add_action( "wp_ajax_load_custom", array( &$meerkat_ql, 'load_custom' ) );
add_action( "wp_ajax_nopriv_load_custom", array( &$meerkat_ql, 'load_custom' ) );

// save
add_action( "wp_ajax_save", array( &$meerkat_ql, 'save' ) );
add_action( "wp_ajax_nopriv_save", array( &$meerkat_ql, 'save' ) );

class MeerkatQuickLinks {
	
	function __construct() {
		global $js;
		$js['purl']['load'] = true;
		session_start();
	}
	
	function quick_links_template() {
		// (this will pick up requests for people who don't have a post)
		if ( substr( $_SERVER['REQUEST_URI'], 0, 3 ) == '/ql' ) {
			status_header( 200 );
			get_template_part( 'template-quicklinks' );
			exit( 0 );
		}
	}
	
	function logout() {
		unset ( $_SESSION['authenticated'] );
		$_COOKIE['quicklinks_user'] = '';
	}
	
	function load_default_links() {
		// grab default list of links  from the flexiform database, print out jsonp response
		$items = $this->get_flexi_data( 'ql: default' );
		$sort = true;
		echo $this->build_flexi_jsonp( $items, $sort );
		die();
	}
	
	function load_all_links() {
		$default_links = $this->get_flexi_data( 'ql: default' );
		//$most_pop_links = $this->get_flexi_data( 'ql: most popular' ); 
		$most_pop_links = $this->get_flexi_data( 'dir: A-Z' );
		$all_links = array();
		$link_sets = array( $default_links, $most_pop_links );
		
		foreach ( $link_sets as $link_set ) {
			foreach ( $link_set as $data ) {
				//	echo '<pre>'; print_r($data); echo '</pre>';
				$title = $data['Title']['value'];
				//	echo "title is $title<br>";
				$url = $data['URL']['value'];
				$temp = array( 'title' => $title, 'url' => $url );
				array_push( $all_links, $temp );
			}
		}
		
		// sort link cats alphabetically
		usort( $all_links, array( $this, 'sort_links' ) );
		
		return $all_links;
	}
	
	function load_feeds() {
		// get a list of rss feeds either from user's quickfeeds cookie or use default database feed list
		
		$feeds = array();
		
		// utf-8 encoded seperator chars
		$pair_sep = "\xE2\x99\xA0";                // spade
		$name_val_sep = "\xE2\x99\xA5";        // heart
		
		// get all feeds from flexiform db
		$flexi_feeds = $this->get_flexi_data( 'rss' );
		// massage data format
		foreach ( $flexi_feeds as $feed => $data ) {
			$feeds[] = array( 'title' => $data['Title']['value'], 'url' => $data['URL']['value'] );
		}
		// order feeds alphabetically
		usort( $feeds, array( $this, 'sort_links' ) );
		
		return $feeds;
	}
	
	function build_flexi_jsonp( $items, $sort = false ) {
		// takes a flexiform array and builds a jsonp string out of it with just the fields we want (title, url)
		$fields = '';
		$num_items = count( $items ) - 1;
		$svelte_items = array();
		
		foreach ( $items as $item => $data ) {
			$title = $data['Title']['value'];
			$url = $data['URL']['value'];
			$temp = array( 'title' => $title, 'url' => $url );
			$svelte_items[] = $temp;
		}
		if ( $sort ) {
			// order links alphabetically
			usort( $svelte_items, array( $this, 'sort_links' ) );
		}
		
		for ( $n = 0; $n <= $num_items; $n ++ ) {
			$fields .= '"ql' . $n . '" : { "url" : "' . $svelte_items[ $n ]['url'] . '",';
			$fields .= '"title" : "' . $svelte_items[ $n ]['title'] . '"}';
			if ( $n < $num_items ) {
				$fields .= ',';
			}
		}
		
		$json = '{ ' . $fields . ' }';
		
		return $json;
	}
	
	function sort_links( $a, $b ) {
		if ( $a['title'] == $b['title'] ) {
			return 0;
		}
		
		return ( $a['title'] < $b['title'] ) ? - 1 : 1;
	}
	
	function get_flexi_data( $search ) {
		// queries flexiform directory database for tags/search
		$args = array(
			'schemaID'     => 12076,                     // directory data
			'searchFields' => array( 26549 ),        // tag field
			'searchString' => $search,
		);
		$data = flexiform_get_data( $args );
		
		return $data;
	}
	
	function get_wp_user_id( $unix ) {
		// converts unix name to wordpress user id
		if ( ! $unix ) {
			return;
		}
		// convert username to ID
		$wp_user = get_user_by( 'login', $unix );
		
		return $wp_user->ID;
	}
	
	function load_custom() {
		// loads user's custom links/feeds saved in usermeta wordpress table
		$user = $this->get_wp_user_id( $_REQUEST['username'] );
		if ( ! $user ) {
			return;
		}
		$links = get_user_meta( $user, 'wms_quicklinks', true );
		$feeds = get_user_meta( $user, 'wms_quickfeeds', true );
		
		$arr = array( 'links' => $links, 'feeds' => $feeds );
		echo json_encode( $arr );
		die();
	}
	
	function save() {
		// saves user's links & feeds to a cookie, and if possible to usermeta wordpress table
		$user = $this->get_wp_user_id( $_COOKIE['quicklinks_user'] );
		if ( ! $user ) {
			return;
		}
		
		// add quicklinks to wpusermeta table
		$links = $_COOKIE['quicklinks'];
		$feeds = $_COOKIE['quickfeeds'];
		update_user_meta( $user, 'wms_quicklinks', $links );
		update_user_meta( $user, 'wms_quickfeeds', $feeds );
	}
	
} // end class