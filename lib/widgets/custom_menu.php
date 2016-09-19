<?php
/*
 * rewrite custom menu widget so we can add options & control the html
*/

//---- CUSTOM MENU ----//

class MeerkatMenuWidget extends MeerkatWidget {

	public function __construct() {
 		$desc = 'Use this widget to add one of your custom menus as a widget.';
        parent::__construct( 'nav_menu', // Base ID
                             MK_WIDGET_PREFIX . 'Custom Menu', // Name
                             array( 'description' => $desc ) // Args
                            );

		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		$menu_options = array();
	   	foreach ( $menus as $menu ) {
		  $menu_options[$menu->term_id] = $menu->name;
		}
		
 		if ( $menus ) {
            $this->fields = array( 'title'      => array( 'type'    => 'text',
                                                          'label'   => 'Title' ),
                                   'nav_menu'   => array( 'type'    => 'select',
                                                          'label'   => 'Custom Menu',
                                                          'options' => $menu_options ),
                                   'title_link' => array( 'type'    => 'text',
                                                          'label'   => 'Title links to',
                                                          'hint'    => 'Enter a full url starting with http://, or a relative url starting with /' ),
								   'callout'    => array( 'type'    => 'checkbox', 
														  'label'   => 'Callout style' ),
								   'pic_url'    => array( 'type'    => 'text', 
														  'label'   => 'Image URL',
													      'hint'    => 'Enter a full url starting with http://, or a relative url starting with /' ),
                );
		}
		else {
			$this->fields =  array( 'error' => array ( 'type' => 'message',
													   'label' => 'No custom menus!&nbsp; <a href="/wp-admin/nav-menus.php">Create one</a>' )
				);
		}					  	  
    }

    public function widget( $args, $instance ) {
		$q_args = array( 'echo'      => false,
						 'container' => false,
						 'menu'		 => $instance['nav_menu'],
					     'depth'     => 5, 
					     'walker'    => new Meerkat_Walker_Menu
			           );

		$menu = wp_nav_menu( $q_args );

		if ( $instance['callout'] ){
			$extra_classes = array( 'callout-menu' );
	    }

	    parent::insert_extra_classes($extra_classes, $args['before_widget']);
	    parent::display_title( $args, $instance, true );
	    echo $args['before_insides'];

		if ( $instance['pic_url'] ){
		   echo '<div class="custom-menu-pic"><img src="' . $instance['pic_url'] . '"></div>';
		}

		echo $menu;
	    
		echo $args['after_insides'];
		echo $args['after_widget'];
    }
}

//---- WALKER : MENU ----//

class Meerkat_Walker_Menu extends Walker_Nav_Menu {
	// markup for sub-items
	function start_lvl( &$output, $depth ) {} 
    function end_lvl( &$output, $depth){}

	// markup for top-level items
    function start_el( &$output, $item, $depth ) {		
		$classes = $this->classes( $item, $depth );
		$link = $this->link( $item, $depth );
  		$item  = '<li ' . $classes . ' data-depth="' . $depth . '">' . $link ;
		$output .= $item;
    }
	function end_el( &$output, $element, $depth ){
		$output .= '</li>';
	}


	//---- HTML BUILDERS  ----//

	function classes ( $item, $depth ){
		global $post;
		$classes = $item->classes;

		// highlight category links that are the parent category of current post
		if ( $item->object == 'category' ){
		    if ( has_term( $item->object_id, 'category', $post->ID )){
		        $classes[] = 'current-menu-item';		
		    }
		}

		// add depth marker for submenus		
		if ( $depth > 0 ){
		    $classes[] = 'depth-' . $depth;
			$classes[] = 'sub-item';
    	}
		else {
			 $classes[] = 'top-item';
		}
		return 'class="' . implode( ' ', $classes ) . '"';
	}

	function link( $item ){
		$label = $href = $title = '';
		$label = $item->post_title; 
		if ( ! $label ){
	       $label =  $item->title; 
    	}	
		$href = $item->url; 
		if ( $item->attr_title ){
		   $title = 'title="' . $item->attr_title . '"';
		}
		if ( $item->target ){
		   $target = 'target="' . $item->target . '"';
		}
		$html = "<a $title $target href=\"$href\">$label</a>";
		return $html;
	}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatMenuWidget" );' ) );