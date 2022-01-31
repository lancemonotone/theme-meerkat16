<?php

/*
* Handles search queries, and builds directories (pulling from flexiform database)
*/

class Meerkat_Search {
	private static $instance;

	public function isWmsSearch() {
		if( isset($_GET[ 'q' ]) || isset($_GET[ 'dt' ]) || ( preg_match( '/\/(search|a-z|people|office-directory)(\/|\/\?.*)?$/', $_SERVER[ 'REQUEST_URI' ] ) && ( substr( $_SERVER[ 'SERVER_NAME' ], 0, 3 ) == 'www' ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function getSearchContext() {
		// tab order: Search, People, A-Z, Offices
		preg_match( '/\/(search|a-z|people|office-directory)(\/|\/\?.*)?$/', $_SERVER[ 'REQUEST_URI' ], $matches );
		switch ($matches[1]) {
			case 'search':
				$open = 1;
				break;
			case 'people':
				$open = 2;
				break;
			case 'a-z':
				$open = 3;
				break;
			case 'office-directory':
				$open = 4;
				break;
		}
		$people = do_shortcode( '[wmsdirindex]' );
		return array(
			'open' => $open,
			'searchstring' => $_GET['q'],
			'tab' => 'search',
			'people' => $people,
			'az' => wms_a_z_index(),
			'offices' => wms_dept_office_directory()
		);
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

	protected function __construct(){
	}

	/**
	 * Returns the singleton instance of this class.
	 *
	 * @return Meerkat_Search The singleton instance.
	 */
	public static function instance(){
		if( null === static::$instance ){
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * singleton instance.
	 *
	 * @return void
	 */
	private function __clone(){ }

	/**
	 * Private unserialize method to prevent unserializing of the singleton
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup(){ }
}