<?php

/*
* Handles search queries, and builds directories (pulling from flexiform database)
*/

class meerkatSearch() {

	global $js, $is_magazine_theme, $meercore, $search_term, $is_directory, $directories, $directory_title;

	protected function __construct() {

	}

	public function is_wms_search() {

	}

	function setSearchVars() {

		$is_directory = true;
		$js['directory']['load'] = true;
		$js['purl']['load'] = true;

		$search_term = '';
		if ( $_GET['q'] ) {
			$search_term = $_GET['q'];
		} else {
			$search_term = get_search_query();
		}
		// Sanitize search term.
		$search_term = sanitize_text_field( $search_term );
	}

	function buildSearch() {

		$directories = array(
			'search'           => array(
				'title'  => 'Search Results',
				'in_nav' => false,
				'q_name' => 'q',
				'q_val'  => true
			),
			'a-z'              => array(
				'title'  => 'A-Z Index',
				'in_nav' => true,
				'q_val'  => 'a-z'
			),
			'people'           => array(
				'title'  => 'People',
				'in_nav' => true,
				'q_val'  => 'people'
			),
			'office-directory' => array(
				'title'  => 'Departments & Offices',
				'in_nav' => true,
				'q_val'  => 'dept-office'
			),
			'areas-of-study'   => array(
				'title'  => 'Areas of Study',
				'in_nav' => false,
				'q_val'  => 'areas'
			)
		);

		//---- BUILD SEARCH or DIRECTORY ----//
		foreach ( $directories as $item => $data ) {
			$valid_directory = false;
			if ( preg_match( "/\/$item\/?/", $_SERVER['REQUEST_URI'] ) ) {
				$valid_directory = true;
				$directory_id = $item;
				$directory_title = $page_title = $data['title'];
				if ( $item == 'search' ) {
					// Override page title
					if ( $search_term ) {
						$page_title = 'Search & Directory Results for: <span class="search-term">' . stripslashes( $search_term ) . '</span>';
					} else {
						$page_title = 'Search & Directory Pages';
					}
					// bail after first match
					break;
				}
			}
		}

		if (!$directory_id && $search_term){
			$directory_id = 'search';
		}

	}

	function wms_directory_results ( $type ){
		// map directory type with builder function (all but search are in lib/directories.php)
		if ( $type == 'search' ){
			wms_search_results();
		}
		else if ( $type == 'people' ){
			echo do_shortcode( '[wmsdirindex]' );
		}
		else if ( $type == 'areas-of-study' ){
			return wms_areas_of_study();
		}
		else if ( $type == 'office-directory' ){
			return wms_dept_office_directory();
		}
		else if ( $type == 'a-z' ){
			return	wms_a_z_index();
		}
	}

	function wms_search_results(){
		global $search_term;
		if (strlen($search_term) == 0) return;
		?>
		<table id="results-all" class="cf">
			<tr>
				<td id="results-web" class="bar-header-container">
					<div class="bar-header current" data-target="#web-content"><div class="sprite web"></div>Web Pages</div>
					<div id="web-content" class="results-content">
						<div id="results-navbox-container" class="cf"></div>
						<gcse:searchresults-only queryParameterName="q" linkTarget="_self" webSearchResultSetSize="10"></gcse:searchresults-only>
					</div>
				</td>
				<?php
				// Directory results for lib/directory.php
				echo wms_search_directory( array( 'search' => $search_term, 'live' => true ));
				?>
			</tr>
		</table>
		<?php
	}
}