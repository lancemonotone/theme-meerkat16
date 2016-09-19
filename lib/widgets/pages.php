<?php
/*
 * rewrite pages widget so we can control the html & and add options
*/

//---- PAGES ----//

class MeerkatPagesWidget extends MeerkatWidget {
	
	public function __construct() {
		$desc = "Your site's WordPress Pages";
		parent::__construct( 'pages', // Base ID
			MK_WIDGET_PREFIX . 'Pages', // Name
			array( 'description' => $desc ) // Args
		);
		
		$sort_options = array(
			'post_title' => 'Page title',
			'menu_order' => 'Page order',
			'ID'         => 'Page ID',
			'post_date'  => 'Date'
		);
		
		
		$this->fields = array(
			'title'      => array(
				'type'  => 'text',
				'label' => 'Title'
			),
			'sortby'     => array(
				'type'    => 'select',
				'label'   => 'Sort By',
				'options' => $sort_options
			),
			'title_link' => array(
				'type'  => 'text',
				'label' => 'Title links to'
			),
			'exclude'    => array(
				'type'  => 'text',
				'label' => 'Exclude',
				'hint'  => 'Page IDs, separated by commas'
			),
			'subpages'   => array(
				'type'  => 'checkbox',
				'label' => 'Make this a subpage widget',
				'hint'  => "Create a menu of only this page's child pages."
			),
		);
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];
		
		if ( $sortby == 'menu_order' ) {
			$sortby = 'menu_order, post_title';
		}
		
		$q_args = array(
			'title_li'    => '',
			'echo'        => false,
			'exclude'     => $exclude,
			'sort_column' => $sortby,
			'walker'      => new Meerkat_Walker_Page
		);
		
		if ( $instance['subpages'] ) {
			global $post;
			if ( $post->post_type == 'page' ) {
				// check to see if has children or is a child
				$ances = get_ancestors( $post->ID, 'page' );
				
				if ( count( $ances ) > 0 ) {
					// last array item is oldest ancestor
					$q_args['child_of'] = end( $ances );
				} else {
					$subargs = array( 'post_type' => 'page', 'child_of' => $post->ID, 'post_status' => 'publish' );
					$children = get_pages( $subargs );
					if ( $children ) {
						$q_args['child_of'] = $post->ID;
					} else {
						return;
					}
				}
			} else {
				// not a page, don't do a subpages widget
				return;
			}
		}
		
		$menu = wp_list_pages( $q_args );
		
		echo $args['before_widget'];
		parent::display_title( $args, $instance, true );
		echo $args['before_insides'];
		
		echo '<ul class="menu">' . $menu . '</ul>';
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
}

//---- WALKER : PAGES ----//

class Meerkat_Walker_Page extends Walker_Page {
	// markup for sub-items
	function start_lvl( &$output, $depth ) {
	}
	
	function end_lvl( &$output, $depth ) {
	}
	
	// markup for top-level items
	function start_el( &$output, $item, $depth ) {
		$classes = $this->classes( $item, $depth );
		$link = $this->link( $item );
		$item = '<li ' . $classes . ' data-depth="' . $depth . '">';
		$item .= '<span class="menu-arrow"></span>' . $link;
		$output .= $item;
	}
	
	function end_el( &$output, $element, $depth ) {
		$output .= '</li>';
	}
	
	//---- HTML BUILDERS  ----//
	
	function classes( $item, $depth ) {
		// page only classes
		global $wp_query;
		$classes = array( 'menu-item' );
		if ( $depth > 0 ) {
			$classes[] = 'depth-' . $depth;
			$classes[] = 'sub-item';
		} else {
			$classes[] = 'top-item';
		}
		
		// is current page?
		if ( $item->ID == $wp_query->queried_object->ID ) {
			$classes[] = 'current-menu-item';
		}
		
		// ancestry... (this is done in WP for menu widgets but not pages)
		$ancestors = get_ancestors( $wp_query->queried_object->ID, 'page' );
		foreach ( $ancestors as $ances_id ) {
			if ( $item->ID == $ances_id ) {
				$classes[] = 'current-menu-ancestor';
			}
		}
		
		// parent is always first item in ancestor array
		$parent_id = $ancestors[0];
		if ( $item->ID == $parent_id ) {
			$classes[] = 'current-menu-parent';
		}
		
		return 'class="' . implode( ' ', $classes ) . '"';
	}
	
	/**
	 * @param $item
	 *
	 * @return string
	 */
	function link( $item ) {
		$post_title = $item->post_title;
		$href = get_permalink( $item->ID );
		$html = '<a title="' . $post_title . '" href="' . $href . '">$post_title</a>';
		
		return $html;
	}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatPagesWidget" );' ) );