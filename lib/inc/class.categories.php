<?php

/*

Allows users to configure what bits of information get displayed in the loop when viewing categories of posts or single posts. 
Configuration options are saved on a per-category basis, and are edited via the edit category menus on the dashboard.

This script also provides sticky post support for category views.

@todo: support other taxonomies?

*/

// config options are in a separate file shared by other scripts
include_once( THEME_INC_PATH . 'class.categories_options.php' );

class Meerkat16_Categories {
	private static $instance;
	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Meerkat16_Categories The *Singleton* instance.
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		
		return static::$instance;
	}
	
	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}
	
	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}
	
	function __construct() {
		$this->options = Meerkat16_Categories_Options::instance()->get_options();
		
		$this->current_cat_orderby = '';
		$this->current_cat_order_dir = '';
		
		// HOOKS
		// admin edit category/term/tag
		add_filter( 'category_edit_form', array( &$this, 'edit_term_form' ) );
		
		//add_filter( 'edit_tag_form', array( &$this, 'edit_term_form' ));
		add_action( 'edit_term', array( &$this, 'edit_term_save' ) );
		
		// enable sticky posts for all archive pages (category, tag, etc)
		add_action( 'loop_start', array( &$this, 'enable_stickies' ) );
		
		// load current category options
		add_action( 'loop_start', array( &$this, 'get_current_cat_options' ) );
		
		// enable next/prev links at bottom of single post to use cat config order options
		add_filter( 'get_previous_post_where', array( &$this, 'adjacent_where_prev' ) );
		add_filter( 'get_next_post_where', array( &$this, 'adjacent_where_next' ) );
		add_filter( 'get_previous_post_sort', array( &$this, 'adjacent_sort_prev' ) );
		add_filter( 'get_next_post_sort', array( &$this, 'adjacent_sort_next' ) );
	}
	
	/**
	 * Loads cat config options for the current category when in the context 
	 * of a single post. Takes first valid category match.
	 */
	function get_current_cat_options() {
		global $post;
		if ( ! is_single() ) {
			return;
		}
		$has_match = false;
		$cats = get_the_category( $post->ID );
		foreach ( $cats as $key => $val ) {
			if ( $tmp = $this->get_config_options( $val->term_id ) ) {
				$cat_opt = $tmp;
				$this->current_cat_orderby = $cat_opt['multi_orderby'];
				$this->current_cat_order_dir = $cat_opt['multi_order_dir'];
				$has_match = true;
				break;
			}
		}
		
		if ( ! $has_match ) {
			// no configuration available, use default settings
			$this->current_cat_orderby = $this->options['multi_orderby']['default'];
			$this->current_cat_order_dir = $this->options['multi_order_dir']['default'];
		}
	}
	
	//---- HOOKS to modify next/prev post links (on a single post) ----//
	
	function adjacent_where_prev( $where ) {
		return $this->adjacent_where( $where, 'prev' );
	}
	
	function adjacent_where_next( $where ) {
		return $this->adjacent_where( $where, 'next' );
	}
	
	function adjacent_where( $where, $next_or_prev ) {
		global $post;
		
		$cmp = $this->get_cmp( $next_or_prev, 'symbol' );
		$new_where = 'WHERE ';
		
		switch ( $this->current_cat_orderby ) {
			case 'date' :
				$new_where .= "p.post_date " . $cmp . " '" . $post->post_date . "' AND";
				break;
			case 'title' :
				$new_where .= "p.post_title " . $cmp . " '" . $post->post_title . "' AND";
				break;
			case 'ID' :
				$new_where .= "p.ID " . $cmp . " '" . $post->ID . "' AND";
				break;
			default :
				return $where;
		}
		
		$new_where .= " p.post_type = 'post' AND p.post_status = 'publish' AND tt.taxonomy = 'category'";
		
		return $new_where;
		
	}
	
	function adjacent_sort_prev( $sort ) {
		return $this->adjacent_sort( $sort, 'prev' );
	}
	
	function adjacent_sort_next( $sort ) {
		return $this->adjacent_sort( $sort, 'next' );
	}
	
	function adjacent_sort( $sort, $next_or_prev ) {
		$cmp = $this->get_cmp( $next_or_prev, 'word' );
		$new_sort = ' ORDER BY ';
		
		switch ( $this->current_cat_orderby ) {
			case 'date' :
				$new_sort .= "p.post_date $cmp";
				break;
			case 'title' :
				$new_sort .= "p.post_title $cmp";
				break;
			case 'ID' :
				$new_sort .= "p.ID $cmp";
				break;
			default :
				return $sort;
		}
		
		$new_sort .= " LIMIT 1";
		
		return $new_sort;
	}
	
	function get_cmp( $next_or_prev, $symbol_or_word ) {
		$cmp = '';
		if ( $symbol_or_word == 'symbol' ) {
			$cmp = '>'; // covers ASC next, DESC prev
			if ( $this->current_cat_order_dir == 'ASC' && $next_or_prev == 'prev' ) {
				$cmp = '<';
			}
			if ( $this->current_cat_order_dir == 'DESC' && $next_or_prev == 'next' ) {
				$cmp = '<';
			}
		} else {
			$cmp = 'ASC';
			if ( $this->current_cat_order_dir == 'ASC' && $next_or_prev == 'prev' ) {
				$cmp = 'DESC';
			}
			if ( $this->current_cat_order_dir == 'DESC' && $next_or_prev == 'next' ) {
				$cmp = 'DESC';
			}
		}
		
		return $cmp;
	}
	
	//---- end hooks for next/prev links ----//
	
	//---- SHOW FORM ----//
	
	function edit_term_form( $cat_data ) {
		// creates the form on the edit category/tag page that allows the admin to select options for that term
		// $cat_data is automatically passed in by the category_edit_form hook		
		?>
		
		<h2>Category Configuration Options</h2>
		<div class="cat-config-options">
			<p>
				Control how posts in this category display on your site by selecting which
				pieces of information are shown, in which context.
			</p>
		
		<?php
		$cat = $cat_data->term_id;
		$option_key = 'wms_category_config_' . $cat;
		
		$saved = get_option( $option_key );
		$single_opts = array();
		$multi_opts = array();
		
		foreach ( $this->options as $option => $vals ) {
			// split out into multi & single options
			if ( $vals['view'] == 'single' ) {
				$single_opts[ $option ] = $vals;
			} else {
				$multi_opts[ $option ] = $vals;
			}
		}
		
		// build single view
		$class = 'cat-config-single';
		$title = 'Single Post Display';
		$blurb = 'When viewing an individual post';
		$this->build_option_block( $class, $title, $blurb, $single_opts, $saved );
		
		// build multi view
		$class = 'cat-config-multi';
		$title = 'Multiple Post Display';
		$blurb = 'When viewing a group of posts (e.g. blog, category, search results)';
		$this->build_option_block( $class, $title, $blurb, $multi_opts, $saved );
		
		echo '</div><!-- end cat-config-options -->';
		echo '<div style="clear:both;"></div>';
	}
	
	function build_option_block( $class, $title, $blurb, $opts, $saved ) {
		echo '<div class="' . $class . '">';
		echo '<h3>' . $title . '</h3>';
		echo '<div class="cat-config-blurb">' . $blurb . '</div>';
		foreach ( $opts as $option => $vals ) {
			echo '<div class="cat-config-item">';
			// check for saved, if nothing, use default
			if ( $saved ) {
				$prepop = $saved[ $option ];
				// allow a false saved value too override a true default
				if ( ! $prepop && $vals['default'] ) {
					unset( $vals['default'] );
				}
			} else {
				$prepop = $this->options[ $option ]['default'];
			}
			echo buildField( $option, $vals, $prepop );
			echo '</div>';
		}
		echo '</div>';
	}
	
	//---- SAVE FORM ----//
	// saves the options when user submits edit category/tag form
	function edit_term_save() {
		$cat = $_POST['tag_ID'];
		
		$option_key = 'wms_category_config_' . $cat;
		
		$cat_options = array();
		foreach ( $this->options as $option => $vals ) {
			//  save stuff
			$cat_options[ $option ] = $_POST[ $option ];
		}
		update_option( $option_key, $cat_options );
	}
	
	// grab saved category settings from the options table
	function get_config_options( $term_id ) {
		$option_key = 'wms_category_config_' . $term_id;
		
		return get_option( $option_key );
	}
	
	//---- STICKY POST ----//
	// sticky support- move sticky posts to the top of category/tag pages
	function enable_stickies( $wp_query ) {
		if ( ! is_archive() ) {
			return;
		}
		
		// Put sticky posts at the top of the posts array
		$sticky_posts = get_option( 'sticky_posts' );
		$page = $wp_query->query_vars['paged'];
		if ( $page <= 1 && is_array( $sticky_posts ) && ! empty( $sticky_posts ) ) {
			$num_posts = count( $wp_query->posts );
			$sticky_offset = 0;
			// Loop over posts and relocate stickies to the front.
			for ( $i = 0; $i < $num_posts; $i ++ ) {
				if ( in_array( $wp_query->posts[ $i ]->ID, $sticky_posts ) ) {
					$sticky_post = $wp_query->posts[ $i ];
					// Remove sticky from current position
					array_splice( $wp_query->posts, $i, 1 );
					// Move to front, after other stickies
					array_splice( $wp_query->posts, $sticky_offset, 0, array( $sticky_post ) );
					// Increment the sticky offset.  The next sticky will be placed at this offset.
					$sticky_offset ++;
					// Remove post from sticky posts array
					$offset = array_search( $sticky_post->ID, $sticky_posts );
					unset( $sticky_posts[ $offset ] );
				}
			}
		}
		return $wp_query;
	}
} // end class

Meerkat16_Categories::instance();