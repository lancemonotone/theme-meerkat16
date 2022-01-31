<?php
#
# WILLIAMS GALLERY WIDGET
#

class MeerkatGalleryWidget extends MeerkatWidget {
	
	// register widget with wordpress
	public function __construct() {
		$desc = 'Display thumbnails from a gallery';
		parent::__construct( 'meerkat_gallery', // Base ID
			MK_WIDGET_PREFIX . 'Gallery', // Name
			array( 'description' => $desc ) // Args
		);
		
		// editable widget options & associated data
		
		// list of pages
		$args = array( 'echo' => false );
		$pages = get_pages( $args );
		
		$page_options = array();
		foreach ( $pages as $page_obj => $data ) {
			$page_options[ $data->ID ] = $data->post_title;
		}
		$number_options = array( 2, 4, 6, 8, 10, 12, 16 );
		
		$this->fields = array(
			'title'          => array(
				'type'  => 'text',
				'label' => 'Title'
			),
			'number'         => array(
				'default' => 4,
				'type'    => 'select',
				'options' => $number_options,
				'label'   => 'Number of thumbnails'
			),
			'page'           => array(
				'type'    => 'select',
				'options' => $page_options,
				'label'   => 'Use gallery from page:',
				'hint'    => 'Choose a page above OR list attachment IDs below, separated with commas.'
			),
			'attachment_ids' => array(
				'type'  => 'text',
				'label' => 'Attachment IDs'
			),
			'link_to_parent' => array(
				'type'  => 'checkbox',
				'label' => 'Link image to parent post'
			),
		);
		
	}
	
	// Displays the Widget
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		parent::display_title( $args, $instance );
		echo $args['before_insides'];
		
		echo '<div class="meerkat-gallery" limit="' . $instance['number'] . '">';
		$link = $include = $page = '';
		if ( $instance['link_to_parent'] ) {
			$link = ' link="parent"';
		}
		if ( $instance['page'] ) {
			$page = ' id="' . $instance['page'] . '"';
		} else if ( $instance['attachment_ids'] ) {
			$include = ' include="' . $instance['attachment_ids'] . '"';
		}
		$shortcode = '[gallery' . $page . $include . ' size="thumbnail" columns="2"' . $link . ' tooltip="yes"]';
		echo do_shortcode( $shortcode );
		echo '</div>';
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
}

// register widget
add_action( 'widgets_init', function(){ register_widget( "MeerkatGalleryWidget" ); });