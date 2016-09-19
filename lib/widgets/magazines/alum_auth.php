<?php
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
	
	$this->fields = array( 'title' => array ( 'default' => '', 
											  'type' => 'text', 
											  'label' => 'Title', 
											  'classes' => 'widefat',
											  'wrapper' => 'p' ),											 
 							'help' => array ( 'default' => '',
								              'type' => 'text',
                                              'label' => 'Instructions',
											  'classes' => 'widefat',
											  'wrapper' => 'p' ),											  
							'forgot' => array ( 'default' => '',
								                'type' => 'text',
                                                'label' => 'Forgot password link text',
											    'classes' => 'widefat',
											    'wrapper' => 'p' ),											  
		);
    }
	
    // Displays the Widget
    public function widget($args, $instance){
		extract($args);
	    $redir = home_url() . $_SERVER['REQUEST_URI']; 
		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;
   			
		if ( Meerpeople_Shared::meermag_alum_logged_in() ){
		   echo '<form class="custom harris-login">';
		   echo 'You are currently logged in.';
		   echo '<input id="harris-logout" type="submit" value="Logout">';
		   echo '</form>';
		}
		else { ?>

		   <div class="note"><?php echo $instance['help']; ?></div>
		   <form class="custom harris-login" action="https://www.alumniconnections.com//olc/pub/WLC/login/olclogin.cgi" method="post">
		   <label for="username">Username</label>
		   <input name="username" type="text" value="">
		   <label for="password">Password</label>
		   <input name="password" type="password" value="">
		   <input type="hidden" name="referer" value="https://www.alumniconnections.com/olc/membersonly/WLC/olctransfer.cgi?auth=Y&gotourl=<?php echo $redir; ?>">
		   <input type="hidden" name="SaFormName" value="SubmitLogin__Floginform_html">
		   <input type="submit" value="Login">
		   <a class="forgot-pw" href="https://www.alumniconnections.com/olc/pub/WLC/forgot/forgot.cgi"><?php echo $instance['forgot']; ?></a>
		   </form>
		
		<?php
		}

		echo $after_widget;
    }

}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatAlumAuthWidget" );' ) );

?>
