<?php
/**
* Class Meerkat16_Css
 *
 * Conacten
 */
class Meerkat16_Css {
   private static $instance;
	
	public function __construct() {
        add_action('wp_enqueue_scripts', array(&$this, 'meerkat16_styles'), 100);
	}
	
	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Meerkat16_Css The *Singleton* instance.
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
	


    /**
     * Enqueues scripts and styles.
     *
     * @todo scss-ify blacktie and include in m16 style.scss
     */
    function meerkat16_styles() {
        // 	icon set
        wp_enqueue_style('blacktie-icons', WMS_LIB_URL . '/assets/fonts/blacktie/black-tie.css', array(), '3.4.1');
		// 2021 eph typefaces
		wp_enqueue_style('eph-slab', WMS_LIB_URL . '/assets/fonts/ephfamily/eph-family.css', array(), '1.0.0');
        $version            = null;
        $theme_css_filepath = THEME_CSS_PATH . '/style.css';
        $child_css_filepath = CHILD_CSS_PATH . '/style.css';
        $theme_css_url      = THEME_CSS_URL . '/style.css';
        $child_css_url 		= CHILD_URL . CSSBUILDPATH . '/style.css';

        if (file_exists($theme_css_filepath)) {
            $version = filemtime($theme_css_filepath);
        }else if(file_exists($child_css_filepath)){
            $version = filemtime($child_css_filepath);
        }

        // Theme stylesheet
        // Only load child styles if child theme
        if (THEME_URL===CHILD_URL) {
            wp_enqueue_style('theme-stylesheet', $theme_css_url, array(), $version, 'all');
        }else{
            wp_enqueue_style('child-stylesheet', $child_css_url, array(), $version, 'all');
        }
    }
	
	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}
	
	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}
}

Meerkat16_Css::instance();