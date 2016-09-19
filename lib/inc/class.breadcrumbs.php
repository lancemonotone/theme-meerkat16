<?php
class Breadcrumbs {
	private static $instance;
	public $sep;
	
	/**
	 * Returns the singleton instance of this class.
	 *
	 * @return Breadcrumbs The singleton instance.
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Private clone method to prevent cloning of the instance of the
	 * singleton instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}
	
	/**
	 * Private unserialize method to prevent unserializing of the singleton
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}
	
	protected function __construct() {
		$this->sep = '<span class="breadcrumb-sep"> &raquo; </span>';
	}
	
	function get_sep() {
		return $this->sep;
	}
	
	/**
	 * @return string
	 */
	function make_breadcrumbs() {
		global $wms_homepage, $post, $cat, $meermag;
		$wms_homepage = false;
		
		// college link
		$nav = '<div id="breadcrumbs" class="breadcrumbs">';
		$nav .= $this->one_crumb( 'Williams', Wms_Server::instance()->www, 'wms-home-crumb' );
		
		// are we on the main site?
		if ( WWW_BLOG_ID == Meerkat16::instance()->blog_id ) {
			$wms_homepage = true;
		}
		
		// dept homepage link
		if ( ! $wms_homepage ) {
			$nav .= $this->sep;
			$nav .= $this->one_crumb( get_bloginfo( 'name' ), get_home_url(), 'dept-home-crumb' );
		}
		
		// PROFILE
		if ( Meerkat16_Profiles::instance()->is_wms_profile) {
			// faculty/staff profile gets directory page & name of person as crumb
			if ( $staff_page = get_field( 'staff_url', 'options' ) ) {
				$staff_url = get_permalink( $staff_page->ID );
				$nav .= $this->sep . '<a href="' . $staff_url . '">' . $staff_page->post_title . '</a>';
			}
			$nav .= $this->sep . Meerkat16_Profile_Single::instance()->get_the_profile()['full_name'];
		} else {
			// OTHER PAGE TYPES
			// all other types of pages/conditions.  front page does not need a crumb
			if ( ! is_front_page() ) {
				/* KEK 8/7/14: this is causing duplicate crumbs with buckets now being the main nav
				$menu_crumbs = false;
				if ( has_nav_menu( 'main' ) ){
					// site uses a custom menu
					$menu_crumbs = $this->custom_menu_crumb( $post->ID );
				}
				if ( $menu_crumbs ){
					$nav .= $menu_crumbs;
				}
				*/
				
				if ( Meerkat16::instance()->is_magazine_theme && ! is_page() && ! is_404() ) {
					global $wp_query;
					if ( $wp_query->query_vars['volume_year'] ) {
						$issue = $meermag->get_context_edition_link();
						$nav .= $this->sep . $issue;
					}
				}
				
				global $custom_end_crumb;
				if ( $custom_end_crumb ) {
					$nav .= $custom_end_crumb;
				} else if ( is_page() ) {
					$nav .= $this->page_crumb( $post->ID );
				} elseif ( is_single() ) {
					// a single post
					if ( 86 == Meerkat16::instance()->blog_id && strpos( $_SERVER['REQUEST_URI'], 'experts/' ) == 1 ) {
						// communications faculty experts
						$nav .= $this->sep . '<span class="breadcrumb"><a href="/media-relations/faculty-experts/">Experts</a></span>';
						$nav .= $this->sep . $post->post_title;
					} else {
						$nav .= $this->post_crumb( $post->ID );
					}
				} elseif ( is_category() ) {
					$nav .= $this->category_crumb( $cat );
				} elseif ( is_tag() ) {
					if ( 86 == Meerkat16::instance()->blog_id ) {
						// communications faculty experts
						$experts = '<a href="/media-relations/faculty-experts/">Experts</a>';
						$nav .= $this->sep . $experts . $this->sep . $this->tag_crumb();
					} else {
						$nav .= $this->sep . $this->tag_crumb();
					}
				} elseif ( is_author() ) {
					$nav .= $this->sep . $this->author_crumb();
				} elseif ( is_post_type_archive() ) {
					$nav .= $this->sep . $this->post_type_archive_crumb();
				} elseif ( Meerkat_Search::instance()->isWmsSearch() ) {
					$nav .= $this->sep . 'Search & Directories';
				} elseif ( Meerkat16::instance()->is_magazine_theme && $_GET['s'] ) {
					$nav .= $this->sep . 'Magazine Search';
				} elseif ( is_404() ) {
					$nav .= $this->sep . 'Page Not Found';
				}
			}
		}
		
		$nav .= '</div>';
		return $nav;
	}
	
	//----------------------
	
	/**
	 * @return string
	 */
	function post_type_archive_crumb() {
		global $wp_query, $post_type;
		$title = $wp_query->queried_object->label;
		
		return '<a href="' . get_post_type_archive_link( $post_type ) . '">' . $title . '</a>';
	}
	
	/**
	 * @param $id
	 *
	 * @return bool|string
	 */
	function custom_menu_crumb( $id ) {
		global $post;
		$menu_slug = 'main';
		$lookup_by_post_id = array();
		$lookup_by_menu_id = array();
		$crumbs = array();
		$count = 0;
		
		$locations = get_nav_menu_locations();
		$menu = wp_get_nav_menu_object( $locations[ $menu_slug ] );
		$menu_items = wp_get_nav_menu_items( $menu->term_id );
		
		// iterate through menu items, taking note of whats where
		foreach ( $menu_items as $item => $data ) {
			// $menu items is a big ugly object- set up a way to reference the data we need by both post id (object_id) and menu id
			$lookup_by_post_id[ $data->object_id ] = $count;
			$lookup_by_menu_id[ $data->ID ] = $count;
			$count ++;
		}
		if ( array_key_exists( $id, $lookup_by_post_id ) ) {
			// current post is in custom menu. make unlinked crumb for it
			$crumbs[] = $post->post_title;
			$menu_parent = $menu_items[ $lookup_by_post_id[ $id ] ]->menu_item_parent;
			while ( $menu_parent ) {
				// create linked crumbs for parent menu items
				$url = $menu_items[ $lookup_by_menu_id[ $menu_parent ] ]->url;
				$title = $menu_items[ $lookup_by_menu_id[ $menu_parent ] ]->title;
				$crumbs[] = '<a href="' . $url . '">' . $title . '</a>';
				$menu_parent = $menu_items[ $lookup_by_menu_id[ $menu_parent ] ]->menu_item_parent;
			}
			$crumbs = array_reverse( $crumbs );
			
			return $this->sep . join( $this->sep, $crumbs );
		}
		
		return false;
	}
	
	/**
	 * @param $id
	 *
	 * @return string
	 */
	function page_crumb( $id ) {
		// builds breadcrumb trail for a page and its ancestors
		
		$crumbs = array();
		$page_title = get_the_title( $id );
		
		return $this->hierarchical_crumb( $id, 'page' ) . $this->sep . $page_title;
	}
	
	/**
	 * @param $id
	 * @param bool $link_cat
	 *
	 * @return string
	 */
	function category_crumb( $id, $link_cat = false ) {
		// builds breadcrumb trail for a category and its ancestors
		
		global $meermag;
		$html = '';
		$html = $this->hierarchical_crumb( $id, 'category' ) . $this->sep;
		$cat_info = get_category( $id );
		if ( $link_cat ) {
			// sometmes we want to make the "bottom" level category a link (e.g. when on a post)
			if ( Meerkat16::instance()->is_magazine_theme ) {
				// prepend category link with year/issue.
				$html .= '<a href="' . $meermag->get_issue_href() . '/' . $cat_info->slug . '">' . $cat_info->name . '</a>';
			} else {
				// modified to take into account a non-default category base.
				$name = $cat_info->name;
				$url = get_category_link( $cat_info->cat_ID );
				$html .= $this->one_crumb( $name, $url, 'cat-crumb' );
			}
		} else {
			$html .= $cat_info->name;
		}
		
		return $html;
	}
	
	/**
	 * @param $id
	 * @param $type
	 *
	 * @return bool|string
	 */
	function hierarchical_crumb( $id, $type ) {
		// builds crumb trail for item ancestors (pages, categories)
		
		$crumbs = array();
		$ances = get_ancestors( $id, $type );
		if ( $ances ) {
			// sort the other direction (we want oldest ancestor first)
			$ances = array_reverse( $ances );
			foreach ( $ances as $ances_id ) {
				// get name, url
				$name = $url = '';
				if ( $type == 'page' ) {
					$name = get_the_title( $ances_id );
					$url = get_permalink( $ances_id );
				} elseif ( $type == 'category' ) {
					$cat_info = get_category( $ances_id, ARRAY_A );
					$name = $cat_info['name'];
					$url = '/category/' . $cat_info['slug'];
				}
				// build crumb
				$crumbs[] = $this->one_crumb( $name, $url, 'ances-crumb' );
			}
			return $this->sep . join( $this->sep, $crumbs );
		}
		return false;
	}
	
	/**
	 * @param $name
	 * @param $url
	 * @param bool $class
	 *
	 * @return string
	 */
	function one_crumb( $name, $url, $class = false ) {
		// builds a single breadcrumb
		$html = '<span class="breadcrumb';
		if ( $class ) {
			$html .= ' ' . $class;
		}
		$html .= '"><a href="' . $url . '">' . $name . '</a></span>';
		
		return $html;
	}
	
	/**
	 * @param $id
	 *
	 * @return string
	 */
	function post_crumb( $id ) {
		// single post, out of context of a custom menu. if it belongs to a category, list that.
		
		global $post;
		$html = '';
		$cats = get_the_category( $id );
		usort( $cats, '_usort_terms_by_ID' ); // order by ID
		$first_cat = $cats[0];
		if ( $first_cat->term_id > 1 ) {
			// avoid "articles" or "uncategorized"
			$html = $this->category_crumb( $first_cat->term_id, true );
		}
		
		return $html . $this->sep . $post->post_title;
		
	}
	
	/**
	 * @return string
	 */
	function tag_crumb() {
		// builds crumb for a tag page
		global $tag;
		$term = get_term_by( 'slug', $tag, 'post_tag', ARRAY_A );
		
		return '<span class="breadcrumb"><a href="/tag/' . $tag . '">' . $term['name'] . '</a></span>';
	}
	
	/**
	 * @return string
	 */
	function author_crumb() {
		// builds crumb for an author page
		
		global $author;
		$author_obj = get_user_by( 'id', $author );
		
		return '<span class="breadcrumb">Author: <a href="/author/' . $author_obj->user_login . '">' . $author_obj->user_nicename . '</a></span>';
	}
}