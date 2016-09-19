<?php
#
# WILLIAMS LINKS WIDGET
#
# choose a category of links to display, choose sort order
#

class MeerkatLinksWidget extends MeerkatWidget {
	
	// register widget with wordpress
	public function __construct() {
		$desc = 'Display your links';
		parent::__construct( 'meerkat_links', // Base ID
			MK_WIDGET_PREFIX . 'Links', // Name
			array( 'description' => $desc ) // Args
		);
		
		// editable widget options & associated data
		
		// list of link categories
		$link_cats = get_terms( 'link_category', array( 'hide_empty' => 0 ) );
		$link_cat_options = array( '0' => 'All' );
		foreach ( $link_cats as $link_cat ) {
			$link_cat_options[ $link_cat->term_id ] = $link_cat->name;
		}
		
		$order_options = array(
			'id'      => 'ID',
			'url'     => 'URL',
			'name'    => 'Link name',
			'updated' => 'Last updated',
			'rand'    => 'Random',
			'length'  => 'Length of link name',
			'rating'  => 'Rating'
		);
		
		$this->fields = array(
			'title'    => array(
				'default' => 'Links',
				'type'    => 'text',
				'label'   => 'Title'
			),
			'link_cat' => array(
				'type'    => 'select',
				'options' => $link_cat_options,
				'label'   => 'Link Category'
			),
			'orderby'  => array(
				'default' => 'rating',
				'type'    => 'select',
				'options' => $order_options,
				'label'   => 'Order by'
			),
		);
	}
	
	// Displays the Widget
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		parent::display_title( $args, $instance );
		echo $args['before_insides'];
		
		?>
		<ul class="meerkat-links">
			
			<?php
			// get all links that belong to the stated link category
			$term = get_term_by( 'id', $instance['link_cat'], 'link_category' );
			$link_cat_name = $term->name;
			$book_args = array( 'orderby' => $instance['orderby'] );
			if ( $link_cat_name ) {
				$book_args['category_name'] = $link_cat_name;
			}
			$bookmarks = get_bookmarks( $book_args );
			foreach ( $bookmarks as $bookmark ) {
				echo '<li><a title="' . $bookmark->link_description . '" href="' . $bookmark->link_url . '">' . $bookmark->link_name;
				echo '</a></li>';
			}
			?>
		</ul>
		<?php
		
		echo $args['after_insides'];
		echo $args['after_widget'];
	}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatLinksWidget" );' ) );