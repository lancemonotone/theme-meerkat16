<?php
/*
Plugin Name: Alumni Authentication
Plugin URI: http://oit.williams.edu/?p=7410
Description: Require alumni to authenticate before viewing page content
Version: 1.0
Author: <a href='mailto:webteam@williams.edu?subject=Wordpress Plugin: Williams Alumni Auth'>Williams Webteam</a>
*/
#
# HARRIS AUTH WIDGET
#
#

class MeerkatAlumAuthWidget extends MeerkatWidget {
	
	// register widget with wordpress
	public function __construct() {
		$desc = 'Harris Login';
		parent::__construct( 'meerkat_alum_auth', // Base ID
			MK_WIDGET_PREFIX . 'Alumni Auth', // Name
			array( 'description' => $desc ) // Args
		);
		
		$this->fields = array(
			'title'  => array(
				'default' => '',
				'type'    => 'text',
				'label'   => 'Title',
				'classes' => 'widefat',
				'wrapper' => 'p'
			),
			'help'   => array(
				'default' => '',
				'type'    => 'text',
				'label'   => 'Instructions',
				'classes' => 'widefat',
				'wrapper' => 'p'
			),
			'forgot' => array(
				'default' => '',
				'type'    => 'text',
				'label'   => 'Forgot password link text',
				'classes' => 'widefat',
				'wrapper' => 'p'
			),
		);
	}
	
	// Displays the Widget
	public function widget( $args, $instance ) {
		$redir = home_url() . $_SERVER['REQUEST_URI'];
		
		echo $args['before_widget'];
		parent::display_title( $args, $instance );
		echo $args['before_insides'];
		
		if ( wmsAlumniAuthentication::alum_is_logged_in() ) {
			echo wmsAlumniAuthentication::get_logout_js();
			echo wmsAlumniAuthentication::get_widget_status();
		} else {
			echo wmsAlumniAuthentication::get_form( $redir );
		}
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatAlumAuthWidget" );' ) );

class wmsAlumniAuthentication {
	
	function __construct() {
		add_action( 'init', array( $this, 'cleanup_url' ) );
		$this->add_field_to_pages();
		add_filter( 'the_content', array( $this, 'check_access_rules' ) );
	}
	
	public static function alum_is_logged_in() {
		// check for authentication cookie
		if ( $_COOKIE['harris_alum_id'] && $_COOKIE['harris_alum_sig'] ) {
			return true;
		}
		
		return false;
	}
	
	public static function get_alum_id() {
		return $_COOKIE['harris_alum_id'];
	}
	
	public static function check_access_rules( $content ) {
		$content_restriction = get_field( 'alumni_only' );
		if ( $content_restriction == 'alumni_only' || $content_restriction == 'alumni_agent_only' ) {
			$is_logged_in = self::alum_is_logged_in();
			if ( $is_logged_in ) {
				$is_agent = self::alum_is_agent();
			}
			if ( ! $is_logged_in ) {
				// Log in form
				$redir = home_url() . $_SERVER['REQUEST_URI'];
				$form_header = ( $content_restriction == 'alumni_only' ) ? 'Alumni content - please login.' : 'Volunteer exclusive content - please login.';
				
				return "<h3>$form_header</h3><div class='half'>" . self::get_form( $redir ) . '</div>';
				
			} else if ( $content_restriction == 'alumni_agent_only' && ! $is_agent ) {
				// Alum is not an agent
				return self::get_logout_js() . self::get_status_box() . '<h3>Alumni volunteer content</h3>' . '<p>The content of this page is exclusively available to Williams volunteers, primarily class agents and class officers. If you are receiving this message in error or if you are having trouble logging in, or, to become a volunteer, please contact <a href="mailto:alumni.relations@williams.edu">Alumni Relations</a> by calling <a href="tel:1-413-597-4151">413-597-4151</a>.</p>';
			} else {
				// Logged in with proper access level, add status
				// box with log out link to top of page
				return self::get_logout_js() . self::get_status_box() . $content;
			}
		} else {
			// No content restrictions set
			return $content;
		}
	}
	
	public static function get_logout_js() {
		return <<<EOF
<script type="text/javascript">
jQuery(document).ready(function($){
   // logout
   $('#harris-logout').click( function (e){
      // unset our cookie
      $.cookie('harris_alum_id', null, { domain: 'williams.edu', path: '/' });
      $.cookie('harris_alum_sig', null, { domain: 'williams.edu', path: '/' });
      // redirect to their logout page
      window.location.href = 'https://www.alumniconnections.com/olc/pub/WLC/login/app.sph/olclogin.app';
      e.preventDefault();
   });

});
</script>
EOF;
	}
	
	public static function get_widget_status() {
		return <<<EOF
<form class="custom harris-login">
You are currently logged in.
<input id="harris-logout" type="submit" value="Logout">
</form>
EOF;
	}
	
	// Returns HTML indicating logged in status and containing log out link
	public static function get_status_box() {
		return <<<EOF
<div class="content-box right">Status: logged in (<a id="harris-logout" href="http://www.alumniconnections.com/olc/pub/WLC/login/app.sph/olclogin.app">log out)</a></div>
EOF;
	}
	
	// Returns login form
	public static function get_form( $redir ) {
		$form = <<<EOF
		   <div class="note">Sign in to the Williams Alumni Web Community.</div>
		   <form class="custom harris-login" action="https://www.alumniconnections.com//olc/pub/WLC/login/olclogin.cgi" method="post">
		   <label for="username">Username</label>
		   <input name="username" type="text" value="">
		   <label for="password">Password</label>
		   <input name="password" type="password" value="">
		   <input type="hidden" name="referer" value="https://www.alumniconnections.com/olc/membersonly/WLC/olctransfer.cgi?auth=Y&gotourl=$redir">
		   <input type="hidden" name="SaFormName" value="SubmitLogin__Floginform_html">
		   <input type="submit" value="Login">
		   <a class="forgot-pw" href="https://www.alumniconnections.com/olc/pub/WLC/forgot/forgot.cgi">Forgot your login?</a>
		   </form>
EOF;
		
		return $form;
	}
	
	function add_field_to_pages() {
		if ( function_exists( 'acf_add_local_field_group' ) ):
			
			acf_add_local_field_group( array(
				'key'                   => 'group_564f41709310a',
				'title'                 => 'Alumni Authentication',
				'fields'                => array(
					array(
						'key'               => 'field_564f4170a3ffb',
						'label'             => 'Authentication Required',
						'name'              => 'alumni_only',
						'type'              => 'radio',
						'instructions'      => 'Require visitors to login with their alumni account to view this page. Access can be further refined to only alums designated as class agents.',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => array(
							'no_auth'           => 'None',
							'alumni_only'       => 'Alumni Only',
							'alumni_agent_only' => 'Alumni Agents Only',
						),
						'other_choice'      => 0,
						'save_other_choice' => 0,
						'default_value'     => 'no_auth',
						'layout'            => 'horizontal',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'page',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => 1,
				'description'           => 'Require authentication with alumni account to view page.',
			) );
		
		endif;
	}
	
	//--------------------------- ALUMNI AUTH ------------------------------//
	static function cleanup_url() {
		// looks for ugly query string set by harris authentication and transfer info into cookie
		if ( $_GET['client_id'] && $_GET['sig'] ) {
			//error_log("clean up url: harris query string present");
			if ( self::validate_auth( $_GET['client_id'], $_GET['sig'] ) ) {
				//error_log("valid auth");
				$expires = time() + 60 * 60 * 24; // 1 day
				setcookie( 'harris_alum_id', $_GET['client_id'], $expires, '/', 'williams.edu' );
				setcookie( 'harris_alum_sig', $_GET['sig'], $expires, '/', 'williams.edu' );
			}
			$path_bits = explode( '?', $_SERVER['REQUEST_URI'] );
			$orig_url = home_url() . $path_bits[0];
			//error_log("redirecting to page $orig_url");
			wp_redirect( $orig_url, 301 );
			exit();
		}
	}
	
	static function validate_auth( $client_id, $sig ) {
		// validate harris auth token
		//error_log("validating auth...");
		if ( ! $client_id || ! $sig ) {
			//error_log("missing client id [$client_id] or sig [$sig]");
			return false;
		}
		// secret string shared between harris and us.
		$secret = 'play0ffs';
		
		$sig = str_replace( '@', '+', $sig );
		// use unpack to split sig into time and an md5 hash of secret, time, and client id
		$external = unpack( 'lwhen/a*mdfive', base64_decode( $sig ) );
		$when = $external['when'];
		$authtime = self::changebyteorder( $when );
		
		// build our own md5 hash and see if it matches.
		$md5 = md5( $secret . pack( 'l', $when ) . $client_id, true );
		if ( $external['mdfive'] == $md5 ) {
			// valid data. now check time
			if ( ( time() - $authtime ) / 60 < 5 ) {
				// link used within 5 minutes of creation. we should probably make this shorter.
				//error_log("good auth");
				return true;
			}
		}
		
		//error_log("bad auth");
		return false;
	}
	
	static function changeByteOrder( $num ) {
		// Change byte order: little to big endian or vice versa.
		$data = dechex( $num );
		if ( strlen( $data ) <= 2 ) {
			return $num;
		}
		$u = unpack( "H*", strrev( pack( "H*", $data ) ) );
		$f = hexdec( $u[1] );
		
		return $f;
	}
	
	function alum_is_agent() {
		$agents = self::get_class_leaders();
		$alum_id = self::get_alum_id();
		if ( $agents[ $alum_id ] ) {
			return true;
		} else {
			return false;
		}
	}
	
	function get_class_leaders() {
		// Provide a list of class leaders for a given class year.
		// Try first to get the info from a transient. If it's not
		// available, create by loading the csv file.
		
		$alumni_leaders_transient = 'Alumni-Class-Agents';
		
		// Check for a transient. Transient value expires after 24 hours.
		$list_of_agents = get_transient( $alumni_leaders_transient );
		if ( is_array( $list_of_agents ) ) {
			return $list_of_agents;
		}
		
		// Build the list of agents
		$agents = array();
		$class_leaders_csv = WP_CONTENT_DIR . '/protected/Alum-Class-Leaders-2.CSV';
		if ( file_exists( $class_leaders_csv ) && is_readable( $class_leaders_csv ) ) {
			if ( ( $handle = fopen( $class_leaders_csv, 'r' ) ) !== false ) {
				while ( ( $row = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
					// class, pref name, committee, contact
					//
					// Discard header row
					if ( $row[0] == 'ADV ID' ) {
						continue;
					}
					//
					$agents[ $row[0] ] = true;
				}
			}
		}
		if ( is_array( $agents ) && count( $agents ) > 0 ) {
			set_transient( $alumni_leaders_transient, $agents, DAY_IN_SECONDS );
			
			return $agents;
		} else {
			return false;
		}
	}
	
}

$wms_alum_auth = new wmsAlumniAuthentication();

?>
