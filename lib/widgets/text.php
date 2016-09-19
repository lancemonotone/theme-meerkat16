<?php
#
# WILLIAMS TEXT WIDGET
#
# adds style options to wordpress text widget
#

class MeerkatTextWidget extends MeerkatWidget {
	
	// register widget with wordpress
	public function __construct() {
		$desc = 'Arbitrary text or HTML';
		parent::__construct( 'text', // Base ID
			MK_WIDGET_PREFIX . 'Text', // Name
			array( 'description' => $desc ) // Args
		);
		
		// editable widget options & associated data
		$bgcolors = array(
			'white-bkg' => 'white',
			'black-bkg' => 'black'
		);
		
		$this->fields = array(
			'title'   => array(
				'type'  => 'text',
				'label' => 'Title'
			),
			'text'    => array(
				'type'  => 'textarea',
				'label' => 'Content'
			),
			'filter'  => array(
				'type'  => 'checkbox',
				'label' => 'Automatically add paragraphs'
			),
			'no_pad'  => array(
				'type'  => 'checkbox',
				'label' => 'Omit content padding'
			),
			'border'  => array(
				'type'  => 'checkbox',
				'label' => 'Add border'
			),
			'bgcolor' => array(
				'type'    => 'select',
				'options' => $bgcolors,
				'label'   => 'Alternate background color'
			),
		);
	}
	
	// Displays the Widget
	public function widget( $args, $instance ) {
		$extra_classes = array();
		if ( $instance['no_pad'] ) {
			$extra_classes[] = 'no-pad';
		}
		if ( $instance['bgcolor'] ) {
			$extra_classes[] = $instance['bgcolor'];
		}
		if ( $instance['border'] ) {
			$extra_classes[] = 'outlined';
		}
		
		parent::insert_extra_classes( $extra_classes, $args['before_widget'] );
		parent::display_title( $args, $instance, false );
		echo $args['before_insides'];
		
		$content = do_shortcode( $instance['text'] );
		
		echo '<div class="textwidget">';
		if ( $instance['filter'] ) {
			// add paragraphs 
			echo wpautop( $content );
		} else {
			echo $content;
		}
		echo '</div>';
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
	
} // end class

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatTextWidget" );' ) );

?>
