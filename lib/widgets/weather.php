<?php
#
# WILLIAMSTOWN WEATHER
#      shows current weather conditions in williamstown
#
# version 1.0
#

class MeerkatWeatherWidget extends MeerkatWidget {

	// register widget with wordpress
	public function __construct() {
		$desc = 'Display the current temperature & conditions in Williamstown';
		parent::__construct( 'meerkat_weather', // Base ID
			MK_WIDGET_PREFIX . 'Williamstown Weather', // Name
			array( 'description' => $desc ) // Args
		);

		// editable widget options & associated data
		$this->fields = array(
			'title' => array(
				'default' => 'Williamstown Weather',
				'type'    => 'text',
				'label'   => 'Title'
			)
		);
	}

	// Displays the Widget
	function widget( $args, $instance ) {
		echo $args['before_widget'];
		parent::display_title( $args, $instance );
		echo $args['before_insides'];

		$weather = $this->get_weather();
		// widget main content - display weather
		echo <<<EOD
		<div class="wms_weather">
			<img class="weather_icon" src="{$weather['icon']}">
			<span class="weather_conditions">{$weather['conditions']},</span>
			<span class="weather_temp">{$weather['temp']}&#176; F</span>
		</div>
EOD;

		echo $args['after_insides'];
		echo $args['after_widget'];
	}

	function get_weather() {

		$weather_url = 'http://weather.yahooapis.com/forecastrss?p=USMA0492&u=f';

		$request = new HTTPRequest( $weather_url );
		$html = $request->DownloadToString();

		/* cull down to just the info we want
		   <yweather:condition  text="Mostly Cloudy"  code="28"  temp="77"  date="Wed, 27 Apr 2011 4:50 pm EDT" />
		   <description>
		   <img src="http://l.yimg.com/a/i/us/we/52/34.gif"/><br /> 
		*/

		// echo $raw_html;
		$start = '<yweather:condition';
		// find our starting position
		$start_pos = strpos( $html, $start );

		// get html starting at start
		$html = substr( $html, $start_pos + strlen( $start ) );

		$stop_pos = strpos( $html, '/>' );
		$html = substr( $html, 0, $stop_pos );

		// get actual values we care about (text and temp)
		preg_match( '|text="(.*?)"|', $html, $matches );
		$conditions = $matches[1];

		preg_match( '|temp="(\d+)"|', $html, $matches );
		$temp = $matches[1];

		preg_match( '|code="(\d+)"|', $html, $matches );
		$code = $matches[1];

		$icon = "http://l.yimg.com/a/i/us/we/52/" . $code . '.gif';

		$weather = array(
			'conditions' => $conditions,
			'temp'       => $temp,
			'icon'       => $icon
		);

		return $weather;
	}
}

// register widget
add_action( 'widgets_init', function(){ register_widget( "MeerkatWeatherWidget" ); });