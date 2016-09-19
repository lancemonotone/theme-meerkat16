<?php
#
# LOCALIST CALENDAR
#
# create a event calendar on your site from your localist calendar events
#

class MeerkatLocalistWidget extends MeerkatWidget {
	
	// register widget with wordpress
	public function __construct() {
		$desc = "Display events from the Williams College calendar";
		parent::__construct( 'meerkat_localist', // Base ID
			MK_WIDGET_PREFIX . 'Williams Events Calendar', // Name
			array( 'description' => $desc ) // Args
		);
		
		// editable widget options & associated data
		$url_hint = '<a target="_new" href="http://wordpress.williams.edu/events-widget/"><b>Documentation &raquo;</b></a>';
		
		$this->fields = array(
			'title'        => array(
				'type'  => 'text',
				'label' => 'Title'
			),
			'localist_url' => array(
				'type'  => 'text',
				'label' => 'Localist URL',
				'hint'  => $url_hint
			),
		);
	}
	
	// Displays the Widget
	function widget( $args, $instance ) {
		//
		// Add transient to cache events. 15 minutes? 1 hour?
		//
		$localist_url = 'https://events.williams.edu';
		// bail on invalid url
		if ( strpos( $instance['localist_url'], $localist_url ) === false ) {
			return;
		}
		
		// parse localist_url and extract parameters
		$url_parts = explode("?", $instance['localist_url']);
		parse_str($url_parts[1], $params);
		// Build URL for Localist API
		$api_url = $localist_url . '/api/2/events?';
		// Parameter names differ from feed to api
		$key_map = array('types' => 'type', 'num' => 'pp');
		foreach ($params as $k => $v) {
			if (in_array($k, array('format','schools'))) continue;
			$k = ($key_map[$k]) ? $key_map[$k] : $k;
			if (strpos($v, ',') !== false) {
				$values = explode(',', $v);
				foreach ($values as $val) {
					$param_array[] = $k . '[]=' . $val;
				}
				$all_params[] = implode('&', $param_array);
			} else {
				$all_params[] = "$k=$v";
			}
		}
		$api_url .= 'recurring=false&' . implode('&', $all_params);
		// Request events from localist API
		if ( ! $json_response = include_file( $api_url ) ) {
			return;
		}
		$events_obj = json_decode($json_response);
		if ( count($events_obj->events) == 0 || $events_obj->status == 'error') {
			// display nothing if no events are returned or there is an error
			return;
		}
		foreach ($events_obj->events as $event) {
			// fix some weird data nesting
			$events[] = $event->event;
		}
		
		echo $args['before_widget'];
		parent::display_title( $args, $instance );
		echo $args['before_insides'];
		
		echo '<div class="meerkat_localist_widget">';
		
//		echo $html;
		Timber::render( 'events.twig', array('events' => $events) );
		$rss_url = str_replace( 'format=html', 'format=rss', $instance['localist_url'] );
		echo '<div class="wms-cal-rss"><a href="' . $rss_url . '"><div class="icon-16 sprite rss"></div>Subscribe</a></div>';
		echo '<a class="wms-cal-link" href="http://events.williams.edu">Williams Calendar &raquo;</a>';
		echo '</div>';
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
}

// register widget
add_action( 'widgets_init', create_function( '', 'return register_widget( "MeerkatLocalistWidget" );' ) );