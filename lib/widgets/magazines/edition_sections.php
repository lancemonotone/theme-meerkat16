<?php
#
# Links to sections of currently viewed edition
#

class MeerkatEditionSectionsWidget extends MeerkatWidget {

    // register widget with wordpress
    public function __construct() {
        $desc = 'Links to sections of the edition of the magazine currently being viewed. Use for Alumni News.';
        parent::__construct('meerkat_edition_sections', // Base ID
			    MK_WIDGET_PREFIX . 'Edition Sections', // Name
			    array( 'description' => $desc ) // Args
			    );	
    }
    
    /**
     * Displays the Widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ){
		extract( $args );
		global $wp_query, $meermag;
		
		// returns only issues with content
		$issues = $meermag->get_editions();

        // Get context issue.  
        $context_issue = $meermag->get_context_issue();
        $context_year = $meermag->get_context_year();
        $context_edition = $context_year . ',' . $context_issue['slug'];
        
        $prev_issue = $meermag->get_adjacent_value( $context_edition, $issues ); 
        $next_issue = $meermag->get_adjacent_value( $context_edition, $issues, false );
        
        $prev_issue = $prev_issue ? explode( ',', $prev_issue ) : null; // 0 => year, 1 => issue
        $next_issue = $next_issue ? explode( ',', $next_issue ) : null; // 0 => year, 1 => issue
        
        $current_issue = $meermag->get_edition_array();
        $current_term = $wp_query->query['category_name'];

		echo $before_widget; 

		if ( get_field( 'meermag_sections_rpt', 'option' )){ 

			echo '<div class="widget_arrow_nav">';
		    echo $before_title;

			// HEADER
			$header_links = array ( 'prev' => array( 'disabled' => $prev_issue ? false : true,
													 'href' => '/' . $prev_issue[0] . '/' . $prev_issue[1] . '/',
													 'title' => $prev_issue[1] ),
									'curr' => array( 'disabled' => false,
													 'href' => '/' . $context_year . '/' . $context_issue['slug'] . '/',
													 'title' => $current_issue['issue']['name'] . ' ' . $context_year ),
									'next' => array( 'disabled' => $next_issue ? false : true,
													 'href' => '/' . $next_issue[0] . '/' . $next_issue[1] . '/',
													 'title' => $next_issue[1] )
									);

			echo $this->build_link_row( $header_links );
			echo $after_title;
			
			// SECTIONS
			while( the_repeater_field( 'meermag_sections_rpt', 'option' ) ){
	   		    $section = get_sub_field( 'mermag_sections' );
                if (is_array($section)) {
                    $section = get_term($section[0], 'category');
                }

				// generate titles
				$title_prev_pref = $prev_issue[1] . ' ' . $prev_issue[0] . ' - ';
				$title_next_pref = $next_issue[1] . ' ' . $next_issue[0] . ' - ';
				$title = $section->name;

				// generate links
				$href_pref_pref = '/' .	$prev_issue[0] . '/' . $prev_issue[1]; 
				$href_curr_pref = '/' .	$context_year . '/' . $context_issue['slug'];
				$href_next_pref = '/' .	$next_issue[0] . '/' . $next_issue[1]; 
				$href =  '/' . $section->slug;

				// next/prev disabled? 
				$prev_disabled = $next_disabled = true;
				if ( $prev_issue && $meermag->issue_has_content( $prev_issue, $section->slug )){
				    $prev_disabled = false;
				}
				if ( $next_issue && $meermag->issue_has_content( $next_issue, $section->slug )){
				    $next_disabled = false;
				}

				// is this the current section?
				$selected_section = false;
				$uri = $_SERVER['REQUEST_URI'];
				$uri_bits = explode( '/', $uri );
				if ( $uri_bits[1] == $context_year &&
				     $uri_bits[2] == $context_issue['slug'] &&
					 $uri_bits[3] == $section->slug ){
				    $selected_section = true;
				}

				$row_links = array ( 'prev' => array( 'disabled' => $prev_disabled,
													  'href' => $href_pref_pref . $href,
													  'title' => $title_prev_pref . $title  ),
									 'curr' => array( 'disabled' => false,
													  'href' => $href_curr_pref . $href,
													  'title' => $title,
													  'selected' => $selected_section ),
									 'next' => array( 'disabled' => $next_disabled,
													  'href' => $href_next_pref . $href,
													  'title' => $title_next_pref . $title )
									);
				echo $this->build_link_row( $row_links );
			}
		}
		echo '</div>';
		echo $after_widget;
   }

   function build_link_row ( $data ){
	   $html = '<div class="widget_arrow_row">';
       foreach ( $data as $position => $atts ){
	       $html .= '<div class="';
		   if ( $position == 'curr' ){
			   if ( $atts['selected'] ){
		       	  // highlight current section
				  $html .= 'highlight ';
			   }
		   }
		   else {
			   $html .= 'direction ';
		   }
		   if ( $atts['disabled'] ){
			   $html .= 'disabled ';
		   }
		   $html .= $position . '">' . $this->build_link( $position, $atts ) . '</div>';
       }
       return $html . '</div>'; 
    }

	function build_link ( $position, $atts ){   
        $html = '<a href="' . $atts['href'] . '"';
		if ( $position != 'curr' ){
   	  	   $html .= ' title="' . ucfirst( $atts['title'] ) . '"';
		}
		$html .= '>';
		if ( $position == 'curr' ){
			$html .= ucfirst( $atts['title'] );
		}else{
			$html .= '<span class="menu-arrow"></span>';
		}
        return $html . '</a>';
    }  
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "MeerkatEditionSectionsWidget" );' ) );

?>
