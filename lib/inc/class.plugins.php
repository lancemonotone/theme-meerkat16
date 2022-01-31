<?php

/*
 * Put functions related to plugins here
*/
// need for is_plugin_active() for autoloading plugins
require_once( WPMU_ADMIN_PATH . '/includes/plugin.php' );
require_once( THEME_PLUGINS_PATH . '/index.php' );

class M16_Plugins {
    private static $instance;

    protected $auto_activate = array(
        // drm2 - now network activated
        //'advanced-custom-fields-pro/acf.php',
        'wms-directory/wms-directory.php',
        'wms-navbox/index.php',
        'wms-shortcode/shortcode-builder.php',
    );

    protected $auto_deactivate = array(
        'device-theme-switcher/dts_controller.php',
        'breadcrumb-navxt/breadcrumb_navxt_admin.php',
        'page-links-to/page-links-to.php',
        'wms-widgets-2/wms-basic-utils.php',
        'wms-peoplesoft/wms-profile.php',
        'wms-thickbox/wms-thickbox.php',
        'wms-slideshow/wms-slideshow.php'
    );

    protected function __construct() {
        if ( Meerkat16::instance()->is_magazine_theme || Meerkat16::instance()->is_homepage_theme ) {
            $this->auto_activate[] = 'search-everything/search-everything.php';
        }
        $this->auto_activate_plugins($this->auto_activate);


        if ( Meerkat16::instance()->is_homepage_theme ) {
            // ngg screws up localist template
            $this->auto_deactivate[] = 'nextgen-gallery/nggallery.php';
        }
        $this->auto_deactivate_plugins($this->auto_deactivate);
    }

    /**
     * Activate plugins
     *
     * @param array $activate ['plugin_dir/plugin_file.php']
     */
    function auto_activate_plugins(array $activate): void {
        foreach ( $activate as $plugin ) {
            if ( ! is_plugin_active( $plugin ) ) {
                activate_plugin( $plugin );
            }
        }
    }

    /**
     * Deactivate conflicting plugins/widgets
     *
     * @param array $deactivate ['plugin_dir/plugin_file.php']
     */
    function auto_deactivate_plugins(array $deactivate):void {
        deactivate_plugins( $deactivate );
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return M16_Plugins The singleton instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone() {
    }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup() {
    }
}

M16_Plugins::instance();
