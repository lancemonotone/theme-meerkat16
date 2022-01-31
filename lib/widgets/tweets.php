<?php
#
# WILLIAMS TWEETS WIDGET
#
#

// note: this uses the twitter 1.1 api, with the developer account williamswebops 
// twitterchangeditsAPI

class MeerkatTweetsWidget extends MeerkatWidget {
	
	// register widget with wordpress
	public function __construct() {
		$desc = 'Display latest tweets from a Twitter account';
		parent::__construct( 'meerkat_tweets', // Base ID
			MK_WIDGET_PREFIX . 'Tweets', // Name
			array( 'description' => $desc ) // Args
		);
		
		// editable widget options & associated data
		
		$num_tweets_options = array( 1, 2, 3, 4, 5, 6, 8, 10, 15, 20 );
		$this->fields = array(
			'title'        => array(
				'default' => 'Tweets',
				'type'    => 'text',
				'label'   => 'Title'
			),
			'twitter_user' => array(
				'default' => 'williamscollege',
				'type'    => 'text',
				'label'   => 'Twitter user name'
			),
			'num_tweets'   => array(
				'default' => 5,
				'type'    => 'select',
				'options' => $num_tweets_options,
				'label'   => 'Number of tweets to display'
			),
			'show_date'    => array(
				'default' => true,
				'type'    => 'checkbox',
				'label'   => 'Show date?'
			),
		);
		
	}
	
	// Displays the Widget
	public function widget( $args, $instance ) {
		include( WMS_EXT_LIB . '/twitter/twitter.php' );
		$twitter = new WmsTwitter();
		
		// link to twitter account
		$instance['title_link'] = 'https://twitter.com/' . $instance['twitter_user'];
		
		echo $args['before_widget'];
		parent::display_title( $args, $instance );
		echo $args['before_insides'];
		
		$tweets = $twitter->getTweets( $instance['num_tweets'], $instance['twitter_user'] );
		echo $twitter->formatTweets( $tweets, $instance['show_date'] );
		echo '<a href="' . $instance['title_link'] . '"><div class="twitter-follow"><div class="sprite icon-16 twitter-bird"></div>Follow @' . $instance['twitter_user'] . '</div></a>';
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
}

// register widget
add_action( 'widgets_init', function(){ register_widget( "MeerkatTweetsWidget" ); });