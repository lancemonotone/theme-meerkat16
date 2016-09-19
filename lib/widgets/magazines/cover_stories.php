<?php
#
# Cover story widget for people/alumni news mag
#

class MeerkatCoverStoryWidget extends MeerkatWidget {

    // register widget with wordpress
    public function __construct() {
        $desc = 'Display images and blurbs for Alumni News current edition cover stories';
        parent::__construct('meerkat_cover_story', // Base ID
			    MK_WIDGET_PREFIX . 'Cover Stories', // Name
			    array( 'description' => $desc ) // Args
			    );
	
		$this->fields = array( 'title' => array ( 'default' => '', 
												  'type' => 'text', 
												  'label' => 'Title', 
												  'classes' => 'widefat',
												  'wrapper' => 'p' ),											  
		);
    }
	
    // Displays the Widget
    public function widget($args, $instance){
		extract($args);

		global $meerkat_mobile, $meermag;
		if ( $meerkat_mobile ) return;

		echo $before_widget;
		echo $before_title . '<a href="/category/cover-stories/">' . $instance['title'] . '</a>' . $after_title;

		// get cover stories for current edition
        $issue = $meermag->get_context_issue();
        $year = $meermag->get_context_year();

		$args = array ( 'post_type' => 'cover_story',
						'tax_query' => array(
							array(
								'taxonomy' => 'volume_issue',
								'field' => 'slug',
								'terms' => $issue['slug']
								),
							array(
								'taxonomy' => 'volume_year',
								'field' => 'slug',
								'terms' => $year
								),
							),
							'suppress_filters' => true
			           );
		$cover_stories = new WP_Query( $args );
		$thumb_attr = array ( 'class' => 'cover-story-thumb' );

		while ( $cover_stories->have_posts() ){
			$cpost = $cover_stories->the_post();
		    echo '<div class="cover-story-post">';
			echo '<a href="' . get_permalink() . '">';
	        the_post_thumbnail( 'thumbnail', $thumb_attr );
			echo '</a>';
		    the_excerpt();
			// deliberately avoiding get_the_title here, don't want the filters that adds extra info
			$title = $cover_stories->post->post_title;
		    echo '<div class="cover-story-title"><a href="' . get_permalink() . '">' . $title . '</a></div>';
		    echo '</div>'; 
		}
		echo $after_widget;
    }
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatCoverStoryWidget" );' ) );

?>
