<?php
// DEFINES
define('WMS_PHP_LIB', '/web/lib/');

define('WWW_BLOG_ID', 93);
define('CAMPUS_IP_PREFIX', '137.165');
define('WWW_BLOG_URL', get_site_url(WWW_BLOG_ID));
define('GIVING_BLOG_URL', 'http://giving.' . Wms_Server::instance()->domain);
define('MIN_FEATURED_HEADER_IMG_WIDTH', 696);

define('JSBUILDPATH', '/assets/build/js');
define('CSSBUILDPATH', '/assets/build/css');

define('THEME_JS_PATH', TEMPLATEPATH . JSBUILDPATH);
define('THEME_CSS_PATH', TEMPLATEPATH . CSSBUILDPATH);

define('THEME_LIB_PATH', TEMPLATEPATH . '/lib');
define('THEME_PLUGINS_PATH', THEME_LIB_PATH . '/plugins');
define('THEME_INC_PATH', THEME_LIB_PATH . '/inc');
define('THEME_WIDGETS_PATH', THEME_LIB_PATH . '/widgets');

define('THEME_URL', get_template_directory_uri());
define('SECURE_THEME_URL', str_replace('http', 'https', THEME_URL));
define('THEME_CSS_URL', THEME_URL . CSSBUILDPATH);
define('THEME_JS_URL', THEME_URL . JSBUILDPATH);
define('THEME_IMG_URL', THEME_URL . '/assets/build/img');
define('THEME_PLUGINS_URL', THEME_URL . '/lib/plugins');

define('CHILD_CSS_PATH', STYLESHEETPATH . CSSBUILDPATH);
define('CHILD_JS_PATH', STYLESHEETPATH . JSBUILDPATH);
define('CHILD_URL', get_stylesheet_directory_uri());
define('CHILD_JS_URL', CHILD_URL . JSBUILDPATH);

define('ADDTHIS_ID', 'ra-512bca8860afc729');

// INCLUDES
require_once(WMS_EXT_LIB . '/HTTPrequest.php'); // calls content from http location ************

class Meerkat16 {
    private static $instance;

    var $using_timber = true,
        $is_homepage_theme = false,
        $is_magazine_theme = false,
        $is_ctd_theme = false,
        $blog_id,
        $is_dev,
        $is_stage,
        $content_restricted = false,
        $cat_config,
        $cat_opts,
        $viewing_context,
        $is_page,
        $is_google_search,
        $is_profile_page = false;

    protected function __construct() {
        global $blog_id;
        $this->blog_id = $blog_id;
        $this->add_actions();
    }

    /**
     * Hooks and filters
     */
    protected function add_actions() {

        add_action('after_setup_theme', array(&$this, 'init_theme'), 10);
        add_action('pre_get_posts', array($this, 'pre_get_posts'));
        add_action('wp_enqueue_scripts', array(&$this, 'load_dashicons_front_end'));
        add_action('meerkat_body_begin', array(&$this, 'body_begin'));
        add_action('template_redirect', array(&$this, 'is_google_search'), 1);
        add_action('template_redirect', array(&$this, 'is_profile_page'), 1);
        add_action('template_redirect', array(&$this, 'alt_theme'));
        add_action('template_redirect', array(&$this, 'is_custom_404'), 1);
        add_action('template_redirect', array(&$this, 'page_redirect'), 10);
        add_action('customize_controls_print_styles', array(&$this, 'tweak_customizer_css'));
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('customize_register', array(&$this, 'tweak_customizer'));
        add_action('admin_head', array(&$this, 'admin_head'));
        add_action('admin_menu', array(&$this, 'remove_meta_boxes'));

        add_filter('get_custom_404', array(&$this, 'get_custom_404'), 1);
        add_filter('widget_title', array(&$this, 'tweak_widget_title'));
        add_filter('body_class', array(&$this, 'body_class_tweak'));
        add_filter('admin_body_class', array(&$this, 'admin_body_class_tweak'));
        add_filter('excerpt_more', array(&$this, 'new_excerpt_more'));
        add_filter('the_title', array(&$this, 'title_tweak'), 10, 1);
        add_filter('get_the_title', array(&$this, 'title_tweak'), 10, 2);
        add_filter('wp_title', array(&$this, 'title_tweak'), 10, 2);
        add_filter('show_post_locked_dialog', array(&$this, 'prevent_lock_dialog'), 99, 3);
        add_filter('admin_post_thumbnail_html', array(&$this, 'add_featured_image_instruction'), 100, 2);

        // allow html & shortcodes in category descriptions
        remove_filter('pre_term_description', 'wp_filter_kses');
        remove_filter('term_description', 'wp_kses_data');
        add_filter('term_description', 'do_shortcode', 11);
        update_option('image_default_link_type', 'file');

        // SITE SPECIFIC FILTERS
        if (60 == $this->blog_id || 184 == $this->blog_id) {
            // math site (and test site) - latex notation
            add_filter('the_content', array(&$this, 'convert_latex'));
            add_filter('comment_text', array(&$this, 'convert_latex'));
            add_filter('the_excerpt', array(&$this, 'convert_latex'));
        }
        if (173 == $this->blog_id) {
            // alumni site - reverse rss list order for harris rss events feeds
            add_action('wp_feed_options', array(&$this, 'reverseRSS'));
        }
        if (194 == $this->blog_id) {
            // wordpress help doc site - acf related content listed after post
            add_action('meerloop_after_content', array(&$this, 'display_related_content'));
        }
    }

    /**
     * Returns Human-readable name of CPT or term
     * @deprecated in M21
     */
    public function get_queried_obj_name() {
        $queried_obj = get_queried_object();

        return $queried_obj instanceof WP_Post_Type ? $queried_obj->label : $queried_obj->name;
    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Meerkat16 The *Singleton* instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Get next and previous elements in an array
     *
     * @param string $seed
     * @param mixed  $array
     * @param bool   $previous
     *
     * @return string Next/Previous element
     */
    static function get_adjacent_value($seed, $array, $previous = true, $wrap = false) {
        $current_index = array_search($seed, $array);

        // Find the index of the next/prev items
        if ($previous) {
            if ($wrap) {
                $output = $array[ ($current_index - 1 < 0) ? count($array) - 1 : $current_index - 1 ];
            } else {
                $output = $array[ ($current_index - 1 < 0) ? null : $current_index - 1 ];
            }
        } else {
            if ($wrap) {
                $output = $array[ ($current_index + 1 == count($array)) ? 0 : $current_index + 1 ];
            } else {
                $output = $array[ ($current_index + 1 == count($array)) ? null : $current_index + 1 ];
            }
        }

        return $output;
    }

    /**
     * Initiate theme setup
     */
    function init_theme() {
        $this->do_includes();

        // FOOTER INFO - SOCIAL & CONTACT
        $this->footer_defaults();

        // necessary user capability to get edit links on front end for sidebar, etc.
        define('CAPABILITY_THRESH', 'edit_theme_options');

        // THEME DETECTION
        switch (get_option('stylesheet')) {
            case 'meerkat16-home':
                $this->is_homepage_theme = true;
                break;
            case 'meerkat-magazine':
            case 'meerkat-people':
                $this->is_magazine_theme = true;
                break;
            case 'meerkat-ctd':
                $this->is_ctd_theme = true;
                break;
        }
    }

    /**
     * Initialize category and single post category options, which are
     * used in @Meerkat16::instance()->do_display().
     */
    private function load_cat_config() {
        global $cat;
        $this->is_page = is_page() ? true : false;
        if (! $this->is_page) {
			if (!is_numeric($cat)) {
				// $cat may not be set in some instances. try to get it from wp_query
                global $wp_query;
                $cat = $wp_query->queried_object->term_id;
			}
            $this->viewing_context = is_single() || is_page() ? 'single' : 'multi';
            $this->cat_opts        = Meerkat16_Categories_Options::instance()->get_options();
            $this->cat_config      = Meerkat16_Categories_Options::instance()->get_saved_config($cat);
        }
    }

    protected function do_includes() {
        // all includes, needs to be done after_setup_theme
        require_once(THEME_INC_PATH . '/index.php');
    }

    function footer_defaults() {
        global $social, $default_contact;

        // default urls for social media icons in footer
        // url = main williams version
        // dept = can departments have their own version?
        $social = array(
            'facebook'  => array(
                'url'   => 'http://www.facebook.com/williamscollege',
                'title' => 'Facebook',
                'dept'  => true
            ),
            'youtube'   => array(
                'url'   => 'http://www.youtube.com/williamscollege',
                'title' => 'YouTube',
                'dept'  => true
            ),
            'twitter'   => array(
                'url'   => 'http://twitter.com/williamscollege',
                'title' => 'Twitter',
                'dept'  => true
            ),
            'flickr'    => array(
                'url'   => 'http://www.flickr.com/photos/williamscollege/collections/',
                'title' => 'Flickr',
                'dept'  => true
            ),
            'tumblr'    => array(
                'url'   => false,
                'title' => 'Tumblr',
                'dept'  => true
            ),
            'linkedin'  => array(
                'url'   => false,
                'title' => 'Linked In',
                'dept'  => false
            ),
            'instagram' => array(
                'url'   => 'http://instagram.com/williamscollege',
                'title' => 'Instagram',
                'dept'  => true
            ),
            'feeds'     => array(
                'url'   => Wms_Server::instance()->www . '/feeds',
                'title' => 'Feeds',
                'dept'  => false
            )
        );

        $default_contact = array(
            'address_1'     => '',
            'address_2'     => '',
            'city'          => 'Williamstown',
            'state'         => 'MA',
            'zipcode'       => '01267',
            'phone'         => '',
            'fax'           => '',
            'contact_email' => '',
            'contact_notes' => '',
        );
    }

    public function pre_get_posts($query) {
        if (is_category() || is_archive()) {
            $cat      = get_queried_object_id();
            $cat_opts = get_option('wms_category_config_' . $cat);
            // Check for custom ordering in this category if ...
            if (
                $cat_opts['multi_orderby']                 // there might be a custom order
                && ! is_admin()                            // and we are on the front end
                && ! $this->do_display('facetwp_template') // and we are not using a custom template
            ) {
                // only impose the custom order if it differs from wordpress defaults
                if ( ! ($cat_opts['multi_orderby'] == 'date' && $cat_opts['multi_order_dir'] == 'DESC')) {
                    $query->set('orderby', $cat_opts['multi_orderby']);
                    $query->set('order', $cat_opts['multi_order_dir']);
                }
            }
        }
        return $query;
    }

    function load_dashicons_front_end() {
        wp_enqueue_style('dashicons');
    }

    /**
     * Twig helper function.
     *
     * Decide whether to display $item based on config options.
     *
     * {{ theme.do_display('author') }}
     *
     * @param      $item
     * @param bool $get_value Whether to echo the value of the key
     *
     * @return bool
     */
    public function do_display($item, $get_value = false) {
        $do_display = false;
        if ( ! $this->is_page) {
            $value = '';

			// make sure we have the config array
			if (!$this->cat_config) {
                $this->load_cat_config();
			}

            // construct key name for config option array - ie single_show_date
            $key = $this->viewing_context . '_show_' . $item;

            // Check to see if this context/option pairing is turned on or not
            if ($this->cat_config && $this->cat_config[ $key ]) {
                $do_display = true;
                $value      = $this->cat_config[ $key ];
            } else {
                // Nothing set - use defaults
                if ( ! $this->cat_config && $this->cat_opts[ $key ]['default']) {
                    $do_display = true;
                    $value      = $this->cat_opts[ $key ]['default'];
                }
            }
        }
        if ($get_value) {
            return $value;
        } else {
            return $do_display;
        }
    }

    /**
     * Capture output from a function which echos its output. Useful for storing output in a variable.
     * {{ theme.do_echo('function') }}
     *
     * @param string $function
     *
     * @return string
     */
    public function do_echo($function, $params = null) {
        ob_start();
        call_user_func($function, $params);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Prevents post lock dialog for super-admins, useful for walking clients through editing.
     *
     * @param bool  $show
     * @param mixed $post
     * @param mixed $user
     *
     * @return bool
     */
    function prevent_lock_dialog($show, $post, $user) {
        if (is_super_admin()) {
            return false;
        }

        return $show;
    }

    /**
     * adds pixel notes for header images on pages
     *
     * @param mixed $content
     * @param int   $id
     *
     * @return mixed
     */
    function add_featured_image_instruction($content, $id) {
        if (is_admin() && get_post_type() == 'page') {
            return $content .= '<p>Header images must be 1200px wide.</p>';
        }

        return $content;
    }

    /**
     * Adds extra info to title in certain contexts
     *
     * @param string $title
     * @param int    $id
     *
     * @return string
     * @see Magazine and Alumni News
     *
     */
    function title_tweak($title) {
        global $wp_query, $post, $post_type, $meermag;
        // We're going to repurpose this to also handle <title> element
        // by supplying stripped-down extra_title_info.
        $is_wp_title = current_filter() === 'wp_title' ? true : false;
        if ($is_wp_title && is_search()) {
            return $title;
        }
        if ( ! $is_wp_title && is_admin()) {
            return $title;
        }
        if ( ! $is_wp_title && $post_type == 'profile') {
            return $title;
        }
        if ( ! $is_wp_title && $this->is_ctd_theme) {
            global $meerkat_ctd;

            if ($post_type == 'event' || $meerkat_ctd->page_template == 'template-season.php' || $meerkat_ctd->page_template == 'template-season-music.php') {
                // get ACF fields to add to title
                $before_title = get_field('before_title');
                $after_title  = get_field('after_title');

                $html = '<div class="before-title">' . $before_title . '</div>' . $title;
                $html .= '<div class="after-title">' . $after_title . '</div>';

                return $html;
            } else {
                return $title;
            }
        } else if ($this->is_magazine_theme) {
            // add section & edition info to article title
            if ( ! $is_wp_title && (is_404() || is_single() || is_page() || is_admin())) {
                return $title;
            }

            // edition fully specified, no need for extra info
            if ( ! $is_wp_title && $wp_query->query_vars['volume_year'] && $wp_query->query_vars['volume_issue']) {
                return $title;
            }

            if (Wms_Server::instance()->subdomain === 'magazine' && ! is_search() && ! $is_wp_title) {
                return $title;
            }

            $extra_title_info = $show_section = $show_edition = false;
            $show_title       = true;

            // magazine only does title mods on search page
            if (is_search()) {
                $extra_title_info = $show_section = $show_edition = true;
            }

            if (is_category()) {
                $extra_title_info = $show_edition = true;
                if (strpos($_SERVER['REQUEST_URI'], '/category/') == 0) {
                    // url is like blah.williams.edu/category/blah, so we know which section we are in
                } else {
                    $show_section = true;
                }
            }

            // we are looking at a volume year, volume issue, or class year
            if (is_tax()) {
                $extra_title_info = $show_edition = $show_section = true;
                $show_title       = false;
                if ($wp_query->query_vars['class_year']) {
                    $show_section = false;
                    $show_edition = false;
                    $show_title   = true;
                    // We want to add the nice name to the title for class year portal pages.
                    $portal_pretty_names = array(
                        'photo'      => 'Photos',
                        'class_note' => 'Class Notes',
                        'wedding'    => 'Weddings',
                        'birth'      => 'Births &amp; Adoptions',
                        'obituary'   => 'Obituaries'
                    );
                    $portal              = '';
                    foreach ($_GET as $k => $v) {
                        if (isset($portal_pretty_names[ $k ])) {
                            $portal = $portal_pretty_names[ $k ] . ' | ';
                        }
                    }
                    $title = 'Class of ' . get_queried_object()->slug . ' | ' . $portal;
                }
                // Class of YYYY |
            }

            // tweak entire title on article pages
            if ($is_wp_title && is_single()) {
                $extra_title_info = $show_section = $show_edition = true;
            }
            // if not a single article, remove the default title because it's redundant
            if ($is_wp_title && ! is_404() && ! is_tax() && ! is_page() && ! is_single() && ! $meermag->is_front_page()) {
                $show_title       = false;
                $extra_title_info = $show_section = $show_edition = true;
            }
            // if this is the front page, we don't care about section
            if ($is_wp_title && $meermag->is_front_page()) {
                if ($_SERVER['REQUEST_URI'] == '/') {
                    return $title;
                }
                $extra_title_info = $show_edition = true;
                $show_title       = $show_section = false;
            }

            if ($extra_title_info) {
                // list section & issue along with post title
                // get section name
                $post_cats = get_the_category($post->ID);
                $post_cat  = reset($post_cats);
                $post_cat  = $post_cat->name;

                $issue = $meermag->get_post_issue($post->ID);
                $year  = $meermag->get_post_year($post->ID);

                if ($post_cat || $issue) {
                    $extra = $is_wp_title ? '' : '</a> <span class="title-extra">';
                    if ($show_section) {
                        $extra .= $is_wp_title ? $post_cat : ' <span class="extra-section">' . $post_cat . '</span>';
                    }
                    if ($show_section && $show_edition) {
                        $extra .= $is_wp_title ? ' | ' : '<span class="extra-sep"></span>';
                    }
                    if ($show_edition) {
                        $extra .= $is_wp_title ? $issue['name'] . ' ' . $year : ' <span class="extra-edition">' . $issue['name'] . ' ' . $year . '</span>';
                    }
                    if ($is_wp_title && ($show_section || $show_edition)) {
                        $extra .= ' | ';
                    } else if ( ! $is_wp_title) {
                        $extra .= '</span><!-- .title-extra -->';
                    }

                    return $show_title ? $title . $extra : $extra;
                }
            }
        }

        return $title;
    }

    // TITLE
    function title() {
        // outputs content between <title> and </title>
        global $page, $paged, $profile_user, $is_directory, $directory_title;
        if ($is_directory) {
            if ($directory_title) {
                echo $directory_title . ' | ';
            } else {
                echo 'Search & Directories | ';
            }
        } else if ($profile_user) {
            /* if a post is not found, but it's still a valid profile user name, we don't want "Page not found"
            as the title since there will be a "page" shown-  override it here */
            echo $profile_user . ' | ';
        } else {
            wp_title('|', true, 'right');
        }

        // Add the blog name.
        bloginfo('name');

        // Add the blog description for the home/front page.
        $site_description = get_bloginfo('description', 'display');
        if ($site_description && (is_home() || is_front_page())) {
            echo " | $site_description";
        }

        // Add a page number if necessary:
        if ($paged >= 2 || $page >= 2) {
            echo ' | ' . sprintf(__('Page %s', '_s'), max($paged, $page));
        }
    }

    function display_related_content() {
        // at the end of a post, for the wordpress documentation site, display a list of related links.
        // content comes from a repeater field in acf.
        if (have_rows('related_content')) {
            echo '<div class="related-content callout">';
            echo '<h2>Related Content</h2><ul>';
            while (have_rows('related_content')) : the_row();
                $url   = get_sub_field('related_content_url');
                $label = get_sub_field('related_content_label');
                echo '<li><a class="raquo" href="' . $url . '">' . $label . '</a></li>';
            endwhile;
            echo '</ul></div>';
        }
    }

    /**
     * @param $title
     *
     * @return string
     *
     * @todo Register widget in Meerkat16 fixes this problem. Do we need this function?
     */
    function tweak_widget_title($title) {
        // blank titles break html (before_title and after_title of register_widget do not trigger)
        if ( ! $title) {
            $title = '&nbsp;';
        }

        return $title;
    }

    function is_custom_404() {
        global $wp_query; // Set page title for header.

        if (isset($wp_query->query['name'])) {
            $queried_post = get_page_by_path($wp_query->query['name'], null, 'post');
        }

        $queried_page = get_queried_object();

        if (isset($queried_post) || isset($queried_page)) {
            $queried_object = $queried_page ? $queried_page : $queried_post;
        }

        if (isset($queried_object->post_status) && $queried_object->post_status === 'private' && ! current_user_can('read_private_pages')) {
            header("HTTP/1.0 200 OK");
            $per_page_content = get_field('private_page_content', $queried_object->ID);
            $per_site_content = get_field('private_page_content', 'option');
            if ($per_page_content || $per_site_content) {
                $wp_query->is_404             = false;
                $wp_query->is_page            = $queried_object->post_type === 'page';
                $wp_query->is_single          = $queried_object->post_type === 'post';
                $wp_query->is_singular        = true;
                $queried_object->post_content = $per_page_content ? $per_page_content : $per_site_content;
                $wp_query->queried_object     = $queried_object;
            }
        }
    }

    /**
     * ACF Private page custom content / Default 404
     *
     * @param $post
     *
     * @return WP_Post
     */
    function get_custom_404($post) {
        global $wp_query;
        $queried_object = isset($wp_query->post) ? $wp_query->post : $wp_query->queried_object;
        if ($queried_object->ID === $post->ID
            && $queried_object->post_status === 'private'
            && ! current_user_can('read_private_pages')
            || (current_user_can('read_private_pages') && is_preview())
        ) {
            $post->post_title   = $queried_object->post_title;
            $post->post_content = $queried_object->post_content;
        }
        if (post_password_required($post)) {
            $post->post_content = get_the_password_form($post);
        }

        return $post;
    }

    //----------------- THEME OUTPUT & PAGE OPTIONS ----------------//

    /** M21: Deprecated in favor of lib/class.favicons, don't transfer */
    function get_favicon() {
        $html = '<link rel="shortcut icon" href="' . THEME_IMG_URL . '/favicon.ico' . '">';

        return $html;
    }

    // FAVICO

    function get_seo() {
        // add meta descriptions for pages
        $desc_tag = '<meta name="description" content="';
        $desc_val = '';

        if (is_front_page()) {
            $desc_val = get_field('site_meta_desc', 'options');
        } else {
            // regular page or post
            $desc_val = get_field('page_meta_desc');
        }
        if ($desc_val) {
            echo "\n" . $desc_tag . esc_html($desc_val) . '"/>' . "\n";
        }
    }

    // SEO & META

    function page_redirect() {
        /*
		 * 404s get special handling on www. There are legitmate URLs that need to be
		 * passed through to the web.williams.edu server (which used to be the www
		 * server way back when)
        */
        if (is_404() && strpos(Wms_Server::instance()->name, 'www') === 0) {
            $url = 'https://web.williams.edu' . Wms_Server::instance()->request_uri;
            // Does URL exist on web.williams.edu server?
            $file_headers = @get_headers($url, 1);
            if (is_array($file_headers) && ! strpos($file_headers[0], '404') && ! strpos($file_headers[0], '403')) {
                // Target server likely knows about this URL so redirect user
                //
                // Log valid URLs
                //
                // $files = wp_upload_dir();
                // Disabling for now; collected enough data
                // @file_put_contents($files['path'].'/redirect_log.txt', "$url\n", FILE_APPEND);
                //
                // Try to detect post data
                //
                if (Wms_Server::instance()->request_method == 'POST') {
                    header("HTTP/1.0 307 Temporary redirect");
                } else {
                    header("HTTP/1.0 301 Moved Permanently");
                }
                header('Location: ' . $url);
                die();
            }
            // Bogus URL, no redirect
        }
        /*
		 * handles page option of redirecting to another url
		 */
        if (is_single() || is_page()) {
            if ($link = get_field('page_redirect')) {
                wp_redirect($link, 301);
                exit;
            }
        }
    }

    // REDIRECT

    function campus_only_check($post_ID) {
        // checks to see if the campus only restriction (custom field) is set on a page or a post.
        $campus_only = get_field('campus_only', $post_ID);
        if ($campus_only) {
            if (is_off_campus()) {
                // Page is designated on-campus only, do not display
                return false;
            }
        }

        return true;
    }

    // CAMPUS ONLY

    function contact_info() {
        // return saved or default values for contact information
        global $default_contact;
        $site_contact = array();
        foreach ($default_contact as $field => $default) {
            $val = get_field($field, 'options');
            if ( ! $val) {
                $val = $default_contact[ $field ];
            }
            $site_contact[ $field ] = $val;
        }

        return $site_contact;
    }

    // CONTACT

    function new_excerpt_more($more) {
        // this is handled in the loop, override wp default
        return false;
    }

    // request would look like http://blah.williams.edu/category?theme=plain
    function alt_theme() {
        $allowed_themes = array('plain');
        if (isset($_GET['theme']) && in_array($_GET['theme'], $allowed_themes)) {
            get_template_part('template', $_GET['theme']);
            exit;
        }
    }

    //----------------- END THEME OUTPUT & PAGE OPTIONS----------------//

    //----------------- ALTERNATIVE CONTENT DELIVERY ----------------//

    function admin_init() {
        include_once(WMS_EXT_LIB . '/form_utils.php');        // builds html form elements
        wp_register_style('meerkat-admin', THEME_CSS_URL . '/admin.css');
        wp_register_script('meerkat-admin', THEME_JS_URL . '/lib/admin.js', array('jquery'), '', true);
    }

    //----------------- END ALTERNATIVE CONTENT DELIVERY ----------------//

    //----------------- ADMIN & SITE OPTIONS ----------------//

    // INIT

    function admin_head() {
        // location interpreted as releative to theme root - no full URLS
        add_editor_style('css/editor-style.css');
        add_editor_style('css/content.css');

        wp_enqueue_script('jquery');
        wp_enqueue_style('meerkat-admin');
        wp_enqueue_script('meerkat-admin');
    }

    // HEAD

    function tweak_customizer_css() {
        // remove problematic parts of the customizer for non-super-admins (stuff that couldn't be unset accurately via php):
        // header upload/removal
        // widget editing interface (does not include all of our customizations yet)
        if ( ! is_super_admin()) {
            ?>
            <!--Customizer CSS-->
            <style type="text/css">
                #customize-controls #customize-control-header_image .actions {
                    display: none;
                }

                #customize-controls #accordion-panel-widgets,
                #customize-controls #accordion-panel-widgets * {
                    display: none;
                }
            </style>
            <!--/Customizer CSS-->
            <?php
        }
    }

    // CUSTOMIZER

    function tweak_customizer($wp_customize) {
        // remove problematic parts of the customizer for non-super-admins
        if ( ! is_super_admin()) {
            $wp_customize->remove_section('nav'); // we don't want people editing the buckets outside of the menu editor context atm
        }
    }

    function remove_meta_boxes() {
        // hides custom fields boxes on page & post edit interface. they are confusing and cluttery
        if (function_exists('get_field') && ! get_field('show_custom_fields', 'options')) {
            remove_meta_box('postcustom', 'post', 'normal');
            remove_meta_box('postcustom', 'page', 'normal');
        }
    }

    // META BOXES

    function is_google_search() {
        if (Meerkat_Search::instance()->isWmsSearch()) {
            header("HTTP/1.1 200 OK");
            global $wp_query;
            $this->is_google_search = true;
            $wp_query->is_404       = false;
        }
    }

    // GOOGLE SEARCH RESULTS

    function body_begin() {
        $this->load_cat_config();
    }

    // profile page not-404
    function is_profile_page() {
        $page_path = explode('/', $_SERVER['REQUEST_URI']);
        if ($page_path[1] == 'profile') {
            $this->is_profile_page = true;
        } else if ($page_path[2] == 'profile') {
            // Check if we are on a subdirectory site
            $current_site = get_site(get_current_blog_id());
            if ($page_path[1] == trim($current_site->path, '/')) {
                $this->is_profile_page = true;
            }
        }
        if ($this->is_profile_page == true) {
            header("HTTP/1.1 200 OK");
            global $wp_query;
            $wp_query->is_404      = false;
        }
    }


    function body_class_tweak($classes) {
        // unset home class on body for google searches
        global $is_wms_profile, $meerkat_mobile, $unix_from_url;
        $classes [] = 'meerkat-16';
        if ($this->is_google_search) {
            if ($classes[0] == 'home') {
                unset($classes[0]);
            }
            $classes[] = 'directory';
        }
        if ($this->is_profile_page) {
            unset($classes);
            $classes[] = 'wms-profile';
            $classes[] = 'uid-' . $unix_from_url;
        }
        if ($this->is_magazine_theme) {
            $classes[] = 'meerkat-magazine';
        }
        if ($meerkat_mobile) {
            $classes[] = 'meerkat-mobile';
        }

        return $classes;
    }

    // BODY CLASS

    /**
     * @return string
     */
    function admin_body_class_tweak() {
        return $this->is_magazine_theme ? 'meerkat-magazine' : '';
    }

    // ADMIN BODY CLASS

    function convert_latex($content) {
        // convert williams latex notation: $\forall/$  to standard wp latex notation: [latex]\forall[/latex]
        $pattern     = '|\$([^\$]*?)/\$|';
        $replacement = '[latex]' . "$1" . '[/latex]';
        $content     = preg_replace($pattern, $replacement, $content);

        return str_replace('$$', '$', $content);
    }

    function reverseRSS(&$simple_pie) {
        // reverse listing order for rss widget, to support event listings (soonest date first)
        $simple_pie->order_by_date = 0;
    }

    function dequeue_jquery() {
        wp_dequeue_script('jquery');
        wp_deregister_script('jquery');
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private
    function __clone() {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private
    function __wakeup() {
    }
} // end class

//----- INSTANTIATE -----//
Meerkat16::instance();
