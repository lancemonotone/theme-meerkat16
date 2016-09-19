<?php
/**
 * Displays supplemental nav for class notes & class portals
 */

class MeerkatClassYearNavWidget extends MeerkatWidget {

	// register widget with wordpress
	public function __construct() {
		$desc = 'Displays links to related class year content.';
		parent::__construct( 
			'meerkat_class_year_nav', // Base ID
			MK_WIDGET_PREFIX . 'Class Year Navigation', // Name
			array( 'description' => $desc ) // Args
		);
	}

    function set_widget_vars(){
		global $post, $meermag;
		
		// get min & max class year
		list ( $this->min_class_year, $this->max_class_year ) = Meerpeople_Shared::get_min_and_max_class_year();
		
		// context detection
		$this->editions = $meermag->get_editions();

		$this->vol_year = get_query_var( 'volume_year' );
		$this->vol_issue = get_query_var( 'volume_issue' );

		// what section we are in
		$uri = $_SERVER['REQUEST_URI'];
		$uri_bits = explode( '/', $uri );

		$this->nav_type = $this->widget_title = '';
		if ( $uri_bits[1] == 'class_year' && is_numeric( $uri_bits[2] )){
			// class portal view
			$this->nav_type = 'portal';
			$this->widget_title = 'More Class News';

		    // what class year are we in?
			$this->class_year = $uri_bits[2];
		}
		else if ( $post->post_type == 'class_note' && is_single() ){
			$this->nav_type = 'note';
			$this->widget_title = 'More Class Notes';

		    // what class year are we in?
			$class_years = get_the_terms( $post->ID, 'class_year' );
			$class_year = array_shift( $class_years );
			$this->class_year = $class_year->slug;
		}

		if ( $this->nav_type ){		    		
			// adjacent class year info
			$this->next_class_year = $this->class_year + 1;
			$this->prev_class_year = $this->class_year -1;

			// adjacent issue info
			$needle = $this->vol_year . ',' . $this->vol_issue;
			$this->prev_iss = $meermag->get_adjacent_value( $needle, $this->editions, true );
			$this->next_iss = $meermag->get_adjacent_value( $needle, $this->editions, false );
		}
    }


	function get_class_portal_link ( $class_year, $position ){
	    // creates a link to a class portal page of a given edition/class year combo
		$link_info = array ( 'disabled' => false, 
							 'href' =>  '/class_year/' . $class_year,
							 'title' => 'Class of ' . $class_year,
							 'position' => $position );
		if ( $class_year > $this->max_class_year || $class_year < $this->min_class_year ){
		    $link_info['disabled'] = true;
		    $link_info['href'] = '';
		}
		return $link_info;
	}

	function get_class_note_link ( $year, $issue, $class_year, $position, $text='' ){
	    // creates a link to a class note of a given edition/class year combo
		$href = '';
	 	$link_info = array ( 'disabled' => true,
							 'position' => $position,
							 'title' => $text ? $text :  'Class of ' . $class_year,
							 'href' => ''
		);
		if ( ! $year || ! $issue ) return $link_info;
	   
 		$args = array ( 'post_type' => 'class_note',
						'suppress_filters' => true,
						'tax_query' => array(),
						'posts_per_page' => 1 );
        $args['tax_query'][] = array ( 'taxonomy' => 'volume_year', 'field' => 'slug', 'terms' => $year );
        $args['tax_query'][] = array ( 'taxonomy' => 'volume_issue', 'field' => 'slug', 'terms' => $issue );
        $args['tax_query'][] = array ( 'taxonomy' => 'class_year', 'field' => 'slug', 'terms' => $class_year );
    	$notes = new WP_Query( $args );
		if ( $notes->posts[0] ){
		    $link_info['href'] = get_permalink( $notes->posts[0]->ID );
			$link_info['disabled'] = false;
		}

		return $link_info;
	}
	
	/*function build_nav_html ( $prev_link_info, $next_link_info ){
		$html = '<div class="widget_arrow_row">';
		$params = array ( $prev_link_info, $next_link_info );
		foreach ( $params as $link_info => $atts ){	
			$a_label = $a_arrow = $label = '';
			if ( $atts['href'] ){
				$a_label = $a_arrow = '<a href="' . $atts['href'] .'"';
				$a_arrow .= ' title="' . $atts['title'] . '"></a>';
				$a_label .= '>' . $atts['title'] . '</a>';
				$label = $a_label;
			}
			else {
				$label = $atts['title'];
			}

			$disabled = $atts['disabled'] ? ' disabled' : '';
			$arrow = '<div class="widget_arrow_sprite ' . $atts['position'] . $disabled . '">' . $a_arrow . '</div>';
			$label = '<div class="middle' . $disabled . '">' . $label . '</div>';
			if ( $atts['position'] == 'prev' ){
				$html .= $arrow . $label;
			}
			else {
				$html .= $label . $arrow;
			}
		}
		$html .= '</div>';		
		return $html;
	}*/

	function build_nav_html ( $prev_link_info, $next_link_info ){
		$html = '<div class="widget_arrow_row">';
		$params = array ( $prev_link_info, $next_link_info );
		foreach ( $params as $link_info => $atts ){
			$link = '<a href="' . $atts['href'] . '">' . $atts['title'] . '<span class="menu-arrow"></span></a>';
			$disabled = $atts['disabled'] ? ' disabled' : '';
			$html .= '<div class="direction ' . $atts['position'] . $disabled . '">' . $link . '</div>';
		}
		$html .= '</div>';
		return $html;
	}

	function get_class_portal_nav (){
		 // get adjacent class years from same issue
         $prev_portal_link_info = $this->get_class_portal_link( $this->prev_class_year, 'prev' );
         $next_portal_link_info = $this->get_class_portal_link( $this->next_class_year, 'next' );
		 return $this->build_nav_html ( $prev_portal_link_info, $next_portal_link_info );
	}

	function get_class_notes_nav( $hold_constant ){
		$html = '';
		$prev_link_info = array ();
		$next_link_info = array ();
		if ( $hold_constant == 'issue' ){
			// get adjacent class years from same issue
			$prev_link_info = $this->get_class_note_link( $this->vol_year, $this->vol_issue, $this->prev_class_year, 'prev' );
			$next_link_info = $this->get_class_note_link( $this->vol_year, $this->vol_issue, $this->next_class_year, 'next' );
		}
		else if ( $hold_constant == 'class_year' ){
			if ( $this->prev_iss ){
				list ( $prev_iss_year, $prev_iss_iss ) =  explode( ',', $this->prev_iss );
				$prev_link_info = $this->get_class_note_link( $prev_iss_year, $prev_iss_iss, $this->class_year, 'prev', 'Previous Issue' );
			}
			else {
				$prev_link_info = $this->get_class_note_link( '', '', $this->class_year, 'prev', 'Previous Issue' );
			}
			if ( $this->next_iss ){
				list ( $next_iss_year, $next_iss_iss ) =  explode( ',', $this->next_iss );
				$next_link_info = $this->get_class_note_link( $next_iss_year, $next_iss_iss, $this->class_year, 'next', 'Next Issue' );
			}
			else {
				$next_link_info = $this->get_class_note_link( '', '', $this->class_year, 'next', 'Next Issue' );
			}
		}

		$html = $this->build_nav_html( $prev_link_info, $next_link_info );
		return $html;
	}

	function widget( $args, $instance ){
		global $post;
		extract( $args );

		$this->set_widget_vars();
		if ( ! $this->nav_type ) return;

		$content = '';
		if ( $this->nav_type == 'portal' ){
			$content = $this->get_class_portal_nav();
		}
		else if ( $this->nav_type == 'note' ){
			// adjacent class years, same issue
			$content = $this->get_class_notes_nav( 'issue' );
			// adjacent issues, same class year
			$content .= $this->get_class_notes_nav( 'class_year' );
		}

		echo $before_widget;
		echo '<div class="widget_arrow_nav">';
		echo $before_title . $this->widget_title .  $after_title;
		echo $content;
		echo '</div>';
		echo $after_widget;

	} 
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatClassYearNavWidget" );' ) );

?>
