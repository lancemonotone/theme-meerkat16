<?php
/* 
 * THEME OPTIONS (ACF)
 * sets up fields for use by the plugin advanced custom fields, which handles all the heavy lifting
 * base code exported via the plugin settings, then cleaned up & commented
 *
 * Register field groups
 * The register_field_group function accepts 1 array which holds the relevant data to register a field group
 * You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
 * This code must run every time the functions.php file is read
 */
// @since ACF 5.0
// drm2: deprecated - moved to class.login
/*if ( function_exists( 'acf_add_options_page' ) ) {
	acf_add_options_page();
}*/

function add_admin_body_class($classes) {
    if (is_super_admin()) {
        $classes .= ' super-admin';
    }
    $classes .= ' blog-' . get_current_blog_id();

    return $classes;
}

add_filter('admin_body_class', 'add_admin_body_class');

/**
 * @todo Is it necessary to run this on every pageload?
 * @todo No, this is stupid. Write SQL to look for Academic sites using meerkat* themes.
 *
 */
if (function_exists('register_field_group')) {

    // Rebuild list of academic sites if transient has expired.
    // No need to switch to every blog on every page load
    function get_meerkat_acad_sites($transient_name) {
        global $wpdb;
        $meerkat_acad_sites = array();
        $sql                = $wpdb->prepare("SELECT blog_id, domain FROM $wpdb->blogs WHERE deleted = %d AND blog_id > %d ORDER BY domain", 0, 1);
        $blogs              = $wpdb->get_results($sql);
        foreach ($blogs as $blog => $data) {
            switch_to_blog($data->blog_id);
            $theme    = get_option('template');
            $sitename = get_option('blogname');
            $type     = get_option('site_type');
            // Include meerkat*
            if (preg_match('/(meerkat)/i', $theme) && $type == 'Academic') {
                $meerkat_acad_sites[ $data->blog_id ] = $sitename;
            }
            restore_current_blog();
        }
        set_site_transient($transient_name, $meerkat_acad_sites, WEEK_IN_SECONDS);

        return $meerkat_acad_sites;
    }

    // get a list of sites in the Meerkat theme for one of the profile select menus
    $academic_sites_transient = 'academic_sites_array';
    if ( ! $meerkat_acad_sites = get_site_transient($academic_sites_transient)) {
        // rebuild the transient and get array
        $meerkat_acad_sites = get_meerkat_acad_sites($academic_sites_transient);
    }

    //---------- NAVIGATION

    if ( ! Meerkat16::instance()->is_magazine_theme) {
        $navigation_all = array(
            'staff_url'       => array(
                'key'               => 'field_50523d38972qq',
                'label'             => 'Where is your faculty/staff page?',
                'name'              => 'staff_url',
                'prefix'            => '',
                'type'              => 'post_object',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'post_type'         => array(
                    0 => 'page',
                ),
                'taxonomy'          => '',
                'allow_null'        => 1,
                'multiple'          => 0,
                'return_format'     => 'object',
                'ui'                => 1,
            ),
            'alt_ql_label'    => array(
                'key'               => 'field_50523d38972zz',
                'label'             => 'Quick Links Alt Label',
                'name'              => 'alt_ql_label',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            'alt_ql_url'      => array(
                'key'               => 'field_50523d38972yy',
                'label'             => 'Quick Links Alt Link',
                'name'              => 'alt_ql_url',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            'alt_event_label' => array(
                'key'               => 'field_50523d38972rr',
                'label'             => 'Events Alt Label',
                'name'              => 'alt_event_label',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            'alt_event_url'   => array(
                'key'               => 'field_50523d38972ss',
                'label'             => 'Events Alt Link',
                'name'              => 'alt_event_url',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
        );

        /*if(!is_super_admin()){
            $remove_keys = array('alt_ql_label','alt_ql_url','alt_event_label','alt_event_url');
            $navigation_all = array_diff_key($navigation_all, array_flip($remove_keys));
        }*/

        register_field_group(array(
            'key'                   => 'group_54fdb401d4c2c',
            'title'                 => 'Navigation',
            'fields'                => $navigation_all,
            'location'              => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'acf-options',
                    ),
                ),
            ),
            'menu_order'            => 1,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
        ));

        // Assemble list of all sites for Parent Site select box below.
        $site_objects = get_sites(array('orderby' => 'domain', 'number' => 500));
        $all_sites    = array('' => '');
        foreach ($site_objects as $site) {
            $details                     = get_blog_details($site->blog_id);
            $all_sites[ $site->blog_id ] = $details->blogname;
        }
        $site_options_all = array(
            'show_custom_fields'     => array(
                'key'               => 'field_4feb6716de76b',
                'label'             => 'Show custom fields',
                'name'              => 'show_custom_fields',
                'prefix'            => '',
                'type'              => 'true_false',
                'instructions'      => 'Show WordPress custom field input on post/page editing screen.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show',
                    'id'    => '',
                ),
                'message'           => '',
                'default_value'     => 0,
            ),
            'parent_site'            => array(
                "key"               => "field_5d701341d4985",
                "label"             => "Parent Site",
                "name"              => "parent_site",
                "type"              => "select",
                "instructions"      => "Setting a parent site will insert a link to the parent site into the breadcrumb directly before the link to this site's homepage.",
                "required"          => 0,
                "conditional_logic" => 0,
                "wrapper"           => array(
                    "width" => "",
                    "class" => "",
                    "id"    => ""
                ),
                "choices"           => $all_sites,
                "default_value"     => array(),
                "allow_null"        => 0,
                "multiple"          => 0,
                "ui"                => 0,
                "return_format"     => "array",
                "ajax"              => 0,
                "placeholder"       => ""
            ),
            'site_meta_desc'         => array(
                'key'               => 'field_5022a3c886d5e',
                'label'             => 'SEO description (homepage)',
                'name'              => 'site_meta_desc',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => 'A short blurb (160 char or less) that often shown in search engine results that help users decide if your site is relevant to their search.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            'private_page_content'   => array(
                'key'               => 'field_5spwn2w02vd7y',
                'label'             => 'Custom Private Page Content',
                'name'              => 'private_page_content',
                'type'              => 'wysiwyg',
                'instructions'      => 'Do you need to provide a generic message for private pages on this site? Place explanatory text in the field below (there is also a per-page option which will override this message), then set the <a href="http://wordpress.williams.edu/page-privacy/" title="Privacy Settings Documentation">visibility</a> of the pages in question to "Private." The page content will be hidden and your message will be displayed instead. See <a href="http://wordpress.williams.edu/page-options/" title="Page Options Documentation">documentation</a> for more information.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 0,
            ),
            'additional_login_links' => array(
                'key'               => 'field_58486a9f58c44',
                'label'             => 'Helpful Login Page Links',
                'name'              => 'additional_login_links',
                'type'              => 'repeater',
                'instructions'      => 'Add one or more links which will appear underneath the form on the Wordpress login page. ',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'collapsed'         => '',
                'min'               => '',
                'max'               => '',
                'layout'            => 'table',
                'button_label'      => 'Add Link',
                'sub_fields'        => array(
                    array(
                        'key'               => 'field_58486b4d58c45',
                        'label'             => 'Link Title',
                        'name'              => 'additional_login_link_title',
                        'type'              => 'text',
                        'instructions'      => 'Enter the link text.',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'default_value'     => '',
                        'placeholder'       => 'Enter title',
                        'prepend'           => '',
                        'append'            => '',
                        'maxlength'         => '',
                        'readonly'          => 0,
                        'disabled'          => 0,
                    ),
                    array(
                        'key'               => 'field_58486b8f58c46',
                        'label'             => 'Link URL',
                        'name'              => 'additional_login_link_url',
                        'type'              => 'url',
                        'instructions'      => 'Enter complete link address, including <strong>http://</strong>. ',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'default_value'     => '',
                        'placeholder'       => '',
                    ),
                ),
            ),
            'dept_title_font_size'   => array(
                'key'               => 'field_5086e2b285461',
                'label'             => 'Title font size',
                'name'              => 'dept_title_font_size',
                'prefix'            => '',
                'type'              => 'select',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show www-hide',
                    'id'    => '',
                ),
                'choices'           => array(
                    20 => 20,
                    22 => 22,
                    24 => 24,
                    26 => 26,
                    28 => 28,
                    30 => 30,
                    32 => 32,
                    34 => 34,
                    36 => 36,
                    38 => 38,
                    40 => 40,
                ),
                'default_value'     => array(
                    0 => 32,
                ),
                'allow_null'        => 0,
                'multiple'          => 0,
                'ui'                => 0,
                'ajax'              => 0,
                'placeholder'       => '',
                'disabled'          => 0,
                'readonly'          => 0,
            ),
            'dept_logo'              => array(
                'key'               => 'field_50900131e9fa7',
                'label'             => 'Department logo',
                'name'              => 'dept_logo',
                'prefix'            => '',
                'type'              => 'image',
                'instructions'      => 'Use a logo for your department name instead of text. Graphic must be a PNG file of 296 x 172 px or smaller on a transparent background.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show www-hide',
                    'id'    => '',
                ),
                'return_format'     => 'url',
                'preview_size'      => 'full',
                'library'           => 'all',
                'min_width'         => '',
                'min_height'        => '',
                'min_size'          => '',
                'max_width'         => '',
                'max_height'        => '',
                'max_size'          => '',
                'mime_types'        => 'png',
            ),
            'wordmark'               => array(
                'key'               => 'field_50523901ba40c',
                'label'             => 'Alternate Williams Wordmark',
                'name'              => 'wordmark',
                'prefix'            => '',
                'type'              => 'image',
                'instructions'      => 'Upload a PNG file with a transparent background to use instead of the default Williams wordmark.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden www-show',
                    'id'    => '',
                ),
                'return_format'     => 'url',
                'preview_size'      => 'full',
                'library'           => 'all',
                'min_width'         => '',
                'min_height'        => '',
                'min_size'          => '',
                'max_width'         => '',
                'max_height'        => '',
                'max_size'          => '',
                'mime_types'        => '',
            ),
        );

        /*if(!is_super_admin()){
            $remove_keys = array('show_custom_fields');
            $site_options_all = array_diff_key($site_options_all, array_flip($remove_keys));
        }*/

        /*if(!is_super_admin() || WWW_BLOG_ID == Meerkat16->instance()->blog_id ){
            $remove_keys = array('dept_logo','dept_title_font_size');
            $site_options_all = array_diff_key($site_options_all, array_flip($remove_keys));
        }*/

        if (247 == Meerkat16::instance()->blog_id) {
            $site_options_all['events_covid_message'] = array(
                'key' => 'field_61d86091fed44',
                'label' => 'Covid Message',
                'name' => 'covid_message',
                'type' => 'wysiwyg',
                'instructions' => 'Any text entered here will show up at the top of the Events homepage. Leave blank to hide message.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 0,
            );
        }

        register_field_group(array(
            'key'                   => 'group_54fdb73f0e624',
            'title'                 => 'Site Options',
            'fields'                => $site_options_all,
            'location'              => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'acf-options',
                    ),
                ),
            ),
            'menu_order'            => 2,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => array(
                0 => 'custom_fields',
                1 => 'discussion',
                2 => 'comments',
                3 => 'slug',
                4 => 'author',
            ),
        ));
    }

    //---------- Google Analytics Link Tracking
    register_field_group(array(
        'key'                   => 'group_54fdbcd443d11',
        'title'                 => 'Link Tracking',
        'fields'                => array(
            array(
                'key'               => 'field_537f95f0f0a06',
                'label'             => 'Google Analytics Link Tracking',
                'name'              => 'ga_links',
                'prefix'            => '',
                'type'              => 'repeater',
                'instructions'      => 'Enter the URL or <a target="_blank" href="http://api.jquery.com/category/selectors/">jQuery selector</a> of the link you wish to track. Click the \'Add Link\' button to add additional links.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => 'acf-hidden super-admin-show',
                    'id'    => '',
                ),
                'min'               => '',
                'max'               => '',
                'layout'            => 'table',
                'button_label'      => 'Add Link',
                'sub_fields'        => array(
                    array(
                        'key'               => 'field_537f95f0f0b58',
                        'label'             => 'Link',
                        'name'              => 'ga_link',
                        'prefix'            => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'default_value'     => '',
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                        'maxlength'         => '',
                        'readonly'          => 0,
                        'disabled'          => 0,
                    ),
                ),
            ),
        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'acf-options',
                ),
            ),
        ),
        'menu_order'            => 3,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => '',
    ));

    //---------- CONTACT INFO

    register_field_group(array(
        'key'                   => 'group_54fdbe1bb9791',
        'title'                 => 'Contact Information',
        'fields'                => array(
            array(
                'key'               => 'field_4fa66f4957a89',
                'label'             => 'Address 1',
                'name'              => 'address_1',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fa66f495804a',
                'label'             => 'Address 2',
                'name'              => 'address_2',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fa66f4958339',
                'label'             => 'City',
                'name'              => 'city',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => 'Williamstown',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fa66f49585e9',
                'label'             => 'State',
                'name'              => 'state',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '2-digit abbreviation',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => 'MA',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fa66f4958877',
                'label'             => 'Zip Code',
                'name'              => 'zipcode',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '5-digit ZIP, or ZIP+4',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '01267',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fa66f4958bd3',
                'label'             => 'Phone',
                'name'              => 'phone',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => 'Suggested format: 413.597.3131',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fa66f4958zzz',
                'label'             => 'Fax',
                'name'              => 'fax',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => 'Suggested format: 413.597.3131',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fa66f4958eda',
                'label'             => 'Contact Email',
                'name'              => 'contact_email',
                'prefix'            => '',
                'type'              => 'email',
                'instructions'      => 'Email address for the primary contact person for this website.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
            ),
            array(
                'key'               => 'field_4fa66f4958zzzzz',
                'label'             => 'Contact Notes',
                'name'              => 'contact_notes',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'acf-options',
                ),
            ),
        ),
        'menu_order'            => 998,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => '',
    ));

    function wms_register_page_options() {

        /*
         * PAGE OPTIONS
        */

        $page_options_locations = array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'post',
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'page',
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'fl-theme-layout',
                ),
            ),
        );

        // Add this field to any custom post types defined by us
        if (function_exists('cptui_get_post_type_slugs')) {
            $cpts = cptui_get_post_type_slugs();
            if ($cpts) {
                foreach ($cpts as $cpt) {
                    $page_options_locations[] = array(
                        array(
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => $cpt,
                        ),
                    );
                }
            }
        }

        $page_options_all = array(
            'page_custom_breadcrumb' => array(
                'key'           => 'field_5022a528d0b77123',
                'label'         => 'Custom Breadcrumb Text',
                'name'          => 'page_breadcrumb',
                'type'          => 'text',
                'instructions'  => 'If you wish the page breadcrumb to be different or shorter, enter that text here. If this field is left blank, the page title will be used in the breadcrumb.',
                'required'      => '0',
                'default_value' => '',
                'formatting'    => 'html',
                'order_no'      => '1',
            ),
            'text_cols'              => array(
                'key'           => 'field_4feccb8bc0514',
                'label'         => 'Number of text columns',
                'name'          => 'text_cols',
                'type'          => 'radio',
                'instructions'  => 'Splits content into newspaper-like columns.',
                'required'      => '0',
                'choices'       => array(
                    1 => '1',
                    2 => '2',
                    3 => '3',
                ),
                'default_value' => '1',
                'layout'        => 'horizontal',
                'order_no'      => '0',
            ),
            'dek'                    => array(
                'key'           => 'field_575f0803a999b',
                'label'         => 'Stylized first paragraph (dek)',
                'name'          => 'dek',
                'type'          => 'true_false',
                'instructions'  => '',
                'required'      => 0,
                'message'       => '',
                'default_value' => 0,
                'order_no'      => '0.5',
            ),
            'last_updated'           => array(
                'key'          => 'field_4feccb8bc53xx',
                'label'        => 'Show last updated',
                'name'         => 'last_updated',
                'type'         => 'true_false',
                'instructions' => 'Displays last updated information at the bottom of the page.',
                'required'     => '0',
                'message'      => '',
                'order_no'     => '1',
            ),
            'page_meta_desc'         => array(
                'key'           => 'field_5022a528d0b77',
                'label'         => 'SEO Description',
                'name'          => 'page_meta_desc',
                'type'          => 'text',
                'instructions'  => 'A short blurb (160 chars or less) often shown in search engine results that helps users decide if your site is relevant to their search.',
                'required'      => '0',
                'default_value' => '',
                'formatting'    => 'html',
                'order_no'      => '2',
            ),
            'private_page_content'   => array(
                'key'               => 'field_5660a10c8fd75',
                'label'             => 'Custom Private Page Content',
                'name'              => 'private_page_content',
                'type'              => 'wysiwyg',
                'instructions'      => 'Do you need to hide this page temporarily? Place explanatory text in the field below, then set the <a href="http://wordpress.williams.edu/page-privacy/" title="Privacy Settings documentation">visibility</a> of the page to "private." The page content will be hidden and your message will be displayed instead. <strong>Note</strong>: There is also a <a href="' . get_admin_url(null, '/admin.php?page=acf-options') . '" title="Admin Options Page">site-wide option</a>. See <a href="http://wordpress.williams.edu/page-options/" title="Page Options documentation">documentation</a> for more information.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => '3',
            ),
            'campus_only'            => array(
                'key'          => 'field_4feccb8bc53ad',
                'label'        => 'Campus only',
                'name'         => 'campus_only',
                'type'         => 'true_false',
                'instructions' => 'Display content only to people with an on-campus IP address.',
                'required'     => '0',
                'message'      => '',
                'order_no'     => '3',
            ),
            'hide_sidebar'           => array(
                'key'          => 'field_4feccb8bbde05',
                'label'        => 'Hide sidebar',
                'name'         => 'hide_sidebar',
                'type'         => 'true_false',
                'instructions' => '',
                'required'     => '0',
                'message'      => '',
                'order_no'     => '5',
            ),
            'hide_print'             => array(
                'key'      => 'field_4feccb8bc53yy',
                'label'    => 'Hide print button',
                'name'     => 'hide_print',
                'type'     => 'true_false',
                'required' => '0',
                'message'  => '',
                'order_no' => '6',
            ),
            'hide_title'             => array(
                'key'      => 'field_4feccb8bc53zz',
                'label'    => 'Hide page title and breadcrumbs',
                'name'     => 'hide_title',
                'type'     => 'true_false',
                'required' => '0',
                'message'  => '',
                'order_no' => '7',
            ),
            'page_style'             => array(
                'key'           => 'field_4feccb8bc53ww',
                'label'         => 'Add page style',
                'name'          => 'page_style',
                'type'          => 'select',
                'instructions'  => '',
                'required'      => '0',
                'choices'       => array(
                    ''          => '-- none --',
                    'intro'     => 'Intro page (larger type, more spacing)',
                    'splash'    => 'Splash page (large image w/ caption)',
                    //'quad'      => '4 block',
                    'wide'      => 'Wide',
                    'mediawall' => 'Media wall'
                ),
                'default_value' => '',
                'allow_null'    => '0',
                'multiple'      => '0',
                'order_no'      => '8',
            ),
    
        );

        // We want only these fields for Magazine
        if (Meerkat16::instance()->is_magazine_theme) {
            $keep_keys        = array('text_cols');
            $page_options_all = array_intersect_key($page_options_all, array_flip($keep_keys));
        }

        register_field_group(array(
            'key'                   => 'group_54fdc1d324727',
            'title'                 => 'Page Options',
            'fields'                => $page_options_all,
            'location'              => $page_options_locations,
            'menu_order'            => 100,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
        ));
    }

    add_action('init', 'wms_register_page_options');

    //---------- PROFILE

    register_field_group(array(
        'key'                   => 'group_54fdfc274792c',
        'title'                 => 'Profile',
        'fields'                => array(
            array(
                'key'               => 'field_4fedad922565d',
                'label'             => 'Williams User Name',
                'name'              => 'profile_unix',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_999965c054910',
                'label'             => 'Primary Profile Site',
                'name'              => 'profile_alt_dept',
                'prefix'            => '',
                'type'              => 'select',
                'instructions'      => 'If this is not your main department, select the site that has your primary profile, and leave the rest of this profile blank.<br>The profile information from the site you select below will be used when viewing your profile on this site.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'choices'           => $meerkat_acad_sites,
                'default_value'     => array(
                    '' => '',
                ),
                'allow_null'        => 1,
                'multiple'          => 0,
                'ui'                => 0,
                'ajax'              => 0,
                'placeholder'       => '',
                'disabled'          => 0,
                'readonly'          => 0,
            ),
            array(
                'key'               => 'field_4fedad9227d5b',
                'label'             => 'Additional Contact Info',
                'name'              => 'profile_contact',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => 'Your basic contact information will automatically be listed. If you\'d like to include additional information (e.g. office hours, lab phone), please enter it below.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 1,
            ),
            array(
                'key'               => 'field_502965c054910',
                'label'             => 'External Website',
                'name'              => 'profile_website',
                'prefix'            => '',
                'type'              => 'url',
                'instructions'      => 'Enter URL, starting with http://',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_55c128f60bf57',
                'label'             => 'Upload your CV',
                'name'              => 'profile_cv_upload',
                'prefix'            => '',
                'type'              => 'file',
                'instructions'      => 'Upload a pdf file. To update your CV, first remove the file below, then upload your modified pdf.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'return_format'     => 'url',
                'library'           => 'uploadedTo',
                'min_size'          => '',
                'max_size'          => '',
                'mime_types'        => '.pdf',
            ),
            array(
                'key'               => 'field_4fedad922a47a',
                'label'             => 'At Williams Since',
                'name'              => 'profile_at_wms_since',
                'prefix'            => '',
                'type'              => 'text',
                'instructions'      => 'A year, e.g. 2001',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'placeholder'       => '',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'readonly'          => 0,
                'disabled'          => 0,
            ),
            array(
                'key'               => 'field_4fedad922cbd7',
                'label'             => 'Areas of Expertise',
                'name'              => 'profile_interests',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 1,
            ),
            array(
                'key'               => 'field_502965c05701e',
                'label'             => 'Scholarship & Creative Work',
                'name'              => 'profile_publications',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => 'You can list publications, artwork, projects, etc. below.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 1,
            ),
            array(
                'key'               => 'field_502965c05972e',
                'label'             => 'Awards, Fellowships & Grants',
                'name'              => 'profile_grants',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 1,
            ),
            array(
                'key'               => 'field_502965c05bea3',
                'label'             => 'Professional Affiliations',
                'name'              => 'profile_affiliations',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 1,
            ),
            array(
                'key'               => 'field_wms_profile_other_service',
                'label'             => 'Other Service',
                'name'              => 'profile_other_service',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => 'List non-standing committees and other service work here. This section will appear below standing committees (supplied by PeopleSoft), if any.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 1,
            ),
            array(
                'key'               => 'field_502965c05e54e',
                'label'             => 'Other Information',
                'name'              => 'profile_other',
                'prefix'            => '',
                'type'              => 'wysiwyg',
                'instructions'      => 'The section below can be used to list thesis students, previous posts, etc.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value'     => '',
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 1,
            ),
            array(
                'key'               => 'field_502965c05e59x',
                'label'             => 'Hide Degree Dates',
                'name'              => 'profile_supress_dates',
                'prefix'            => '',
                'type'              => 'true_false',
                'instructions'      => 'Hide the years in which you received your degrees.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'message'           => '',
                'default_value'     => 0,
            ),
        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'profile',
                ),
            ),
        ),
        'menu_order'            => 999,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => '',
    ));

    //---------- MAGAZINE

    if (Meerkat16::instance()->is_magazine_theme) {
        register_field_group(array(
            'key'                   => 'group_54fdc1b28262c',
            'title'                 => 'TOC Excerpt',
            'fields'                => array(
                array(
                    'key'               => 'field_51422448977c3',
                    'label'             => '',
                    'name'              => 'section_grid_excerpt',
                    'prefix'            => 'TOC Excerpt',
                    'type'              => 'text',
                    'instructions'      => 'Enter an excerpt to appear on the home page image rollover.	Keep it short!',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'default_value'     => '',
                    'formatting'        => 'html',
                    'maxlength'         => '',
                    'placeholder'       => '',
                    'prepend'           => '',
                    'append'            => '',
                    'readonly'          => 0,
                    'disabled'          => 0,
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'feature',
                    ),
                ),
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'post',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => array(),
        ));

        register_field_group(array(
            'key'                   => 'group_54fdc1b28744e',
            'title'                 => 'Features',
            'fields'                => array(
                array(
                    'key'               => 'field_5140dd1289101',
                    'label'             => 'Section Grid Image',
                    'name'              => 'features_grid_image',
                    'prefix'            => '',
                    'type'              => 'image',
                    'instructions'      => '<p>Upload an image for the home page grid.</p><p>Top Image: 786w x 374h (px) <br>Bottom Images: 260w x 260h (px)</p>',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'preview_size'      => 'medium',
                    'return_format'     => 'id',
                    'library'           => 'all',
                    'min_width'         => 0,
                    'min_height'        => 0,
                    'min_size'          => 0,
                    'max_width'         => 0,
                    'max_height'        => 0,
                    'max_size'          => 0,
                    'mime_types'        => '',
                ),
                array(
                    'key'               => 'field_5140dd128b810',
                    'label'             => 'Section Grid Location',
                    'name'              => 'section_grid_location',
                    'prefix'            => '',
                    'type'              => 'radio',
                    'instructions'      => 'Choose the location for the Feature Story on the home page grid.',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'choices'           => array(
                        0 => 'Top',
                        1 => 'Bottom Left',
                        2 => 'Bottom Middle',
                        3 => 'Bottom Right',
                    ),
                    'default_value'     => '',
                    'layout'            => 'vertical',
                    'other_choice'      => 0,
                    'save_other_choice' => 0,
                ),
                array(
                    'key'               => 'field_513f23921f6ab',
                    'label'             => 'Feature Sections',
                    'name'              => 'features_repeater',
                    'prefix'            => '',
                    'type'              => 'repeater',
                    'instructions'      => '<p>Enter sections of composite stories here.</p>',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'row_min'           => 0,
                    'row_limit'         => '',
                    'layout'            => 'row',
                    'button_label'      => 'Add Section',
                    'min'               => 0,
                    'max'               => 0,
                    'sub_fields'        => array(
                        array(
                            'key'               => 'field_513f239221dc2',
                            'label'             => 'Section Title',
                            'name'              => 'features_section_title',
                            'prefix'            => '',
                            'type'              => 'text',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'default_value'     => '',
                            'formatting'        => 'html',
                            'maxlength'         => '',
                            'placeholder'       => '',
                            'prepend'           => '',
                            'append'            => '',
                            'readonly'          => 0,
                            'disabled'          => 0,
                        ),
                        array(
                            'key'               => 'field_513f2392244b0',
                            'label'             => 'Section Subhead',
                            'name'              => 'features_section_subhead',
                            'prefix'            => '',
                            'type'              => 'text',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'default_value'     => '',
                            'formatting'        => 'html',
                            'maxlength'         => '',
                            'placeholder'       => '',
                            'prepend'           => '',
                            'append'            => '',
                            'readonly'          => 0,
                            'disabled'          => 0,
                        ),
                        array(
                            'key'               => 'field_513f2392292cb',
                            'label'             => 'Section Excerpt',
                            'name'              => 'features_section_excerpt',
                            'prefix'            => '',
                            'type'              => 'textarea',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'default_value'     => '',
                            'new_lines'         => 'br',
                            'maxlength'         => '',
                            'placeholder'       => '',
                            'readonly'          => 0,
                            'disabled'          => 0,
                            'rows'              => '',
                        ),
                        array(
                            'key'               => 'field_513f239226c1f',
                            'label'             => 'Section Content',
                            'name'              => 'features_section_content',
                            'prefix'            => '',
                            'type'              => 'wysiwyg',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'toolbar'           => 'full',
                            'media_upload'      => 1,
                            'the_content'       => 'yes',
                            'tabs'              => 'all',
                            'default_value'     => '',
                        ),
                    ),
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'feature',
                    ),
                ),
            ),
            'menu_order'            => 1,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => array(
                0 => 'custom_fields',
                1 => 'discussion',
                2 => 'comments',
                3 => 'slug',
                4 => 'format',
            ),
        ));

        register_field_group(array(
            'key'                   => 'group_54fef2c939760',
            'title'                 => 'Magazine Options',
            'fields'                => array(
                array(
                    'key'               => 'field_54fef303e6242',
                    'label'             => 'Current Edition Year',
                    'name'              => 'current_edition_year',
                    'prefix'            => '',
                    'type'              => 'taxonomy',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'taxonomy'          => 'volume_year',
                    'field_type'        => 'select',
                    'allow_null'        => 0,
                    'load_save_terms'   => 0,
                    'return_format'     => 'object',
                    'multiple'          => 0,
                ),
                array(
                    'key'               => 'field_54fef388e6243',
                    'label'             => 'Current Edition Season/Month',
                    'name'              => 'current_edition_issue',
                    'prefix'            => '',
                    'type'              => 'taxonomy',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'taxonomy'          => 'volume_issue',
                    'field_type'        => 'select',
                    'allow_null'        => 0,
                    'load_save_terms'   => 0,
                    'return_format'     => 'object',
                    'multiple'          => 0,
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'acf-options',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
        ));

        // Edition Archive, Edition Options & Edition Gallery all used by Edition Options widget.
        register_field_group(array(
            'key'                   => 'group_54fe08c50401f',
            'title'                 => 'Edition Options',
            'fields'                => array(
                array(
                    'key'               => 'field_51965fc173e7c',
                    'label'             => 'Instructions',
                    'name'              => '',
                    'prefix'            => '',
                    'type'              => 'message',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'message'           => 'Important! This post must be named <strong>YYYY [Spring|Summer|Fall|Winter]</strong>',
                    'esc_html'          => 0,
                ),
                array(
                    'key'               => 'field_5140bb3703ed8',
                    'label'             => 'Edition Sections',
                    'name'              => 'toc_sections_rpt',
                    'prefix'            => '',
                    'type'              => 'repeater',
                    'instructions'      => 'Choose the sections for this edition by selecting them. Drag them up or down to reorder.',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'min'               => '',
                    'max'               => '',
                    'layout'            => 'table',
                    'button_label'      => 'Add Section',
                    'sub_fields'        => array(
                        array(
                            'key'               => 'field_5140bda041984',
                            'label'             => 'Section',
                            'name'              => 'toc_section',
                            'prefix'            => '',
                            'type'              => 'taxonomy',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'taxonomy'          => 'category',
                            'field_type'        => 'select',
                            'allow_null'        => 0,
                            'load_save_terms'   => 0,
                            'return_format'     => 'object',
                            'multiple'          => 0,
                        ),
                        array(
                            'key'               => 'field_5140bda04198b',
                            'label'             => 'Description',
                            'name'              => 'toc_section_description',
                            'prefix'            => '',
                            'type'              => 'text',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'default_value'     => '',
                            'placeholder'       => '',
                            'prepend'           => '',
                            'append'            => '',
                            'maxlength'         => '',
                            'readonly'          => 0,
                            'disabled'          => 0,
                        ),
                    ),
                ),
                array(
                    'key'               => 'field_5142133175285',
                    'label'             => 'Edition Gallery',
                    'name'              => 'edition_gallery_posts',
                    'prefix'            => '',
                    'type'              => 'relationship',
                    'instructions'      => 'Select 4 posts for use in the Edition Gallery widget. The featured image from the posts will be used for the widget. Drag selected posts up or down to order them.',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'post_type'         => array(
                        0 => 'post',
                        1 => 'feature',
                    ),
                    'taxonomy'          => '',
                    'filters'           => array(
                        0 => 'search',
                        1 => 'post_type',
                        2 => 'taxonomy',
                    ),
                    'elements'          => '',
                    'max'               => 4,
                    'return_format'     => 'object',
                ),/*
        		array (
        			'key' => 'field_514341b79e60d',
        			'label' => 'Edition Archive PDF',
        			'name' => 'archive_pdf',
        			'prefix' => '',
        			'type' => 'text',
        			'instructions' => 'Paste PDF file link here. This field not used.',
        			'required' => 0,
        			'conditional_logic' => 0,
        			'wrapper' => array (
        				'width' => '',
        				'class' => '',
        				'id' => '',
        			),
        			'default_value' => '',
        			'placeholder' => '',
        			'prepend' => '',
        			'append' => '',
        			'maxlength' => '',
        			'readonly' => 0,
        			'disabled' => 0,
        		),*/
                array(
                    'key'               => 'field_514341b7a0d20',
                    'label'             => 'PDF Cover',
                    'name'              => 'archive_pdf_cover',
                    'prefix'            => '',
                    'type'              => 'image',
                    'instructions'      => 'Upload cover image.	The image should be 150w x 194h (px).',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'return_format'     => 'id',
                    'preview_size'      => 'full',
                    'library'           => 'all',
                    'min_width'         => '',
                    'min_height'        => '',
                    'min_size'          => '',
                    'max_width'         => '',
                    'max_height'        => '',
                    'max_size'          => '',
                    'mime_types'        => '',
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'toc_desc',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
        ));
    }

    if (194 == Meerkat16::instance()->blog_id) {
        register_field_group(array(
            'key'                   => 'group_5527d60197abe',
            'title'                 => 'Related Content',
            'fields'                => array(
                array(
                    'key'               => 'field_5527d6107c947',
                    'label'             => 'Related Content',
                    'name'              => 'related_content',
                    'prefix'            => '',
                    'type'              => 'repeater',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'min'               => '',
                    'max'               => '',
                    'layout'            => 'table',
                    'button_label'      => 'Add Row',
                    'sub_fields'        => array(
                        array(
                            'key'               => 'field_5527d64c7c949',
                            'label'             => 'Label',
                            'name'              => 'related_content_label',
                            'prefix'            => '',
                            'type'              => 'text',
                            'instructions'      => '',
                            'required'          => 1,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'default_value'     => '',
                            'placeholder'       => '',
                            'prepend'           => '',
                            'append'            => '',
                            'maxlength'         => '',
                            'readonly'          => 0,
                            'disabled'          => 0,
                        ),
                        array(
                            'key'               => 'field_5527d6327c948',
                            'label'             => 'URL',
                            'name'              => 'related_content_url',
                            'prefix'            => '',
                            'type'              => 'text',
                            'instructions'      => '',
                            'required'          => 1,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'default_value'     => '',
                            'placeholder'       => '',
                            'prepend'           => '',
                            'append'            => '',
                            'maxlength'         => '',
                            'readonly'          => 0,
                            'disabled'          => 0,
                        ),
                    ),
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'post',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
        ));
    }

    //---------- PAGE REDIRECT
    function wms_register_page_redirect_field() {
        $locations = array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'post',
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'page',
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'profile',
                ),
            ),
        );
        // Add this field to any custom post types defined by us
        if (function_exists('cptui_get_post_type_slugs')) {
            $cpts = cptui_get_post_type_slugs();
            if ($cpts) {
                foreach ($cpts as $cpt) {
                    $object = cptui_get_cptui_post_type_object($cpt);
                    if (preg_match("/page[\s\-_]redirect/i", $object['custom_supports'])) {
                        $locations[] = array(
                            array(
                                'param'    => 'post_type',
                                'operator' => '==',
                                'value'    => $cpt,
                            ),
                        );
                    }
                }
            }
        }

        register_field_group(array(
            'key'                   => 'group_54fe045d452eb',
            'title'                 => 'Page Redirect',
            'fields'                => array(
                array(
                    'key'               => 'field_502abdb7f1030',
                    'label'             => 'Redirect URL',
                    'name'              => 'page_redirect',
                    'prefix'            => '',
                    'type'              => 'text',
                    'instructions'      => 'Instead of showing this page/post, the user will be redirected to this URL.',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'default_value'     => '',
                    'placeholder'       => '',
                    'prepend'           => '',
                    'append'            => '',
                    'maxlength'         => '',
                    'readonly'          => 0,
                    'disabled'          => 0,
                ),
            ),
            'location'              => $locations,
            'menu_order'            => 999,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
        ));
    }

    //---------- Page Redirect field needs to be added later
    add_action('init', 'wms_register_page_redirect_field');
}

//always hide options for non-admins, this covers ACF json options also
function wms_custom_menu_page_removing() {
    if ( ! current_user_can('activate_plugins')) { // Administrator
        remove_menu_page('acf-options');
    }
}

add_action('admin_menu', 'wms_custom_menu_page_removing', 10000);

/**
 * Class M20_BB_Acf
 *
 * @see https://www.advancedcustomfields.com/resources/custom-location-rules/
 * @see https://www.billerickson.net/acf-custom-location-rules/
 */
class M16_Acf_Options {

    public function __construct() {
        // Next 3 filters add a Site field to the ACF location rules dropdown.
        add_filter('acf/location/rule_types', array(__CLASS__, 'acf_rule_type_site_id'));
        add_filter('acf/location/rule_values/site_id', array(__CLASS__, 'acf_rule_values_site_id'));
        add_filter('acf/location/rule_match/site_id', array(__CLASS__, 'acf_location_rule_match_site_id'), 10, 3);
    }

    function acf_rule_type_site_id($choices) {
        $choices['Site']['site_id'] = 'Site';

        return $choices;
    }

    function acf_rule_values_site_id($choices) {
        $sites = get_sites(array(
            'public'  => 1,
            'number'  => 500,
            'orderby' => 'domain'
        ));
        foreach ($sites as $site) {
            $choices[ $site->id ] = WP_Site::get_instance($site->id)->blogname;
        }

        return $choices;
    }

    function acf_location_rule_match_site_id($match, $rule, $screen) {
        $site          = get_current_blog_id();
        $selected_site = (int) $rule['value'];

        if ($rule['operator'] == "==") {
            $match = ($site == $selected_site);
        } elseif ($rule['operator'] == "!=") {
            $match = ($site != $selected_site);
        }

        return $match;
    }
}

new M16_Acf_Options();
