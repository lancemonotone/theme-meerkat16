<?php
/**
 * Adds config options for custom posts similar to categories.
 */

// config options are in a separate file shared by other scripts
include_once(THEME_INC_PATH . '/class.categories_options.php');

class Meerkat16_CustomPostTypes {
    private static $instance;

    protected function __construct() {
        $this->options = Meerkat16_Categories_Options::instance()->get_options();
        add_action('in_admin_footer', array(&$this, 'load_config_form'));

        // Add CPT to FacetWP query
        if (is_plugin_active('facetwp/index.php') && ! empty($_POST['post_type'])) {
            add_filter('facetwp_indexer_query_args', function($args) {
                $args['post_type'] = $_POST['post_type'];

                return $args;
            });
        }
    }

    function load_config_form() {

        if ( ! is_post_type_archive()) {
            return;
        }
        // Save CPT options
        $this->edit_cpt_save();

        // Load CPT options form
        $custom_post_data = get_post_type_object(get_post_type());
        $this->edit_cpt_form($custom_post_data);
    }

    /**
     * Saves the options when user submits edit category/tag form
     */
    function edit_cpt_save() {
        if ( ! $cpt_type = $_POST['cpt_type']) {
            return;
        }
        $cpt_name   = $_POST['cpt_name'];
        $option_key = 'wms_category_config_' . $cpt_type;

        $cpt_options = array();
        foreach ($this->options as $option => $vals) {
            //  save stuff
            $cpt_options[ $option ] = $_POST[ $option ];
        }

        update_option($option_key, $cpt_options);

        // Create widget area for template.

        if ( ! is_plugin_active('wms-shortcode/shortcode-builder.php')) {
            return;
        }

        $widgetized_area = new WmsWidgetizedArea();
        $sidebar_name    = 'Filters for ' . $cpt_name;

        if ($_POST['multi_show_facetwp_filters']) {
            if (is_plugin_active('facetwp/index.php')) {
                $widgetized_area->create_new_sidebar($sidebar_name);
            }
        } else {
            $widgetized_area->delete_sidebar($sidebar_name);
        }
    }


    /**
     * Creates the form on the CPT list page
     * which allows the admin to select options for that post type archive
     *
     * @param $custom_post_data
     */
    function edit_cpt_form($custom_post_data) {
        $current_screen = get_current_screen();
        $cpt_type       = $custom_post_data->name;
        $cpt_name       = $custom_post_data->labels->name;
        ?>

        <form name="edit_cpt" id="edit_cpt" method="post" action="<?php echo $current_screen->parent_file ?>" class="validate">
            <input type="hidden" name="cpt_type" value="<?php echo $cpt_type ?>">
            <input type="hidden" name="cpt_name" value="<?php echo $cpt_name ?>">
            <!--<input type="hidden" name="taxonomy" value="category">-->
            <!--<input type="hidden" name="_wp_original_http_referer" value="http://admission.local.williams.edu/wp-admin/term.php?taxonomy=category&amp;tag_ID=1&amp;post_type=post&amp;wp_http_referer=%2Fwp-admin%2Fedit-tags.php%3Ftaxonomy%3Dcategory">-->
            <!--<input type="hidden" id="_wpnonce" name="_wpnonce" value="3f13e8ef1c">-->
            <!--<input type="hidden" name="_wp_http_referer" value="/wp-admin/term.php?taxonomy=category&amp;tag_ID=1&amp;post_type=post&amp;wp_http_referer=%2Fwp-admin%2Fedit-tags.php%3Ftaxonomy%3Dcategory&amp;message=3">-->

            <h2>Custom Post Type Configuration Options</h2>
            <div class="cat-config-options">
                <p>
                    Control how posts in this archive display on your site by selecting which pieces of information are shown, in which context.
                    <br><em>Note: The template must support these options or they will be ignored.</em>
                </p>

                <?php
                $option_key  = 'wms_category_config_' . $cpt_type;
                $saved       = get_option($option_key);
                $single_opts = array();
                $multi_opts  = array();

                foreach ($this->options as $option => $vals) {
                    // split out into multi & single options
                    if ($vals['view'] == 'single') {
                        $single_opts[ $option ] = $vals;
                    } else {
                        $multi_opts[ $option ] = $vals;
                    }
                }

                // build single view
                $class = 'cat-config-single';
                $title = 'Single Post Display';
                $blurb = 'When viewing an individual post';
                $this->build_option_block($class, $title, $blurb, $single_opts, $saved);

                // build multi view
                $class = 'cat-config-multi';
                $title = 'Multiple Post Display';
                $blurb = 'When viewing a group of posts (archive)';
                $this->build_option_block($class, $title, $blurb, $multi_opts, $saved);

                ?>
            </div><!-- end cat-config-options -->
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Update"><span class="spinner"></span>
            </p>
            <div style="clear:both;"></div>
        </form>
        <?php
    }

    function build_option_block($class, $title, $blurb, $opts, $saved) {
        echo '<div class="' . $class . '">';
        echo '<h3>' . $title . '</h3>';
        echo '<div class="cat-config-blurb">' . $blurb . '</div>';
        foreach ($opts as $option => $vals) {
            echo '<div class="cat-config-item">';
            // check for saved, if nothing, use default
            if ($saved) {
                $prepop = $saved[ $option ];
                // allow a false saved value too override a true default
                if ( ! $prepop && $vals['default']) {
                    unset($vals['default']);
                }
            } else {
                $prepop = $this->options[ $option ]['default'];
            }
            echo buildField($option, $vals, $prepop);
            echo '</div>';
        }
        echo '</div>';
    }

    // grab saved category settings from the options table
    function get_config_options($term_id) {
        $option_key = 'wms_category_config_' . $term_id;

        return get_option($option_key);
    }

    //---- STICKY POST ----//
    // sticky support- move sticky posts to the top of category/tag pages
    function enable_stickies($wp_query) {
        if ( ! is_archive()) {
            return;
        }

        // Put sticky posts at the top of the posts array
        $sticky_posts = get_option('sticky_posts');
        $page         = $wp_query->query_vars['paged'];
        if ($page <= 1 && is_array($sticky_posts) && ! empty($sticky_posts)) {
            $num_posts     = count($wp_query->posts);
            $sticky_offset = 0;
            // Loop over posts and relocate stickies to the front.
            for ($i = 0; $i < $num_posts; $i++) {
                if (in_array($wp_query->posts[ $i ]->ID, $sticky_posts)) {
                    $sticky_post = $wp_query->posts[ $i ];
                    // Remove sticky from current position
                    array_splice($wp_query->posts, $i, 1);
                    // Move to front, after other stickies
                    array_splice($wp_query->posts, $sticky_offset, 0, array($sticky_post));
                    // Increment the sticky offset.  The next sticky will be placed at this offset.
                    $sticky_offset++;
                    // Remove post from sticky posts array
                    $offset = array_search($sticky_post->ID, $sticky_posts);
                    unset($sticky_posts[ $offset ]);
                }
            }
        }

        return $wp_query;
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return  The singleton instance.
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

Meerkat16_CustomPostTypes::instance();