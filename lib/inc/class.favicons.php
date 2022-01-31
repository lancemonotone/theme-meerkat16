<?php

class Meerkat16_Favicons {
    //public $img_url = THEME_IMG_URL . '/favicons';
    public function __construct() {
        add_action('wp_head', array(&$this, 'get_favicons'), 5);
        add_filter('language_attributes', array(&$this, 'add_opengraph_doctype'));
    }

    //Adding the Open Graph in the Language Attributes
    function add_opengraph_doctype($output) {
        return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
    }

    /**
     * Add favicons, homescreen icons and social thumbnails
     */
    function get_favicons() {
        global $post, $user_ID;
        
        $site_name = get_bloginfo('name');
        if (is_archive()) {
            $q = get_queried_object();
            if ($q->term_id) {
                // category or tag
                $title = $q->name;
                $permalink = get_category_link($q->term_id);
            } else {
                // custom post type archive
                $title = $q->labels->name;
                $permalink = get_post_type_archive_link($q->name);
            }
            $description = get_the_archive_description();
            $post_id = false;
        } else {
            $title     = get_the_title();
            $permalink = get_permalink();
            $post_id = $post->ID;
            $seo_description = get_field('page_meta_desc');
            $description     = $seo_description ? $seo_description : ($post->post_excerpt ? $post->post_excerpt : $post->post_content);
        }
        if ( $post_id && has_post_thumbnail($post_id)) {
            $thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
            $fb_image      = esc_attr($thumbnail_src[0]);
        } else {
            //replace this with a default image on your server or an image in your media library?
            $fb_image = \Wms_Server::instance()->www . '/favicons/wordmark_facebook_300x300.png?v=E6wdjK8q8b';
        }
        // sanitize for header
        $description = nl2br($description);
        $description = wp_strip_all_tags($description, true);
        $description = strip_shortcodes($description);
        $description = esc_attr($description);
        $description = wp_trim_words($description, 300);

        echo <<<EOD
            <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png?v=E6wdjK8q8b">
            <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png?v=E6wdjK8q8b">
            <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png?v=E6wdjK8q8b">
            <link rel="manifest" href="/favicons/site.webmanifest?v=E6wdjK8q8b">
            <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg?v=E6wdjK8q8b" color="#4f0083">
            <link rel="shortcut icon" href="/favicons/favicon.ico?v=E6wdjK8q8b">
            <meta name="msapplication-TileColor" content="#4f0083">
            <meta name="theme-color" content="#ffffff">
        
            <meta property="fb:admins" content="$user_ID"/>
            <meta property="fb:app_id" content="226845604445812"/>
            <meta property="og:title" content="$title"/>
            <meta property="og:type" content="article"/>
            <meta property="og:url" content="$permalink"/>
            <meta property="og:site_name" content="$site_name"/>
            <meta property="og:image" content="$fb_image"/>
            <meta property="og:description" content="$description"/>

EOD;
    }
}

new Meerkat16_Favicons();
