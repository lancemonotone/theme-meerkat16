<?php
$custom_end_crumb = false;

class Timberizer {

    private static $instance;

    protected function __construct() {
        /**
         * @see \TimberLoader::get_twig
         */
        if ( ! defined('TWIG_DEBUG')) {
            define('TWIG_DEBUG', true);
        }

        if ( ! class_exists('Timber')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin.</p></div>';
            });

            return;
        }

        \Timber\Timber::$dirname = array_merge((array) \Timber\Timber::$dirname, array('views'));

        add_filter('timber/context', array($this, 'add_to_context'), 1);
        add_filter('timber/twig', function(\Twig_Environment $twig) {
            // Make WP edit_post_link() available within twigs
            $twig->addFunction(new \Timber\Twig_Function('edit_post_link', 'edit_post_link'));
            $twig->addFilter(new \Twig\TwigFilter('json_decode', 'json_decode'));

            return $twig;
        });
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Timberizer The singleton instance.
     */
    public static function instance() {
        if ( ! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $context
     *
     * @return mixed
     */
    public function add_to_context($context) {
        // Get global and social nav menus by ID from WWW
        switch_to_blog(93);
        $locations                     = get_nav_menu_locations();
        $context['global_nav']         = new \Timber\Menu($locations['mega_global']);
        $context['featured_links_nav'] = new \Timber\Menu($locations['mega_featured']);
        $context['global_social_nav']  = new \Timber\Menu($locations['mega_social']);
        restore_current_blog();

        // Get site nav menus
        if ( ! Meerkat16::instance()->is_homepage_theme) {
            foreach (get_nav_menu_locations() as $nav => $id) {
                if (has_nav_menu($nav)) {
                    $context[ $nav . '_nav' ] = new Timber\Menu($id);
                }
            }
        }

        unset($context['wp_nav_menu']); // Don't need this because we're invoking them individually.

        $context['breadcrumbs'] = Breadcrumbs::instance()->make_breadcrumbs();
        $context['theme']       = Meerkat16::instance();
        $context['facets']      = Meerkat16_Facets::instance();
        $context['js']          = Meerkat16_Js::instance();
        $context['theme_uri']   = get_stylesheet_directory_uri();
        if (is_active_sidebar('sidebar')) {
            $context['sidebar_widgets'] = array(
                'widgets' => \Timber\Timber::get_widgets('sidebar'),
                'id'      => 'tertiary',
                'class'   => 'sidebar'
            );
        }
        $context['navbox_widget'] = Timber\Helper::ob_function('the_widget', array('WMS_Navbox_Widget'));
        //show/hide site title and breadcrumbs- site-masthead in site-header
        $context['exclude_masthead'] = ! ((get_current_blog_id() != '93') or (get_current_blog_id() == '93' and ! is_front_page()));
        // Get network sidebar message from mu-plugin
        $context['network_sidebar_message'] = \WMS\Network_Sidebar_Message::display_message();

        return $context;
    }

    /**
     * @param $twig
     *
     * @return mixed
     */
    public function add_to_twig($twig) {
        /* this is where you can add your own functions to twig */
        $twig->addExtension(new Twig_Extension_StringLoader());

        return $twig;
    }

    /**
     * Render Twig template based on queried object
     *
     * $context['template'] is used general all-purpose twig, but you can be more
     * specific by creating a twig to match $context['page'].
     *
     * @param $extra_args {Array} of extra data to pass to context - array('key' => mixed)
     */
    public static function render_template($extra_args = null) {
        global $post, $wp_query;
        $context            = \Timber\Timber::get_context();
        $context['options'] = get_fields('options');

        $search_param = defined('SEARCH_PARAM') ? SEARCH_PARAM : 's';

        if ( ! in_array($post->post_type, array('page', 'post'))) {
            $post_type = $post->post_type;
        }

        if ($extra_args && is_array($extra_args)) {
            foreach ($extra_args as $k => $v) {
                $context[ $k ] = $v;
            }
        }

        if (is_singular()) { // post or page, check for custom 404 content
            $context['post'] = apply_filters('get_custom_404', new TimberPost($wp_query->queried_object ? $wp_query->queried_object->ID : null));
        } else { // anything else
            // Remove pagination for pages that are more than 2 away from current
            $pagination            = \Timber\Timber::get_pagination(array('mid_size' => 1));
            $context['pagination'] = $pagination;
        }

        if (isset($extra_args['template'])):
            $context['page']     = $extra_args['template'];
            $context['template'] = $extra_args['template'];

        elseif (is_single()) : // single post
            $context['page']     = 'single';
            $context['template'] = 'single';

        elseif (is_page()) : // single page
            $context['header_image'] = Meerkat16_Images::get_header_image();
            if (is_front_page()): // home page
                $context['page'] = 'home';
                if (is_active_sidebar('home-widget-area')) {
                    $context['home_widgets'] = array(
                        'widgets' => \Timber\Timber::get_widgets('home-widget-area'),
                        'id'      => 'home-widgets',
                        'class'   => 'home-widgets'
                    );
                }
                $context['template'] = 'page';

            else : // generic page
                $context['page']     = 'page';
                $context['template'] = 'page';

            endif;

        elseif (is_home()): // posts/blog homepage
            $context['page']     = 'home';
            $context['template'] = 'archive';

        elseif (is_category()):
            $context['archive_title']       = get_cat_name(get_query_var('cat'));
            $context['archive_description'] = term_description();
            $context['page']                = 'category';
            $context['template']            = 'archive';

        elseif (is_tag()):
            $tag_name                       = get_tag(get_query_var('tag_id'));
            $context['archive_title']       = $tag_name;
            $context['archive_description'] = term_description();
            $context['page']                = 'tag';
            $context['template']            = 'archive';

        elseif (is_archive()):
            $queried_obj                    = get_queried_object();
            $context['archive_title']       = $queried_obj->label;
            $context['archive_description'] = $queried_obj->description;
            $context['page']                = $queried_obj->name;
            $context['template']            = 'archive';

        elseif (is_author()):
            $context['archive_title'] = get_the_author();
            $context['page']          = 'author';
            $context['template']      = 'archive';

        elseif (is_404()):
            if (function_exists('legacyRedirect')) {
                // Check to see if this request has a home.
                legacyRedirect();
            }
            $context['page']     = 'is-404';
            $context['template'] = '404';

        elseif (is_search() && isset($_GET[ $search_param ])):
            // && $_GET['s'] != ''):
            $context['archive_title'] = 'Results for: ' . stripslashes($_GET[ $search_param ]);
            $context['page']          = 'wp-search';
            $context['template']      = 'wp-search';

        elseif (Meerkat_Search::instance()->isWmsSearch()):
            $context['page']         = 'search';
            $context['search']       = Meerkat_Search::getSearchContext();
            $context['hide_sidebar'] = true;
            $context['template']     = 'search';

        endif;

        $context = apply_filters('timberizer_before_render', $context);

        // render using Twig template index.twig
        \Timber\Timber::render(array(
            'page-' . $extra_args['template'] . '.twig',
            'page-' . $post->post_name . '.twig',
            'page-' . $post_type . '.twig',
            'loop-' . $context['page'] . '.twig', // allows for specific twigs
            'loop-' . $context['template'] . '.twig'
        ), $context);
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

if (Meerkat16::instance()->using_timber) {
    Timberizer::instance();
}
