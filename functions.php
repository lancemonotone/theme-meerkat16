<?php
/**
 * Twenty Sixteen functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

/**
 * Twenty Sixteen only works in WordPress 4.4 or later.
 */
if (version_compare($GLOBALS['wp_version'], '4.4-alpha', '<')) {
    require get_template_directory() . '/lib/back-compat.php';
}

// Set for Localist template. No longer needed.
//date_default_timezone_set('America/New_York');

require(get_template_directory() . '/lib/class.meerkat16.php');

if ( ! function_exists('meerkat16_setup')) {
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     *
     * Create your own meerkat16_setup() function to override in a child theme.
     *
     * @since Twenty Sixteen 1.0
     * @todo merge theme setup with meerkat-core.
     */
    function meerkat16_setup() {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on Twenty Sixteen, use a find and replace
         * to change 'twentysixteen' to the name of your theme in all the template files
         */
        load_theme_textdomain('twentysixteen', get_template_directory() . '/languages');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
         */
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(1200, 9999);

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        /*
         * Enable support for Post Formats.
         *
         * See: https://codex.wordpress.org/Post_Formats
         */
        /*add_theme_support( 'post-formats', array(
            'aside',
            'image',
            'video',
            'quote',
            'link',
            'gallery',
            'status',
            'audio',
            'chat',
        ) );*/

        /*
         * This theme styles the visual editor to resemble the theme style,
         * specifically font, colors, icons, and column width.
         */
        add_editor_style(array(
            //			CSS_URL . 'editor-style.min.css',
            meerkat16_fonts_url()
        ));
    }
}; // meerkat16_setup
add_action('after_setup_theme', 'meerkat16_setup');

/**
 * Sets the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 *
 * @since Twenty Sixteen 1.0
 */
function meerkat16_content_width() {
    $GLOBALS['content_width'] = apply_filters('meerkat16_content_width', 840);
}

add_action('after_setup_theme', 'meerkat16_content_width', 0);

if ( ! function_exists('meerkat16_fonts_url')) :
    /**
     * Register Google fonts for Twenty Sixteen.
     *
     * Create your own meerkat16_fonts_url() function to override in a child theme.
     *
     * @since Twenty Sixteen 1.0
     *
     * @return string Google fonts URL for the theme.
     */
    function meerkat16_fonts_url() {
        $fonts_url = '';
        $fonts     = array();
        $subsets   = 'latin,latin-ext';

        if ($fonts) {
            $fonts_url = add_query_arg(array(
                'family' => urlencode(implode('|', $fonts)),
                'subset' => urlencode($subsets),
            ), 'https://fonts.googleapis.com/css');
        }

        return $fonts_url;
    }
endif;

/**
 * Add SVG capabilities
 */
function meerkat16_svg_mime_type($mimes = array()) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';

    return $mimes;
}

add_filter('upload_mimes', 'meerkat16_svg_mime_type');

/**
 * Adds custom classes to the array of body classes.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param array $classes Classes for the body element.
 *
 * @return array (Maybe) filtered body classes.
 */
function meerkat16_body_classes($classes) {
    // Adds a class of group-blog to sites with more than 1 published author.
    if (is_multi_author()) {
        $classes[] = 'group-blog';
    }

    // Adds a class of hfeed to non-singular pages.
    if ( ! is_singular()) {
        $classes[] = 'hfeed';
    }

    return $classes;
}

add_filter('body_class', 'meerkat16_body_classes');

/**
 * Converts a HEX value to RGB.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param string $color The original color, in 3- or 6-digit hexadecimal form.
 *
 * @return array Array containing RGB (red, green, and blue) values for the given
 *               HEX code, empty array otherwise.
 */
function twentysixteen_hex2rgb($color) {
    $color = trim($color, '#');

    if (strlen($color) === 3) {
        $r = hexdec(substr($color, 0, 1) . substr($color, 0, 1));
        $g = hexdec(substr($color, 1, 1) . substr($color, 1, 1));
        $b = hexdec(substr($color, 2, 1) . substr($color, 2, 1));
    } else if (strlen($color) === 6) {
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
    } else {
        return array();
    }

    return array('red' => $r, 'green' => $g, 'blue' => $b);
}

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/lib/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/lib/customizer.php';

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images
 *
 * @since Twenty Sixteen 1.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 *
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function meerkat16_content_image_sizes_attr($sizes, $size) {
    $width = $size[0];

    840 <= $width && $sizes = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 1362px) 62vw, 840px';

    if ('page' === get_post_type()) {
        840 > $width && $sizes = '(max-width: ' . $width . 'px) 85vw, ' . $width . 'px';
    } else {
        840 > $width && 600 <= $width && $sizes = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 984px) 61vw, (max-width: 1362px) 45vw, 600px';
        600 > $width && $sizes = '(max-width: ' . $width . 'px) 85vw, ' . $width . 'px';
    }

    return $sizes;
}

add_filter('wp_calculate_image_sizes', 'meerkat16_content_image_sizes_attr', 10, 2);

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails
 *
 * @since Twenty Sixteen 1.0
 *
 * @param array $attr Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size Registered image size or flat array of height and width dimensions.
 *
 * @return string A source size value for use in a post thumbnail 'sizes' attribute.
 */
function meerkat16_post_thumbnail_sizes_attr($attr, $attachment, $size) {
    if ('post-thumbnail' === $size) {
        is_active_sidebar('sidebar-1') && $attr['sizes'] = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 984px) 60vw, (max-width: 1362px) 62vw, 840px';
        ! is_active_sidebar('sidebar-1') && $attr['sizes'] = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 1362px) 88vw, 1200px';
    }

    return $attr;
}

add_filter('wp_get_attachment_image_attributes', 'meerkat16_post_thumbnail_sizes_attr', 10, 3);

/**
 * Modifies tag cloud widget arguments to have all tags in the widget same font size.
 *
 * @since Twenty Sixteen 1.1
 *
 * @param array $args Arguments for tag cloud widget.
 *
 * @return array A new modified arguments.
 */
function meerkat16_widget_tag_cloud_args($args) {
    $args['largest']  = 1;
    $args['smallest'] = 1;
    $args['unit']     = 'em';

    return $args;
}

add_filter('widget_tag_cloud_args', 'meerkat16_widget_tag_cloud_args');

/*
 * Change the upload path of the "Update Directory Photo" GravityForm
 * Plugin: Williams Directory Photo Manager
 * Current "Update Your Williams Directory Photograph" Form ID: 3
 */

// Actions and Filters. Change trailing number in hook to match the Form ID above.
add_filter( 'gform_upload_path_3', 'change_upload_path', 10, 2 );
add_action( 'gform_after_submission_3', "rename_file", 10, 2);
add_filter( 'gform_confirmation_anchor_3', '__return_false' );

// Set the upload path for the photo submissions
function change_upload_path( $path_info, $form_id ) {
   $path_info['path'] = wp_upload_dir()['basedir'] . "/pending/user/";
   $path_info['url'] = wp_upload_dir()['baseurl'] . "/pending/user/";
   return $path_info;
}

// Set the filename of the photo submissions
function rename_file($entry, $form) {
    
    // Change if necessary
    $form_id = "3";

    $fileupload_field_id = "1";
    
    //ignore other forms
    if($form["id"] != $form_id)
        return;

    $upload_info = wp_upload_dir();

    //original name/path
    $original_file_url = $entry[$fileupload_field_id];
    $original_file_path = str_replace($upload_info["baseurl"], $upload_info["basedir"], $original_file_url);
    $original_file_info = pathinfo($original_file_url);

    //New File Name (without extension).
    $new_name =  wp_get_current_user()->user_login . "." . $original_file_info["extension"];

    $new_file_url = str_replace($original_file_info["basename"], $new_name, $original_file_url);
    $new_file_path = str_replace($original_file_info["basename"], $new_name, $original_file_path);

    //rename file
    $is_success = false;
    if(file_exists($original_file_path)) {
        $is_success = rename($original_file_path, $new_file_path);
    }

    //if file was renamed successfully, updating entry so that it points to the new file
    if($is_success){
        global $wpdb;
        $wpdb->update(RGFormsModel::get_lead_details_table_name(), array("value" => $new_file_url), array("lead_id" => $entry["id"], "value" => $original_file_url));
    }
}
