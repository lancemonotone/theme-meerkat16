<?php
/**
 * Displays edition TOC and Gallery options for Williams Magazine.
 * Contains two widgets: MeerkatEditionTOCWidget & MeerkatEditionGalleryWidget.
 *
 * @uses Custom Post Type: toc_desc - container post for each editions's TOC and gallery widget.
 * @uses ACF Custom Field: toc_sections_rpt - choose section taxonomies for display in the TOC.
 * @uses ACF Custom Field: edition_gallery_posts - choose posts (with thumbs) for display in the Edition Gallery Widget.
 *
 */
register_post_type( 'toc_desc', array(
	'label'               => 'Editon Options',
	'description'         => 'Complete these for each Editon.  Name them YYYY/[Spring|Summer|Fall|Winter].',
	'public'              => true,
	'show_ui'             => true,
	'show_in_menu'        => true,
	'menu_position'       => 3,
	'capability_type'     => 'post',
	'hierarchical'        => false,
	'rewrite'             => array( 'slug' => '' ),
	'query_var'           => true,
	'exclude_from_search' => true,
	'supports'            => array( 'title', ),
	'labels'              => array(
		'name'               => 'Editon Options',
		'singular_name'      => 'Editon Options',
		'menu_name'          => 'Editon Options',
		'add_new'            => 'Add Editon',
		'add_new_item'       => 'Add New Editon',
		'edit'               => 'Edit',
		'edit_item'          => 'Edit Editon Options',
		'new_item'           => 'New Editon Options',
		'view'               => 'View Editon Options',
		'view_item'          => 'View Editon Options',
		'search_items'       => 'Search Editon Options',
		'not_found'          => 'No Editon Options Found',
		'not_found_in_trash' => 'No Editon Options Found in Trash',
		'parent'             => 'Parent Editon Options',
	),
) );

class MeerkatEditionTOCWidget extends MeerkatWidget {

	// register widget with wordpress
	public function __construct() {
		$desc = 'Shows TOC of currently viewed edition';
		parent::__construct( 'meerkat_edition_toc', // Base ID
			MK_WIDGET_PREFIX . 'Edition TOC', // Name
			array( 'description' => $desc ) // Args
		);

	}

	/**
	 * Displays the TOC Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		global $wp_query, $meermag;

		// Get edition names by year.  This is a dumb array.  We don't yet know if these editions actually exist.
		$issues = $meermag->get_toc_posts();

		// Get context issue.  This is the issue currently active on the front.
		$context_issue = $meermag->get_context_issue();
		$context_year = $meermag->get_context_year();
		$context_edition = $context_year . ',' . $context_issue['slug'];

		$prev_edition = $meermag->get_adjacent_value( $context_edition, $issues );
		$next_edition = $meermag->get_adjacent_value( $context_edition, $issues, false );

		$prev_edition = $prev_edition ? explode( ',', $prev_edition ) : null; // 0 => year, 1 => issue
		$next_edition = $next_edition ? explode( ',', $next_edition ) : null; // 0 => year, 1 => issue

		$current_term = $wp_query->query['category_name'];

		$tooltip_class = ! wp_is_mobile() ? 'apply-tooltip' : '';

		echo $before_widget;
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		echo $before_title . $title . $after_title;
		?>

		<?php if ( $features = $meermag->get_magazine_features() ) { ?>
			<div class="toc-features">
				<h2 class="features-header cf">
					<span class="direction prev">
						<a title="<?php echo ucfirst( $prev_edition[1] ) . ' ' . $prev_edition[0] . ' - ' . __( 'Features' ) ?>"
							class="<?php echo $tooltip_class ?> tooltip-bottom <?php $meermag->maybe_disabled( $prev_edition, 'features' ) ?>"
							href="<?php echo home_url() . '/' . $prev_edition[0] . '/' . $prev_edition[1] ?>/features">
							<span class="menu-arrow"></span>
						</a>
					</span>
					<span>
						<a class="issue-link<?php echo $current_term == 'features' ? ' current_term' : '' ?>"
							href="<?php echo home_url() . '/' . $context_year . '/' . $context_issue['slug'] ?>/features">
							<?php _e( 'Features' ) ?>
						</a>
					</span>
					<span class="direction next">
						<a title="<?php echo ucfirst( $next_edition[1] ) . ' ' . $next_edition[0] . ' - ' . __( 'Features' ) ?>"
							class="<?php echo $tooltip_class ?> tooltip-left <?php $meermag->maybe_disabled( $next_edition, 'features' ) ?>"
							href="<?php echo home_url() . '/' . $next_edition[0] . '/' . $next_edition[1] ?>/features">
							<span class="menu-arrow"></span>
						</a>
					</span>
				</h2>
				<ul class="cf"><?php
					foreach ( $features as $feature ) { ?>
						<li>
							<div class="feature-header">
								<span>
									<a class="issue-link" href="<?php echo get_permalink( $feature->ID ) ?>"><?php echo $feature->post_title; ?></a>
									<p><?php echo get_field( 'section_grid_excerpt', $feature->ID ) ?></p>
								</span>
							</div>
						</li>
						<?php
					} ?>
				</ul>
			</div><!-- .toc-features --><?php
		}

		if ( $toc_post = $meermag->get_toc_post( $context_year . '-' . $context_issue['slug'] ) ) {
			if ( get_field( 'toc_sections_rpt', $toc_post[0]->ID ) ) {
				?>
				<div class="toc-departments">
					<h2 class="title"><?php _e( 'Departments' ) ?></h2>
					<ul class="cf"><?php
						while ( the_repeater_field( 'toc_sections_rpt', $toc_post[0]->ID ) ) {
							$section = get_sub_field( 'toc_section' );
							$description = get_sub_field( 'toc_section_description' );
							?>
							<li class="cf">
								<div class="department-header">
									<span class="direction prev">
										<a title="<?php echo ucfirst( $prev_edition[1] ) . ' ' . $prev_edition[0] . ' - ' . $section->name ?>"
											class="<?php echo $tooltip_class ?> tooltip-bottom <?php $meermag->maybe_disabled( $prev_edition, $section->slug ) ?>"
											href="<?php echo home_url() . '/' . $prev_edition[0] . '/' . $prev_edition[1] . '/' . $section->slug ?>">
											<span class="menu-arrow"></span>
										</a>
									</span>
									<span>
										<a class="issue-link <?php $meermag->maybe_disabled( array(
											$context_year,
											$context_issue['slug']
										), $section->slug ) ?> <?php echo $current_term == $section->slug ? 'current_term' : '' ?>"
											href="<?php echo home_url() . '/' . $context_year . '/' . $context_issue['slug'] . '/' . $section->slug ?>">
											<?php echo $section->name ?>
										</a>
										<p><?php echo $description ?></p>
									</span>
									<span class="direction next">
										<a title="<?php echo ucfirst( $next_edition[1] ) . ' ' . $next_edition[0] . ' - ' . $section->name ?>"
											class="<?php echo $tooltip_class ?> tooltip-left <?php $meermag->maybe_disabled( $next_edition, $section->slug ) ?>"
											href="<?php echo home_url() . '/' . $next_edition[0] . '/' . $next_edition[1] . '/' . $section->slug ?>">
											<span class="menu-arrow"></span>
										</a>
									</span>
								</div>
							</li>
							<?php
						} ?>
					</ul>
				</div><!-- .toc-departments --><?php
			}
		} //end if
		?>
		<?php echo $after_widget;
	} // end function
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatEditionTOCWidget" );' ) );

class MeerkatEditionGalleryWidget extends MeerkatWidget {
	// register widget with wordpress
	public function __construct() {
		$desc = 'Shows gallery of currently viewed edition';
		parent::__construct( 'meerkat_edition_gallery', // Base ID
			MK_WIDGET_PREFIX . 'Edition Gallery', // Name
			array( 'description' => $desc ) // Args
		);

	}

	/**
	 * Displays the edition gallery widget
	 *
	 * @param mixed $args
	 * @param mixed $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		global $wp_query, $meermag;

		// Get edition issue.  This is the edition currently active on the front.
		$context_issue = $meermag->get_context_issue();
		$context_year = $meermag->get_context_year();

		if ( $toc_post = $meermag->get_toc_post( $context_year . '-' . $context_issue['slug'] ) ) {
			echo $before_widget;
			$title = empty( $instance['title'] ) ? '' : $instance['title'];
			echo $before_title . $title . $after_title;
			if ( $gallery_posts = get_field( 'edition_gallery_posts', $toc_post[0]->ID ) ) {
				foreach ( $gallery_posts as $gp ) {
					if ( has_post_thumbnail( $gp->ID ) ) {
						$excerpt = get_field( 'section_grid_excerpt', $gp->ID );
						$excerpt = $excerpt ? $excerpt : $gp->post_title; ?>
					<a class="edition_gallery_thumb apply-tooltip" href="<?php echo get_permalink( $gp->ID ) ?>"
						title="<?php echo esc_attr( $excerpt ) ?>">
						<?php $post_thumbnail_id = get_post_thumbnail_id( $gp->ID );
						$feature_img_src = wp_get_attachment_image_src( $post_thumbnail_id ); ?>
						<img class="cover-image" src="<?php echo $feature_img_src[0] ?>"
							width="<?php echo $feature_img_src[1] ?>" alt="<?php echo esc_attr( $excerpt ) ?>"
							height="<?php echo $feature_img_src[2] ?>">
						</a><?php
					};
				}
			} ?>
			<?php echo $after_widget;
		}
	}


}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatEditionGalleryWidget" );' ) );

?>