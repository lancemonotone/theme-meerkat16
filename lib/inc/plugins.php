<?php

/*
 * Put functions related to plugins here
*/

class Plugin_Setup {
	var $auto_activate = array(
		'advanced-custom-fields-pro/acf.php',
		'wms-directory/wms-directory.php',
		'wms-navbox/index.php'
	), $auto_deactivate = array(
		'device-theme-switcher/dts_controller.php',
		'breadcrumb-navxt/breadcrumb_navxt_admin.php',
		'page-links-to/page-links-to.php',
		'wms-widgets-2/wms-basic-utils.php',
		'wms-peoplesoft/wms-profile.php',
		'wms-thickbox/wms-thickbox.php',
		'wms-slideshow/wms-slideshow.php'
	);

	public function __construct() {
		$this->auto_activate_plugins();
	}

	// ACTIVATE PLUGINS
	function auto_activate_plugins() {
		// need for is_plugin_active() for autoloading plugins
		require_once( WPMU_ADMIN_PATH . '/includes/plugin.php' );
		require_once( THEME_PLUGINS_PATH . 'index.php' );

		if ( Meerkat16::instance()->is_magazine_theme || Meerkat16::instance()->is_homepage_theme ) {
			$this->auto_activate[] = 'search-everything/search-everything.php';
		}

		foreach ( $this->auto_activate as $plugin ) {
			if ( ! is_plugin_active( $plugin ) ) {
				activate_plugin( $plugin );
			}
		}// deactivate conflicting plugins/widgets

		if ( Meerkat16::instance()->is_homepage_theme ) {
			// ngg screws up localist template
			$this->auto_deactivate[] = 'nextgen-gallery/nggallery.php';
		}
		deactivate_plugins( $this->auto_deactivate );
	}
}

new Plugin_Setup();
