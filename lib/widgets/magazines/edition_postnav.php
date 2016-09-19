<?php
/**
 * Displays TOC for Williams Magazine
 */

class MeerkatPostnavWidget extends MeerkatWidget {

	// register widget with wordpress
	public function __construct() {
		$desc = 'Displays links to navigate between next and previous posts within the same section and issue.';
		parent::__construct( 
			'meerkat_edition_postnav', // Base ID
			MK_WIDGET_PREFIX . 'Edition Postnav', // Name
			array( 'description' => $desc ) // Args
		);
	}

	/**
	 * Display adjacent post link.
	 *
	 * @param string $format
	 * @param string $link
	 * @param bool $previous
     * @return string
	 */
	function same_edition_and_section_adjacent_post_link ( $format='&laquo; %link', $link='%title', $previous = true, $echo = true ) {
		global $wp_query, $wpdb, $post, $meermag;

		$year = get_term_by( 'slug', $wp_query->query['volume_year'], 'volume_year' );
		$issue = get_term_by( 'slug', $wp_query->query['volume_issue'], 'volume_issue' );
		$section_id = wp_get_post_categories( $post->ID );
		$section = get_term_by( 'id', $section_id[0], 'category');
		
		$year = $year->term_taxonomy_id;
		$issue = $issue->term_taxonomy_id;
		$section = $section->term_taxonomy_id;
				
		$query = "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
				JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				JOIN {$wpdb->term_relationships} tr1 ON p.ID = tr1.object_id
				JOIN {$wpdb->term_relationships} tr2 ON p.ID = tr2.object_id
				AND tr.term_taxonomy_id = $year
				AND tr1.term_taxonomy_id = $issue
				AND tr2.term_taxonomy_id = $section
				AND p.post_status = 'publish' 
				ORDER BY p.post_date DESC;";

		$results = $wpdb->get_col( $query );
		
		if ( ! $id = $meermag->get_adjacent_value( $post->ID, $results, $previous )){
			return null;
		}

		$rel = $previous ? 'prev' : 'next';

		$title = get_the_title( $id );
		$string = '<a href="' . get_permalink( $id ) . '" rel="' . $rel . '">';
		$link = str_replace( '%title', $title, $link );
		$link = $string . $link . '</a>';

		$format = str_replace( '%link', $link, $format );

		$adjacent = $previous ? 'previous' : 'next';
		$the_link = apply_filters( "{$adjacent}_post_link", $format, $link );
		
		if( $echo ) echo $the_link;
		else return $the_link;
	}

	/**
     * Displays the Widget
     *
     * @param array $args
     * @param array $instance
     */
	public function widget( $args, $instance ){
		global $post_type;
		if( ! is_single() ) return; // we only want post navigation on post pages.
		if ($post_type == 'feature') return ; // amy requested postnav be removed on features 

		extract( $args );
		global $post, $wp_query;
		$year = get_term_by( 'slug', $wp_query->query['volume_year'], 'volume_year');
		$issue = get_term_by( 'slug', $wp_query->query['volume_issue'], 'volume_issue');
		$section_id = wp_get_post_categories( $post->ID );
		$section = get_term_by( 'id', $section_id[0], 'category' );
		
		$year = $year->name;
		$issue = $issue->name;
		$section = $section->name;
		
		$prev_link = self::same_edition_and_section_adjacent_post_link ( '%link', '%title', true, false );
		$next_link = self::same_edition_and_section_adjacent_post_link ( '%link', '%title', false, false );
		
		if( $prev_link || $next_link ){
			echo $before_widget;
			$title = 'Also in ' . $section;			
			echo $before_title . $title . $after_title;		
		    ?>

			<ul>
			<?php if( $prev_link ){?><li class="postnav nav-previous"><?php echo $prev_link; ?></li><?php } ?>
			<?php if( $next_link ){?><li class="postnav nav-next"><?php echo $next_link; ?></li><?php }?>
			</ul>
			<?php  

			echo $after_widget;
		} // end if

	} // end function
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatPostnavWidget" );' ) );

?>
