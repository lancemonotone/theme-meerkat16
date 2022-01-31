<?php

/**
 * Class Theme_Setup
 *
 * @todo Revisit this header image stuff. Is it necessary?
 */
class Theme_Setup {
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Theme_Setup The *Singleton* instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
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

    protected function __construct() {
        add_theme_support('automatic-feed-links');
        add_theme_support('post-thumbnails');
        // Not necessary because register_nav_menus() does this
        //add_theme_support( 'menus' );
        add_theme_support('editor_style');

        $www_nav = array(
            'mega_social'   => __('Megamenu Social'),
            'mega_featured' => __('Megamenu Featured'),
            'mega_global'   => __('Megamenu Global')
        );

        $network_nav = array(
            'site'   => __('Site Navigation'),
            'social' => __('Site Social Navigation'),
            'footer' => __('Site Footer Links'),
        );

        global $blog_id;
        if (WWW_BLOG_ID === $blog_id) {
            $network_nav = array_merge($network_nav, $www_nav);
        }

        register_nav_menus($network_nav);

        // CUSTOM HEADERS
        $custom_header_args = array(
            'width'         => 1200,
            'height'        => 240,
            'header-text'   => true,
            'uploads'       => false,
            'default-image' => THEME_IMG_URL . '/banners/chapin.jpg',
        );

        if (Meerkat16::instance()->is_homepage_theme) {
            $custom_header_args['height'] = 290;
        } else if (Meerkat16::instance()->is_magazine_theme) {
            $custom_header_args['width']  = 843;
            $custom_header_args['height'] = 154;
        }

        // only superadmins can upload new headers
        if (is_super_admin() || Wms_Server::instance()->is_dev(true)) {
            $custom_header_args['uploads'] = true;
        }

        add_theme_support('custom-header', $custom_header_args);

        if ( ! Meerkat16::instance()->is_magazine_theme) {
            $this->build_header_lib();
        }
    }

    public static function get_custom_header() {
        $img_src = self::get_custom_header_src();
        if ($img_src) {
            return '<img src="' . $img_src . '" width="' . HEADER_IMAGE_WIDTH . '" height="' . HEADER_IMAGE_HEIGHT . '" alt="" />';
        } else {
            return '';
        }
    }

    public static function get_custom_header_src() {
        global $post;
        $img_src = '';
        if (Meerkat16::instance()->blog_id == 186 && is_front_page()) {
            return false;
        } // 186 = 62CTD
        if (is_page() && has_post_thumbnail($post->ID)) {
            // check for feature image that is large enough to be a banner
            $feat_img = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'post-thumbnail');
            if ($feat_img[1] >= HEADER_IMAGE_WIDTH) {
                $img_src = $feat_img[0];
            }
        }
        if ( ! $img_src) {
            // check appearance options for standard banner
            $img_src = get_header_image();
        }

        return $img_src;
    }

    // CUSTOM BANNER LIB
    function build_header_lib() {
        $headers = array();

        // a list of all the available custom headers
        if (Meerkat16::instance()->is_homepage_theme) {
            // homepage needs 1200 x 290
            $headers = array('Chapin Hall'         => 'chapin.jpg',                                // content dm
                             'Campus Aerial'       => 'campus-aerial.jpg',                    // ndcso
                             'Paresky'             => 'paresky.jpg',                                // ndcso
                             'Paresky w/ Students' => 'paresky-students-back.jpg',        // ??
                             'Stetson Ivy'         => 'stetson-ivy.jpg',                        // ??
                             'Maple Foliage'       => 'maple-sugar.jpg',                        // wikimedia commons
            );
        } else {
            // dept sites can handle 1200 x 240 and 1200 x 290
            $headers = array('Chapin Hall'           => 'chapin.jpg',                                // content dm
                             'Paresky'               => 'paresky.jpg',                                // ncdso
                             'Stetson Ivy'           => 'stetson-ivy.jpg',                        // ??
                             'Campus Aerial'         => 'campus-aerial.jpg',                    // ncdso
                             'Frosh Quad Gates'      => 'frosh-quad-gates.jpg',                // ??
                             'Climb High, Climb Far' => 'climb-high.jpg',                // hclemow
                             'Goodrich & WCMA'       => 'goodrich-eyes.jpg',                    // hclemow
                             'WCMA Eyes'             => 'wcma-eyes.jpg',                            // flickr creative commons
                             'Paresky w/ Students'   => 'paresky-students-back.jpg',        // ??
                             'Maple Foliage'         => 'maple-sugar.jpg',                        // wikimedia commons
                             'Balloons'              => 'balloons.jpg',                                // ??
                             'Cows'                  => 'cows.jpg',                                        // ndcso
                             'Adirondack Chairs'     => 'paresky-chairs.jpg',                // hclemow
                             'Science Lab'           => 'chemlab.jpg',                            // ncdso
            );
        }
        // offers multiple choices for site header image
        $to_register = array();
        foreach ($headers as $title => $img) {
            $to_register[ $title ] = array(
                'url'           => THEME_IMG_URL . '/banners/' . $img,
                'thumbnail_url' => THEME_IMG_URL . '/banners/thumbs/' . $img,
                'description'   => $title
            );
        }
        register_default_headers($to_register);
    }

}

Theme_Setup::instance();
