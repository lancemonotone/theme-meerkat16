<?php
#
# 62 CENTER UPCOMING EVENTS
#

/* todo: switch blog so it works on thea/dance, choose categories to pull from */

class MeerkatCTDEvents extends MeerkatWidget {
	var $ctd_blog_id, $music_blog_id, $type_parent_cat;
	
	// register widget with wordpress
	public function __construct() {
		
		$this->ctd_blog_id = 186;
		$this->music_blog_id = 196;
		$this->type_parent_cat = 71368;  // parent category ID for specific event types on 62ctd site
		
		$desc = 'List Upcoming Events';
		parent::__construct( 'ctd_events', // Base ID
			MK_WIDGET_PREFIX . 'CTD Upcoming Events', // Name
			array( 'description' => $desc ) // Args
		);
		
		// get list of event type categories from CTD site
		if ( $this->ctd_blog_id != Meerkat16::instance()->blog_id ) {
			switch_to_blog( $this->ctd_blog_id );
		}
		
		$event_cats = get_terms( 'category', array( 'hide_empty' => 0 ) );
		$event_cat_options = array( '0' => 'All' );
		foreach ( $event_cats as $e_cat ) {
			if ( $e_cat->parent == $this->type_parent_cat ) {
				$event_cat_options[ $e_cat->term_id ] = $e_cat->name;
			}
		}
		
		if ( $this->ctd_blog_id != Meerkat16::instance()->blog_id ) {
			restore_current_blog();
		}
		
		$this->fields = array(
			'title'      => array(
				'type'  => 'text',
				'label' => 'Title'
			),
			'num_events' => array(
				'type'  => 'text',
				'label' => 'Number of events to display'
			),
			'event_type' => array(
				'type'    => 'select',
				'options' => $event_cat_options,
				'label'   => 'Event Type'
			),
			'show_cat'   => array(
				'type'  => 'checkbox',
				'label' => 'Show event category'
			)
		);
	}
	
	/**
	 * Displays the Widget
	 * 
	 * @todo Make $meerkat_ctd singleton
	 * 
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $meerkat_ctd;
		
		echo $args['before_widget'];
		parent::display_title( $args, $instance );
		echo $args['before_insides'];
		
		echo $meerkat_ctd->get_upcoming_events( $instance['num_events'], $instance['event_type'], $instance['show_cat'] );
		
		// default case - 62 center
		$domain = '62center';
		$link_text = 'Full Calendar &raquo;';
		
		// music version
		if ( $this->music_blog_id == Meerkat16::instance()->blog_id ) {
			$domain = 'music';
		}
		
		$href = 'http://' . $domain . Meerkat16::instance()->server . '.williams.edu/calendar/';
		
		echo '<div class="widget-more"><a href="' . $href . '">' . $link_text . '</a></div>';
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
	
} // end class

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatCTDEvents" );' ) );

?>
