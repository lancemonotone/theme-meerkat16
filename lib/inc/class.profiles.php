<?php

/*
Creates a profile by merging information from peoplesoft with information from a custom post of type profile.
Unix ID must be entered into custom post to connect info between the post & peoplesoft
Intercepts requests to blah.williams.edu/profile/unix so that even if the person has no post, peoplesoft info will still be available.
*/

class Meerkat16_Profiles {
    private static $instance;
    // this is what advanced custom fields call the input box for a person's unix user name
    public $acf_unix_field_id = 'field_4fedad922565d';
    public $is_wms_profile = false;

    protected function __construct() {
        $this->add_hooks();
    }

    private function add_hooks() {
        // template handling
        add_action('template_redirect', array(&$this, 'profile_template'));
        // define custom post types
        add_action('init', array(&$this, 'custom_post_support'), 1);
        // add new input fields into post editor
        add_action('admin_menu', array(&$this, 'add_profile_boxes'), 1);
        // change owner of post to faculty member
        add_filter('wp_insert_post_data', array(&$this, 'chown_post'));
        // give better title prompt
        add_filter('enter_title_here', array(&$this, 'default_title'));
        // remove view link from quick edit, it goes to the wrong place
        add_filter('post_row_actions', array(&$this, 'quick_edit_modifications'));
        // remove bulk actions for profiles. this just gets messy
        add_filter('bulk_actions-edit-profile', array(&$this, 'remove_bulk_actions'));
        // tweak post updated message, which provides incorrect preview url
        add_filter('post_updated_messages', array(&$this, 'tweak_post_updated'));
        // fix ambiguous profile links in dashboard menus
        add_action('admin_bar_menu', array(&$this, 'tweak_toolbar'), 999);
        add_action('admin_menu', array(&$this, 'tweak_admin_menu'));
    }

    function custom_post_support() {
        // add support for the 'profile' custom post type

        $labels = array(
            'name'               => 'Faculty/Staff',
            'singular_label'     => 'Profile',
            'add_new'            => 'Add New Profile',
            'add_new_item'       => 'Add New Profile',
            'new_item'           => 'Create Profile',
            'edit_item'          => 'Edit Profile',
            'view_item'          => 'View Profile',
            'search_items'       => 'Search Profiles',
            'not_found'          => 'No profiles found',
            'not_found_in_trash' => 'No profiles found in Trash',
        );

        $args = array(
            'labels'            => $labels,
            'public'            => true,
            'show_ui'           => true,
            'capability_type'   => 'post',
            'show_in_nav_menus' => false,
            'supports'          => array('title', 'author', 'thumbnail'),
            'rewrite'           => array('slug' => 'profile'),
            'menu_icon'         => 'dashicons-id',
            'taxonomies'        => array('topics', 'category'),

        );

        register_post_type('profile', $args);
    }

    function add_profile_boxes() {
        // create our custom input boxes for the profile edit screen ("about" section)
        // add_meta_box( $id, $title, $callback, $page, $context, $priority, $callback_args );
        add_meta_box('admin_profile_container', 'About Profiles', array(
            &$this,
            'make_profile_box'
        ), 'profile', 'normal', 'high');
    }

    function make_profile_box() {
        // create html for profile edit screen
        global $post;

        // intro title & blurb
        ?>
        <div class="metabox-blurb">
            <a class="metabox-help-docs" href="http://wordpress.williams.edu/wms-profile/" target="_new">Profile Help Documentation</a>
            <h4>A basic profile page is automatically created for all faculty & staff. It includes:</h4>
            <ul>
                <li>Name & position title</li>
                <li>Contact information (phone, email, office)</li>
                <li>Higher education degrees</li>
                <li>Courses taught this academic year</li>
                <li>Current Williams faculty committee membership</li>
                <li>Directory photo</li>
            </ul>
            <h4>To customize your profile:</h4>
            <ul>
                <li>Information supplied in the form below will be added to your profile.</li>
                <li>If you'd like to upload a different photo of yourself, use the "Profile Image" tool in the right sidebar.
                </li>
            </ul>

            <?php
            $view_href = $this->get_profile_view_link();

            if ($view_href) {
                echo '<a class="button view_profile" target="_new" href="' . $view_href . '">View Williams Profile</a>';
            } else {
                // if this is a create & not edit, we don't know who the profile is for, so we guess the current user
                global $current_user;
                get_currentuserinfo();
                $user = $current_user->data->user_login;
                echo '<a class="button view_profile" target="_new" href="/profile/' . $user . '">View Default Profile</a>';
            }
            ?>
        </div>

        <?php
    }

    function chown_post($data) {
        // change ownership of post to the user (as defined by williams user id) if they exist
        $acf_fields = $_POST['acf'];

        $unix = sanitize_title($acf_fields[ $this->acf_unix_field_id ]);
        if ($unix) {
            // does unix name exist as a wpmu user?
            if ($user = get_userdatabylogin($unix)) {
                // make sure user has author+ status on this blog
                if (user_can($user->ID, 'edit_posts')) {
                    // change owner of this post to the person it represents
                    $data['post_author'] = $user->ID;
                }
            }
            // modify guid to a profile/unix url instead of profile/first-last
            $data['guid'] = get_site_url() . '/profile/' . $unix;
        }

        return $data;
    }

    function default_title($post) {
        // overrides default 'Enter Title Here' for profile
        if (isset($_GET['post_type']) && $_GET['post_type'] == 'profile') {
            return 'Enter Full Name Here';
        } else {
            return 'Enter Title Here';
        }
    }

    function quick_edit_modifications($actions) {
        /* since we're doing wacky stuff with this post type, view goes to the wrong place
           and quick edit doesn't do anything useful, so nuke 'em */
        if ($_GET['post_type'] == 'profile') {
            // alter "view" link
            $unix            = get_field('profile_unix');
            $profile_url     = get_site_url() . '/profile/' . $unix;
            $actions['view'] = '<a href="' . $profile_url . '">View</a>';
            // get rid of "quick edit" link
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }

    function remove_bulk_actions() {
        return false;
    }

    function tweak_post_updated($messages) {
        // status messages on post save have wrong link.
        global $post;
        if (get_post_type($post) == 'profile') {
            unset ($messages['post'][1]); // post updated
            unset ($messages['post'][6]); // post published
            unset ($messages['post'][8]); // post submitted
        }

        return $messages;
    }

    function tweak_toolbar() {
        // modifies links under "howdy, username" referencing profiles in top toolbar
        global $wp_admin_bar, $current_user, $post;

        // replace 'Edit Profile' link to edit wordpress profile with link for williams profile
        $edit_href = $this->get_profile_edit_link($current_user->user_login, true);

        if ($edit_href) {
            $wms_args = array(
                'id'     => 'edit-profile',
                'title'  => 'Edit Williams Profile',
                'href'   => $edit_href,
                'parent' => 'user-actions'
            );
            $wp_admin_bar->add_node($wms_args);
        } else {
            $wp_admin_bar->remove_menu('edit-profile');
        }

        // remove other edit wordpress profile link (linked username text)
        $wp_admin_bar->remove_menu('user-info');

        // remove incorrect view profile link in toolbar when editing profile
        if (isset($post) && $post->post_type == 'profile') {
            $wp_admin_bar->remove_menu('view');
            if (is_admin()) {
                $view_href = $this->get_profile_view_link();
                $view_args = array(
                    'id'    => 'view',
                    'title' => 'View Profile',
                    'href'  => $view_href
                );
            } else {
                $edit_href = get_edit_post_link();
                $view_args = array(
                    'id'    => 'view',
                    'title' => 'Edit Profile',
                    'href'  => $edit_href
                );
            }
            $wp_admin_bar->add_node($view_args);
        }
    }

    function tweak_admin_menu() {
        // modifies links to profiles in left hand admin menu
        global $current_user, $menu, $submenu;

        // add link to individual's profile under "Faculty/Staff" menu item
        $profile_url = $this->get_profile_edit_link($current_user->user_login, false);
        if ($profile_url) {
            add_submenu_page('edit.php?post_type=profile', 'Your Williams Profile', 'Your Williams Profile', 'edit_posts', $profile_url);
        }

        // remove references to "Profile" in admin users menu to avoid confusing the WordPress user settings with the Williams Profile
        if (current_user_can('add_users')) {
            // admins have a "Users" menu with a "Your Profile" subitem
            $submenu['users.php'][15][0] = 'Your User Settings';
        } else {
            // authors, editors, etc. have a "Profile" menu instead of a "Users" menu- change Profile label to User Settings
            $menu[70][0]                  = 'User Settings';
            $submenu['profile.php'][5][0] = 'Your User Settings';
        }
    }

    function get_profile_view_link() {
        $unix = get_field('profile_unix');
        if ($unix) {
            return get_site_url() . '/profile/' . $unix;
        }

        return false;
    }

    function get_profile_edit_link($user_login, $include_wp_admin) {
        // construct dashboard link to profile page, if user has one
        $profile_post = false;

        $args     = array('post_type' => 'profile', 'meta_key' => 'profile_unix', 'meta_value' => $user_login);
        $profiles = get_posts($args);
        foreach ($profiles as $profile) {
            $profile_post = $profile->ID;
        }
        wp_reset_postdata();

        if ($profile_post) {
            $edit_url = '/post.php?action=edit&post=' . $profile_post;
            if ($include_wp_admin) {
                return '/wp-admin' . $edit_url;
            } else {
                return $edit_url;
            }
        }

        return false;
    }

    function profile_template() {
        // intercept all requests to site.williams.edu/profile/X
        // (this will pick up requests for people who don't have a post)
        //if( substr( $_SERVER[ 'REQUEST_URI' ], 0, 9 ) == '/profile/' ){
        if( Meerkat16::instance()->is_profile_page ){
            $this->is_wms_profile = true;
            if ($profile = Meerkat16_Profile_Single::instance()->get_the_profile()) {
                Timberizer::render_template(array('template' => 'profile', 'profile' => $profile));
            } else {
                Timberizer::render_template(array('template' => '404'));
            }
            exit(0);
        }
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Meerkat16_Profile_Single The singleton instance.
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

Meerkat16_Profiles::instance();
