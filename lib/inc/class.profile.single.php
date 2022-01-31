<?php

class Meerkat16_Profile_Single {
    private static $instance;
    var $profile_id,
        $profile_post,
        $unix_from_url,
        $has_ldap = false,
        $has_post = false,
        $profile_site_id,
        $ldap_record,
        $current_blog;

    protected function __construct(){
        $this->init();
    }

    public function get_the_profile(){
        // check for redirect
        if( $this->profile_id ){
            $this->do_redirect();
        }
        // bail
        if( ! $this->has_post && ! $this->has_ldap ){
            return false;
        }

        // we want to allow SOME students, but only if they have a post associated with them (ie are likely to be a TA)
        if( $this->ldap_record[ 'is_student' ] == 1 && ! $this->has_post ){
            return false;
        }

        return $this->profile_post;
    }

    protected function init(){
        $this->set_current_blog();
        $this->set_unix_from_url();

        $profile_args = array(
            'meta_key'   => 'profile_unix',
            'meta_value' => $this->unix_from_url,
            'post_type'  => 'profile'
        );

        $this->get_profile_post( $profile_args );

        if( $this->has_post ){
            $this->set_profile_id();
        }

        if( $this->profile_id ){
            $this->get_alt_profile_posts( $profile_args );
        }

        $this->set_ldap_record();

        if( $this->has_ldap ){
				$this->is_faculty = strpos($this->ldap_record['wmsaffiliation'], 'EMPF') !== false ? true : false;
        }

        if( $this->has_ldap || $this->has_post ){
            $this->build_profile();
        }
    }

    protected function build_profile(){
        $profile_post = array();
        if( $this->has_ldap ){
            $profile_post[ 'full_name' ] = $this->ldap_record[ 'full_name' ];

            // get courses (if any) taught by this person
            $profile_post[ 'wms_course' ] = $this->get_wms_course();

            // get committees
            $profile_post[ 'committees' ] = $this->get_committees();
        } else if( $this->has_post ){
            $profile_post[ 'full_name' ] = $this->profile_post->post_title;
        }

        if( $this->has_post ){
            // grab custom meta fields
            $profile_fields = array(
                'profile_unix',
                'profile_website',
                'profile_cv_upload',
                'profile_at_wms_since',
                'profile_contact',
                'profile_interests',
                'profile_publications',
                'profile_affiliations',
                'profile_grants',
                'profile_other',
                'profile_other_service',
                'profile_supress_dates'
            );

            if( $this->profile_site_id != $this->current_blog->blog_id ){
                switch_to_blog( $this->profile_site_id );
            }

            foreach( $profile_fields as $field ){
                $profile_post[ $field ] = get_field( $field, $this->profile_post->ID );
            }

            if( $this->profile_site_id != $this->current_blog->blog_id ){
                restore_current_blog();
            }
        }

        $profile_post[ 'educ' ]                      = $this->get_education( $profile_post );
        $profile_post[ 'post_id' ]                   = $this->profile_id;
        $profile_post[ 'pic' ]                       = $this->get_profile_pic();
        $profile_post[ 'title' ]                     = $this->build_directory_item( $this->ldap_record[ 'title' ], 'profile-dir-title', 'h2' );
        $profile_post[ 'email' ]                     = $this->get_email();
        $profile_post[ 'profile_website' ]           = $this->get_profile_website( $profile_post );
        $profile_post[ 'profile_cv_upload' ]         = $this->get_profile_cv_upload( $profile_post );
        $profile_post[ 'phone' ]                     = $this->build_directory_item( $this->ldap_record[ 'phone' ], 'profile-dir-phone', 'div' );
        $profile_post[ 'address' ]                   = $this->build_directory_item( $this->ldap_record[ 'address' ], 'profile-dir-addr' );
        $profile_post[ 'profile_at_wms_since' ] = $this->get_at_williams_since( $profile_post );
        $profile_post[ 'profile_contact' ]           = $this->get_profile_contact( $profile_post );
        $profile_post[ 'profile_interests' ]         = $this->build_profile_section( 'profile_interests', 'Areas of Expertise', $profile_post );
        $profile_post[ 'profile_publications' ]      = $this->build_profile_section( 'profile_publications', 'Scholarship/Creative Work', $profile_post );
        $profile_post[ 'profile_grants' ]            = $this->build_profile_section( 'profile_grants', 'Awards, Fellowships & Grants', $profile_post );
        $profile_post[ 'profile_affiliations' ]      = $this->build_profile_section( 'profile_affiliations', 'Professional Affiliations', $profile_post );
        $profile_post[ 'profile_other' ]             = $this->build_profile_section( 'profile_other', '', $profile_post );
        $profile_post[ 'profile_other_service' ]     = $this->build_profile_section( 'profile_other_service', '', $profile_post, 'p' );

        $this->profile_post = $profile_post;
    }

    protected function build_profile_section( $meta_key, $title, $profile_post, $tag="div" ){
        if( ! $profile_post[ $meta_key ] ){
            return false;
        }

        $out = '<' . $tag . ' class="profile-section  profile-' . $meta_key . ' profile-auto">';
        if( $title ){
            $out .= '<h3>' . $title . '</h3>';
        }
        $out .= $profile_post[ $meta_key ];
        $out .= '</' . $tag . '>';

        return $out;
    }

    protected function build_directory_item( $ldap_val, $class, $tag="div" ){
        if( ! $ldap_val ){
            return false;
        }

        $out = "<" . $tag . " class=\"profile_dir_item $class\">";
        if( is_array( $ldap_val ) ){
            $ldap_val = implode( ' ', $ldap_val );
        }

        $ldap_val = preg_replace( '|^(.*?@williams\.edu)$|', '<a href="mailto:' . "$1" . '">' . "$1" . '</a>', $ldap_val );
        $ldap_val = preg_replace( '|^413\/(.*)$|', '413-' . "$1", $ldap_val );

        $out .= $ldap_val . '</' . $tag . '>';

        return $out;
    }

    /**
     * Get the posts associated with this unix user name (which is a custom field)
     */
    protected function get_profile_post( $args ){
        $this->set_profile_post( new WP_Query( $args ) );
    }

    /**
     * @param $args
     */
    protected function get_alt_profile_posts( $args ){
        // check for an alternate department profile
        if( $alt_profile_site_id = get_field( 'profile_alt_dept', $this->profile_id ) ){
            // get site id for other department
            if( is_numeric( $alt_profile_site_id ) ){
                switch_to_blog( $alt_profile_site_id );
                // check to see if the other site has a profile post
                if( $this->set_profile_post( new WP_Query( $args ) ) ){
                    $this->set_profile_site_id( $alt_profile_site_id );
                }
                restore_current_blog();
            }
        }
    }

    protected function set_profile_post( $query ){
        global $wp_query;
        if( $query->posts ){
            $wp_query           = $query;
            $this->profile_post = reset( $query->posts );
            $this->has_post     = true;

            return true;
        }
        return false;
    }

    protected function set_unix_from_url(){
        // get user id from URL
        $url_parts           = explode( '/', $_SERVER[ 'REQUEST_URI' ] );
        if ($url_parts[2] == 'profile') {
            $this->unix_from_url = $url_parts[3];
        } else {
            $this->unix_from_url = $url_parts[2];
        }
    }

    protected function set_current_blog(){
        global $current_blog;
        $this->current_blog = &$current_blog;
        $this->set_profile_site_id( $this->current_blog->blog_id );
    }

    protected function set_profile_site_id( $id ){
        $this->profile_site_id = $id;
    }

    /**
     * @uses $this->profile_posts->posts
     */
    protected function set_profile_id(){
        $this->profile_id = $this->profile_post->ID;
    }

    /**
     * @return array
     */
    protected function set_ldap_record(){
        // go look this person up in the directory- we need their name in case they don't have a post
        include_once( WMS_EXT_LIB . "/ldap/wms-ldap.class.php" );
        $wms_ldap = new WilliamsLdap();

        $args = array(
            'uid'        => $this->unix_from_url,
            'is_student' => true,
            // set to true so students (ie TAs) can have faculty entries too (otherwise no students show)
        );

        if( $this->ldap_record = $wms_ldap->get_record( $args ) ){
            $this->has_ldap = true;
            $this->filter_ldap_record();
        }
    }

    protected function filter_ldap_record(){
        include_once( WMS_EXT_LIB . "/ldap/wms-directory.class.php" );
        $wms_ldap_extras = new WilliamsPeopleDirectory();
        $wms_ldap_extras->filter_record( $this->ldap_record ); // record is passed by reference
    }

    protected function do_redirect(){
        // check for page-links-to redirect
        if( $redirect = get_field( 'page_redirect', $this->profile_id ) ){
            wp_redirect( $redirect, 301 );
            exit;
        }
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Meerkat16_Profile_Single The singleton instance.
     */
    public static function instance(){
        if( null === static::$instance ){
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @return bool|string
     */
    protected function get_wms_course(){
        if( Wms_Server::instance()->is_local() ){
            return false;
        }

        $course_lib = WPMU_MUPLUGIN_PATH . 'wms-peoplesoft/lib/';
        include_once( "$course_lib/courses.php" );

        $wms_course = new WilliamsCourseList();

        $dept   = $subjattr;
        $detail = 'none';
        if( ! $course_html = $wms_course->do_course_list( $dept, $this->unix_from_url, $subjattr, $detail ) ){
            return false;
        }

        $out = '<div class="profile-section profile-courses profile-auto">';
        $out .= '<h3>Courses</h3>';
        $out .= '<div class="profile-subsection">';

        $out .= "$course_html";
        $out .= '</div></div>';

        return $out;
    }

    /**
     * @return bool|string
     */
    protected function get_committees(){
        if( Wms_Server::instance()->is_local() ){
            return false;
        }

        $com_lib = WPMU_MUPLUGIN_PATH . 'wms-peoplesoft/lib/committees.php';
        include_once( $com_lib );

        $committees = wms_get_committees( $this->unix_from_url );

        if( ! $committees ){
            return false;
        }
        $out = '<div class="profile-section profile-committees profile-auto">';
        $out .= '<h3>Current Committees</h3><div class="profile-subsection"><ul>' . $committees . '</ul></div>';
        $out .= '</div>';

        return $out;
    }

    /**
     * @return mixed
     */
    protected function get_profile_pic(){
        $pic = '';
        if( $this->has_ldap ){
            $photo       = $this->ldap_record[ 'photo' ];
            $photo_class = 'profile-photo' . ( $this->ldap_record[ 'photo_suppressed' ] ? ' suppressed' : '' );
            $pic = '<img alt="Photo of '. $this->ldap_record['full_name'] . '" class="' . $photo_class . '" src="' . $photo . '">';
        }
        if( $this->has_post ){ //if profile post, check for image and replace if true
            $title    = esc_attr( get_the_title( $this->profile_post ) );
            $pic_attr = array(
                'class' => 'profile-photo',
                'alt'   => $title,
                'title' => $title,
            );
			if (get_the_post_thumbnail( $this->profile_id, 'medium', $pic_attr )){
            	$pic = get_the_post_thumbnail( $this->profile_id, 'medium', $pic_attr );
            }
        }
		 
        return $pic;
    }

    /**
     * @param $profile_post
     *
     * @return bool|string
     */
    protected function get_education( $profile_post ){
        if( 
		    ! $this->has_ldap                              // not in directory
			|| (! $this->is_faculty && ! $this->has_post ) // is staff, but no profile page 
			|| Wms_Server::instance()->is_local()          // local can't connect to edu DB
		){
            return;
        }
		/*
		 * Educaction displayed if:
		 *   - you are faculty
		 *   - you are staff AND you have a profile post
		 */
        $educ_lib = WPMU_MUPLUGIN_PATH . 'wms-peoplesoft/lib/education.php';
        include_once( $educ_lib );

        if( ! $educ = wms_get_education( $this->unix_from_url, $profile_post[ 'profile_supress_dates' ] ) ){
            return false;
        }

        $out = '<div class="profile-section profile-education profile-auto">';
        $out .= '<h3>Education</h3><div class="profile-subsection">' . $educ . '</div>';
        $out .= '</div>';

        return $out;
    }

    /**
     * @return bool|string
     */
    protected function get_email(){
        if( ! $this->ldap_record[ 'email' ] ){
            return false;
        }

        return '<div class="profile-email"><a href="mailto:' . $this->ldap_record[ 'email' ] . '">' . $this->ldap_record[ 'email' ] . '</a></div>';
    }

    /**
     * @param $profile_post
     *
     * @return bool|string
     */
    protected function get_profile_website( $profile_post ){
        if( ! $profile_post[ 'profile_website' ] ){
            return false;
        }

        return '<div class="profile-website"><a href="' . $profile_post[ 'profile_website' ] . '" target="_blank">Website</a></div>';
    }

    /**
     * @param $profile_post
     *
     * @return bool|string
     */
    protected function get_profile_cv_upload( $profile_post ){
        if( ! $profile_post[ 'profile_cv_upload' ] ){
            return false;
        }

        return '<div class="profile-cv"><a href="' . $profile_post[ 'profile_cv_upload' ] . ' " target="_blank">CV</a></div>';
    }

    /**
     * @param $profile_post
     *
     * @return bool|string
     */
    protected function get_at_williams_since( $profile_post ){
        if( ! $profile_post[ 'profile_at_wms_since' ] ){
            return false;
        }

        return '<div class="profile_at_wms_since"><i>At Williams since ' . $profile_post[ 'profile_at_wms_since' ] . '</i></div>';
    }

    /**
     * @param $profile_post
     *
     * @return bool|string
     */
    protected function get_profile_contact( $profile_post ){
        if( ! $profile_post[ 'profile_contact' ] ){
            return false;
        }

        return '<div class="profile-additional-contact">' . $profile_post[ 'profile_contact' ] . '</div>';
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone(){ }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup(){ }
}
