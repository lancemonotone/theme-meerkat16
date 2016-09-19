<?php
// DEFINES
define( 'WMS_PHP_LIB', '/web/lib/' );

define( 'THEME_PATH_CSS', TEMPLATEPATH . '/assets/css/' );
define( 'THEME_LIB_PATH', TEMPLATEPATH . '/lib/' );
define( 'THEME_INC_PATH', THEME_LIB_PATH . 'inc/' );
define( 'THEME_WIDGETS_PATH', THEME_LIB_PATH . 'widgets/' );
define( 'THEME_PLUGINS_PATH', THEME_LIB_PATH . 'plugins/' );

define( 'CHILD_PATH_CSS', STYLESHEETPATH . '/assets/css/' );
define( 'CHILD_LIB_PATH', STYLESHEETPATH . '/lib/' );
define( 'CHILD_INC_PATH', CHILD_LIB_PATH . 'inc/' );
define( 'CHILD_WIDGETS_PATH', CHILD_LIB_PATH . 'widgets/' );
define( 'CHILD_PLUGINS_PATH', CHILD_LIB_PATH . 'plugins/' );

define( 'THEME_URL', get_template_directory_uri() . '/' );
define( 'CHILD_URL', get_stylesheet_directory_uri() . '/' );
define( 'SECURE_THEME_URL', str_replace( 'http', 'https', THEME_URL ) );
define( 'CSS_URL', THEME_URL . 'assets/css/' );
define( 'JS_URL', THEME_URL . 'assets/js/' );
define( 'IMG_URL', THEME_URL . 'assets/img/' );
define( 'THEME_PLUGINS_URL', THEME_URL . 'lib/plugins/' );

define( 'QUICKLINKS_URL', '/ql/' );   // this url has to be on a subdomain with a legit certificate
define( 'ADDTHIS_ID', 'ra-512bca8860afc729' );

define( 'WWW_BLOG_ID', 93 );
define( 'CAMPUS_IP_PREFIX', '137.165' );
define( 'WWW_BLOG_URL', Wms_Server::instance()->www );
define( 'GIVING_BLOG_URL', 'http://giving.' . Wms_Server::instance()->domain );

// INCLUDES
require_once( WMS_EXT_LIB . '/HTTPrequest.php' ); // calls content from http location ************

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
        $saved_cagetory_config,
        $category_config_options,
        $viewing_context,
        $is_page,
        $is_google_search,
        $is_profile_page;

    protected function __construct(){
        global $blog_id;
        $this->blog_id = $blog_id;
        $this->add_actions();
    }

    /**
     * Hooks and filters
     */
    protected function add_actions(){
        add_action( 'after_setup_theme', array( &$this, 'init_theme' ), 10 );
        add_action( 'template_redirect', array( &$this, 'page_redirect' ), 10 );
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, 'load_dashicons_front_end' ) );
        add_action( 'meerkat_body_begin', array( &$this, 'body_begin' ) );
        add_action( 'meerloop_before_loop', array( &$this, 'meerloop_before_loop' ), 1 );
        add_action( 'template_redirect', array( &$this, 'is_google_search' ), 1 );
        add_action( 'template_redirect', array( &$this, 'is_profile_page' ), 1 );
        add_action( 'template_redirect', array( &$this, 'alt_theme' ) );
        add_action( 'customize_controls_print_styles', array( &$this, 'tweak_customizer_css' ) );
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
        add_action( 'customize_register', array( &$this, 'tweak_customizer' ) );
        add_action( 'admin_head', array( &$this, 'admin_head' ) );
        add_action( 'admin_menu', array( &$this, 'remove_meta_boxes' ) );

        add_filter( 'widget_title', array( &$this, 'tweak_widget_title' ) );
        add_filter( 'body_class', array( &$this, 'body_class_tweak' ) );
        add_filter( 'admin_body_class', array( &$this, 'admin_body_class_tweak' ) );
        add_filter( 'excerpt_more', array( &$this, 'new_excerpt_more' ) );
        add_filter( 'the_title', array( &$this, 'title_tweak' ), 10, 2 );
        add_filter( 'get_the_title', array( &$this, 'title_tweak' ), 10, 2 );
        add_filter( 'wp_title', array( &$this, 'title_tweak' ), 10, 2 );
        add_filter( 'show_post_locked_dialog', array( &$this, 'prevent_lock_dialog' ), 99, 3 );

        // allow html & shortcodes in category descriptions
        remove_filter( 'pre_term_description', 'wp_filter_kses' );
        remove_filter( 'term_description', 'wp_kses_data' );
        add_filter( 'term_description', 'do_shortcode', 11 );
        update_option( 'image_default_link_type', 'file' );

        // SITE SPECIFIC FILTERS
        if( 60 == $this->blog_id || 184 == $this->blog_id ){
            // math site (and test site) - latex notation
            add_filter( 'the_content', array( &$this, 'convert_latex' ) );
            add_filter( 'comment_text', array( &$this, 'convert_latex' ) );
            add_filter( 'the_excerpt', array( &$this, 'convert_latex' ) );
        }
        if( 173 == $this->blog_id ){
            // alumni site - reverse rss list order for harris rss events feeds
            add_action( 'wp_feed_options', array( &$this, 'reverseRSS' ) );
        }
        if( 194 == $this->blog_id ){
            // wordpress help doc site - acf related content listed after post
            add_action( 'meerloop_before_content', array( &$this, 'display_related_content' ) );
        }
    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Meerkat16 The *Singleton* instance.
     */
    public static function instance(){
        if( null === static::$instance ){
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
    static function get_adjacent_value( $seed, $array, $previous = true, $wrap = false ){
        $current_index = array_search( $seed, $array );

        // Find the index of the next/prev items
        if( $previous ){
            if( $wrap ){
                $output = $array[ ( $current_index - 1 < 0 ) ? count( $array ) - 1 : $current_index - 1 ];
            } else {
                $output = $array[ ( $current_index - 1 < 0 ) ? null : $current_index - 1 ];
            }
        } else {
            if( $wrap ){
                $output = $array[ ( $current_index + 1 == count( $array ) ) ? 0 : $current_index + 1 ];
            } else {
                $output = $array[ ( $current_index + 1 == count( $array ) ) ? null : $current_index + 1 ];
            }
        }

        return $output;
    }

    /**
     * Initiate theme setup
     */
    function init_theme(){
        $this->do_includes();

        // FOOTER INFO - SOCIAL & CONTACT
        $this->footer_defaults();

        // necessary user capability to get edit links on front end for sidebar, etc.
        define( 'CAPABILITY_THRESH', 'edit_theme_options' );

        // THEME DETECTION
        switch( get_option( 'stylesheet' ) ){
            case 'meerkat-homepage':
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

    protected function do_includes(){
        // all includes, needs to be done after_setup_theme
        require_once( THEME_INC_PATH . 'index.php' );
    }

    function footer_defaults(){
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
        );
    }

    public function pre_get_posts( $query ){
        if( is_category() ){
            $cat      = get_queried_object_id();
            $cat_opts = get_option( 'wms_category_config_' . $cat );
            if( $cat_opts[ 'multi_orderby' ] ){
                // skip if we've just got the default options
                if( ! ( $cat_opts[ 'multi_orderby' ] == 'date' && $cat_opts[ 'multi_order_dir' ] == 'DESC' ) ){
                    /*$custom_args = array(
                        'cat'     => $cat,
                        'orderby' => $cat_opts['multi_orderby'],
                        'order'   => $cat_opts['multi_order_dir']
                    );
                    $query->query_vars = array_merge( $query->query_vars, $custom_args );*/
                    $query->set( 'orderby', $cat_opts[ 'multi_orderby' ] );
                    $query->set( 'order', $cat_opts[ 'multi_order_dir' ] );
                }
            }
        }

        return $query;
    }

    function load_dashicons_front_end(){
        wp_enqueue_style( 'dashicons' );
    }

    /**
     * Run before loop to initialize category and single post category options, which are
     * used in @Meerkat16::instance()->do_display().
     */
    function meerloop_before_loop(){
        $this->is_page = is_page() ? true : false;
        if( ! $this->is_page ){
            $this->viewing_context         = is_single() || is_page() ? 'single' : 'multi';
            $this->category_config_options = Meerkat16_Categories_Options::instance()->get_options();
            $this->saved_cagetory_config   = Meerkat16_Categories_Options::instance()->get_saved_config();
        }
    }

    /**
     * Twig helper function.
     *
     * Decide whether to display $item based on config options.
     *
     * {% if meerkat16.do_display('author') %}
     *
     * @param $item
     *
     * @return bool
     */
    public function do_display( $item ){
        if( ! $this->is_page ){
            // construct key name for config option array - ie single_show_date
            $key = $this->viewing_context . '_show_' . $item;

            // check to see if this context/option pairing is turned on or not
            if( $this->saved_cagetory_config && $this->saved_cagetory_config[ $key ] ){
                return true;
            } else {
                // nothing set- use defaults
                if( ! $this->saved_cagetory_config && $this->category_config_options[ $key ][ 'default' ] ){
                    return true;
                }
            }
        }

        return false;
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
    function prevent_lock_dialog( $show, $post, $user ){
        if( is_super_admin() ){
            return false;
        }

        return $show;
    }

    /**
     * Adds extra info to title in certain contexts
     *
     * @see Magazine and Alumni News
     *
     * @param string $title
     * @param int    $id
     *
     * @return string
     */
    function title_tweak( $title, $id ){
        global $wp_query, $post, $post_type, $meermag;
        // We're going to repurpose this to also handle <title> element
        // by supplying stripped-down extra_title_info.
        $is_wp_title = current_filter() === 'wp_title' ? true : false;
        if( $is_wp_title && is_search() ){
            return $title;
        }
        if( ! $is_wp_title && is_admin() ){
            return $title;
        }
        if( ! $is_wp_title && $post_type == 'profile' ){
            return $title;
        }

        if( ! $is_wp_title && $this->is_ctd_theme ){
            global $meerkat_ctd;

            if( $post_type == 'event' || $meerkat_ctd->page_template == 'template-season.php' || $meerkat_ctd->page_template == 'template-season-music.php' ){
                // get ACF fields to add to title
                $before_title = get_field( 'before_title' );
                $after_title  = get_field( 'after_title' );

                $html = '<div class="before-title">' . $before_title . '</div>' . $title;
                $html .= '<div class="after-title">' . $after_title . '</div>';

                return $html;
            } else {
                return $title;
            }
        } else if( $this->is_magazine_theme ){
            // add section & edition info to article title
            if( ! $is_wp_title && ( is_404() || is_single() || is_page() || is_admin() ) ){
                return $title;
            }

            // edition fully specified, no need for extra info
            if( ! $is_wp_title && $wp_query->query_vars[ 'volume_year' ] && $wp_query->query_vars[ 'volume_issue' ] ){
                return $title;
            }

            if( Wms_Server::instance()->subdomain === 'magazine' && ! is_search() && ! $is_wp_title ){
                return $title;
            }

            $extra_title_info = $show_section = $show_edition = false;
            $show_title       = true;

            // magazine only does title mods on search page
            if( is_search() ){
                $extra_title_info = $show_section = $show_edition = true;
            }

            if( is_category() ){
                $extra_title_info = $show_edition = true;
                if( strpos( $_SERVER[ 'REQUEST_URI' ], '/category/' ) == 0 ){
                    // url is like blah.williams.edu/category/blah, so we know which section we are in
                } else {
                    $show_section = true;
                }
            }

            // we are looking at a volume year, volume issue, or class year
            if( is_tax() ){
                $extra_title_info = $show_edition = $show_section = true;
                $show_title       = false;
                if( $wp_query->query_vars[ 'class_year' ] ){
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
                    foreach( $_GET as $k => $v ){
                        if( isset( $portal_pretty_names[ $k ] ) ){
                            $portal = $portal_pretty_names[ $k ] . ' | ';
                        }
                    }
                    $title = 'Class of ' . get_queried_object()->slug . ' | ' . $portal;
                }
                // Class of YYYY |
            }

            // tweak entire title on article pages
            if( $is_wp_title && is_single() ){
                $extra_title_info = $show_section = $show_edition = true;
            }
            // if not a single article, remove the default title because it's redundant
            if( $is_wp_title && ! is_404() && ! is_tax() && ! is_page() && ! is_single() && ! $meermag->is_front_page() ){
                $show_title       = false;
                $extra_title_info = $show_section = $show_edition = true;
            }
            // if this is the front page, we don't care about section
            if( $is_wp_title && $meermag->is_front_page() ){
                if( $_SERVER[ 'REQUEST_URI' ] == '/' ){
                    return $title;
                }
                $extra_title_info = $show_edition = true;
                $show_title       = $show_section = false;
            }

            if( $extra_title_info ){
                // list section & issue along with post title
                // get section name
                $post_cats = get_the_category( $post->ID );
                $post_cat  = reset( $post_cats );
                $post_cat  = $post_cat->name;

                $issue = $meermag->get_post_issue( $post->ID );
                $year  = $meermag->get_post_year( $post->ID );

                if( $post_cat || $issue ){
                    $extra = $is_wp_title ? '' : '</a> <span class="title-extra">';
                    if( $show_section ){
                        $extra .= $is_wp_title ? $post_cat : ' <span class="extra-section">' . $post_cat . '</span>';
                    }
                    if( $show_section && $show_edition ){
                        $extra .= $is_wp_title ? ' | ' : '<span class="extra-sep"></span>';
                    }
                    if( $show_edition ){
                        $extra .= $is_wp_title ? $issue[ 'name' ] . ' ' . $year : ' <span class="extra-edition">' . $issue[ 'name' ] . ' ' . $year . '</span>';
                    }
                    if( $is_wp_title && ( $show_section || $show_edition ) ){
                        $extra .= ' | ';
                    } else if( ! $is_wp_title ){
                        $extra .= '</span><!-- .title-extra -->';
                    }

                    return $show_title ? $title . $extra : $extra;
                }
            }
        }

        return $title;
    }

    // TITLE
    function title(){
        // outputs content between <title> and </title>
        global $page, $paged, $profile_user, $is_directory, $directory_title;
        if( $is_directory ){
            if( $directory_title ){
                echo $directory_title . ' | ';
            } else {
                echo 'Search & Directories | ';
            }
        } else if( $profile_user ){
            /* if a post is not found, but it's still a valid profile user name, we don't want "Page not found"
            as the title since there will be a "page" shown-  override it here */
            echo $profile_user . ' | ';
        } else {
            wp_title( '|', true, 'right' );
        }

        // Add the blog name.
        bloginfo( 'name' );

        // Add the blog description for the home/front page.
        $site_description = get_bloginfo( 'description', 'display' );
        if( $site_description && ( is_home() || is_front_page() ) ){
            echo " | $site_description";
        }

        // Add a page number if necessary:
        if( $paged >= 2 || $page >= 2 ){
            echo ' | ' . sprintf( __( 'Page %s', '_s' ), max( $paged, $page ) );
        }
    }

    function display_related_content(){
        // at the end of a post, for the wordpress documentation site, display a list of related links.
        // content comes from a repeater field in acf.
        if( have_rows( 'related_content' ) ){
            echo '<div class="related-content callout">';
            echo '<h2>Related Content</h2><ul>';
            while( have_rows( 'related_content' ) ) : the_row();
                $url   = get_sub_field( 'related_content_url' );
                $label = get_sub_field( 'related_content_label' );
                echo '<li><a class="raquo" href="' . $url . '">' . $label . '</a></li>';
            endwhile;
            echo '</ul></div>';
        }
    }

    // SOCIAL & CONTACT INFO

    /**
     * @param $title
     *
     * @return string
     *
     * @todo Register widget in Meerkat16 fixes this problem. Do we need this function?
     */
    function tweak_widget_title( $title ){
        // blank titles break html (before_title and after_title of register_widget do not trigger)
        if( ! $title ){
            $title = '&nbsp;';
        }

        return $title;
    }

    /**
     * ACF Private page custom content / Default 404
     *
     * @return array
     */
    function get_404_content(){
        global $wp_query; // Set page title for header.
        $queried_page = get_queried_object();
        $queried_post = get_page_by_path( $wp_query->query[ 'name' ], null, 'post' );
        if( $queried_post || $queried_page ){
            $queried_object = $queried_page ? $queried_page : $queried_post;
        }
        if( $queried_object->post_status === 'private' && ! current_user_can( 'read_private_pages' ) ){
            header( "HTTP/1.0 200 OK" );
            $page_title       = $queried_object->post_title;
            $content          = '';
            $per_page_content = get_field( 'private_page_content', $queried_object->ID );
            $per_site_content = get_field( 'private_page_content', 'option' );
            if( $per_page_content || $per_site_content ){
                $wp_query->is_404    = false;
                $wp_query->is_page   = $queried_object->post_type === 'page';
                $wp_query->is_single = $queried_object->post_type === 'post';
                $content             = $per_page_content ? $per_page_content : $per_site_content;
            }
        } else {
            $page_title = __( 'Page Not Found' );
            $content    = __( 'Sorry, we couldn\'t find that page.' );
        }

        return array( $page_title, $content );
    }

    //----------------- THEME OUTPUT & PAGE OPTIONS ----------------//

    function get_favico(){
        $html = '<link rel="shortcut icon" href="';
        if( $favico = get_field( 'favico', 'options' ) ){
            $html .= $favico;
        } else {
            $html .= IMG_URL . 'favicon.ico';
        }
        $html .= '">';

        return $html;
    }

    // FAVICO

    function get_seo(){
        // add meta descriptions for pages
        $desc_tag = '<meta name="description" content="';
        $desc_val = '';

        if( is_front_page() ){
            $desc_val = get_field( 'site_meta_desc', 'options' );
        } else {
            // regular page or post
            $desc_val = get_field( 'page_meta_desc' );
        }
        if( $desc_val ){
            echo "\n" . $desc_tag . esc_html( $desc_val ) . '"/>' . "\n";
        }
    }

    // SEO & META

    function page_redirect(){
        // 404s get special handling on www
        if (is_404() && strpos($_SERVER['SERVER_NAME'], 'www') === 0) {
            $url = 'http://web.williams.edu' . $_SERVER['REQUEST_URI'];
            // try to detect post data
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                header("HTTP/1.0 307 Temporary redirect");
            }
            header('Location: ' . $url);
            die();
        }
        // handles page option of redirecting to another url
        if( is_single() || is_page() ){
            if( $link = get_field( 'page_redirect' ) ){
                wp_redirect( $link, 301 );
                exit;
            }
        }
    }

    // REDIRECT

    function campus_only_check( $post_ID ){
        // checks to see if the campus only restriction (custom field) is set on a page or a post.
        $campus_only = get_field( 'campus_only', $post_ID );
        if( $campus_only ){
            if( is_off_campus() ){
                // Page is designated on-campus only, do not display
                return false;
            }
        }

        return true;
    }

    // CAMPUS ONLY

    function contact_info(){
        // return saved or default values for contact information
        global $default_contact;
        $site_contact = array();
        foreach( $default_contact as $field => $default ){
            $val = get_field( $field, 'options' );
            if( ! $val ){
                $val = $default_contact[ $field ];
            }
            $site_contact[ $field ] = $val;
        }

        return $site_contact;
    }

    // CONTACT

    function new_excerpt_more( $more ){
        // this is handled in the loop, override wp default
        return false;
    }

    function alt_theme(){
        $allowed_themes = array( 'plain' );
        $alt_theme      = '';

        // request would look like http://blah.williams.edu/category?theme=plain
        $alt_theme = $_GET[ 'theme' ];

        if( $alt_theme && in_array( $alt_theme, $allowed_themes ) ){
            get_template_part( 'template', $alt_theme );
            exit;
        }
    }

    //----------------- END THEME OUTPUT & PAGE OPTIONS----------------//

    //----------------- ALTERNATIVE CONTENT DELIVERY ----------------//

    function admin_init(){
        include_once( WMS_EXT_LIB . '/form_utils.php' );        // builds html form elements
        wp_register_style( 'meerkat-admin', CSS_URL . 'admin.css' );
        wp_register_script( 'meerkat-admin', JS_URL . 'lib/admin.js', array( 'jquery' ), '', true );
    }

    //----------------- END ALTERNATIVE CONTENT DELIVERY ----------------//

    //----------------- ADMIN & SITE OPTIONS ----------------//

    // INIT

    function admin_head(){
        // location interpreted as releative to theme root - no full URLS
        add_editor_style( 'css/editor-style.css' );
        add_editor_style( 'css/content.css' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_style( 'meerkat-admin' );
        wp_enqueue_script( 'meerkat-admin' );
    }

    // HEAD

    function tweak_customizer_css(){
        // remove problematic parts of the customizer for non-super-admins (stuff that couldn't be unset accurately via php):
        // header upload/removal
        // widget editing interface (does not include all of our customizations yet)
        if( ! is_super_admin() ){
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

    function tweak_customizer( $wp_customize ){
        // remove problematic parts of the customizer for non-super-admins
        if( ! is_super_admin() ){
            $wp_customize->remove_section( 'nav' ); // we don't want people editing the buckets outside of the menu editor context atm
        }
    }

    function remove_meta_boxes(){
        // hides custom fields boxes on page & post edit interface. they are confusing and cluttery
        if( function_exists( 'get_field' ) && ! get_field( 'show_custom_fields', 'options' ) ){
            remove_meta_box( 'postcustom', 'post', 'normal' );
            remove_meta_box( 'postcustom', 'page', 'normal' );
        }
    }

    // META BOXES

    function is_google_search(){
        if( Meerkat_Search::instance()->isWmsSearch() ){
            header("HTTP/1.1 200 OK");
            global $wp_query;
            $this->is_google_search = true;
            $wp_query->is_404 = false;
        }
    }
    
    // GOOGLE SEARCH RESULTS

    function body_begin(){
        if( $this->is_magazine_theme ){
            echo '<div class="mag-rule1"></div>';
            echo '<div class="mag-rule2"></div>';
        }
    }
    
    ///profile page not-404     
	function is_profile_page(){
		if(substr($_SERVER['REQUEST_URI'], 0, 9) == '/profile/'){
            header("HTTP/1.1 200 OK");
            global $wp_query;
		    $this->is_profile_page = true;
		    $wp_query->is_404 = false;
		}
	}
    


    function body_class_tweak( $classes ){
        // unset home class on body for google searches
        global $is_wms_profile, $meerkat_mobile, $unix_from_url;
        if( $this->is_google_search ){
            if( $classes[ 0 ] == 'home' ){
                unset( $classes[ 0 ] );
            }
            $classes[] = 'directory';
        }
        if( $this->is_profile_page ){
            unset( $classes );
            $classes[] = 'wms-profile';
            $classes[] = 'uid-' . $unix_from_url;
        }
        if( $this->is_magazine_theme ){
            $classes[] = 'meerkat-magazine';
        }
        if( $meerkat_mobile ){
            $classes[] = 'meerkat-mobile';
        }

        return $classes;
    }

    // BODY CLASS

    /**
     * @return string
     */
    function admin_body_class_tweak(){
        return $this->is_magazine_theme ? 'meerkat-magazine' : '';
    }

    // ADMIN BODY CLASS

    function convert_latex( $content ){
        // convert williams latex notation: $\forall/$  to standard wp latex notation: [latex]\forall[/latex]
        $pattern     = '|\$([^\$]*?)/\$|';
        $replacement = '[latex]' . "$1" . '[/latex]';
        $content     = preg_replace( $pattern, $replacement, $content );

        return str_replace( '$$', '$', $content );
    }

    function reverseRSS( &$simple_pie ){
        // reverse listing order for rss widget, to support event listings (soonest date first)
        $simple_pie->order_by_date = 0;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone(){
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup(){
    }
} // end class

//----- INSTANTIATE -----//
Meerkat16::instance();
