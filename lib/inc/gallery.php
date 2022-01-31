<?php
/* Handles [gallery] shortcodes, extends native [gallery] functionality by adding fancybox, slideshow, and filmstrip support. 
   Also enables fancybox for images formatted like
   <a class="fancybox" href="link to full sized pic" rel="something consistent"><img ... alt="this is required" ... /></a>
   version: 3.0
*/

class Meerkat_Gallery {

    function init() {
        add_shortcode('gallery', array(&$this, 'handle_shortcode'));
        add_shortcode('wms_gallery', array(&$this, 'handle_shortcode'));

        add_action('print_media_templates', array(&$this, 'add_gallery_options'));
        //add_action( 'wp_enqueue_media', array( $this, 'enqueue_gallery_options' ) );
        //$this->enable_fancybox_for_class();
    }

    /**
     * https://wordpress.stackexchange.com/a/90443/20344
     */
    function add_gallery_options() {
        // define your backbone template;
        // the "tmpl-" prefix is required,
        // and your input field should have a data-setting attribute
        // matching the shortcode name
        ?>
        <script type="text/html" id="tmpl-my-custom-gallery-setting">
            <label class="setting">
                <span><?php _e('Show Captions'); ?></span>
                <select data-setting="caption">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </label>
        </script>

        <script>

          jQuery( document ).ready( function () {

            // add your shortcode attribute and its default value to the
            // gallery settings list; $.extend should work as well...
            _.extend( wp.media.gallery.defaults, {
              caption: 'no'
            } );

            // merge default gallery settings template with yours
            wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend( {
              template: function ( view ) {
                return wp.media.template( 'gallery-settings' )( view )
                  + wp.media.template( 'my-custom-gallery-setting' )( view );
              }
            } );

          } );

        </script>
        <?php
    }


    /**
     * @deprecated No more fancybox. No more global $js
     */
    function enable_fancybox_for_class() {
        global $js;
        $args                   = array('padding'              => 2,
                                        'prevEffect'           => 'fade',
                                        'nextEffect'           => 'fade',
                                        'helpers'              => "{ title : { type : 'inside' }}",
                                        'beforeLoad'           => "function() { this.title = $(this.element).find('img').attr('alt') + '&nbsp;'; }",
                                        'fb_show_close_button' => true,
        );
        $selector               = 'a.fancybox';
        $function               = 'fancybox';
        $js['fancybox']['code'] .= $this->attach_js($selector, $function, $args);
    }

    /**
     * @param $gallery_id
     *
     * @deprecated No more fancybox. No more global $js
     *
     */
    function enable_fancybox($gallery_id) {
        global $js, $post;

        // hack for hi res photo galleries
        if ($post->post_title == 'Press Images') return;

        $args                   = array('padding'              => 2,
                                        'prevEffect'           => 'fade',
                                        'nextEffect'           => 'fade',
                                        'helpers'              => "{ title : { type : 'inside' }}",
                                        'beforeLoad'           => "function() { this.title = $(this.element).attr('caption') ? $(this.element).attr('caption') : '&nbsp;'; }",
                                        'fb_show_close_button' => true,
        );
        $selector               = '#' . $gallery_id . ' .gallery-icon a';
        $function               = 'fancybox';
        $js['fancybox']['code'] .= $this->attach_js($selector, $function, $args);
    }

    /**
     * @param $gallery_id
     * @param $caption_id
     * @param $speed
     * @param $caption
     *
     * @deprecated No more global $js
     *
     */
    function enable_cycle($gallery_id, $caption_id, $speed, $caption) {
        // attach cycle functionality to a specific gallery -  see http://jquery.malsup.com/cycle2/api/ for documentation on cycle2

        global $js;
        $js['cycle']['load'] = true;
        $args                = array('fx'      => 'fade',
                                     'timeout' => $speed * 1000
        );
        if ($caption) {
            $args['caption']         = '#' . $caption_id;
            $args['captionTemplate'] = '{{cycleTitle}}';
        }

        $selector            = '#' . $gallery_id;
        $function            = 'cycle';
        $js['cycle']['code'] .= $this->attach_js($selector, $function, $args);
    }

    function attach_js($selector, $function, $args) {
        // builds js code to attach a function (with properly formatted args) to a selector
        $js = "\n" . '$("' . $selector . '").' . $function . "({\n";
        foreach ($args as $key => $val) {
            $js .= "$key : ";
            if ((substr($val, 0, 1) == '{' && substr($val, 1, 1) != '{') ||
                substr($val, 0, 8) == 'function' ||
                is_int($val)) {
                // dont quote sub-arrays or functions or integers
                $js .= $val . ",\n";
            } else {
                $js .= "'$val',\n";
            }
        }
        $js .= "})\n";

        return $js;
    }

    function handle_shortcode($atts, $content = null) {
        // supports all gallery attributes except the tag wrappers (itemtag, icontag, captiontag)
        // also supports custom attributes built for Williams College

        // specify defaults for shortcode attributes
        $defaults = array('columns' => '3',
                          'id'      => get_the_id(),     // use attachments from another post
                          'ids'     => '',               // attachment ids, comma sep
                          'size'    => 'thumbnail',      // thumbnail (or thumb), medium, large, full
                          'orderby' => 'menu_order',     // menu_order, title, post_date, rand (or random)
                          'order'   => 'ASC',            // ASC or DESC
                          'link'    => 'file',           // permalink, file, none, parent
                          'include' => '',               // attachment ids, comma sep
                          'exclude' => '',                 // attachment ids, comma sep

            // beyond the [gallery] - williams custom attributes
                          'caption' => 'no',             // shows caption below pic
                          'tooltip' => 'no',             // shows caption in tooltip on mouseover [***IN DEVELOPMENT]
                          'type'    => 'grid',           // grid, slideshow, or filmstrip
                          'speed'   => 5,                // slideshow speed in seconds
                          'align'   => 'center',         // alignment for slideshow
                          'pause'   => 'yes',             // add a pause button for the slideshow
                          'classes' => ''                 // a space separated list of classes to assign to the gallery
        );

        $shortcode_atts = shortcode_atts($defaults, $atts);
        extract($shortcode_atts, EXTR_SKIP);

        //-------- VALIDATE --------//

        // specify allowed values for shortcode attributes
        $allowed_columns_min = 1;
        $allowed_columns_max = 9;

        $allowed_orderby = array('menu_order', 'title', 'post_date', 'rand', 'ID');
        $allowed_order   = array('asc', 'desc');
        $allowed_link    = array('permalink', 'file', 'parent', 'none');
        $allowed_type    = array('grid', 'slideshow', 'filmstrip');
        $allowed_align   = array('left', 'right', 'center');
        $allowed_size    = array('thumbnail', 'medium', 'large', 'full');

        // convert string values to lowercase and trim
        $size = trim(strtolower($size));
        if ($size == 'thumb') $size == 'thumbnail';
        $orderby = trim($orderby);
        if ($orderby == 'random' || $order == 'rand' || $order == 'random') $orderby = 'rand';
        $order   = trim(strtolower($order));
        $link    = trim(strtolower($link));
        $caption = trim(strtolower($caption)) == "yes" ? true : false;
        $tooltip = trim(strtolower($tooltip)) == "yes" ? true : false;
        $pause   = trim(strtolower($pause)) == "yes" ? true : false;
        $classes = esc_attr(trim($classes));

        // type cast strings as necessary
        $id      = absint($id);
        $columns = absint($columns);
        $speed   = absint($speed);

        // test for allowed values
        $columns = max($allowed_columns_min, min($allowed_columns_max, $columns));
        if ( ! in_array($orderby, $allowed_orderby)) $orderby = $defaults['orderby'];
        if ( ! in_array($order, $allowed_order)) $order = $defaults['order'];
        if ( ! in_array($link, $allowed_link)) $link = $defaults['link'];
        if ( ! in_array($type, $allowed_type)) $type = $defaults['type'];
        if ( ! in_array($align, $allowed_align)) $align = $defaults['align'];
        if ( ! in_array($size, $allowed_size)) $size = $defaults['size'];

        // attachment IDs: includes, excludes, ids
        // ids (added in 5.3) is basically the same thing as include.
        // safely parse include/exclude/ids for use with get_posts().
        $att_ids = array('ids' => $ids, 'include' => $include, 'exclude' => $exclude);

        foreach ($att_ids as $att => $ids) {
            $ids = trim($ids);
            if (empty($ids)) {
                $att_ids[ $att ] = false;
            } else {
                $clean_ids       = explode(',', $ids);
                $clean_ids       = array_map('trim', $clean_ids);
                $clean_ids       = array_map('absint', $clean_ids);
                $att_ids[ $att ] = $clean_ids;
            }
        }

        // can only use 1 of the 3 attributes - prioritize ids > exclude > include
        $include_arr = $exclude_arr = array();
        if ($att_ids['ids']) {
            $include_arr = $att_ids['ids'];
        } else {
            if ($att_ids['exclude']) {
                $exclude_arr = $att_ids['exclude'];
            } else if ($att_ids['include']) {
                // treat include as ids
                $include_arr    = $att_ids['include'];
                $att_ids['ids'] = $att_ids['include'];
            }
        }

        //-------- GET PICS --------//

        $att_args = array('post_type'      => 'attachment',
                          'post_mime_type' => 'image',
                          'numberposts'    => -1,
                          'post_status'    => null,
                          'post_parent'    => $id,
                          'order'          => $order,
                          'orderby'        => $orderby,
                          'include'        => $include_arr,
                          'exclude'        => $exclude_arr
        );
        if (count($include_arr) > 0) {
            unset($att_args['post_parent']);
        }
        if (($att_ids['ids'] && $orderby != 'rand')) {
            // for newer [gallery] builder where you can drag & drop to order the items and it's reflected in the
            // ids attribute. this overrides any order/orderby parameters provided.
            $_attachments = get_posts($att_args);
            $attachments  = array();
            $lookup       = array();
            foreach ($_attachments as $index => $val) {
                $lookup[ $val->ID ] = $index;
            }
            foreach ($att_ids['ids'] as $index => $val) {
                $attachments[ $index ] = $_attachments[ $lookup[ $val ] ];
            }
        } else {
            // for [gallery] types that don't use the ids attribute or have selected random order.
            $attachments = get_posts($att_args);
        }

        if (empty($attachments)) return '';

        // load JS support
        $rand            = rand(1, 9999);
        $rand_gallery_id = 'meerkat-gallery-' . $rand;
        $rand_caption_id = 'gallery-caption-' . $rand;
        if ($type == 'grid') $this->enable_fancybox($rand_gallery_id);
        if ($type == 'slideshow') $this->enable_cycle($rand_gallery_id, $rand_caption_id, $speed, $caption);

        $i          = 0;
        $num_attach = count($attachments);

        //-------- BUILD GALLERY ITEMS --------//
        if ($orderby == 'rand') {
            shuffle($attachments);
        }

        // ---- LOOP ----//
        foreach ($attachments as $attachment) {
            $i++;

            // check for exclude;
            if ( ! empty($exclude_arr) && in_array($attachment->ID, $exclude_arr)) {
                continue;
            }

            // get image sources
            if ($type == 'filmstrip') $size = 'full';
            $gallery_images = wp_get_attachment_image_src($attachment->ID, $size);
            $full_images    = wp_get_attachment_image_src($attachment->ID, 'full');
            $thumb_images   = wp_get_attachment_image_src($attachment->ID, 'thumbnail');

            // all attributes for this loop item
            $item = array('title'   => strip_tags($attachment->post_title),
                          'alt'     => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
                          'caption' => strip_tags($attachment->post_excerpt),
                          'src'     => $gallery_images[0],
                          'width'   => $gallery_images[1],
                          'height'  => $gallery_images[2],
                          'href'    => $full_images[0],
            );
            if ($type == 'filmstrip') {
                $item['fullpic'] = $gallery_images[0];
                $item['width']   = $gallery_images[1];
                $item['src']     = $thumb_images[0];
            }

            // a temp holder for html bits
            $html = array();

            // HREF override
            if ($link == 'permalink') $item['href'] = get_attachment_link($attachment->ID);
            if ($link == 'parent') {
                $item['href'] = get_permalink($attachment->post_parent);
            }

            //---- LINK ----//
            if ($type !== 'grid' || ($type === 'grid' && $link !== 'none')) {
                $link_html = '<a ';
                if ($type == 'filmstrip') {
                    $link_html .= 'fullpic="' . $item['fullpic'] . '"';
                } else {
                    $link_html .= 'data-featherlight-gallery href="' . $item['href'] . '"';
                }
                if ($item['caption']) {
                    $link_html .= ' caption="' . htmlspecialchars($item['caption']) . '"';
                }
                $link_html .= '>';

                $html['link']     .= $link_html;
                $html['link_end'] .= '</a>';
            }

            //---- ALT ----//
            if ($type == 'slideshow' || $type == 'filmstrip') {
                $item['alt'] = $item['caption'];
            }

            //---- IMG ---- //
            $html['img'] = '<img alt="' . htmlspecialchars($item['alt']) . '" src="' . $item['src'] . '"';

            if ($type == 'filmstrip') {
                $html['img'] .= ' class="film-thumb" ';
            } else if ($type == 'slideshow') {
                $html['img'] .= ' data-cycle-title="' . $item['alt'] . '"';
            }

            $html['img'] .= '>';

            //---- CAPTION ----//
            if ($caption && $type != 'slideshow') {
                $adjusted_w = $item['width'];
                if (strpos($classes, 'ngg') !== false) {
                    $adjusted_w = $item['width'] + 10;
                }
                $html['caption'] = '<div class="custom-caption-text">' . htmlspecialchars($item['caption']) . '</div>';
            }

            //---- BUILD ITEM ----//
            if ($type == 'slideshow') {
                $all_items .= $html['img'];
            } else if ($type == 'filmstrip') {
                $all_items .= $html['link'] . $html['img'] . $html['link_end'];
            } else {
                $html['inner']     = '<div class="inner">';
                $html['inner_end'] = '</div><!-- .inner -->';

                $all_items .= <<< EOD
                    <li class="gallery-icon icon-$size">
                        {$html['link']}
                            {$html['inner']}
                                {$html['img']}
                            {$html['inner_end']}
                            {$html['caption']}
                        {$html['link_end']}
                    </li>
EOD;
            }
        }

        //-------- BUILD GALLERY --------//

        // for filmstrips & galleries, we determine container size based on first image
        $start_img = wp_get_attachment_image_src($attachments[0]->ID, $size);
        $gallery_w = $start_img[1];
        $gallery_h = $start_img[2];

        //--  SLIDESHOW --//
        if ($type == 'slideshow') {
            $final_html = '<div class="cycle-container ' . $align . ' slideshow-' . $size . '" style="max-width:' . $gallery_w . 'px;">';
            $final_html .= '<div id="' . $rand_gallery_id . '" class="' . $classes . ' meerkat-image-gallery cycle-gallery" data-cycle-log="false">';
            $final_html .= $all_items . '</div>';
            if ($pause) {
                // js associated with these controls can be found in main.js
                $final_html .= '<div class="cycle-control cycle-pause"></div>';
            }
            if ($caption) {
                $final_html .= '<div id="' . $rand_caption_id . '" class="gallery-caption" style="max-width:' . $gallery_w . 'px;"></div>';
            }
            $final_html .= '</div>';
        } //-- FILMSTRIP --//
        else if ($type == 'filmstrip') {
            $final_html = '<div class="' . $classes . ' meerkat-image-gallery gallery-filmstrip cf">';
            $final_html .= '<div class="filmstrip-backdrop" data-img-width="' . $gallery_w . '">';
            $final_html .= '<div class="filmstrip-current" style="max-height:' . $gallery_h . 'px;">';

            $final_html .= '<img src="' . $start_img[0] . '">';
            $final_html .= '</div>';
            if ($caption) {
                $final_html .= '<div class="filmstrip-caption"></div>';
            }
            $prev_arrow = '<div class="filmstrip-prev filmstrip-nav"><div class="sprite"></div></div>';
            $next_arrow = '<div class="filmstrip-next filmstrip-nav"><div class="sprite"></div></div>';
            $final_html .= '<div class="strip-container">' . $prev_arrow;
            $final_html .= '<div class="strip-pics">' . $all_items . '</div>' . $next_arrow . '</div></div></div>';
        } //-- GRID --//
        else {
            $final_html = '<div id="' . $rand_gallery_id . '" class="' . $classes . ' meerkat-image-gallery gallery-columns-' . $columns . ' gallery-grid">';
            $final_html .= '<ul>' . $all_items . '</ul></div>';
        }

        return $final_html;
    }
}

global $meerkat_gallery;
if ( ! $meerkat_gallery) {
    $meerkat_gallery = new Meerkat_Gallery;
}
add_action('init', array($meerkat_gallery, 'init'));

?>
