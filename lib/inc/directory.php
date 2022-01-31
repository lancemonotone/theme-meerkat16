<?php

add_action( 'wp_ajax_nopriv_search_directory', 'search_directory' ); //user is not logged in
add_action( 'wp_ajax_search_directory', 'search_directory' );  //user is logged in
add_shortcode( 'wmsDirectory', 'display_directory' );

function display_directory() {
	return wms_areas_of_study();
}

function wms_search_directory_html( $r ) {
	if ( $r['dept_id'][0] == 5100000 && strpos( $r['location']['mailto'], 'Service Building' ) !== false ) {
		// Special case for facilities employees
		$map_location = urlencode( 'Facilities Service Building' );
	} else {
		$map_location = urlencode( $r['address']['building'] );
	}

	$person_address = '';
	if ( count( $r['address'] ) ) {
		$address_building = $r['address']['building'];
		if ( $r['is_student'] ) {
			$person_address .= $address_building;
		} else {
			$person_address .= '<a href="/map/?b=' . $map_location . '">' . $address_building . '</a>';
		}
		if ( $address_building && $r['address']['room'] ) {
			$person_address .= ', ';
		}
		if ( $r['address']['room'] ) {
			$person_address .= $r['address']['room'];
		}
	}

	$person_email = '';
	if ( $r['email'] || $r['long_email'] ) {
		if ( $r['email'] ) {
			$person_email .= '<a href="mailto:' . $r['email'] . '">' . $r['email'] . '</a>';
		}
		if ( $r['long_email'] ) {
			$person_email .= '<p class="email"><a href="mailto:' . $r['long_email'] . '"> ' . $r['long_email'] . '</a></p>';
		}
	}

	$people_results = <<< EOT
<div class="directory-item">
	<div class="directory-title">
		<span class="person-name">{$r['full_name']}</span>
	</div><!-- .directory-title -->
	<div class="directory-detail">
		<div class="person-title">{$r['title']}</div><!-- .person-title -->
		<div class="person-department">{$r['dept'][0]}</div><!-- .person-department -->
EOT;
	if ( $r['phone'] ) {
		$people_results .= <<< EOT
		<div class="person-phone">{$r['phone']}</div><!-- .person-phone -->
EOT;
	}
	if ( $person_address ) {
		$people_results .= <<< EOT
		<div class="person-address">{$person_address}</div><!-- .person-address -->
EOT;
	}
	if ( $person_email ) {
		$people_results .= <<< EOT
		<div class="person-email">{$person_email}</div><!-- .person-email -->
EOT;
	}
	$people_results .= <<< EOT
	</div><!-- .directory-detail -->
</div><!-- .directory-item -->
EOT;

	return $people_results;
}

function wms_search_directory( $data ) {
	$live = true;
	if ( ! $data ) {
		// Called via ajax
		$live = false;
		parse_str( $_GET['data'], $data );
	}
	extract( $data );

	if ( ! $search ) {
		echo 'No search term supplied.';
		if ( ! $live ) {
			die();
		}
	}

	/*
	 * Do an LDAP search for people.
	 */
	$people = new WilliamsPeopleDirectory();
	$people->ldap->search( $search );
	$people_results = '';
	if ( ! empty( $people->ldap->facstaff_records ) || ! empty( $people->ldap->student_records ) ) {
		if ( ! empty( $people->ldap->facstaff_records ) ) {
			//Print records for fac/staff
			$records = $people->filter_records( $people->ldap->facstaff_records );
			if ( $records ) {
				$people_found = count( $records );
				foreach ( $records as $r ) {
					$people_results .= wms_search_directory_html( $r );
				}
			}
		}
		if ( ! empty( $people->ldap->student_records ) ) {
			//Print student records
			$records = $people->filter_records( $people->ldap->student_records );
			if ( $records ) {
				$people_found += count( $records );
				$people_results .= '<h3 class="mustard-text">Students</h3>';
				foreach ( $records as $r ) {
					$people_results .= wms_search_directory_html( $r );
				}
			}
		}
		$people = $people_found == 1 ? 'Person' : 'People';
	} else {
		$people = 'People';
		$people_found = 0;
		$people_results = '<div>&nbsp;</div>';
	}
	$people_header = '<div class="bar-header" data-target="#people-content"><div class="sprite users"></div><span id="people-count">' . $people_found . ' </span>' . $people . '</div>';
	$people_html = '<td id="results-people" class="bar-header-container">' . $people_header . '<div id="people-content" class="results-content">' . $people_results . '</div></td>';

	/*
	 * Search the A-Z directory stored in the flexiform database.
	 */
	if ( function_exists( 'flexiform_get_data' ) ) {
		$args = array(
			'schemaID'     => 12076,
			'searchFields' => array( 12081, 16972, 27196 ),
			'searchString' => $search,
			//	'searchWhere' => array('26549' => 'dir: Dept/Office'),
		);
		$result = flexiform_get_data( $args );
		if ( $result ) {
			$az_found = 0;
			foreach ( $result as $d ) {
				// Only inlcude data tagged 'dir: Dept/Office'.
				$tags = array();
				if ( $d['Tag']['value'] ) {
					$tags[ $d['Tag']['value'] ] = 1;
				} else if ( is_array( $d['Tag'] ) ) {
					foreach ( $d['Tag'] as $t ) {
						$tags[ $t['value'] ] = 1;
					}
				}
				if ( ! $tags['dir: Dept/Office'] ) {
					continue;
				}
				$az_found ++;
				if ( $d['URL']['value'] ) {
					$title = '<a href="' . $d['URL']['value'] . '">' . $d['Title']['value'] . '</a>';
				} else {
					$title = $d['Title']['value'];
				}
				$az_results .= '<div class="directory-item">' . '<div class="directory-title"><span class="a-z-name">' . $title . '</span></div>' . '<div class="directory-detail">' . '<div class="a-z-location"><a href="/map?b=' . urlencode( $d['Building']['value'] ) . '">' . $d['Building']['value'] . '</a></div>';
				if ( $d['Phone']['value'] ) {
					$az_results .= '<div class="a-z-phone">' . $d['Phone']['value'] . '</div>';
				} else if ( is_array( $d['Phone'] ) ) {
					foreach ( $d['Phone'] as $phone ) {
						$az_results .= '<div class="a-z-phone">' . $phone['value'];
						if ( $phone['type'] && $phone['type'] != 'main' ) {
							$az_results .= ' ' . $phone['type'];
						}
						$az_results .= '</div>';
					}
				}
				$az_results .= '</div>' . '</div>';
			}
		} else {
			$az_found = 0;
			$az_results = '<div>&nbsp;</div>';
			//echo '<strong>You search term did not match any items in the college directory.</strong>';
		}
		$places = $az_found == 1 ? 'Place' : 'Places';

		$places_header = '<div class="bar-header" data-target="#a-z-content"><div class="sprite house"></div><span id="place-count">' . $az_found . ' </span>' . $places . '</div>';
		$places_html = '<td id="results-places" class="bar-header-container">' . $places_header . '<div id="a-z-content" class="results-content">' . $az_results . '</div></td>';
	}

	if ( $live ) {
		return $people_html . $places_html;
	} else {
		echo $people_html . $places_html;
		die();
	}
}

/*
 * Department and Office Directory
 *
 * Query flexiform db for deparment and office data. Format
 * in a table. Returns table html.
 */
function wms_dept_office_directory() {

	$args = array(
		'schemaID'     => 12076,
		'searchFields' => array( 26549 ), // This is the 'Tag' field
		'searchString' => 'dir: Dept/Office',
	);
	$result = flexiform_get_data( $args );
	$list = array();
	foreach ( $result as $dept ) {
		if ( $dept['Parent']['value'] ) {
			$list[ $dept['Parent']['object']['Title']['value'] ]['children'][ $dept['Title']['value'] ] = $dept;
		} else {
			$list[ $dept['Title']['value'] ]['value'] = $dept;
		}
	}
	ksort( $list );
	$count = 0;
	foreach ( $list as $parent ) {
		$count ++;
		$html .= build_html_row( array( 'item' => $parent['value'], 'level' => 0, 'count' => $count ) );
		if ( ! is_array( $parent['children'] ) ) {
			continue;
		}
		ksort( $parent['children'] );
		$child_count = 0;
		foreach ( $parent['children'] as $title => $data ) {
			$child_count ++;
			$count ++;
			$html .= build_html_row( array( 'item'        => $data,
			                                'level'       => 1,
			                                'count'       => $count,
			                                'child_count' => $child_count
			) );
		}
	}
	$print_html = '<div class="dept-print sidebarcol"><p>The directory is available in <a href="http://hr.williams.edu/files/directory.pdf">printable PDF format</a>.</p>
		<p>Submit changes and corrections to <a href="mailto:sma1@williams.edu">sma1@williams.edu</a>.</p></div>';

	return $print_html . '<table class="wms-dept-info">' . $html . '</table>';

}

function build_html_row( $data ) {
	extract( $data );
	$classes = $level;

	$html = '<tr class="dept-level-' . $classes . '">';

	$class = ( $level ) ? 'child' : 'parent';
	if ( $item['URL'] ) // Link the name in the online version.
	{
		$name = '<a href="' . $item['URL']['value'] . '">' . $item['Title']['value'] . '</a>';
	} else {
		$name = $item['Title']['value'];
	}
	$html .= '<td class="' . $class . '">' . $name;
	if ( $item['Building'] ) {
		$html .= ', <a href="http://map.williams.edu/#!s/key=' . $item['Building']['value'] . '">' . $item['Building']['value'] . '</a>';
	}
	if ( $item['cross_reference'] ) {
		$html .= ' <span>' . $item['cross_reference'] . '</span>';
	}
	$html .= "</td>\n";
	/*
	* phone zone
	*/
	$ph = array();
	if ( $item['Phone']['value'] ) {
		$ph[ $item['Phone']['type'] ] = $item['Phone']['value'];
	} else if ( $item['Phone'] ) {
		foreach ( $item['Phone'] as $phone ) {
			$ph[ $phone['type'] ] = $phone['value'];
		}
	}
	$p = explode( '-', $ph['main'] );
	$ap = explode( '-', $ph['alternate'] );
	if ( $p[0] == 597 && $p[0] == $ap[0] ) {
		$ph['main'] .= '/' . $ap[1];
	} else if ( $ph['alternate'] ) {
		// On the off chance it's not a 597 #.
		$ph['main'] .= '/' . $ph['alternate'];
	}
	$html .= '<td class="telephone">' . $ph['main'] . "</td>\n";
	$fax = $ph['fax'] ? $ph['fax'] . ' fax' : '';  // 131021 drm2: not necessary to add &nbsp;, also this breaks mobile styling
	$html .= '<td class="fax">' . $fax . "</td></tr>\n";
	if ( $item['note'] ) {
		$html .= '<tr><td colspan="3" class="note">' . $item['note'] . "</td></tr>\n";
	}

	return $html;
}

function wms_a_z_index() {

	if ( $az_from_db = get_transient( 'AtoZIndexPage' ) ) {
		return $az_from_db;
	}

	$args = array(
		'schemaID'     => 12076,
		'searchFields' => array( 26549 ),
		'searchString' => 'dir: A-Z',
	);

	$result = flexiform_get_data( $args );

	/*
	 * Create a-z nav with links to anchors
	 */
	$alpha_nav = '<div id="alphabet-nav" class="filter cf">';
	$alpha_nav .= "<div class='a-z-alpha a-z-alpha-0-9'><a href='#0-9'>#</a></div>";
	$c = 0;
	$alphabet = range( 'A', 'Z' );
	foreach ( $alphabet as $char ) {
		$class = 'a-z-alpha';
		if ( $c % 5 == 0 ) {
			$class .= ' a-z-alpha-left';
		}
		$alpha_nav .= "<div class='$class'><a href='#$char'>$char</a></div>";
		$c ++;
	}
	$alpha_nav .= "<div class='a-z-alpha a-z-alpha-left a-z-alpha-all'><a href='#all'>all</a></div>";
	$alpha_nav .= '</div><!-- end A-Z Nav -->';

	/*
	 * Add filter for list
	 */
	$filter = '<form action="" id="a-z-filter">
               <input type="text" class="filter" name="a-z-filter">
               </form>';

	/*
	 * List the directory
	 */
	foreach ( $result as $azthing ) {
		$index[ $azthing['Title']['value'] ] = $azthing;
		if ( $azthing['Sort title']['value'] ) {
			$index[ $azthing['Sort title']['value'] ] = $azthing;
		}
	}
	ksort( $index );
	$alphaArray = $alphabet;
	$alphaList = array();
	$char = '0-9';
	$activeChar = '0-9';
	foreach ( $index as $title => $item ) {
		$itemChar = substr( $title, 0, 1 );
		if ( ! in_array( $itemChar, $alphabet ) ) {
			$itemChar = '0-9';
		} else if ( $itemChar != $char ) {
			while ( $itemChar != $char && $alphaArray ) {
				$char = array_shift( $alphaArray );
			}
		}
		if ( $itemChar != $activeChar ) {
			$group_count ++;
			$activeChar = $itemChar;
			$listing .= '</div><div class="letter-group ';
			if ( $group_count % 2 != 0 ) {
				$listing .= ' letter-right';
			}
			//			$listing .= '"><div class="a-z-section bar-header ' . $charClass . '"><a name="' . $itemChar . '">' . $itemChar . '</a></div>';
		}
		$name = $title;
		if ( $item['URL']['value'] ) {
			$name = '<a href="' . $item['URL']['value'] . '">' . $name . '</a>';
		}
		//$listing .= '<div class="a-z-item ' . $charClass . '">' . $name . '</div>';
		$alphaList[ $itemChar ] .= '<div class="a-z-item ' . $charClass . '">' . $name . '</div>';
		$activeChar = $itemChar;
	}
	$group_count = 0;
	//array_unshift($alphabet, '0-9');
	$listing = '<div id="a-z-list">';
	$listing .= '<div class="letter-group"><div class="a-z-section bar-header a-z-0-9"><a name="0-9">#</a></div>';
	$listing .= $alphaList['0-9'] . '</div>';
	foreach ( $alphabet as $letter ) {
		$group_count ++;
		$listing .= '<div class="letter-group';
		if ( $group_count % 2 != 0 ) {
			$listing .= ' letter-right';
		}
		$listing .= '"><div class="a-z-section bar-header a-z-' . $letter . '"><a name="' . $letter . '">' . $letter . '</a></div>';
		if ( $alphaList[ $letter ] ) {
			$listing .= $alphaList[ $letter ];
		}
		$listing .= '</div>';
	}
	$listing .= '</div>';
	$a_z_page = '<div class="a-z-tools">' . $alpha_nav . $filter . '</div>' . $listing;
	set_transient( 'AtoZIndexPage', $a_z_page, HOUR_IN_SECONDS );

	return $a_z_page;

}

function wms_areas_of_study() {

	$args = array(
		'schemaID'     => 12076,
		'searchFields' => array( 26549 ), // This is the 'Tag' field
		'searchString' => 'study:',
	);

	$result = flexiform_get_data( $args );

	if ( ! is_array( $result ) ) {
		return "No data found.";
	}

   $types = array(
		'study: major' => array('label' => 'Major', 'sort' => 'a'),
		'study: concentration' => array('label' => 'Concentration', 'sort' => 'b'),
		'study: other' => array('label' => 'More information', 'sort' => 'c'),
	);
	$list = array();
	foreach ( $result as $area ) {
		if ( ! $area['Tag']['value'] ) {
			foreach ( $area['Tag'] as $tag ) {
				$area[ 'tag: ' . $tag['value'] ] = 1;
				if (strpos($tag['value'], 'study:') === 0) {
					$areatype = $tag['value'];
				}
			}
		} else {
			$area[ 'tag: ' . $area['Tag']['value'] ] = 1;
			$areatype = $area['Tag']['value'];
		}
		$titlekey = rtrim($area['Title']['value']) . $types[$areatype]['sort'];
		$area['aos-type'] = $types[$areatype]['label'];
		$list[$titlekey] = $area;
	}
	ksort( $list );
	$c = 0;
	foreach ( $list as $data ) {
		$name = $data['Title']['value'];
		if ( $data['URL']['value'] ) {
			$name = '<a href="' . $data['URL']['value'] . '">' . $name . '</a>';
		}
		$c ++;
		$class = ( $c % 2 == 0 ) ? '' : ' class="alt-stripe"';
		$table .= "<tr$class><td>$name</td>";
		$type = $data['aos-type'];
		$info = '';
		if ( $data['More URL']['value'] ) {
			$info = '<a href="' . $data['More URL']['value'] . '">' . $type . '</a>';
		} else if ( $data['Description']['value'] ) {
			$info = $data['Description']['value'];
		}
		$table .= '<td> ' . $info . '</td>';
		$table .= '</tr>';
	}
	$table = "<table class='data' id='areas-of-study'><tbody>
		<tr><th id='area-col' style='width: 48%'>Area of Study</th><th id='major-col' style='width: 48%'>What's Offered</th></tr>
		$table
		</tbody></table>";

	return $table;

}

?>
