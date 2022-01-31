<?php

/*
    the array used for determining the options for each category/tag
*/

class Meerkat16_Categories_Options {
    var $options, $saved_config;
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Meerkat16_Categories_Options The *Singleton* instance.
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

    function __construct() {
        $this->set_options();
        add_action('edit_term', array(&$this, 'edit_term_save'));
    }
    //  ---------------- CONFIG FUNCTIONS --------------------------

    /*
    grabs category configuration options for this category/tag from the db,
    and loads them into the globally accessible array $wms_saved_config_options.
    resolves any conflict if a single post has multiple tags/cats.
    */
    public function get_saved_config($term_id = '') {
        $this->load_saved_config($term_id);

        return $this->saved_config;
    }

    public function edit_term_save($term_id) {
        $cat             = get_term($term_id);
        $widgetized_area = new WmsWidgetizedArea();
        $sidebar_name    = 'Filters for ' . $cat->name;

        if ($_POST['multi_show_facetwp_filters'] && is_plugin_active('wms-shortcode/shortcode-builder.php')) {
            $widgetized_area->create_new_sidebar($sidebar_name);
        } else {
            $widgetized_area->delete_sidebar($sidebar_name);
        }
    }

    private function load_saved_config($term_id = '') {
        if (empty($term_id)) {

            global $cat, $wp_query;

            // we need the term id to get at the saved options for the term

            // echo '<pre>';print_r($wp_query);echo '</pre>';

            // we don't need to keep loading this if we're not mixing categories or tags...
            if ($this->saved_config && (is_category() || is_tag() || is_archive())) {
                return;
            }

            // term id lives in different places depending on context
            if (is_category()) {
                $term_id = $cat;
            } else if (is_tag()) {
                $term_id = $wp_query->query_vars['tag_id'];
            } else if (is_tax()) {
                $term_id = $wp_query->queried_object->term_taxonomy_id;
            } else if (is_archive()) {
                $term_id = $wp_query->queried_object->query_var;
            } else {
                // this handles if we're in single mode, or dealing with something that's potentially a mix of cats (ie index, author)

                // a single post can belong to multiple categories/tags, so we need to prioritize
                /*   category with saved options >
                     tag with saved options >
                     category with default options >
                     tag with default options
                */

                // load up cat & tag options, save in array associated with its priority
                $terms = array();
                $cats  = get_the_category();
                foreach ($cats as $c) {
                    $option_key = 'wms_category_config_' . $c->cat_ID;
                    if ($opt = get_option($option_key)) {
                        $terms[ $c->cat_ID ] = 4;
                    } else {
                        $terms[ $c->cat_ID ] = 2;
                    }
                }
                $tags = get_the_tags();
                if ($tags) {
                    foreach ($tags as $t) {
                        $option_key = 'wms_category_config_' . $t->term_id;
                        if ($opt = get_option($option_key)) {
                            $terms[ $t->term_id ] = 3;
                        } else {
                            $terms[ $t->term_id ] = 1;
                        }
                    }
                }
                // sort by priority, grab first one
                asort($terms);
                foreach ($terms as $id => $priority) {
                    $term_id = $id;
                    continue;
                }
            }
        }

        //  grab the configuration options for this term & dump it into a global array
        $option_key = 'wms_category_config_' . $term_id;
        //echo "option key is $option_key<br>";
        $this->saved_config = get_option($option_key);
        //echo 'SAVED ******<pre>';print_r($wms_saved_config_options);echo '</pre>]';
    }

    public function get_options() {
        return $this->options;
    }

    private function set_options() {
        $this->options = array(
            //  date
            'multi_show_date'                => array(
                'label'   => 'Show date of post',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_make_primary_breadcrumb' => array(
                'label'   => 'Make primary breadcrumb',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_skip_breadcrumb'         => array(
                'label'   => 'Skip this breadcrumb',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_show_date'               => array(
                'label'   => 'Show date of post',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // thumbnail
            'multi_show_thumb'               => array(
                'label'   => 'Show featured image thumbnail',
                'default' => true,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_thumb'              => array(
                'label'   => 'Show featured image thumbnail',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // related taxonomy
            'multi_show_related_tax'         => array(
                'label'   => 'Show category & tag links',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_related_tax'        => array(
                'label'   => 'Show category & tag links',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // comments
            'multi_show_comment_status'      => array(
                'label'   => 'Show comment count/link',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_comment_form'       => array(
                'label'   => 'Show comment form',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // author
            'multi_show_author'              => array(
                'label'   => 'Show author byline',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_author'             => array(
                'label'   => 'Show author byline',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_show_author_bio'         => array(
                'label'   => 'Show author bio',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_show_sharing'            => array(
                'label'   => 'Show social media sharing links',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // content-excerpt
            'multi_show_content'             => array(
                'label'   => 'Show full content (instead of excerpt)',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            // prev-next post
            'single_show_pagination'         => array(
                'label'   => 'Show previous/next post links',
                'default' => true,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // order by criteria
            'multi_orderby'                  => array(
                'label'   => 'Order posts by',
                'default' => 'date',
                'type'    => 'select',
                'options' => array('date' => 'Date', 'title' => 'Title', 'ID' => 'ID'),
                'view'    => 'multi'
            ),
            // order by criteria
            'multi_order_dir'                => array(
                'label'   => 'Order direction',
                'default' => 'DESC',
                'type'    => 'select',
                'options' => array('ASC' => 'Ascending', 'DESC' => 'Descending'),
                'view'    => 'multi'
            )
        );
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('facetwp/index.php')) {
            $this->options['multi_show_facetwp_search'] = array(
                'label'   => 'Show FacetWP Search Box',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            );
            if (is_plugin_active('wms-shortcode/shortcode-builder.php')) {
                $this->options['multi_show_facetwp_filters'] = array(
                    'label'   => 'Create/show widget area for filters',
                    'default' => false,
                    'type'    => 'checkbox',
                    'view'    => 'multi'
                );
            }
            $this->options['multi_show_facetwp_template'] = array(
                'label'   => 'Use FacetWP template',
                'default' => '',
                'type'    => 'text',
                'view'    => 'multi'
            );
        }
    }
}
