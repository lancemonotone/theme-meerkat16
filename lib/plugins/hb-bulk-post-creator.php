<?php
/*
Plugin Name: Bulk Post Creator
Plugin URI: http://howdyblog.com/plugins/bulk-post-creator/
Description: This plugin takes a simple list of titles and quickly turns them into published posts or pages.
Version: 999 (1.1)
Author: Sarah @ Howdy Blog
Author URI: http://howdyblog.com
*/

/*  Copyright 2010  Abundant Media, Inc.  (email : sarah@howdyblog.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Add admin menu
// Create admin form (including nonces)
// Parse results of admin form
// Create a new post for each title

class HBBulkPostCreator {
	
	static $upgrade_message = 'Please upgrade to the current version of WordPress. Not only is it necessary for this plugin to work properly, but it will also help prevent hackers from getting into your blog through old security holes.';
	static $nonce_name = 'hb-bulk-post-creator-create-bulk-posts';
	
	
	static public function bulk_post_add_form() {
		echo '<div class="wrap">' . PHP_EOL;
		echo '<h2>Bulk Post Creator</h2>' . PHP_EOL;
		if ( ! empty ( $_POST['bulk_post_titles'] ) ) {
			self::create_posts( $_POST['bulk_post_titles'] );
		} else {
			self::display_form();
		}
		
		echo '</div>' . PHP_EOL;
		
	}
	
	private function display_form() {
		echo '<form method="post" action="">' . PHP_EOL;
		if ( function_exists( 'wp_nonce_field' ) ) {
			wp_nonce_field( 'hb-bulk-post-creator-create-bulk-posts' );
			//wp_nonce_field(self::$nonce_name);
		} else {
			die ( '<p>' . self::$upgrade_message . '</p>' );
		}
		
		echo '<table class="form-table">
			<tr valign="top">
				<th scope="row">Enter your lists of titles here, one on each line</th>
				<td><textarea name="bulk_post_titles" cols="60" rows="20"></textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row">Categories...</th>
				<td>';
		wp_dropdown_categories( array(
			'taxonomy'         => array( 'category' ),
			'name'             => 'category',
			'order'            => 'asc',
			'selected'         => $category->parent,
			'show_option_none' => __( 'Section' ),
			'hide_empty'       => 0
		) );
		$taxes = get_taxonomies( array(), 'objects' );
		foreach ( $taxes as $tax ) {
			if ( ! $tax->_builtin ) {
				wp_dropdown_categories( array(
					'order'            => 'asc',
					'hide_empty'       => 0,
					'taxonomy'         => array( $tax->name ),
					'name'             => $tax->name,
					'show_option_none' => __( $tax->label )
				) );
			}
		}
		echo '		</td>
			</tr>
			<tr valign="top">
				<th scope="row">Create...</th>
				<td>
					<select name="bulk_post_type">';
		$types = get_post_types( array(), 'object' );
		foreach ( $types as $type => $typedata ) {
			if ( $typedata->public ) {
				echo '<option value="' . $type . '">' . $typedata->label . '</option>' . PHP_EOL;
			}
		}
		echo '		</select>
				</td>
			</tr>
			</table>' . PHP_EOL;
		
		echo '<input type="hidden" name="action" value="update" />' . PHP_EOL;
		echo '<p class="submit">
			<input type="submit" class="button-primary" value="' . __( 'Create Now' ) . '" />
			</p>' . PHP_EOL;
	}
	
	private function create_posts( $titles = null ) {
		check_admin_referer( 'hb-bulk-post-creator-create-bulk-posts' );
		//check_admin_referer(self::$nonce_name);
		if ( ! empty( $titles ) ) :
			$titles = explode( PHP_EOL, $titles );
			$unixtime = time() - 60 * 60 * 24;

			if ( $_POST['category'] > 0 ) {
				$categories[] = $_POST['category'];
			}
			$taxes = get_taxonomies( array(), 'objects' );
			foreach ( $taxes as $tax ) {
				if ( ! $tax->_builtin && $_POST[ $tax->name ] > 0 ) {
					$custom_cats[ $tax->name ] = $_POST[ $tax->name ];
				}
			}
			echo '<ul>' . PHP_EOL;
			foreach ( $titles as $title ) {
				$title = trim( $title );
				$unixtime -= 60 * 5;
				$postdate = date( "Y-m-d H:i:s", $unixtime );
				if ( 'photo' == $_POST['bulk_post_type'] ) {
					if ( $new_post_id = self::create_post( $title, 'photo', $categories, $custom_cats, $postdate ) ) {
						echo '<li>Created <a href="post.php?action=edit&post=' . $new_post_id . '">' . $title . '</a>' . PHP_EOL;
					}
				} else if ( 'post' == $_POST['bulk_post_type'] ) {
					if ( $new_post_id = self::create_post( $title, 'post', $categories, $custom_cats, $postdate ) ) {
						echo '<li>Created <a href="post.php?action=edit&post=' . $new_post_id . '">' . $title . '</a>' . PHP_EOL;
					}
				} else {
					if ( $new_post_id = self::create_post( $title, 'page' ) ) {
						echo '<li>Created <a href="page.php?action=edit&post=' . $new_post_id . '">' . $title . '</a>' . PHP_EOL;
					}
				}
			}
			echo '<ul>' . PHP_EOL;
			if ( 'post' == $_POST['bulk_post_type'] ) {
				echo '<p>All done! <a href="edit.php">See all posts &raquo;</a></p>' . PHP_EOL;
			} else {
				echo '<p>All done! <a href="edit-pages.php">See all posts &raquo;</a></p>' . PHP_EOL;
			}

		endif;
	}
	
	private function create_post( $title = null, $type = 'post', $categories = array(), $custom_cats = array(), $postdate ) {
		if ( ! empty( $title ) ) {
			global $wpdb;
			
			$new_post = array(
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_title'    => $title,
				'post_type'     => 'post',
				'post_category' => $categories,
				'post_date'     => $postdate,
				'tax_input'     => $custom_cats
			);
			/*
			echo '<pre>c ' . print_r($postdate, true) . '</pre>';
			echo '<pre>cc ' . print_r($custom_cats, true) . '</pre>';
			return;
			 */
			if ( 'post' != $type ) {
				$new_post['post_type'] = $type;
			}
			
			if ( $new_post_id = wp_insert_post( $new_post ) ) {
				return $new_post_id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	static public function set_plugin_meta( $links, $file ) {
		$plugin = plugin_basename( __FILE__ );

		// create link
		if ( $file == $plugin ) {
			return array_merge( $links, array( sprintf( '<a href="edit.php?page=%s">%s</a>', $plugin, __( 'Settings' ) ) ) );
			$settings_link = '<a href="options-general.php?page=custom-field-template.php">' . __( 'Settings' ) . '</a>';
			$links = array_merge( array( $settings_link ), $links );
		}

		return $links;
	}
	
	static public function add_plugin_menu() {
		add_posts_page( 'Bulk Post Creator', 'Create Bulk Posts', 'edit_posts', 'bulk-post-creator/hb-bulk-post-creator.php', array(
			'HBBulkPostCreator',
			'bulk_post_add_form'
		) );
	}
}

$hb_bulk_post_creator = new HBBulkPostCreator();

add_filter( 'plugin_row_meta', array( 'HBBulkPostCreator', 'set_plugin_meta' ), 10, 2 );
add_action( 'admin_menu', array( 'HBBulkPostCreator', 'add_plugin_menu' ) );
