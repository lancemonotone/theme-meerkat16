<?php
/*
 * @see template-quicklinks.php
 */
// load wp libraries
define('WP_USE_THEMES', false);
require_once( '../../../../wp-load.php' );
require_once( ABSPATH . WPINC . '/registration.php' );

// load ldap library
require_once('/web/lib/ldap/ldap-auth.php');

login( $_REQUEST['username'], $_REQUEST['password'] );

function login( $username, $pw ) {
	$status = 'ok';
	$cookie_expires = time(mktime(1, 2, 3, 10, 30, 2030));
	$ten_years = 1 * 60 * 60 * 24 * 365 * 10; 
	$cookie_expires = time() + $ten_years;

	// login form is filled out
    if ( $username && $pw ){
		// legit ldap user?
		$email = authenticateAgainstLDAP( $username, $pw, 'email' );
		if ( $email && $email != 'UNKNOWN' ){
			$_SESSION['authenticated'] = 'yes';
			$_SESSION['username'] = $username;
			$_SESSION['email'] = $email;
			
			// does user have a wp account?
			$wp_uid = username_exists( $username );
			// create user if they does not exist yet
            if ( ! $wp_uid && $pw && $email ){
                $wp_uid = wpmu_create_user( $username, $pw, $email );
                if ( ! $wp_uid ){
                   $status = 'ERROR: could not create account';
                }
            }

            if ($status == 'ok'){
			    $status = $_REQUEST['fx'];
                // save to cookie so they do not have to re-login
				setcookie('Quicklinks', $username, $cookie_expires, '/', 'williams.edu' );
            }
        }
        else {
           // failed authentication
            $status = 'ERROR: authentication failed.';
        }
    }
    else {
         // missing param
         $status = 'ERROR: please fill in both your username and password.';
    }

	setcookie('quicklinks_login_status', $status, $cookie_expires, '/', 'williams.edu' );

	// redirect back to quicklinks iframe w status message
	$redirect = 'http://' . $_SERVER['SERVER_NAME'] . '/ql/?iframe';
	header("Location: $redirect");
	die();
}
