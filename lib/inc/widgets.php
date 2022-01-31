<?php
require_once(THEME_WIDGETS_PATH . '/index.php');
define('MK_WIDGET_PREFIX', '. ');

class MeerkatWidget extends WP_Widget {
    function update($new_instance, $old_instance) {
        $instance = array();
        // iterate over widget custom options, updating the value for each one
        foreach ($this->fields as $field => $data) {
            // if field is checkbox, then it won't come through as set if unchecked. set to zero so it's not empty.
            if ($data['type'] == 'checkbox') {
                $instance[ $field ] = isset($new_instance[ $field ]) ? $new_instance[ $field ] : 0;
            }
            $instance[ $field ] = $new_instance[ $field ]; // needs more sanitizing, but cannot flat out strip tags because of text widget.
        }

        return $instance;
    }

    function form($instance) {
        echo '<div class="meerkat_widget_form">';
        foreach ($this->fields as $field => $data) {
            $name       = $this->get_field_name($field);
            $id         = $this->get_field_id($field);
            $cur_val    = esc_attr($instance[ $field ]);
            $data['id'] = $id;
            if ( ! $data['classes']) {
                if ($data['type'] != 'checkbox') {
                    $data['classes'] = 'widefat';
                }
            }
            if ( ! $data['wrapper']) {
                $data['wrapper'] = 'p';
            }
            echo buildField($name, $data, $cur_val);
        }
        echo '</div>';
    }

    function display_title($args, $instance, $context_sensitive = false) {
        // $args and $instance are the widget args and instance
        // $context_sensitive is a boolean, should there be a context sensitive icon?
        // $extra classes is an array of classes that will be applied to div.widget-insides
        echo $this->get_title($args, $instance, $context_sensitive);
    }

    function get_title($args, $instance, $context_sensitive = false) {
        // see display_title for description of parameters
        global $post;

        $subpage_link = '';
        $title        = $instance['title'];
        $title_link   = $instance['title_link'];
        $before_title = $title != "" ? $args['before_title'] : "";
        $after_title  = $title != "" ? $args['after_title'] : "";

        // callout style for title bar
        if ($instance['callout'] && $title != '') {
            $pattern      = '|h2 class="title"|';
            $replacement  = 'h2 class="title callout-menu"';
            $before_title = preg_replace($pattern, $replacement, $before_title);
        }

        // context arrow
        if ($context_sensitive && ! $instance['callout']) {
            if ( ! empty($instance['wms-context-widget']) > 0 && $instance['wms-context-widget'][0] != -99) {
                $before_title = '<div class="sprite context-menu"></div>' . $before_title;
            }
        }

        if ($instance['subpages']) {
            $before_title = '<div class="sprite context-menu"></div>' . $before_title;

            // use context - page title
            $ancestors = get_ancestors($post->ID, 'page');
            if (count($ancestors) > 0) {
                // last array item is oldest ancestor
                $ances_id     = end($ancestors);
                $title        = get_the_title($ances_id);
                $subpage_link = get_permalink($ances_id);
            } else {
                $title        = $post->post_title;
                $subpage_link = get_permalink($post->ID);
            }
        }

        if ($title) {
            if ($title_link) {
                $title = '<a href="' . esc_url($title_link) . '">' . $title . '</a>';
            }
            if ($instance['subpages'] && $subpage_link) {
                $title = '<a href="' . $subpage_link . '">' . $title . '</a>';
            }
        } else {
            // phase this out once functions.php changes register sidebar args
            $before_title = str_replace('<h2 class="title">', '', $before_title);
            $before_title = str_replace('<h2 class="widgettitle">', '', $before_title);
            $after_title  = str_replace('</h2>', '', $after_title);
        }

        $html = $before_title . $title . $after_title;

        return $html;
    }

    /**
     * @param array  $extra_classes
     * @param string $element
     * @param string $selector
     *
     * @return mixed
     * @todo This is a badly-named function
     * @todo Return instead of echo (easier to debug)
     */
    function insert_extra_classes($extra_classes, $element, $selector = "widget") {
        if ( ! empty($extra_classes)) {
            $pattern     = '/\b' . $selector . '\b/';
            $replacement = $selector . ' ' . implode(' ', $extra_classes);
            $element     = preg_replace($pattern, $replacement, $element);
        }

        echo $element;
    }


} // end class

// deregister widgets that are redundant 
add_action('widgets_init', 'meerkat_unregister_widgets');

function meerkat_unregister_widgets() {
    // pointless/confusing widgets go away
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Meta');
    unregister_widget('WP_Widget_Search');

    // widgets we are overriding
    unregister_widget('WP_Widget_Links');
    unregister_widget('WP_Widget_Pages');
    unregister_widget('WP_Nav_Menu_Widget');
    unregister_widget('WP_Widget_Text');
    unregister_widget('Akismet_Widget');  // this is hideous and pointless :<
}