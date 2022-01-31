<?php

class Breadcrumbs {
    private static $instance;
    public $sep = '<span class="breadcrumb-sep"> &raquo; </span>';
    public $crumbs = array();

    /**
     * Parses page query and determines breadcrumb structure. Outputs
     * breadcrumb HTML block.
     *
     * @return string
     */
    function make_breadcrumbs() {
        global $wms_homepage, $post, $meermag;
        $wms_homepage = false;

        // college link
        $this->crumbs [] = $this->one_crumb('Williams', Wms_Server::instance()->www, 'wms-home-crumb');

        // If we're on a subdirectory site, append the parent subdomain crumb.
        $current_site = get_site(get_current_blog_id());
        if ($current_site->path !== '/') {
            // site is subdirectory
            $parent = get_blog_details(get_blog_id_from_url(Wms_Server::instance()->name));
            if (intval($parent->blog_id) !== WWW_BLOG_ID) {
                $this->crumbs [] = $this->one_crumb($parent->blogname, $parent->siteurl);
            }
        }

        // are we on the main site?
        if (WWW_BLOG_ID == Meerkat16::instance()->blog_id) {
            $wms_homepage = true;
        }

        // has a parent site been configured on the Options page?
        if ($parent = get_field('parent_site', 'option')) {
            $site             = get_blog_details($parent['value']);
            $this->crumbs  [] = $this->one_crumb($site->blogname, 'https://' . $site->domain, 'dept-home-crumb');
        }

        // dept homepage link
        if ( ! $wms_homepage) {
            $this->crumbs [] = $this->one_crumb(get_bloginfo('name'), get_home_url(), 'dept-home-crumb');
        }

        // PROFILE
        if (Meerkat16_Profiles::instance()->is_wms_profile) {
            // faculty/staff profile gets directory page & name of person as crumb
            if ($staff_page = get_field('staff_url', 'options')) {
                $this->crumbs       [] = $this->one_crumb($staff_page->post_title, get_permalink($staff_page->ID));
            }
            $this->crumbs [] = Meerkat16_Profile_Single::instance()->get_the_profile()['full_name'];
        } else {
            // OTHER PAGE TYPES
            // all other types of pages/conditions.  front page does not need a crumb
            if ( ! is_front_page()) {
                if (Meerkat16::instance()->is_magazine_theme && ! is_page() && ! is_404()) {
                    global $wp_query;
                    if ($wp_query->query_vars['volume_year']) {
                        $this->crumbs [] = $meermag->get_context_edition_link();
                    }
                }

                global $custom_end_crumb;
                if ($custom_end_crumb) {
                    $this->crumbs [] = $custom_end_crumb;
                } elseif ($custom_crumb_title = apply_filters('custom_crumb_title', null)) {
                    $this->crumbs [] = $this->one_crumb($custom_crumb_title);
                } elseif (is_page()) {
                    $this->add_page_crumbs();
                } elseif (is_single()) {
                    // a single post
                    $this->add_post_crumbs();
                } elseif (is_tag()) {
                    $this->add_tag_crumbs();
                } elseif (is_post_type_archive()) {
                    $this->add_post_type_archive_crumbs();
                } elseif (is_archive() || is_category()) {
                    $terms        [] = get_queried_object();
                    $primary_term    = $this->get_primary_term($terms);
                    $this->add_category_crumbs($primary_term);
                } elseif (is_author()) {
                    $this->add_author_crumbs();
                } elseif (Meerkat_Search::instance()->isWmsSearch()) {
                    $this->crumbs [] = $this->one_crumb('Search & Directories');
                } elseif (Meerkat16::instance()->is_magazine_theme && $_GET['s']) {
                    $this->crumbs [] = $this->one_crumb('Magazine Search');
                } elseif (is_404() && ! (substr($_SERVER['REQUEST_URI'], 0, 12) == '/catalog.php')) {
                    $this->crumbs [] = $this->one_crumb('Page Not Found');
                }
            }
        }
        $out = '<div id="breadcrumbs" class="breadcrumbs" aria-label="breadcrumb home">';
        $out .= join($this->sep, $this->crumbs);
        $out .= '</div>';

        return $out;
    }

    /**
     * Searches through all taxonomies for terms attached to the post
     * then sorts the terms hierarchically by primary term (selected in category options),
     * then by menu order, if set, and finally by id. It also sets the 'skip_breadcrumb'
     * flag
     *
     * @param WP_Post $post
     *
     * @return WP_Term|WP_Error|false
     */
    public function get_primary_term($terms) {
        if (empty($terms)) return false;

        // Get each term's parent and their configs to check them for skip and/or primary breadcrumb flags
        foreach ($terms as &$term) {
            // Get saved config for term and check flags
            $config = Meerkat16_Categories_Options::instance()->get_saved_config($term->term_id);
            // Used error control operator to prevent PHP 'Creating default object from empty value' warning
            @$term->{'skip_breadcrumb'} = ! empty($config['single_skip_breadcrumb']) && $config['single_skip_breadcrumb'] === 'on';
            $term->{'primary_term'}  = false;
            $term->{'primary_terms'} = array();

            // Is this term primary? This will be overwritten if there are primary parents.
            // Eventually this holds the top-level primary.
            if ( ! empty($config['single_make_primary_breadcrumb']) && $config['single_make_primary_breadcrumb'] === 'on') {
                $term->primary_term     = true;
                $term->primary_terms [] = $term;
            }

            // Init to push possible parents
            $term->{'parents'} = array();
            $parent            = get_term($term->parent, $term->taxonomy);

            // Loop parents until we get to the top
            while ( ! is_wp_error($parent)) {
                $parent_config = Meerkat16_Categories_Options::instance()->get_saved_config($parent->term_id);
                @$parent->{'skip_breadcrumb'} = ! empty($parent_config['single_skip_breadcrumb']) && $parent_config['single_skip_breadcrumb'] === 'on';

                if ( ! empty($parent_config['single_make_primary_breadcrumb']) && $parent_config['single_make_primary_breadcrumb'] === 'on') {
                    // Flag parent (debug housekeeping - not necessary)
                    $parent->{'primary_term'} = true;
                    // Set child's primary to parent (may be overwritten if more parents in branch)
                    $term->primary_terms [] = $parent;
                    // Set parent term order on child for later sorting if post has more than one term with a primary parent
                    $term->{'primary_term_order'} = $parent->term_order;
                }

                $term->parents [] = $parent;
                $parent           = get_term($parent->parent, $term->taxonomy);
            }
        }

        // Check to see if any of the terms are primary
        $primary_terms = array_filter($terms, function($term) {
            return ! empty($term->primary_terms);
        });

        // Sort by primary term order or term id
        if ( ! empty($primary_terms)) {
            $primary_terms = wp_list_sort($primary_terms, 'primary_term_order');
            $primary_term  = reset($primary_terms);
        } else {
            $terms        = wp_list_sort($terms, 'term_id');
            $primary_term = reset($terms);
        }

        return $primary_term;
    }

    /**
     * @param WP_Post $post
     *
     * @return array|WP_Error
     */
    public function get_post_terms(WP_Post $post) {
        // Get all site taxonomies because we can't get post terms without knowing their taxonomy
        $taxonomies = array_keys(get_taxonomies('', 'names'));
        // Get all post terms for all taxonomies
        $terms = wp_get_object_terms($post->ID, $taxonomies);

        return $terms;
    }

    /**
     */
    function add_post_type_archive_crumbs() {
        global $wp_query, $post_type;
        $title = $wp_query->queried_object->label;

        $this->crumbs [] = $this->one_crumb($title, get_post_type_archive_link($post_type));
    }

    /**
     * @param $id
     */
    function custom_menu_crumb($id) {
        global $post;
        $menu_slug         = 'main';
        $crumbs            = array();
        $lookup_by_post_id = array();
        $lookup_by_menu_id = array();
        $count             = 0;

        $locations  = get_nav_menu_locations();
        $menu       = wp_get_nav_menu_object($locations[ $menu_slug ]);
        $menu_items = wp_get_nav_menu_items($menu->term_id);

        // iterate through menu items, taking note of whats where
        foreach ($menu_items as $item => $data) {
            // $menu items is a big ugly object- set up a way to reference the data we need by both post id (object_id) and menu id
            $lookup_by_post_id[ $data->object_id ] = $count;
            $lookup_by_menu_id[ $data->ID ]        = $count;
            $count++;
        }
        if (array_key_exists($id, $lookup_by_post_id)) {
            // current post is in custom menu. make unlinked crumb for it
            $crumbs[]    = $post->post_title;
            $menu_parent = $menu_items[ $lookup_by_post_id[ $id ] ]->menu_item_parent;
            while ($menu_parent) {
                // create linked crumbs for parent menu items
                $url         = $menu_items[ $lookup_by_menu_id[ $menu_parent ] ]->url;
                $title       = $menu_items[ $lookup_by_menu_id[ $menu_parent ] ]->title;
                $crumbs[]    = $this->one_crumb($title, $url);
                $menu_parent = $menu_items[ $lookup_by_menu_id[ $menu_parent ] ]->menu_item_parent;
            }
            $this->crumbs = array_merge($this->crumbs, array_reverse($crumbs));
        }
    }

    /**
     * Builds breadcrumb trail for a page and its ancestors
     *
     * @param $id
     */
    function add_page_crumbs() {
        global $post;

        $parents = array_map('get_post', array_reverse((array) get_post_ancestors($post)));
        foreach ($parents as $parent) {
            $this->crumbs [] = $this->one_crumb($parent->post_title, get_permalink($parent));
        }

        $custom_page_bc  = get_field('page_breadcrumb');
        $page_title      = $custom_page_bc ? $custom_page_bc : get_the_title($post);
        $this->crumbs [] = $this->one_crumb($page_title);
    }

    /**
     * @param      $term
     * @param bool $is_link
     */
    function add_category_crumbs($term, $is_link = false) {
        if ($term->slug === 'uncategorized') return;

        // builds breadcrumb trail for a category and its ancestors
        if ($ancestors = $term->parents) {
            // sort the other direction (we want oldest ancestor first)
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor) {
                if ( ! ($ancestor->skip_breadcrumb)) {
                    $this->crumbs[] = $this->one_crumb($ancestor->name, get_term_link($ancestor->term_id), 'ancestor-crumb');
                }
            }
        }

        if ( ! $term->skip_breadcrumb) {
            if ($is_link) {
                // sometimes we want to make the "bottom" level category a link (e.g. when on a post)
                if (Meerkat16::instance()->is_magazine_theme) {
                    global $meermag;
                    $cat_info = get_category($term);
                    // prepend category link with year/issue.
                    $this->crumbs [] = $this->one_crumb($cat_info->name, $meermag->get_issue_href() . '/' . $cat_info->slug);
                } else {
                    // modified to take into account a non-default category base.
                    $this->crumbs [] = $this->one_crumb($term->name, get_term_link($term->term_id), 'cat-crumb');
                }
            } else {
                $this->crumbs [] = $this->one_crumb($term->name);
            }
        }
    }

    /**
     * @param      $name
     * @param      $url
     * @param bool $class
     *
     * @return string
     */
    function one_crumb($name, $url = null, $class = null): string {
        // builds a single breadcrumb
        $html = '<span class="breadcrumb';
        $html .= $class ? ' ' . $class : '';
        $html .= '">';
        $html .= $url ? '<a aria-label="breadcrumb ' . $class . '" href="' . $url . '">' : '';
        $html .= $name;
        $html .= $url ? '</a>' : '';
        $html .= '</span>';

        return $html;
    }

    /**
     * Single post, out of context of a custom menu. If it belongs to a category, list that.
     */
    function add_post_crumbs() {
        global $post;
        $post_type = get_post_type();

        /*
         * Check if a custom post type would like to override the default breadcrumb
         * by adding 'breadcrumb_priority' to CPT UI's 'custom supports' field.
         */
        if ($post_type != 'post' && function_exists('cptui_get_cptui_post_type_object')) {
            $cpt_crumb = array();
            $cpt       = (array) cptui_get_cptui_post_type_object($post_type);
            if ($cpt && preg_match("/breadcrumb[\s\-_]priority/i", $cpt['custom_supports'])) {
                $cpt_crumb = array('label' => $cpt['label'], 'link' => '/' . $cpt['name']);
            }
        }

        if ($cpt_crumb) {
            $this->crumbs[] = $this->one_crumb($cpt_crumb['label'], $cpt_crumb['link']);
        } elseif ( is_a($post, 'WP_Post')) {
            $terms = $this->get_post_terms($post);
            if (empty($terms)) return;
            $primary_term = $this->get_primary_term($terms);
            $this->add_category_crumbs($primary_term, true);
        }

        $custom_page_bc = get_field('page_breadcrumb');
        $post_title     = $custom_page_bc ? $custom_page_bc : $post->post_title;

        $this->crumbs [] = $this->one_crumb($post_title);
    }

    /**
     * @return void
     */
    function add_tag_crumbs(): void {
        // builds crumb for a tag page
        $tag_obj = get_tag(get_query_var('tag_id'));
        if ($term = get_term_by('slug', $tag_obj->slug, 'post_tag')) {
            // Deprecated
            //$this->crumbs []= '<span class="breadcrumb" aria-label="breadcrumb ' . $tag_obj->name . '"><a href="/tag/' . $term['slug'] . '">' . ucwords($term['name']) . '</a></span>'
            $this->crumbs [] = $this->one_crumb(ucwords($term->name));
        }
    }

    /**
     */
    function add_author_crumbs() {
        // builds crumb for an author page

        global $author;
        $author_obj = get_user_by('id', $author);

        $this->crumbs [] = $this->one_crumb($author_obj->user_nicename, '/author/' . $author_obj->user_login);
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

    protected function __construct() {
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Breadcrumbs The singleton instance.
     */
    public static function instance() {
        if ( ! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
