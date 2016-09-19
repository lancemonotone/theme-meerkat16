<?php
/**
 * @todo This should be a class.
 */
$widget_areas = array(
	array(
		'name'        => 'Sidebar',
		'id'          => 'sidebar',
		'description' => 'Right sidebar widgets'
	),
	array(
		'name'        => 'Homepage Widget Area',
		'id'          => 'home-widget-area',
		'description' => 'Located at the top of your site\'s homepage'
	)
);

/**
 * @param $widget_areas
 */
function sidebar_setup( $widget_areas ) {
	foreach ( $widget_areas as $widget_area ) {
		register_sidebar( array(
			'name'           => $widget_area['name'],
			'id'             => $widget_area['id'],
			'description'    => $widget_area['description'],
			'before_widget'  => '<div id="%1$s" class="widget %2$s">',
			'after_widget'   => '</div>',
			'before_title'   => '<h3 class="widgettitle">',
			'after_title'    => '</h3>',
			'before_insides' => '<div class="widget-insides">',
			'after_insides'  => edit_widget_link( $widget_area['id'] ) . '</div>'
		) );
	}
}

/**
 * @param $sidebar
 *
 * @return bool|string
 */
function edit_widget_link( $sidebar ) {
	// creates an edit link for logged in admins that appears in sidebars/widgetized areas
	// note: edit widget link is modified in main.js to point specifically to the widget, instead of just the sidebar
	if ( current_user_can( CAPABILITY_THRESH ) ) {
		return <<< EOD
            <a class="edit-me href="/wp-admin/widgets.php?sidebar=$sidebar">Edit Widget</a>
EOD;
	} else {
		return false;
	}
}

sidebar_setup( $widget_areas );