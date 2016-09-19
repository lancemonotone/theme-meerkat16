<?php

$custom_end_crumb = false;

class Timberizer {

    private static $instance;

    protected function __construct(){
        /**
         * @see \TimberLoader::get_twig
         */
        if( ! defined( 'TWIG_DEBUG' ) ){
            define( 'TWIG_DEBUG', true );
        }

        require_once( 'plugins/timber/timber.php' );
        if( ! class_exists( 'Timber' ) ){
            add_action( 'admin_notices', function(){
                echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin.</p></div>';
            } );

            return;
        }

        Timber::$dirname = array( 'views', 'views/modules', 'views/widgets' );

        add_filter( 'timber/context', array( $this, 'add_to_context' ) );
        //add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Timberizer The singleton instance.
     */
    public static function instance(){
        if( ! self::$instance ){
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Render Twig template based on queried object
     *
     * @param $extra_args Array array of extra data to pass to context - array('key' => array)
     */
    public static function render_template( $extra_args = null ){
        global $post;
        $context = Timber::get_context();

        if( $extra_args && is_array( $extra_args ) ){
            foreach( $extra_args as $k => $v ){
                $context[ $k ] = $v;
            }
        }

        if( is_singular() ): // post or page
            $context[ 'post' ] = new TimberPost();

        else : // anything else
            // Remove pagination for pages that are more than 2 away from current
            $pagination              = Timber::get_pagination( array( 'mid_size' => 1 ) );
            $context[ 'pagination' ] = $pagination;

        endif;

        if( isset( $extra_args[ 'template' ] ) ):
            $context[ 'page' ] = $extra_args[ 'template' ];
            $template          = $extra_args[ 'template' ];

        elseif( is_single() ) : // single post
            $context[ 'page' ] = 'single';
            $template          = 'single';

        elseif( is_page() ) : // single page
            $context[ 'header_image' ] = Meerkat16_Images::get_header_image();

            if( is_front_page() ): // home page
                $context[ 'page' ] = 'home';
                if( is_active_sidebar( 'home-widget-area' ) ){
                    $context[ 'home_widgets' ] = array(
                        'widgets' => Timber::get_widgets( 'home-widget-area' ),
                        'id'      => 'home-widgets',
                        'class'   => 'home-widgets'
                    );
                }
                $template = 'page';

            else : // generic page
                $context[ 'page' ] = 'page';
                $template          = 'page';

            endif;

        elseif( is_home() ): // posts/blog homepage
            $context[ 'page' ] = 'home';
            $template          = 'archive';

        elseif( is_category() ):
            $context[ 'archive_title' ]       = get_cat_name( get_query_var( 'cat' ) );
            $context[ 'archive_description' ] = term_description();
            $context[ 'page' ]                = 'category';
            $template                         = 'archive';

        elseif( is_tag() ):
            $tag_name                         = get_tag( get_query_var( 'tag_id' ) );
            $context[ 'archive_title' ]       = $tag_name;
            $context[ 'archive_description' ] = term_description();
            $context[ 'page' ]                = 'tag';
            $template                         = 'archive';

        elseif( is_author() ):
            $context[ 'archive_title' ] = get_the_author();
            $context[ 'page' ]          = 'author';
            $template                   = 'archive';

        elseif( is_404() ):
            if (function_exists('legacyRedirect')) {
                // Check to see if this request has a home.
                legacyRedirect();
            }
            $context[ 'page' ] = 'is-404';
            $template          = '404';

        elseif( Meerkat_Search::instance()->isWmsSearch() ):
            $context[ 'page' ]         = 'search';
            $context[ 'search' ]       = Meerkat_Search::getSearchContext();
            $context[ 'hide_sidebar' ] = true;
            $template                  = 'search';


        endif;

        // render using Twig template index.twig
        Timber::render( array(
                            'page-' . $extra_args[ 'template' ] . '.twig',
                            'page-' . $post->post_name . '.twig',
                            'loop-' . $template . '.twig'
                        ), $context );
    }

    /**
     * @param $context
     *
     * @return mixed
     */
    public function add_to_context( $context ){
        // Get global and social nav menus by ID from WWW
        switch_to_blog( 93 );
        $locations                       = get_nav_menu_locations();
        $context[ 'global_nav' ]         = new TimberMenu( $locations[ 'global' ] );
        $context[ 'featured_links_nav' ] = new TimberMenu( $locations[ 'featured' ] );
        $context[ 'global_social_nav' ]  = new TimberMenu( $locations[ 'social' ] );
        restore_current_blog();

        unset( $context[ 'wp_nav_menu' ] ); // Don't need this because we're invoking them individually.

        $context[ 'breadcrumbs' ] = Breadcrumbs::instance()->make_breadcrumbs();
        $context[ 'theme' ]       = Meerkat16::instance();
        $context[ 'js' ]          = Meerkat16_Js::instance();
        $context[ 'theme_uri' ]   = get_stylesheet_directory_uri();
        $context[ 'site_nav' ]    = new TimberMenu( 'site' );
        $context[ 'social_nav' ]  = new TimberMenu( 'social' );
        if( is_active_sidebar( 'sidebar' ) ){
            $context[ 'sidebar_widgets' ] = array(
                'widgets' => Timber::get_widgets( 'sidebar' ),
                'id'      => 'tertiary',
                'class'   => 'sidebar'
            );
        }
        $context[ 'navbox_widget' ] = TimberHelper::ob_function( 'the_widget', array( 'WMS_Navbox_Widget' ) );

        return $context;
    }

    /**
     * @param $twig
     *
     * @return mixed
     */
    public function add_to_twig( $twig ){
        /* this is where you can add your own functions to twig */
        $twig->addExtension( new Twig_Extension_StringLoader() );

        return $twig;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone(){
    }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup(){
    }

}

if( Meerkat16::instance()->using_timber ){
    Timberizer::instance();
}
