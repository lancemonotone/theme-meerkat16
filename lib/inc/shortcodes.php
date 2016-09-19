<?php

/*
* Handles misc shortcodes for the theme. See gallery.php for [gallery] shortcode.
*
* [wms_javascript] 		  used by: ?? 
* 				   		  legal attributes: script (must be a key in $js array declared in class.js.php)
*                         example: [wms_javascript script="tablesorter"]
* [code]		   		  used by: acad, and potentially all web documentation sites
* 				   		  legal attributes: none
*                         example: [code]<div>some <b>html</b> goes here</div>[/code]
* [faculty_experts] 	  used by: communications only (one-off)
* 						  legal attributes: any $arg understood by the wordpress tagcloud widget
*                         example: [faculty_experts]
*                         all attributes are optional, as reasonable defaults are set up in the shortcode handler
* [mk_calendar]			  used by: 62center
* 				  		  legal attributes: month (m), year (yyyy), post_type, cat (category slug)
*		                  examples: [mk_calendar] [mk_calendar month="6" year="2013" cat="features"]   
*                         post_type OR cat is required. if month/year is not stated, the current month/year is used.
* [mk_cat_filters]		  used by: 62center, music on their calendar pages
* 						  legal atributes: cats (comma separated list of category slugs)
*		                  examples: [mk_cat_filters cats="2007-season, 2008-season"] 
* [mk_cat_expando]		  pulls in posts from a given category, and puts each in an expando
*                         legal atributes: cat is a category slug, orderby is one of 'author', 'title' (default), 'date', or 'modified'
*                         and order, which is either ASC (default) or DESC
*		                  examples: [mk_cat_expando cat="news" orderby="date" order="DESC"] 
* [details] / [expando]   used by: many sites for FAQ-like content
* 						  legal atributes: title (string) content (string)
*		                  examples: [details title="My Title"]This is my content.[/details]
* [reuse_post]	          used by: wordpress documentation site
* 						  legal atributes: id (post id)
*		                  examples: [reuse_post id="45"]
* [tabs] & [tab]		  used by: wordpress.williams.edu
*                         legal attributes for tabs: button_classes (string), box_classes (string), content (string)
*                         		place multiple [tab] shortcodes inside the [tabs] shortcode
*                         legal attributes for tab: title (string) content (string). 
*                         examples: [tabs][tab title="Tab A"]Here is the stuff in tab A[/tab]
*                                   [tab title="Tab B"]Here is the stuff in tab B[/tab][/tabs]
* [rebelmouse] 			  used by www social page. embeds a script tag.
*
* [quad]				  used in the shortcode builder to make single unit for the 4-block layout. 
*						  [quad image="http://.../img/blah.jpg" url="http://foo.com/" overlay="title thingy"]blah blah[/quad]
*					      all attributes (image, url, overlay) are required. text between the open/end shortcode tags, if provided, 
*						  will be placed below the image.
*
*/

class Meerkat_Shortcodes {

    function init(){
        // don't replace quotes n stuff for these shortcodes
        add_filter( 'no_texturize_shortcodes', array( &$this, 'shortcodes_to_exempt_from_wptexturize' ) );

        // set up shortocode handlers
        add_shortcode( 'wms_javascript', array( &$this, 'wms_javascript' ) );
        add_shortcode( 'code', array( &$this, 'code' ) );
        add_shortcode( 'faculty_experts', array( &$this, 'faculty_experts' ) );
        add_shortcode( 'mk_calendar', array( &$this, 'mk_calendar' ) );
        add_shortcode( 'mk_cat_filters', array( &$this, 'mk_cat_filters' ) );
        add_shortcode( 'details', array( &$this, 'details' ) );
        add_shortcode( 'expando', array( &$this, 'details' ) );
        add_shortcode( 'mk_cat_expando', array( &$this, 'mk_cat_expando' ) );
        add_shortcode( 'tabs', array( &$this, 'tabs' ) );
        add_shortcode( 'reuse_post', array( &$this, 'reuse_post' ) );
        add_shortcode( 'rebelmouse', array( &$this, 'rebelmouse' ) );
        add_shortcode( 'quad', array( &$this, 'quad' ) );
    }

    function shortcodes_to_exempt_from_wptexturize( $shortcodes ){
        $meerkat_shortcodes = array(
            'wms_javascript',
            'code',
            'faculty_experts',
            'mk_calendar',
            'mk_cat_filters',
            'details',
            'expando',
            'mk_cat_expando',
            'reuse_post',
            'tabs',
            'rebelmouse',
            'quad'
        );
        foreach( $meerkat_shortcodes as $sc ){
            array_push( $shortcodes, $sc );
        }

        return $shortcodes;
    }

    function quad( $attr, $content ){
        // build a single pic/title/blurb unit for the fourblock layout
        $atts = shortcode_atts( array(
                            'image'   => '',  // absolute or relative URL of the img src
                            'url'     => '',    // absolute or relative URL of the clickthrough destination
                            'overlay' => '' // text to place on top of the image - keep it short
                        ), $attr );

		/* build a foolproof version of the caption shortcode, one which won't break if there's no actual caption. captions look like this:
		[caption id="attachment_982" align="alignnone" width="375"]<a href="http://www.williams.edu/academics/" target="_blank"><img class="size-full wp-image-982" title="ACADEMICS" alt="" src="http://wordpress.williams.edu/files/2014/02/why_Academics.jpg" width="375" height="193" /></a> Tutorials. Winter Study. Summer Science. These are some key aspects of a Williams education. Learn more about them and all of our academic opportunities, as well as about our professors, who care as deeply about students as they do their research.[/caption]*/
		
		if( ! $atts[ 'image' ] || ! $atts[ 'url' ] || ! $atts[ 'overlay' ] ){
            return false;
        }
		
		if( ! $content ){
            // caption shortcode won't work if there's no actual caption. let's fake it.
            $content = '&nbsp;';
        }
		$html = '[caption width="375"]<a href="' . $atts[ 'url' ] . '"><img title="' . $atts[ 'overlay' ] . '" src="' . $atts[ 'image' ] . '"></a>' . $content . '[/caption]';
		
		return do_shortcode( $html );
	}

    function tabs( $atts, $content = null ){
        // build clickable tabs for use on pages/posts
        /*
         * parameters
         *   open - optional, the number of the tab to open by default; ordinal starting with 1
         */
        extract( shortcode_atts( array(
                                     'is_expando' => false,
                                    'open' => 1
                                 ), $atts ) );
        /* sample HTML output:
           <ul class="accordion-tabs [is-expando]">
            <li class="accordion-tab is-active">
                <a href="javascript:void(0)" class="tab-link" data-target="students">Students</a>
                <div class="tab-content is-open">
                    <p></p>
                </div>
            </li>
            <li class="accordion-tab">
                <a href="javascript:void(0)" class="tab-link" data-target="faculty">Faculty</a>
                <div class="tab-content is-open" style="display: block;">
                    <p></p>
                </div>
            </li>
        </ul>
        */

        $is_expando_class = $is_expando == 'true' ? 'is-expando' : '';

        $tab_html = array();

        $tab_chunks = explode( '[/tab]', $content );
        // iterate over each [tab] sc inside of the larger [tabs] one
        $current_tab = 1;
        foreach( $tab_chunks as $tab ){
            // extract content & title
            $matches = array();
            if( preg_match( '|\[\s*tab\s+title\s*=\s*"(.+?)"\s*\]([\s\S]+)|', $tab, $matches ) ){
                $title        = $matches[ 1 ];
                $this_content = $matches[ 2 ];
                if( mb_detect_encoding( $title, "ASCII" ) ){
                    $slug = sanitize_title_with_dashes( $title );
                } else {
                    // exception for admissions viewbook- kanji chars hork up sanitize_title()
                    $slug = $title;
                }
                $li_class = ($current_tab == $open) ? ' is-active' : '';
                $div_class = ($current_tab == $open) ? ' is-open' : '';
                $tab_content = do_shortcode( $this_content );
                $tab_html[]  = <<<EOL
				<li id="{$slug}" class="accordion-tab{$li_class}">
					<a href="javascript:void(0)" class="tab-link">{$title}</a>
					<div class="tab-content{$div_class}">{$tab_content}</div>
				</li>
EOL;
                $current_tab++;
            }
        }
        $tab_html = implode( "\n", $tab_html );

        $output = <<<EOL
		<ul class="accordion-tabs {$is_expando_class}">$tab_html</ul>
EOL;

        return $output;
    }

    function details( $atts, $content = 'CONTENT NEEDED' ){
        extract( shortcode_atts( array(
                                     'title' => 'TITLE NEEDED'
                                 ), $atts ) );

        $tab_html = array();

        $slug        = sanitize_title_with_dashes( $title );
        $tab_content = do_shortcode( $content );
        $tab_html[]  = <<<EOL
		<li id="{$slug}" class="accordion-tab">
			<a href="javascript:void(0)" class="tab-link">{$title}</a>
			<div class="tab-content">{$tab_content}</div>
		</li>
EOL;

        $tab_html = implode( "\n", $tab_html );

        return <<< EOL
		<ul class="accordion-tabs is-expando">$tab_html</ul>
EOL;
    }

    function wms_javascript( $atts ){
        // allows a per page js lib load via shortcode: [wms_javascript script="tablesorter"]
        global $js;
        extract( shortcode_atts( array( 'script' => '' ), $atts ) );
        if( $script && $js[ $script ] ){
            foreach( $js[ $script ][ 'deps' ] as $dep ){
                $dep = str_replace( get_template() . '-', '', $dep );
                if( $js[ $dep ] && ! $js[ $dep ][ 'load' ] ){
                    $js[ $dep ][ 'load' ] = true;
                }
            }
            $js[ $script ][ 'load' ] = true;
        }
    }

    function mk_cat_expando( $atts ){
        extract( shortcode_atts( array( 'cat' => '', 'orderby' => 'title', 'order' => 'ASC' ), $atts ) );

        // validation
        if( ! $cat ){
            return '<code>no cat parameter provided</code>';
        }
        $legal_orderby = array( 'author', 'title', 'date', 'modified' );
        if( ! in_array( $orderby, $legal_orderby ) ){
            return '<code>invalid orderby parameter</code>';
        }
        $legal_order = array( 'ASC', 'DESC' );
        if( ! in_array( $order, $legal_order ) ){
            return '<code>invalid order parameter</code>';
        }
        $cat_obj = get_term_by( 'slug', $cat, 'category' );
        if( ! $cat_obj ){
            return '<code>invalid cat parameter - must be a valid category slug</code>';
        }

        // get posts in cat
        $args      = array(
            'posts_per_page' => - 1,
            'category'       => $cat_obj->term_id,
            'orderby'        => $orderby,
            'order'          => $order
        );
        $cat_posts = get_posts( $args );

        // build expandos
        $html = '';
        foreach( $cat_posts as $post_obj ){
            $content     = apply_filters( 'the_content', $post_obj->post_content );
            $edit_button = $this->build_edit_button( $post_obj->ID, 'Edit Post' );
            $unit_html   = '<div class="wms-details cf">';
            $unit_html .= '<h3 class="wms-summary cf" id="' . sanitize_title_with_dashes( $post_obj->post_title ) . '">';
            $unit_html .= '<div class="summary-arrow"></div>' . $post_obj->post_title . '</h3>';
            $unit_html .= '<div class="summary-detail cf">' . $content . $edit_button . '</div>';
            $unit_html .= '</div><!-- .wms-details -->';

            $html .= '<div class="expando_cat_post">' . $unit_html . '</div>';
        }

        return $html;
    }

    function build_edit_button( $post_id, $label = 'Edit' ){
        $html     = '';
        $edit_url = get_edit_post_link( $post_id );
        if( $edit_url ){
            $html = '<a class="edit-me" href="' . $edit_url . '">' . $label . '</a>';
        }

        return $html;
    }

    function rebelmouse( $atts ){
        // embeds rebelmouse script tag in body of post
        $code = '<script type="text/javascript" class="rebelmouse-embed-script" src="https://www.rebelmouse.com/static/js-build/embed/embed.js?site=socialwilliams&height=1500&flexible=1"></script>';

        return $code;
    }

    function reuse_post( $atts ){
        // embed the content of a page/post into this page/post
        extract( shortcode_atts( array( 'id' => '', ), $atts ) );

        // make sure we have some sort of url to work with
        if( ! $id || ! is_numeric( $id ) ){
            return '<code>Invalid ID</code>';
        }

        $post    = get_post( $id );
        $content = $post->post_content;

        // interpret shortcodes, do oembed, formatting, etc.
        $content = apply_filters( 'the_content', $content );

        $edit_button = $this->build_edit_button( $id, 'Edit Snippet' );

        return '<div class="reused-post cf">' . $content . $edit_button . '</div>';;
    }

    function mk_calendar( $atts ){
        // create a grid calendar based on posts in a category and/or of a certain post type.
        $defaults       = array(
            'month'     => date( 'n' ),      // M
            'year'      => date( 'Y' ),          // YYYY
            'post_type' => 'post',
            'cat'       => '',               // this will be a slug, not an ID
            'acf'       => ''                 // 'true' or '1': does this calendar use ACF?
        );
        $shortcode_atts = shortcode_atts( $defaults, $atts );
        extract( $shortcode_atts, EXTR_SKIP );

        // month/year from query string trumps all (from next/prev nav)
        if( is_numeric( $_GET[ 'cm' ] ) ){
            $month = $_GET[ 'cm' ];
        }
        if( is_numeric( $_GET[ 'cy' ] ) ){
            $year = $_GET[ 'cy' ];
        }
        $acf = ( $acf === 'true' ) || ( $acf === '1' ) ? true : false;

        global $cal;
        if( ! $cal ){
            $cal = new MeerkatPostCalendar( $shortcode_atts );
        }

        return $cal->build_cal( $month, $year, $acf );
    }

    function mk_cat_filters( $atts ){
        // create filter buttons based on post (or other post_type) categories
        extract( shortcode_atts( array( 'cats' => '' ), $atts ) );

        if( ! $cats ){
            return;
        }

        $slugs       = explode( ',', $cats );
        $filter_cats = array();
        foreach( $slugs as $slug ){
            $slug          = trim( $slug );
            $filter_cats[] = get_category_by_slug( $slug );
        }

        $html = $this->build_filter_html( $filter_cats );

        return $html;
    }

    function build_filter_html( $filter_cats ){
        $html = '<div class="category-filters button-group rounded-buttons">';
        $html .= '<a class="show-all category-filter selected-filter">Show All</a>';
        foreach( $filter_cats as $fcat => $data ){
            $url = '/category/' . $data->slug . '/';
            $html .= '<a class="category-filter category-' . $data->slug . '" data-slug="' . $data->slug . '">' . $data->name . '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    function code( $atts, $content ){
        // allows you to display html code inside a page: [code]<div>some <b>html</b> goes here</div>[/code]
        $content = str_replace( '<p>', '', $content );
        $content = str_replace( '</p>', '', $content );
        $content = str_replace( '<br />', '', $content );
        $html    = '<code>';
        $bits    = explode( "\n", $content );
        foreach( $bits as $line ){
            $encoded = htmlspecialchars( $line );
            if( $encoded != '' ){
                $html .= $encoded . '<br>';
            }
        }
        $html .= '</code>';

        return $html;
    }

    function faculty_experts( $atts ){
        // create a tag cloud tuned for the faculty experts on communications sites
        // specify defaults for shortcode attributes
        $defaults       = array(
            'smallest'                  => 14,
            'largest'                   => 30,
            'unit'                      => 'px',
            'number'                    => 9999,
            'format'                    => 'flat',
            'separator'                 => "\n",
            'orderby'                   => 'name',
            'order'                     => 'ASC',
            'exclude'                   => null,
            'include'                   => null,
            'topic_count_text_callback' => default_topic_count_text,
            'link'                      => 'view',
            'taxonomy'                  => 'post_tag',
            'echo'                      => false
        );
        $shortcode_atts = shortcode_atts( $defaults, $atts );
        $tag_cloud      = wp_tag_cloud( $shortcode_atts );

        return '<div id="faculty-experts-tagcloud">' . $tag_cloud . '</div>';
    }
}

global $meerkat_shortcodes;
if( ! $meerkat_shortcodes ){
    $meerkat_shortcodes = new Meerkat_Shortcodes;
}
add_action( 'init', array( $meerkat_shortcodes, 'init' ) );

?>
