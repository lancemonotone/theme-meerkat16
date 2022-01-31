<?php
//include_once( THEME_INC_PATH . '/class.post.php'); // not used yet
include_once(THEME_INC_PATH . '/class.js.php');
include_once(THEME_INC_PATH . '/class.css.php');
include_once(THEME_INC_PATH . '/widget-areas.php');
include_once(THEME_INC_PATH . '/gallery.php');      // lightbox & other extensions for galleries
include_once(WMS_LIB_PATH . '/quicklinks/class.quicklinks.php');   // custom bookmarking system
include_once(THEME_INC_PATH . '/widgets.php');      // widget library, which in turn includes other widgets
include_once(THEME_INC_PATH . '/class.tinymce.php');      // configure TinyMCE
include_once(THEME_INC_PATH . '/options.php');      // theme options built w/ ACF plugin
include_once(THEME_INC_PATH . '/class.breadcrumbs.php');  // breadcrumb navigation
include_once(THEME_INC_PATH . '/class.custom_post_types.php');  // CPT configuration
include_once(THEME_INC_PATH . '/class.categories.php');   // customize what post content shows up in each category
include_once(THEME_INC_PATH . '/class.profiles.php');     // faculty profiles - replaces plugin
include_once(THEME_INC_PATH . '/class.profile.single.php');     // faculty profiles - replaces plugin
include_once(THEME_INC_PATH . '/directory.php');    // allow searching of WMS directories
include_once(THEME_INC_PATH . '/calendar.php');     // code for post calendar
include_once(THEME_INC_PATH . '/class.shortcodes.php');   // theme shortcodes
include_once(THEME_INC_PATH . '/class.plugins.php');      // theme plugins
include_once(THEME_INC_PATH . '/flexiform.php');      // pdf printing and flexiform display
include_once(THEME_INC_PATH . '/theme.php');        // setup theme
include_once(THEME_INC_PATH . '/class.images.php');        // Image helper class
include_once(THEME_INC_PATH . '/class.search.php');        // Search helper class
include_once(THEME_INC_PATH . '/class.facets.php');        // Facets helper class
include_once(THEME_INC_PATH . '/class.favicons.php');        // Facets helper class

if (is_plugin_active('facetwp/index.php')) {
    require_once(WMS_LIB_PATH . '/facetwp_templater/class.facetwp.templater.php');
}
