<?php
#
# Class year widget for people/alumni news mag
#

class MeerkatClassYearPickerWidget extends MeerkatWidget {

    // register widget with wordpress
    public function __construct() {
        $desc = 'Select your class year to get to your class portal';
        parent::__construct('meerkat_class_year', // Base ID
			    MK_WIDGET_PREFIX . 'Class Year Picker', // Name
			    array( 'description' => $desc ) // Args
			    );
	
		$this->fields = array( 'title' => array ( 'default' => '', 
					  							  'type' => 'text', 
												  'label' => 'Title', 
											  	  'classes' => 'widefat',
											  	  'wrapper' => 'p' ),											  
								'select_prompt' => array ( 'default' => '',
                                                  'type' => 'text',
                                                  'label' => 'Select menu prompt text',
                                                  'classes' => 'widefat',
                                                  'wrapper' => 'p' ),

		);
    }
	
    // Displays the Widget
    public function widget($args, $instance){
		extract($args);
		global $meerkat_mobile;

		echo $before_widget;
		$args = array( 'orderby' => 'name' );
		$terms = get_terms( 'class_year', $args );
		$terms = array_reverse($terms, true);
  		$bounds = Meerpeople_Shared::get_min_and_max_class_year();

		if ( $meerkat_mobile ){
		   echo $before_title . $instance['title'] . $after_title;
		}

		echo '<form id="my-class-year">';
		echo '<select name="class_year">';
		if ( $instance['select_prompt'] ){
		   echo '<option value="">' . $instance['select_prompt'] . '</option>';
		}
		else {
		   echo '<option value="">-- select --</option>';
		}
		foreach ($terms as $term => $data){
		    if ( $data->name <= $bounds[1] ){
		       echo '<option value="' . $data->name . '">' . $data->name . '</option>';
			}
		}
		echo '</select></form>';

		if ( ! $meerkat_mobile ){
		   echo $before_title . $instance['title'] . $after_title;
	    }
		echo $after_widget;
    }

}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatClassYearPickerWidget" );' ) );

?>
