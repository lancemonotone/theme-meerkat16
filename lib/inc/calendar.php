<?php
/**
 * Deprecated in favor of Tribe Events Calender. This file does not need to migrate.
 */
// creates a grid calendar based off of wordpress posts in a custom post type or category
// included in functions.php and called via [mk_calendar] shortcode (see shortcodes.php)

if ( ! class_exists( 'MeerkatPostCalendar' ) ) {
	class MeerkatPostCalendar {
		public function __construct( $args ) {
			// shortcode.php sets up args like year, month, post_type w/ reasonable defaults
			$this->args = $args;
			$this->excerpt_length = 45;
			$this->days_of_week = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
		}
		
		function build_cal( $month, $year, $acf ) {
			
			$day1 = mktime( 0, 0, 0, $month, 1, $year );
			
			// month title (month name & next/prev arrows)
			$cal = $this->build_month_header( $day1 );
			
			// month event data
			$events = $this->get_cal_data( $month, $year, $acf );
			
			// month grid
			$cal .= $this->build_month( $day1, $events );
			
			return $cal;
		}
		
		//---- GET MONTH POST DATA
		function get_cal_data( $month, $year, $acf ) {
			$is_ctd_acf_theme = Meerkat16::instance()->is_ctd_theme || $acf;
			// drm2 - quick fix for ACF-based calendars.
			// note: CTD theme uses ACF repeater field for event showings, others use post publish date
			
			$cal_args = array(
				'order'          => 'ASC',
				'orderby'        => 'date',
				'post_type'      => $this->args['post_type'],
				'year'           => $year,
				'monthnum'       => $month,
				'posts_per_page' => - 1,
				'post_status'    => array( 'publish', 'future' )
			
			);
			
			if ( $is_ctd_acf_theme ) {
				unset( $cal_args['monthnum'] );
				unset( $cal_args['year'] );
			}
			if ( $this->args['cat'] ) {
				$cal_args['category_name'] = $this->args['cat'];
			}
			$cal_query = new WP_Query( $cal_args );
			
			if ( $is_ctd_acf_theme ) {
				// use acf repeater field
				$month_events = $this->parse_by_postmeta( $cal_query->posts, $month, $year );
			} else {
				// use post publish date
				$month_events = $this->parse_by_pubdate( $cal_query->posts );
			}
			
			return $month_events;
		}
		
		function parse_by_pubdate( $cal_posts ) {
			if ( ! $cal_posts ) {
				return false;
			}
			
			// build array where keys are day of month
			$month_events = array();
			foreach ( $cal_posts as $cpost => $data ) {
				$pdate = strtotime( $data->post_date );
				$pdom = date( 'j', $pdate );
				$month_events[ $pdom ][] = $data;
			}
			
			return $month_events;
		}
		
		/**
		 * Organize calendar by acf repeater date field - allow post to show up on multiple dates
		 *
		 * @param $cal_posts
		 * @param $month
		 * @param $year
		 *
		 * @return array|bool
		 */
		function parse_by_postmeta( $cal_posts, $month, $year ) {
			if ( ! count( $cal_posts ) ) {
				return false;
			}
			// organize calendar by acf repeater date field - allow post to show up on multiple dates
			// build array where keys are day of month
			$month_events = array();
			foreach ( $cal_posts as $cpost => $data ) {
				// get metadata for 'showing' ACF key
				if ( $showings_acf = get_field( 'showing', $data->ID ) ) {
					foreach ( $showings_acf as $showing_key => $showing ) {
						$edate = strtotime( $showing['date'] );
						$edom = date( 'j', $edate );
						$emon = date( 'n', $edate );
						$eyr = date( 'Y', $edate );
						if ( $emon == $month && $eyr == $year ) {
							$data->time = strtotime( $showing['time'] );
							$month_events[ $edom ][] = $data;
						}
					}
				}
			}
			
			if ( ! count( $month_events ) ) {
				return false;
			}
			// remove duplicates of the same event multiple times on 1 day and order events on same day by time
			foreach ( $month_events as $dom => $events ) {
				$taken = array();
				foreach ( $events as $index => $event ) {
					if ( $taken[ $event->ID ] ) {
						unset( $month_events[ $dom ][ $index ] );
					}
					$taken[ $event->ID ] = true;
				}
				uasort( $month_events[ $dom ], array( $this, 'cmp_event_time' ) );
			}
			
			return $month_events;
		}
		
		function cmp_event_time( $a, $b ) {
			// sort list of events by time of day
			if ( $a->time == $b->time ) {
				return 0;
			}
			
			return ( $a->time < $b->time ) ? - 1 : 1;
		}
		
		//---- BUILD MONTH GRID
		function build_month( $day1, $events ) {
			// determine how many days there are in this month & day of week of first day
			$dim = date( 't', $day1 );
			$dow1 = date( 'w', $day1 );
			
			$html = '<table id="cal-grid">';
			$html .= $this->build_dow();
			$html .= '<tr>';
			
			// blanks until first day
			$dow = 0;
			for ( $n = 0; $n < $dow1; $n ++ ) {
				$html .= $this->build_day( false, $dow, array( 'cal-day-blank' ), false );
				$dow ++;
				
			}
			
			for ( $n = 1; $n <= $dim; $n ++ ) {
				$html .= $this->build_day( $n, $dow, false, $events[ $n ] );
				if ( $dow == 6 ) {
					$html .= '</tr>';
					$dow = 0;
					if ( $n < $dim ) {
						$html .= '<tr>';
					}
				} else {
					$dow ++;
				}
			}
			
			// blanks at end
			if ( $dow > 0 ) {
				while ( $dow < 7 ) {
					$html .= $this->build_day( false, $dow, array( 'cal-day-blank' ), false );
					$dow ++;
				}
			}
			
			$html .= '</tr></table>';
			
			return $html;
		}
		
		//---- SINGLE DAY
		function build_day( $dom, $dow, $classes, $events ) {
			// $dom = numeric representation of day of month, or false if we are not supposed to print it
			// $dow = numeric representation of day of week (0 = sun, 6 = sat)
			// $classes = optional, an array of classes to apply to the day table cell
			// $events = optional, an array of events that occur on this day
			
			if ( $classes ) {
				$classes = implode( ' ', $classes );
			}
			$classes .= $events ? 'cal-day-w-events' : ' cal-day-empty';
			$classes .= ' dow-' . $dow;
			$html = '<td class="cal-day ' . $classes . '">';
			
			if ( $dom ) {
				if ( $events ) {
					$weekday = '<div class="cal-weekday">' . $this->days_of_week[ $dow ] . '</div>';
				}
				$html .= '<div class="cal-dom">' . $dom . $weekday . '</div>';
			}
			
			if ( $events ) {
				foreach ( $events as $event => $data ) {
					// label events with category classes so the category filter can hide/show them
					$e_cats = get_the_terms( $data->ID, 'category' );
					$e_classes = array( 'event-container cf' );
					foreach ( $e_cats as $e_cat => $cat_data ) {
						$e_classes[] = $cat_data->slug;
					}
					$e_classes = implode( ' ', $e_classes );
					$perma = get_permalink( $data->ID );
					$html .= '<div class="' . $e_classes . '">';
					// do special category labelling for 62center
					if ( Meerkat16::instance()->subdomain == '62center' ) {
						global $meerkat_ctd;
						$cat_slugs = $meerkat_ctd->get_subcats( $data->ID, $meerkat_ctd->parent_cat_ids['cal_event_types'] );
						$html .= '<div class="cal-62-cat-boxes">';
						foreach ( $cat_slugs as $slug ) {
							$html .= '<div class="cal-62-cat-box ' . $slug . '"></div>';
						}
						$html .= '</div><!-- .cal-62-cat-boxes -->';
					}
					$html .= '<div class="cal-event-title"><a href="' . $perma . '">' . $data->post_title . '</a></div>';
					
					if ( $data->post_content ) {
						$html .= '<div class="cal-desc">';
						$html .= '<div class="cal-desc-title">' . $data->post_title . '</div>';
						// do special showings info for 62ctd & music
						if ( Meerkat16::instance()->subdomain == '62center' || Meerkat16::instance()->subdomain == 'music' ) {
							$showings = get_field( 'showing', $data->ID );
							if ( $showings ) {
								$html .= '<div class="cal-event-showings">';
								$locations = array();
								$times = array();
								foreach ( $showings as $show ) {
									$show_dom = explode( '/', $show['date'] );
									$show_dom = $show_dom[1];
									// can have multiple showings per post- make sure we're only taking this day's
									if ( $show_dom == $dom ) {
										$locations[] = $show['location'];
										$times[] = $show['time'];
									}
								}
								$unique_loc = array_unique( $locations );
								if ( count( $locations ) > 1 && count( $unique_loc ) == 1 ) {
									$consol_times = implode( ', ', $times );
									$html .= '<div class="cal-event-showing">' . $consol_times . '<span class="sep">|</span>' . $show['location'] . '</div><!-- .cal-event-showing -->';
								} else {
									for ( $n = 0; $n <= count( $locations ) - 1; $n ++ ) {
										$html .= '<div class="cal-event-showing">' . $times[ $n ] . '<span class="sep">|</span>' . $locations[ $n ] . '</div><!-- .cal-event-showing -->';
									}
								}
								$html .= '</div><!-- .cal-event-showings -->';
							}
						}
						
						$html .= '<div class="cal-content">' . $this->get_excerpt_by_id( $data->ID, $this->excerpt_length );
						$html .= '<div class="cal-more"><a href="' . $perma . '">view event page &raquo;</a></div><!-- .cal-more --></div><!-- .cal-content --></div><!-- .cal-desc -->';
					}
					$html .= '</div><!-- .event-container -->';
				}
			}
			
			return $html . '</td>';
		}
		
		function get_excerpt_by_id( $post_id, $excerpt_length ) {
			$the_post = get_post( $post_id );
			// preferentially grab the manual excerpt, but if that's not there, do our own truncation
			$the_excerpt = wpautop( $the_post->post_excerpt, true );
			if ( ! $the_excerpt ) {
				$the_excerpt = $the_post->post_content;
				$the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) );
				$words = explode( ' ', $the_excerpt, $excerpt_length + 1 );
				if ( count( $words ) > $excerpt_length ) {
					array_pop( $words );
					array_push( $words, '...' );
					$the_excerpt = implode( ' ', $words );
				}
			}
			
			return '<p>' . $the_excerpt . '</p>';
		}
		
		//---- MONTH NAME/NAV
		function build_month_header( $day1 ) {
			$month = date( 'n', $day1 );
			$prev_year = $next_year = date( 'Y', $day1 );
			
			$next_month = $month + 1;
			$prev_month = $month - 1;
			
			// year wrap exceptions
			if ( $month == 12 ) {
				$next_month = 1;
				$next_year ++;
			}
			if ( $month == 1 ) {
				$prev_month = 12;
				$prev_year --;
			}
			
			$here = $_SERVER['REQUEST_URI'];
			$here_bits = explode( '?', $here );
			$link_prefix = $here_bits[0];
			
			$prev_link = $link_prefix . '?cm=' . $prev_month . '&cy=' . $prev_year;
			$next_link = $link_prefix . '?cm=' . $next_month . '&cy=' . $next_year;
			
			$html = '<div id="cal-header" class="cf">';
			$html .= '<a class="cal-nav cal-prev-month" href="' . $prev_link . '"></a>';
			$html .= '<h2 class="cal-month">' . date( 'F Y', $day1 ) . '</h2>';
			$html .= '<a class="cal-nav cal-next-month" href="' . $next_link . '"></a>';
			$html .= '</div>';
			
			return $html;
		}
		
		//---- DAY OF WEEK HEADERS
		function build_dow() {
			$html = '<tr class="cal-dow">';
			foreach ( $this->days_of_week as $day ) {
				$html .= '<th>' . $day . '</th>';
			}
			
			return $html . '</tr>';
		}
		
	} // end class
}