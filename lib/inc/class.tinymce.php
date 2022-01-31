<?php
// style from content.css that should appear as tinymce style dropdown choices
// see http://www.wpexplorer.com/wordpress-tinymce-tweaks/ for more documentation
// wrapper needs to be set to false to avoid having space inserted around the content

class TinyMceConfig {
    private $tinymce_styles, $custom_button_vars;

    public function __construct() {
        // Uncomment to add custom plugin to TinyMce
        //add_action('admin_head', array(&$this, 'custom_plugin'));

        $this->set_tinymce_styles();
        add_filter('tiny_mce_before_init', array(&$this, 'customize_tinymce'));
        add_filter('mce_buttons', array(&$this, 'tinymce_buttons_row1'));
        add_filter('mce_buttons_2', array(&$this, 'tinymce_buttons_row2'));
        add_filter('acf/fields/wysiwyg/toolbars', array(&$this, 'customize_acf_tinymce'));
    }

    /**
     * Use this to load a custom plugin (not used)
     *
     * Custom Plugin
     * Connect our custom functions with WordPress's tinymce hooks
     * @return void
     */
    function custom_plugin() {
        // Set config
        $this->custom_button_vars = array(
            'id'         => 'm16_custom',
            'js_url'     => THEME_JS_URL . '/lib/tinymce.js', // assumes all custom buttons in same js file.
            'button_ids' => array(
                0 => 'm16_formatselect'
            )
        );

        // check user permissions
        if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
            return;
        }

        // check if WYSIWYG is enabled
        if ('true' == get_user_option('rich_editing')) {
            add_filter('mce_external_plugins', array($this, 'mce_external_plugins'));
            add_filter('mce_buttons_2', array($this, 'mce_buttons_2'));
            //add_filter( 'mce_css', array( $this, 'plugin_mce_css' ) );
        }
    }

    /**
     * mce_external_plugins
     * Adds our tinymce plugin
     *
     * @param array $plugin_array
     *
     * @return array
     */
    function mce_external_plugins($plugin_array) {
        // declare script for new tinymce button
        $plugin_array[ $this->custom_button_vars['id'] ] = $this->custom_button_vars['js_url'];

        return $plugin_array;
    }

    /**
     * mce_buttons_2
     * Adds our tinymce button to second row
     *
     * @param  array $buttons
     *
     * @return array
     */
    function mce_buttons_2($buttons) {
        array_unshift($buttons, $this->custom_button_vars['button_ids'][0]);

        return $buttons;
    }


    /**
     * @param $buttons
     *
     * @return array
     */
    function tinymce_buttons_row1($buttons) {
        // remove problematic/confusing default buttons
        $remove_these_buttons = array('formatselect', 'strikethrough', 'aligncenter', 'wp_adv');
        $buttons              = array_diff($buttons, $remove_these_buttons);
		array_push( $buttons, 'undo', 'redo', 'wp_adv' );
        array_unshift($buttons, 'styleselect');

        return $buttons;
    }

    /**
     * @param $buttons
     *
     * @return array
     */
    function tinymce_buttons_row2($buttons) {
        // remove problematic/confusing default buttons (or ones we're putting on the top bar)
        $remove_these_buttons = array('underline', 'alignjustify', 'formatselect', 'undo', 'redo');
        $buttons              = array_diff($buttons, $remove_these_buttons);

        // enable style select pulldown
        //array_unshift($buttons, 'styleselect');

        return $buttons;
    }

    /**
     * @param $options
     *
     * @return mixed
     */
    function customize_tinymce($options) {
        // tweaks to the wysiwyg editor
        // prevents tinymce from stripping out non-standard tags/attributes when you flip between text/visual modes
        if ( ! isset($options['extended_valid_elements'])) {
            $options['extended_valid_elements'] = '';
        }
        $options['extended_valid_elements'] .= ',div[*],a[*]';

        // modify options for textcolor
        // http://urosevic.net/wordpress/tips/custom-colours-tinymce-4-wordpress-39/
        $custom_colors            = '
		"512698", "purple1",
		"c3b730", "yellow",
		"EC881D", "orange1",
		"cc7518", "orange2",
		"aa2d00", "red1",
		"8ab840", "green1",
		"27acd3", "blue1",
		"3586aa", "blue2",
		"666666", "grey1",
		"777777", "grey2",
		"FFFFFF", "white",
		"000000", "black"
		';
        $options['textcolor_map'] = "[$custom_colors]";

        // remove custom... row from text color
        //$options['color_picker_callback'] = false;

        // set contents of style pulldown menu. see http://codex.wordpress.org/TinyMCE_Custom_Styles
        $options['style_formats'] = json_encode($this->get_tinymce_styles());

        // allow items in pulldown menu to be styled
        unset($options['preview_styles']);

        return $options;
    }

    public function get_tinymce_styles() {
        return $this->tinymce_styles;
    }

    public function set_tinymce_styles() {
        $this->tinymce_styles = array();
        // magazine specific styles
        if (Meerkat16::instance()->is_magazine_theme) {
            array_push($this->tinymce_styles,
                array(
                    'title' => 'Magazine Styles',
                    'items' => array(
                        array('title' => 'Deck', 'block' => 'div', 'classes' => 'deck', 'wrapper' => true),
                        array('title' => 'Deck2', 'block' => 'div', 'classes' => 'deck2', 'wrapper' => true),
                        array('title' => 'Byline', 'block' => 'div', 'classes' => 'byline', 'wrapper' => true),
                        array('title'   => 'Intext Head',
                              'inline'  => 'span',
                              'classes' => 'intext-head',
                              'wrapper' => false
                        ),
                    ),
                )
            );
        }

        array_push($this->tinymce_styles,
            // headers
            array('title' => 'Paragraph', 'block' => 'p', 'wrapper' => false),
            array('title' => 'Page Subheader', 'block' => 'h2', 'wrapper' => false),
            array('title' => 'Section Header', 'block' => 'h3', 'wrapper' => false),
            array('title' => 'Section Subheader', 'block' => 'h4', 'wrapper' => false),
            array('title' => 'Title', 'block' => 'h5', 'wrapper' => false),
            array('title' => 'Subtitle', 'block' => 'h6', 'wrapper' => false),

            array(
                'title' => 'Columns & Spacers',
                'items' => array(
                    // layout - columns
                    array('title'     => '1/2 width column, left',
                          'block'     => 'div',
                          'classes'   => 'half-w-space left',
                          'wrapper'   => true
                    ),
                    array('title'   => '1/2 width column, right',
                          'block'   => 'div',
                          'classes' => 'half-w-space right',
                          'wrapper' => true
                    ),
                    array('title'   => '1/3 width column, left/middle',
                          'block'   => 'div',
                          'classes' => 'one-third left',
                          'wrapper' => true
                    ),
                    array('title'   => '1/3 width column, right',
                          'block'   => 'div',
                          'classes' => 'one-third right',
                          'wrapper' => true
                    ),
                    array('title'   => '2/3 width column, left',
                          'block'   => 'div',
                          'classes' => 'two-thirds left',
                          'wrapper' => true
                    ),
                    array('title'   => '2/3 width column, right',
                          'block'   => 'div',
                          'classes' => 'two-thirds right',
                          'wrapper' => true
                    ),

                    // layout - other
                    array('title' => 'Add space above', 'block' => 'div', 'classes' => 'mtop-10', 'wrapper' => true),
                    array('title' => 'Add space below', 'block' => 'div', 'classes' => 'mbot-10', 'wrapper' => true),
                    array('title' => 'Clear', 'block' => 'div', 'classes' => 'clear', 'wrapper' => true),
                ),
            ),

            array(
                'title' => 'Quotes & Callouts',
                'items' => array(
                    // content boxes
                    array('title' => 'Content box', 'block' => 'div', 'classes' => 'content-box', 'wrapper' => true),
                    array('title' => 'Callout box', 'block' => 'div', 'classes' => 'callout', 'wrapper' => true),
                    array('title' => 'Blockquote', 'block' => 'blockquote', 'wrapper' => true),
                    array('title' => 'Code', 'block' => 'code', 'wrapper' => true),
                ),
            ),

            array(
                'title' => 'Text',
                'items' => array(
                    // content boxes
                    array('title' => 'Subscript', 'inline' => 'sub', 'wrapper' => false),
                    array('title' => 'Superscript', 'inline' => 'sup', 'wrapper' => false),
                    array('title' => 'Note', 'block' => 'div', 'classes' => 'note', 'wrapper' => true),
                    array('title' => 'Strikethrough', 'inline' => 'del', 'wrapper' => false),
                ),
            ),

            array(
                'title' => 'Links',
                'items' => array(
                    // links
                    array('title' => 'PDF link', 'selector' => 'a', 'classes' => 'pdf', 'wrapper' => false),
                    array('title' => 'Arrow link', 'selector' => 'a', 'classes' => 'raquo', 'wrapper' => false),
                    array('title' => 'Read more link', 'selector' => 'a', 'classes' => 'more', 'wrapper' => false),
                    array('title'      => 'Fancybox image link',
                          'selector'   => 'a',
                          'classes'    => 'fancybox',
                          'wrapper'    => false,
                          'attributes' => array('rel' => 'fancy')
                    ),
                    array('title' => 'Button link', 'selector' => 'a', 'classes' => 'button', 'wrapper' => false),
                ),
            ),

            array(
                'title' => 'Lists & Tables',
                'items' => array(
                    // lists
                    array('title'    => 'Non-bulleted list',
                          'selector' => 'ul',
                          'classes'  => 'no-dot',
                          'wrapper'  => false,
                    ),
                    array('title'    => 'Spacious list',
                          'selector' => 'ul',
                          'classes'  => 'spacious',
                          'wrapper'  => false,
                    ),

                    // table
                    array('title' => 'Data table', 'selector' => 'table', 'classes' => 'data', 'wrapper' => false),
                ),
            ),

            array(
                'title' => 'Device Dependent',
                'items' => array(
                    array('title' => 'Mobile only', 'block' => 'div', 'classes' => 'mobile-only', 'wrapper' => true),
                    array('title' => 'Not mobile', 'block' => 'div', 'classes' => 'not-mobile', 'wrapper' => true),
                ),
            )
        );
    }

    /**
     * @param $toolbars
     *
     * @return mixed
     */
    function customize_acf_tinymce($toolbars) {
        // control which items show up on the acf plugin's wysiwyg editor toolbars
        // (the basic version- the full version will use what we've set up for pages/posts)
        // replace acf's basic list of buttons with our own array.
        $toolbars['Basic'][1] = array(
            'bold',
            'italic',
            'bullist',
            'numlist',
            'hr',
            'link',
            'unlink',
            'forecolor',
            'styleselect',
            'pastetext',
            'charmap',
            'outdent',
            'indent',
            'undo',
            'redo',
            'wp_help',
            'fullscreen'
        );

        return $toolbars;
    }
}

new TinyMceConfig();
