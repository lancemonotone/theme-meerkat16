<?php

class Meerkat16_Images {
    private static $instance;

    public function __construct() {
        // Thumbnails to Admin Post View
        if (function_exists('add_image_size')) {
            add_image_size('admin-thumb', 100, 999999); // 100 pixels wide (and unlimited height)
            //meerkat16-home images added here so they can be scraped---all aspect ratio 4:3 cropped center, center (true)
            add_image_size('newsmix-featured', 832, 624, true); // 832w x 624h
            add_image_size('newsmix-uncropped-thumb', 250, 9999, false);//250 x 100%
            add_image_size('newsmix-featured2', 188, 134, true); //  188w x 134h
        }
        add_filter('manage_posts_columns', array($this, 'posts_columns'), 5);
        add_action('manage_posts_custom_column', array($this, 'posts_custom_columns'), 5, 2);
        add_filter('media_view_settings', array($this, 'set_gallery_default_link_type'));
        add_filter('theme_mod_header_image', array($this, 'fix_header_image_url'), 1, 1);
    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Meerkat16_Images The *Singleton* instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    // retrieves the attachment ID from the file URL
    public static function get_image_id($image_url) {
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid LIKE %s;", '%' . $wpdb->esc_like($image_url)));

        return $attachment[0];
    }

    /**
     * Get all the registered image sizes along with their dimensions
     *
     * @return array $image_sizes The image sizes
     * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
     *
     * @global array $_wp_additional_image_sizes
     *
     */
    public static function get_all_image_sizes() {
        global $_wp_additional_image_sizes;

        $default_image_sizes = get_intermediate_image_sizes();

        foreach ($default_image_sizes as $size) {
            $image_sizes[ $size ]['width']  = intval(get_option("{$size}_size_w"));
            $image_sizes[ $size ]['height'] = intval(get_option("{$size}_size_h"));
            $image_sizes[ $size ]['crop']   = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
        }

        if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
            $image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
        }

        return $image_sizes;
    }

    public function set_gallery_default_link_type($settings) {
        $settings['galleryDefaults']['link'] = 'file';
        $settings['galleryDefaults']['size'] = 'medium';

        return $settings;
    }

    /**
     * This will correct the hardcoded db value for 'theme_mod_header_image'
     * when the image path has changed.
     */
    public function fix_header_image_url($url) {
        if (is_string($url) && ! stristr($url, '/files/') && ! stristr($url, THEME_IMG_URL)) {
            $data = get_theme_mod('header_image_data');
            if ( ! empty($data['thumbnail_url'])) {
                $fixed_url             = preg_replace("/(.*)(\/)(banners\/)(.*)/", THEME_IMG_URL . "\/$3$4", $url);
                $fixed_thumbnail_url   = preg_replace("/(.*)(\/)(banners\/thumbs\/)(.*)/", THEME_IMG_URL . "\/$3$4", $data['thumbnail_url']);
                $data['thumbnail_url'] = $fixed_thumbnail_url;
                $data['url']           = $fixed_url;
                set_theme_mod('header_image', $fixed_url);
                set_theme_mod('header_image_data', $data);
                $url = $fixed_url;
            }
        }

        return $url;
    }

    public static function get_header_image() {
        /**
         * Return custom header image. For home page this is set via WP site options. Interior pages use featured image
         * if dimensions are greater than custom-header width and height.
         */
        $custom_header_sizes = esc_attr(apply_filters('twentysixteen_custom_header_sizes', '(max-width: 709px) 85vw, (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px'));

        if ( ! is_front_page() && has_post_thumbnail()) {
            $custom_header      = (object) array(
                'attachment_id' => get_post_thumbnail_id(),
                'url'           => '',
                'thumbnail_url' => '',
                'width'         => get_theme_support('custom-header', 'width'),
                'height'        => get_theme_support('custom-header', 'height'),
            );
            $attachment_img_src = wp_get_attachment_image_src($custom_header->attachment_id, 'full');
            if ($attachment_img_src[1] >= $custom_header->width && $attachment_img_src[2] >= $custom_header->height) {
                $header_image['src'] = get_the_post_thumbnail_url();
            }
        } elseif (is_front_page()) {
            $custom_header       = get_custom_header();
            $header_image['src'] = get_header_image();
        }

        if ($header_image['src']) {
            $header_image['srcset'] = esc_attr(wp_get_attachment_image_srcset($custom_header->attachment_id));
            $header_image['sizes']  = $custom_header_sizes;
            $header_image['width']  = $custom_header->width;
            $header_image['height'] = $custom_header->height;
            $header_image['alt']    = esc_attr(get_bloginfo('name', 'display'));

            return <<<EOD
			<figure class="header-image" style="background-image: url('{$header_image['src']}')">
	            <meta itemprop="image" content="{$header_image['src']}">
	            <!-- <img src="{$header_image['src']}"
	                    srcset="{$header_image['srcset']}"
	                    sizes="{$header_image['sizes']}"
	                    width="{$header_image['width']}"
	                    height="{$header_image['height']}"
	                    alt="{$header_image['alt']}"> -->
	        </figure><!-- .header-image -->
EOD;
        } else {
            return false;
        }
    }

    /**
     * Displays an optional post thumbnail.
     *
     * Wraps the post thumbnail in an anchor element on index views, or a div
     * element when on single views.
     *
     * Create your own twentysixteen_post_thumbnail() function to override in a child theme.
     *
     * @since Twenty Sixteen 1.0
     */
    public static function get_post_thumbnail() {
        if (post_password_required() || is_attachment() || ! has_post_thumbnail()) {
            return false;
        }

        if (is_singular()) {
            $the_post_thumbnail = get_the_post_thumbnail();
            $out                = <<<EOD
            <div class="post-thumbnail">!
                $the_post_thumbnail
            </div><!-- .post-thumbnail -->
EOD;
        } else {
            $the_permalink      = get_the_permalink();
            $the_post_thumbnail = get_the_post_thumbnail('post-thumbnail', array('alt' => the_title_attribute('echo=0')));
            $out                = <<<EOD
            <a class="post-thumbnail" href="$the_permalink" aria-hidden="true">?
                $the_post_thumbnail;
            </a>
EOD;
        }

        return $out;
    }

    function posts_columns($defaults) {
        $defaults['my_post_thumbs'] = __('Featured Image');

        return $defaults;
    }

    function posts_custom_columns($column_name, $id) {
        if ($column_name === 'my_post_thumbs') {
            echo the_post_thumbnail('admin-thumb');
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

//----- INSTANTIATE -----//
Meerkat16_Images::instance();
