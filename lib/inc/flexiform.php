<?php
require( WMS_EXT_LIB . '/fpdf/fpdf.php' );

add_action( 'flexiform_update', 'wms_update_office_directory_pdf' );
function wms_update_office_directory_pdf( $schemaId ) {
	if ( $schemaId != 12076 ) {
		return;
	}
	// Only update when associated data is changed: Directory data is
	// based on schema id 12076
	$data = flexiform_get_data( array( 'schemaID' => $schemaId, 'searchString' => 'dir: Dept/Office' ) );
	//
	$pdf = new PDF( 'p', 'mm', 'letter', array( 'title'   => 'Williams Department/Office Directory',
	                                            'updated' => date( 'n/j/Y' )
	) );
	$pdf->SetAutoPageBreak( true, 18 );
	$pdf->AliasNbPages();
	$pdf->AddPage();
	//
	$children = array();
	$rows = array();
	$sort = array();
	foreach ( $data as $i => $item ) {
		$row = array();
		$row['Title'] = preg_replace( '/&Amp;/i', '&', $item['Title']['value'] );
		$row['Title'] = iconv( 'UTF-8', 'windows-1252', $row['Title'] );
		$row['Building'] = preg_replace( '/&Amp;/i', '&', $item['Building']['value'] );
		if ( $item['Phone']['value'] ) {
			$row[ $item['Phone']['type'] ] = $item['Phone']['value'];
		} else if ( is_array( $item['Phone'] ) ) {
			foreach ( $item['Phone'] as $p ) {
				$row[ $p['type'] ] = $p['value'];
			}
		}
		if ( $item['Parent'] ) {
			// item has parent
			$row['Parent'] = preg_replace( '/&Amp;/i', '&', $item['Parent']['object']['Title']['value'] );
			$children[ $row['Parent'] ][ $row['Title'] ] = $row;
		} else {
			$rows[] = $row;
			$sort[] = $row['Title'];
		}
	}
	array_multisort( $sort, $rows );
	$count = 0;
	foreach ( $rows as $row ) {
		$count ++;
		build_pdf_row( array( 'pdf' => $pdf, 'item' => $row, 'level' => 0, 'count' => $count ) );
		if ( ! $children[ $row['Title'] ] ) {
			continue;
		}
		ksort( $children[ $row['Title'] ] );
		foreach ( $children[ $row['Title'] ] as $c ) {
			$count ++;
			build_pdf_row( array( 'pdf' => $pdf, 'item' => $c, 'level' => 1, 'count' => $count ) );
		}
	}
	// This operation may be occur in a non-www context, so only update pdf in
	// HR site files dir: hr.williams.edu = site id 27
	$upload_dir_info = wp_upload_dir();
	$pdf->Output( dirname( dirname( $upload_dir_info['basedir'] ) ) . '/27/files/directory.pdf' );

	return;
}

class PDF extends FPDF {
	var $title;
	var $updated;

	function PDF( $orientation = 'P', $unit = 'mm', $size = 'A4', $args = array() ) {
		// Call parent constructor
		$this->FPDF( $orientation, $unit, $size );
		$this->title = $args['title'];
		$this->updated = $args['updated'];
	}

	// Page header
	function Header() {
		// Logo
		//$this->Image('logo.png',10,6,30);
		// Arial bold 15
		$this->SetFont( 'Arial', 'B', 12 );
		// Move to the right
		$this->Cell( 80 );
		// Title
		$this->Cell( 30, 10, $this->title, 0, 0, 'C' );
		// Line break
		$this->Ln();
	}

	// Page footer
	function Footer() {
		// Position at 1.5 cm from bottom
		$this->SetY( - 15 );
		// Arial italic 8
		$this->SetFont( 'Arial', 'I', 8 );
		// Page number
		$this->Cell( 120, 5, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'L' );
		$this->Cell( 77, 5, 'Last updated: ' . $this->updated, 0, 0, 'R' );
	}

	function AlternateFillColor( $row ) {
		if ( $row % 2 == 1 ) {
			$this->SetFillColor( 210, 210, 210 );

			return true;
		} else {
			$this->SetFillColor( 255, 255, 255 );

			return false;
		}
	}
}

function build_pdf_row( $data ) {
	extract( $data );
	$indent = $level * 5;
	$name_len = 83 - $indent;
	$next_len = 50;
	$fill = $pdf->AlternateFillColor( $count );
	if ( $indent ) {
		$pdf->SetFont( 'Arial', '', 9 );
	} else // Parent entries are bold.
	{
		$pdf->SetFont( 'Arial', 'B', 9 );
	}
	if ( $item['Sort title'] ) {
		$len = $pdf->GetStringWidth( $item['Title'] );
		$name_len = ceil( $len ) + 2;
		$next_len = 133 - $name_len;
	}
	if ( $indent ) {
		$pdf->Cell( $indent, 5, '', 0, '', '', $fill );
	}
	$pdf->Cell( $name_len, 5, $item['Title'], 0, '', '', $fill );
	// Everything else is normal text.
	$pdf->SetFont( 'Arial', '', 9 );
	if ( $item['Sort title'] ) {
		$pdf->SetFont( 'Arial', 'i', 9 );
		$pdf->Cell( $next_len, 5, ' ' . $item['Sort title'], 0, '', '', $fill );
		$pdf->SetFont( 'Arial', '', 9 );
	} else {
		$pdf->Cell( $next_len, 5, $item['Building'], 0, '', '', $fill );
	}
	$pdf->Cell( 31, 5, $item['main'], 0, '', '', $fill );
	if ( $item['fax'] ) {
		$item['fax'] .= ' fax';
	}
	$pdf->Cell( 32, 5, $item['fax'], 0, '', '', $fill );
	$pdf->Ln();
	if ( $item['note'] ) {
		$pdf->Cell( 196, 5, '   ' . $item['note'], 0, '', '', $fill );
		$pdf->Ln();
	}
}

function theme_save_updated_flexiform_data( $data_array ) {
	$data_array['save'] = true;

	return theme_display_flexiform_data( $data_array );
}

/*
 * Plugable function to display Flexiform data.
 */
function theme_display_flexiform_data( $data_array ) {
	extract( $data_array );
	switch ( $dataset ) {
		case 'departments':
		case 'Department Office Directory':
			//
			$pdf = new PDF( 'p', 'mm', 'letter', array( 'title'   => 'Williams Department/Office Directory',
			                                            'updated' => date( 'n/j/Y', strtotime( $updated ) )
			) );
			$pdf->SetAutoPageBreak( true, 18 );
			$pdf->AliasNbPages();
			$pdf->AddPage();
			//
			$children = array();
			foreach ( $data as $i => $item ) {
				if ( $item['parent'] ) {
					// item has parent
					$item['name'] = preg_replace( '/&Amp;/i', '&', $item['name'] );
					$item['location'] = preg_replace( '/&Amp;/i', '&', $item['location'] );
					$item['cross_reference'] = preg_replace( '/&Amp;/i', '&', $item['cross_reference'] );
					$item['parent'] = preg_replace( '/&Amp;/i', '&', $item['parent'] );
					$children[ $item['parent'] ][] = $item;
					unset( $data[ $i ] );
				} else {
					$data[ $i ]['name'] = preg_replace( '/&Amp;/i', '&', $item['name'] );
					$data[ $i ]['location'] = preg_replace( '/&Amp;/i', '&', $item['location'] );
					$data[ $i ]['cross_reference'] = preg_replace( '/&Amp;/i', '&', $item['cross_reference'] );
				}
			}
			$count = 0;
			//$header = '<tr class="head"><th>Department</th><th class="fax-head">Phone</th><th>Fax</th></tr>';
			//$html = $header;
			$html = '';
			foreach ( $data as $i => $item ) {
				$count ++;
				$html .= build_html_row( array( 'item' => $item, 'level' => 0, 'count' => $count ) );
				build_pdf_row( array( 'pdf' => $pdf, 'item' => $item, 'level' => 0, 'count' => $count ) );
				foreach ( $children[ $item['name'] ] as $c ) {
					$count ++;
					$html .= build_html_row( array( 'item' => $c, 'level' => 1, 'count' => $count ) );
					build_pdf_row( array( 'pdf' => $pdf, 'item' => $c, 'level' => 1, 'count' => $count ) );
				}
			}
			if ( $save ) {
				$files = wp_upload_dir();
				$file = $files['basedir'] . '/directory.pdf';
				$pdf->Output( $file );

				return $file;
			} else {
				return '<p id="wms-dept-office-updated">Last updated: ' . date( 'n/j/Y', strtotime( $updated ) ) . '</p><table class="wms-dept-info">' . $html . '</table>';
			}
			break;
		case 'Honorary Degrees':
			$count = isset( $args['items_per_page'] ) ? $args['items_per_page'] : count( $data );
			$guts = '<tr><th>First name</th>
			<th>Last name</th>
			<th>Degree</th>
			<th>Year</th>
			</tr>';
			foreach ( $data as $row ) {
				//$guts .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
				$guts .= '<tr><td>' . $row['First name']['value'] . '</td>
				<td>' . $row['Last name']['value'] . '</td>
				<td>' . $row['Degree']['value'] . '</td>
				<td>' . $row['Year']['value'] . '</td>
				</tr>';
			}
			if ( $paging ) {
				$page_links = $paging['first']['link'] . ' ' . $paging['previous']['link'] . ' ' . $paging['next']['link'] . ' ' . $paging['last']['link'];
			} else if ( $args['searchString'] ) {
				// Search probably reduce the data set.
				$page_links = '<a href="' . $_SERVER['REQUEST_URI'] . '">view all data</a>';
			}

			return '<div class="honorary-degree">' . '<table class="wms-dept-info"><tr><td colspan="2">' . $search . '</td><td colspan="2">' . $page_links . '</td></tr>' . $guts . "</table></div>\n<!-- end honorary-degree dev -->\n";
			break;
	} // end switch
	return '<h3>"' . $dataset . '" data not supported by this theme</h3>';
}

/*
 * Plugable function to export Flexiform data.
 */
function theme_export_flexiform_data( $data_array ) {
	extract( $data_array );
	if ( $dataset != 'departments' ) {
		echo "<h3>We do not support '$dataset' data at this time.</h3>";

		return;
	}
	$children = array();
	foreach ( $data as $i => $item ) {
		if ( $item['parent'] ) {
			// item has parent
			$children[ $item['parent'] ][] = $item;
			unset( $data[ $i ] );
		}
	}
	$out = '"dept", "subdept", "location", "phone", "alt_phone", "fax"' . "\n";
	foreach ( $data as $i => $item ) {
		$out .= '"' . $item['name'] . '",,"' . $item['location'] . '","' . $item['phone'] . '","' . $item['alt_phone'] . '","' . $item['fax'] . ' fax"' . "\n";
		foreach ( $children[ $item['name'] ] as $c ) {
			$out .= ', "' . $c['name'] . '","' . $c['location'] . '","' . $c['phone'] . '","' . $c['alt_phone'] . '","' . $c['fax'] . ' fax"' . "\n";
		}
	}

	return $out;
}