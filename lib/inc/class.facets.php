<?php

class Meerkat16_Facets {
    private static $instance;

    protected function __construct() {
        add_filter('after_setup_theme', array(__CLASS__, 'init'), 15);
        /** always ignore archive query as main query for facet **/
        add_filter( 'facetwp_is_main_query', function( $is_main_query, $query ) {
            if ( 
                $query->is_archive() 
                && $query->is_main_query() 
                && Meerkat16::instance()->do_display('facetwp_template')
            ) {
                $is_main_query = false;
            }
            return $is_main_query;
        }, 10, 2 );
    }

    public static function init() {
        add_filter('facetwp_facets', array(__CLASS__, 'load_facets'));
    }


    /**
     * Call back for FacetWP plugin. Adds custom search facet and makes lists of facets
     * available.
     *
     * @return array
     */
    public static function load_facets($facets) {
        // Make available to archive templates
        if ( ! Meerkat16::instance()->is_page) {
            $facets[] = array(
                'label'         => 'Category Search',
                'name'          => 'category_search',
                'type'          => 'search',
                'search_engine' => '',
                'placeholder'   => 'Search Category',
                'auto_refresh'  => 'yes'
            );
        }
        return $facets;
    }

    /**
     * Helper to determine whether a facet is active on the page.
     * @return bool
     */
    public static function has_facet() {
        if (Meerkat16::instance()->do_display('facetwp_template')
            || Meerkat16::instance()->do_display('facetwp_search')
            || Meerkat16::instance()->do_display('facetwp_filters')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Find and return the name of the sidebar auto-created to hold this category's FacetWP facets.
     *
     * @requires WmsWidgetizedArea
     * @return string
     */
    public static function get_facetwp_sidebar() {
        if ( ! class_exists('WmsWidgetizedArea')) return '';

        $sidebar_slug = WmsWidgetizedArea::sanitize_slug('Filters for' . Meerkat16::instance()->get_queried_obj_name());

        return $sidebar_slug;
    }

    /**
     * Twig helper to determine whether to load CPT or category twigs.
     *
     * @return bool
     */
    public static function facetwp_is_cpt() {
        $queried_obj = get_queried_object();
        if ($queried_obj instanceof WP_Post_Type) {
            return $queried_obj->name;
        } else {
            return false;
        }
    }

    function options_from($which) {
        switch ($which) {
            case 'templates':
                $templates = function() {
                    return FWP()->helper->get_templates();
                };
                break;
            case 'facets':
                $templates = function() {
                    return FWP()->helper->get_facets();
                };
                break;
        }
        $options = array(
            0 => '',
        );
        foreach ($templates as $template) {
            $options[ $template['name'] ] = $template['label'];
        }

        return $options;
    }

    function options_from_templates() {
        return self::options_from('templates');

    }

    function options_from_facets() {
        return self::options_from('facets');
    }


    /**
     * Returns the singleton instance of this class.
     *
     * @return Meerkat16_Facets The singleton instance.
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

Meerkat16_Facets::instance();
