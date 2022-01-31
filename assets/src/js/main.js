'use strict';
import '../../../../../lib/assets/js/src/expando_tabs.es6';
import './modules/quad.es6';
import './modules/gallery.es6';
import '../../../../../lib/quicklinks/assets/js/src/app.es6';

!(function ( $ ) {
  /**
   * Email address wrap on @ and . characters.
   *
   * http://stackoverflow.com/questions/27419127/can-i-break-the-line-at-special-characters-using-css
   */
  $( 'a[href^="mailto:"]' ).each( function () {
    var content = $( this ).text().trim();
    var replaced = content.replace( '@', '&#x200b;@' ).replace( '.', '&#x200b;.' );
    $( this ).html( replaced );
  } );

  //--- Gravity Forms Likert Scale - requires .gf_likert styles (in forms.css) ---//
  $( window ).load( function () {
    if ( $( ".gf_likert ul.gfield_radio li input" ).is( ":checked" ) ) {
      $( ".gf_likert ul.gfield_radio li input:checked" ).parent().addClass( "mychoice" );
    }
  } );

  // add some extra classes and markup to make our likert-style radio choices
  $( ".gf_likert ul.gfield_radio li:first-child" ).addClass( "likert-first" );
  $( ".gf_likert ul.gfield_radio li:last-child" ).addClass( "likert-last" );
  $( ".gf_likert ul.gfield_radio li input" ).addClass( "likert-choice" );
  $( ".gf_likert ul.gfield_radio li label" ).wrap( "<div class='likert-label'></div>" );
  // add space to pad label.
  $( '.likert-label label:empty' ).html( '&nbsp;' );
  // add a hover state
  $( ".gf_likert ul.gfield_radio li" ).hover( function () {
    $( this ).addClass( "likert-hover" );
  }, function () {
    $( this ).removeClass( "likert-hover" );
  } );
  // add a selected class to the parent list item
  $( ".likert-choice" ).change( function () {
    if ( $( this ).is( ":checked" ) ) {
      $( this ).parent().parent().parent().find( ".mychoice" ).removeClass( "mychoice" );
      $( this ).parent().addClass( "mychoice" );
    }
  } );

  //--- GOOGLE SEARCH RESULTS ---//
  (function () {
    var cx = '010875475703748055486:qrlq8khyohw';
    var gcse = document.createElement( 'script' );
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
      '//www.google.com/cse/cse.js?cx=' + cx;
    var s = document.getElementsByTagName( 'script' )[0];
    s.parentNode.insertBefore( gcse, s );
  })();

  //---- IPAD ----//
  var $is_ipad = navigator.userAgent.match( /iPad/i ) != null;
  if ( $is_ipad ) {
    $( 'body' ).addClass( 'ipad' );
  }

  var magSearchText = 'Search Magazine';
  if ( location.href.indexOf( 'alumni-news' ) > 0 ) {
    magSearchText = 'Search Alumni News';
  }
  $( 'body.meerkat-magazine #s' ).val( magSearchText ).blur( function () {
    if ( this.value == '' ) {
      this.value = magSearchText;
    }
  } ).focus( function () {
    if ( this.value == magSearchText ) {
      this.value = '';
    }
  } );

  //---- tab filters
  // display correct tab on page load & on hash change
  if ( location.hash ) {
    set_button_tab( location.hash );
  } else {
    set_button_tab( false );
  }
  $( window ).on( 'hashchange', function () {
    set_button_tab( location.hash );
  } );

  // click action for tab
  $( '.button-tabs > a' ).click( function ( e ) {
    var $target = $( this ).attr( 'data-target' );
    set_button_tab( $target );
    e.preventDefault();
  } );

  function set_button_tab( $target ) {
    // get rid of hash mark from things coming from url
    if ( $target ) {
      $target = $target.replace( '#', '' );
    }
    // hide all content & unselect all tabs
    $( '.button-tabs' ).find( '.meerkat-tab' ).hide();
    $( '.button-tabs > a' ).removeClass( 'selected-button' );
    if ( $target ) {
      // reveal & highlight active tab
      var $target_div = $( '.button-tabs' ).find( 'div[data-target="' + $target + '"]' );

      if ( $target_div.length == 0 ) {
        // non-existant tab called, show default
        $( '.button-tabs' ).find( '.meerkat-tab:first-child' ).show();
        $( '.button-tabs > a:first-child' ).addClass( 'selected-button' );
      } else {
        $target_div.show();
        $( '.button-tabs > a[data-target="' + $target + '"]' ).addClass( 'selected-button' );
      }
    } else {
      // show & highlight default (first) tab
      $( '.button-tabs' ).find( '.meerkat-tab:first-child' ).show();
      $( '.button-tabs > a:first-child' ).addClass( 'selected-button' );
    }
  }

  //---- category filters
  $( '.category-filter' ).click( function ( e ) {
    var $desired_cat = $( this ).attr( 'data-slug' );
    var $show_all = $( this ).hasClass( 'show-all' );
    // apply selected filter styles
    $( this ).siblings().removeClass( 'selected-filter' );
    $( this ).addClass( 'selected-filter' );

    // hide all posts on cat page that aren't of this type
    // category view
    $( 'body.category #content' ).find( 'div.status-publish' ).each( function () {
      if ( $show_all || $( this ).hasClass( 'category-' + $desired_cat ) ) {
        $( this ).show();
      } else {
        $( this ).hide();
      }
    } );
    // page view (used as a mk_calendar shortcode on a page)
    $( 'body.page #cal-grid' ).find( 'div.event-container' ).each( function () {
      if ( $show_all || $( this ).hasClass( $desired_cat ) ) {
        $( this ).show();
        if ( $( 'html' ).hasClass( 'ui-mobile' ) ) {
          $( this ).parents( '.cal-day' ).show();
        }
      } else {
        $( this ).hide();
        if ( $( 'html' ).hasClass( 'ui-mobile' ) ) {
          if ( !$( this ).parents( '.cal-day' ).find( 'div.event-container:visible' ).length ) {
            $( this ).parents( '.cal-day' ).hide();
          }
        }
      }
    } );
  } );

  //---- MEDIA ----//

  // animated slideshow stop-go controls
  $( '.cycle-container .cycle-control' ).click( function ( e ) {
    // get current state
    var $paused = $( this ).parent().find( '.meerkat-image-gallery' ).is( '.cycle-paused' );
    if ( $paused ) {
      // go (show pause button)
      $( this ).parent().find( '.meerkat-image-gallery' ).cycle( 'resume' );
      $( this ).removeClass( 'cycle-resume' );
      $( this ).addClass( 'cycle-pause' );
    } else {
      // stop (show go button)
      $( this ).parent().find( '.meerkat-image-gallery' ).cycle( 'pause' );
      $( this ).removeClass( 'cycle-pause' );
      $( this ).addClass( 'cycle-resume' );
    }
  } );

  // gallery widget image sizing
  $( '#sidebar .gallery_widget_layout' ).closest( '.widget' ).addClass( 'widget_meerkat_gallery' );

  var $gallery_widgets = $( '#sidebar .widget_meerkat_gallery' );

  // window resize events
  $( window ).resize( function () {
    mk_gallery_widget_size();
    mk_filmstrip_gallery_size();
  } );
  $gallery_widgets.ready( function () {
    mk_gallery_widget_size();
  } );

  function mk_gallery_widget_size() {
    // responsive sizing of images in gallery widget
    var $pad = 8;
    var $border = 2;
    $gallery_widgets.each( function () {
      var $gallery_container = $( this ).find( '.meerkat-gallery' );
      var $gallery_container_limit = $gallery_container.attr( 'limit' );
      var $gallery_widget_w = $( this ).width() - 1;
      var $gall_item_w = ($gallery_widget_w - ($pad * 3)) / 2;
      var $gall_item_w = Math.floor( $gall_item_w ) - $border;
      $( this ).find( 'img' ).each( function () {
        $( this ).width( $gall_item_w + 'px' );
        $( this ).height( $gall_item_w + 'px' );
      } );
      var $rows = $gallery_container_limit / 2;
      var $h = ($gall_item_w * $rows) + ($rows * $pad) + ($rows * $border);
      $gallery_container.height( $h + 'px' );
    } );
  }

  // Video Fancybox
  // video pops up in fancybox
  const $typeVideo = $( '.type-video,.fancybox-video' );
  $typeVideo
    .attr( 'rel', 'media-gallery' )
    .attr( 'data-featherlight', 'iframe' )
    .featherlight( {
      padding: 10,
      openEffect: 'fade',
      closeEffect: 'fade',
      prevEffect: 'fade',
      nextEffect: 'fade',
      arrows: false,
      helpers: {
        media: {
          youtube: {
            params: {
              wmode: 'opaque',
              autoplay: 0 // 1 = will enable autoplay
            }
          }
        },
        title: { type: 'inside' }
      }
    } );

  $typeVideo.not( ":has(img)" ).addClass( 'no-img' );

  //---- GALLERY FILMSTRIP ----//
  $( '.gallery-filmstrip' ).ready( function () {
    mk_filmstrip_gallery_size();
  } );
  // default caption
  $( '.gallery-filmstrip' ).each( function () {
    var $first_caption = $( this ).find( '.strip-container a:eq(0) img' ).attr( 'alt' );
    $( this ).find( '.filmstrip-caption' ).html( $first_caption );
  } );
  // click or hover to change large picture
  $( '.gallery-filmstrip .strip-container a' ).click( function ( e ) {
    filmstrip_select_pic( $( this ) );
  } );
  $( '.gallery-filmstrip .strip-container a' ).hover( function ( e ) {
    filmstrip_select_pic( $( this ) );
  } );

  function mk_filmstrip_gallery_size() {
    // responsive sizing of images in filmstrip gallery
    var $filmstrip = $( '.gallery-filmstrip' );
    var $backdrop = $filmstrip.find( '.filmstrip-backdrop' );
    var $img_w = $backdrop.attr( 'data-img-width' );
    var $avail_space = $filmstrip.width();
    // subtract padding
    $avail_space = $avail_space - 30;
    if ( $img_w < $avail_space ) {
      $backdrop.css( {
        'width': $img_w,
        'margin-right': 'auto'
      } );
    } else {
      // shrink to fit!
      $backdrop.css( { 'width': '95%' } );
    }
  }

  // clicking/hovering on preview thumb enlarges is
  function filmstrip_select_pic( $pic_link ) {
    var $fullpic = $pic_link.attr( 'fullpic' );
    var $caption = $pic_link.find( 'img' ).attr( 'alt' );
    var $filmstrip = $pic_link.closest( '.gallery-filmstrip' );
    $filmstrip.find( '.filmstrip-current img' ).attr( 'src', $fullpic );
    $filmstrip.find( '.filmstrip-caption' ).html( $caption );
  }

  var $filmstrip_hover = false;
  var $filmstrip_click = false;

  // next/prev arrows scrolls preview container
  $( '.gallery-filmstrip .filmstrip-nav' ).click( function ( e ) {
    $filmstrip_click = true;
    scroll_filmstrip_preview( $( this ) );
  } );
  $( '.gallery-filmstrip .filmstrip-nav' ).hover(
    function () {
      if ( $is_ipad ) {
        return;
      }
      $filmstrip_hover = true;
      scroll_filmstrip_preview( $( this ) );
    },
    function () {
      if ( $is_ipad ) {
        return;
      }
      $filmstrip_hover = false;
      scroll_filmstrip_preview( $( this ) );
    }
  );

  function scroll_filmstrip_preview( $strip ) {
    // hover is recursive, we use a global var $filmstrip_hover to bail
    if ( !$filmstrip_hover && !$filmstrip_click ) {
      return;
    }

    // calculate some dimensions
    var $strip_pics = $strip.parent().find( '.strip-pics' );
    var $num_pics = $strip_pics.find( 'img' ).size();
    var $unit_w = 110; // 1 pic plus margin-right
    var $full_w = $num_pics * $unit_w;
    var $nav_w = 90; // next/prev arrows + margin
    var $big_pic_w = $strip_pics.closest( '.gallery-filmstrip' ).find( '.filmstrip-current img' ).width();
    var $view_w = $big_pic_w - $nav_w;

    // fiture out which direction we're going, and if we're out of bounds
    var $cur_left = parseInt( $strip_pics.css( 'left' ) );
    var $direction = '-=';
    var $out_of_bounds = false;
    if ( $strip.hasClass( 'filmstrip-prev' ) ) {
      $direction = '+=';
      if ( $cur_left >= 0 ) {
        // don't allow previous if we're at the first pic
        $out_of_bounds = true;
      }
    } else {
      if ( (Math.abs( $cur_left ) + $view_w) >= $full_w ) {
        // don't allow next if it would take you to blank space
        $out_of_bounds = true;
      }
    }
    var $increment = 10;
    if ( $filmstrip_click ) {
      // click advances full pic
      $increment = $unit_w;
    }
    $filmstrip_click = false; // prevent recursion on click

    // do the actual animation
    if ( !$out_of_bounds ) {
      $strip_pics.animate( { 'left': $direction + $increment },
        40,
        function () {
          scroll_filmstrip_preview( $strip );
        }
      );
    }
  }

  //---- PRINTFRIENDLY ----------//
  $( '.printfriendly > a' ).click( function ( e ) {
    $( '.wms-details .wms-summary' ).each( function () {
      $( this ).parent().addClass( 'expanded' );
      $( this ).siblings( '.summary-detail' ).show();
    } );
    window.print();
  } );

  //---- PAGE FORMAT SUPPORT ----//
  $( '#content:has(".post-content.wide")' ).addClass( 'wide' );
  $( '#content:has(".post-content.mediawall")' ).addClass( 'mediawall' );

  //---- PAGES/CUSTOM MENUS WIDGET ----//

  // menus with children get an icon hinting at more content
  var $menus = $( '#sidebar' ).find( 'ul.menu' );
  $menus.find( 'li.depth-3' ).prev( 'li.depth-2' ).addClass( 'has-children' ).append( '<span class="menu-arrow"></span>' );
  $menus.find( 'li.depth-2' ).prev( 'li.depth-1' ).addClass( 'has-children' ).append( '<span class="menu-arrow"></span>' );
  $menus.find( 'li.depth-1' ).prev( 'li.top-item' ).addClass( 'has-children' ).append( '<span class="menu-arrow"></span>' );

  // collapse all submenus
  var $sub_items = $menus.find( 'li.sub-item' );
  $sub_items.hide();

  // clicking on parent menu items (li, not link itself) toggles submenu items
  var $parent_items = $menus.find( 'li.has-children' );

  // clicking on link does not trigger toggle
  $menus.find( 'a' ).click( function ( e ) {
    e.stopPropagation();
  } );

  // toggle menu children open/closed
  $parent_items.click( function ( e ) {
    toggle_children( $( this ) );
  } );

  // expanding current menu item & ancestors
  $menus.each( function () {
    show_menu_context( $( this ) );
  } );

  function show_menu_context( $menu ) {
    // expand correct menu items to show the context of current page/item
    // show children of this item
    var $curr_item = $menu.find( '.current-menu-item' );
    toggle_children( $curr_item );

    // current menu ancestor class not properly applied to category menu item
    // parents, do it (note: this does not work at all depths)
    if ( $curr_item.hasClass( 'menu-item-object-category' ) ) {
      var $curr_depth = $curr_item.attr( 'data-depth' );
      if ( $curr_depth > 0 ) {
        $curr_item.prevUntil( 'li.top-item' ).prev().addClass( 'current-menu-ancestor' );
      }
    }

    // expand ancestry
    var $ancestors = $menu.find( '.current-menu-ancestor' );
    $ancestors.each( function () {
      toggle_children( $( this ) );
    } );

  }

  function toggle_children( $item ) {
    // toggle immediate "children" of this parent item
    if ( !$item.hasClass( 'has-children' ) ) {
      return;
    }
    var $depth = $item.attr( 'data-depth' );
    var $next_depth = parseInt( $depth ) + 1;
    if ( $item.hasClass( 'expanded-parent' ) ) {
      // hide
      $item.nextUntil( 'li[data-depth="' + $depth + '"]' ).hide();
      $item.removeClass( 'expanded-parent' );
      // remove expanded parent icon from "children" too
      $item.nextUntil( 'li[data-depth="' + $depth + '"]', 'li.expanded-parent' ).removeClass( 'expanded-parent' );
    } else {
      // show only immediate "children" but not grandchilden, etc.
      // nextUntil param 1: items up to but not including next item of same level as this one
      // nextUntil param 2: match only items that are 1 level deeper than this one
      $item.nextUntil( 'li[data-depth="' + $depth + '"]', 'li[data-depth="' + $next_depth + '"]' ).show();
      $item.addClass( 'expanded-parent' );
    }
  }

  //---- EDIT WIDGET LINKS ----//
  $( '.widget:not(.widget_nav_menu) a.edit-me' ).each( function ( index ) {
    var $widget = $( this ).parents( '.widget' );
    // To skip rule, apply '.widgetized_area' class to widget
    if ( !$widget.hasClass( 'widgetized_area' ) ) {
      // modify the edit widget links to make them use the widget's id
      var $widget_id = $widget.attr( 'id' );
      $( this ).attr( 'href', myAjax.siteurl + '/wp-admin/widgets.php?widget=' + $widget_id );
    }
  } );

  //---- RSS WIDGETS ----//
  // use our own rss icon at the bottom
  $( '.widget_rss' ).each( function () {
    var rss_url = $( this ).find( '.widgettitle a:first-child' ).attr( 'href' ) || $( this ).find( '.title a:first-child' ).attr( 'href' ); // http://communications.williams.edu/category/news-releases/feed/
    var site_url = $( this ).find( '.widgettitle a:last-child' ).attr( 'href' ) || $( this ).find( '.title a:last-child' ).attr( 'href' );
    // for wp category feeds, be "smarter" than the wp widget that just sends you to the top level site
    if ( typeof rss_url !== 'undefined' && rss_url.indexOf( '.williams.edu' ) !== -1 && rss_url.indexOf( '/category/' ) !== -1 ) {
      var url_bits = rss_url.split( '/' );
      var cat_name = '';
      for ( var i = 0; i < url_bits.length; i++ ) {
        if ( url_bits[i] == 'category' ) {
          cat_name = url_bits[i + 1];
          break;
        }
      }
      site_url += 'category/' + cat_name + '/';
      // reset title url
      $( this ).find( '.widgettitle a.rsswidget' ).attr( 'href', site_url );
    }
    $( this ).find( '.widgettitle a.rsswidget:first-child' ).remove();
    $( this ).find( '.title a.rsswidget:first-child' ).remove();
    var rss_icon = '<div class="wms-cal-rss"><a href="' + rss_url + '"><div class="sprite icon-16 rss"></div>Subscribe</a></div>';
    var widgetTitle = $( this ).find( '.widgettitle a' ).text();
    var site_link = '<a class="wms-cal-link" href="' + site_url + '">More ' + widgetTitle + ' &raquo;</a>';
    $( this ).append( rss_icon + site_link );
  } );

  //---- CATEGORY POSTS WIDGET ----//
  // assign a marker class to category posts widgets that display ONLY the title, and style it like a menu
  $( '#sidebar' ).find( '.widget_cat_loop_simple ul.post-loop-wrap' ).each( function () {
    var $extras = $( this ).find( 'li > *:not("p.post-title")' );
    if ( $extras.length == 0 ) {
      $( this ).closest( '.widget_cat_loop_simple' ).addClass( 'ultra-simple-cat' );
    }
    // if this is the current page, highlight it
    $( this ).find( 'li' ).each( function () {
      var $post_url = $( this ).find( 'a' ).attr( 'href' );
      if ( $post_url == location.href ) {
        $( this ).addClass( 'current-menu-item' );
      }
    } );
  } );

  //---- LOCALIST CALENDAR ----//

  // localist markup is not human readable, add some classes so we don't go crazy
  $( 'div#lw' ).addClass( 'localist-outer-wrapper' );
  $( 'div.lw' ).addClass( 'localist-inner-wrapper' );
  $( 'ul#lwe' ).addClass( 'localist-events' );
  $( 'ul#lwe > li.lwe' ).addClass( 'localist-event' );

  $( 'div.lwn' ).addClass( 'localist-basic-info' );
  $( 'span.lwn0' ).addClass( 'localist-date' );
  $( 'div.lwn > a' ).addClass( 'localist-title' );

  $( 'div.lwd' ).addClass( 'localist-details' );
  $( 'span.lwi0' ).addClass( 'localist-thumb-container' );
  $( 'span.lwi0 > a' ).addClass( 'localist-thumb-link' );
  $( 'span.lwi0 > a > img' ).addClass( 'localist-thumb-img' );

  $( 'div.lwl' ).addClass( 'localist-where' );
  $( 'span.lwl0' ).addClass( 'localist-location' );
  $( 'div.lwl > a' ).addClass( 'localist-venue' );

  $( '.localist-details' ).contents().filter( function () {
    return this.nodeType == 3;
  } ).wrap( '<div class="cal-rollover-bot" />' );

  $( '.localist-details' ).wrap( '<div class="cal-rollover" />' );

  var $dow_names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  var $is_math = location.href.substring( 0, 12 ) == 'http://math.';
  $( '.localist-event' ).each( function ( index ) { // for each event
    // duplicate event name & date so it can show in the rollover too
    var $e_name = $( this ).find( '.lwn' ).clone().addClass( 'cal-rollover-top' );
    $( this ).find( '.cal-rollover .lwi0' ).after( $e_name );

    // date calculations
    var $date_html = $( this ).find( '.lwn0:first' );
    var $date = jQuery.trim( $date_html.text() );
    var $date_copy = $date; // save for later
    var $date_bits = $date.split( ' ' );	// $date_bits = ["Sep", "25", "", "7pm"...]
    var $mon = $date_bits[0].toLowerCase();
    var $day = $date_bits[1].replace( ',', '' );
    var $time;
    if ( $date_bits[2] != "" ) {
      $time = $date_bits[2];
    } else {
      $time = $date_bits[3];
    }
    var $is_date_span = false;
    if ( $date_bits.length > 4 ) {
      $is_date_span = true;
    }

    // calculate day of week. year is not included in localist feed :<
    var $today_obj = new Date();
    var $cur_year = $today_obj.getFullYear();
    var $cur_mon = $today_obj.getMonth();
    var $event_date_obj = new Date( $mon + " " + $day + ", " + $cur_year );
    var $event_mon = $event_date_obj.getMonth();
    // check to see if we're crossing into the next year
    if ( $event_mon < $cur_mon ) { // yup, increment year
      $event_date_obj.setFullYear( $cur_year + 1 );
    }

    var $event_dow = $dow_names[$event_date_obj.getDay()];

    // move datetime to after title
    var $roll_date = $( this ).find( '.cal-rollover-top span.lwn0' ).prepend( $event_dow + ', ' ).remove();
    $( this ).find( '.cal-rollover-top' ).append( $roll_date );

    // add month sprite
    var $all_html = '<div class="localist-fancy-date">' +
      '<div class="sprite icon-month ' + $mon + '"></div>' +
      '<div class="day-big">' + $day + '</div>';
    $( this ).find( '.lwn:first' ).prepend( $all_html );

    // move location into rollover
    var $lwl = $( this ).find( '.lwl' ).remove();
    $( this ).find( '.cal-rollover .lwn a' ).after( $lwl );

    // replace date/time in main list with just time
    $date_html.remove();

    if ( $is_math ) {
      if ( $( this ).closest( '#content' ).length ) {
        // copy thumb & link to main display
        var $math_thumb = $( this ).find( '.localist-thumb-container' );
        $( this ).find( '.localist-basic-info' ).prepend( $math_thumb );
        // copy day of week into main display
        var $math_dow = '<div class="math-cal-dow">' + $event_dow + '</div>';
        $( this ).find( '.localist-fancy-date' ).append( $math_dow );
        // add time of day to main display
        var $math_time = '<span class="math-cal-time">' + $time + '</span>: ';
        $( this ).find( '.localist-fancy-date' ).next().prepend( $math_time );
      }
    }

    // add more link
    $url = $( this ).find( '.lwn > a' ).attr( 'href' );
    var $more = '<a class="more" href="' + $url + '">[ more ]</a>';
    $( this ).find( '.cal-rollover-bot' ).append( $more );

    // fix thumb image url - they started giving us dinky ones :<
    var $thumb = $( this ).find( '.cal-rollover img.localist-thumb-img' );
    var $too_small = $thumb.attr( 'src' );
    var $bigger = $too_small.replace( "/small/", "/big/" );
    $thumb.attr( 'src', $bigger );

  } );
  //add classes to links that wrap images
  $( 'a:has(img)' ).addClass( 'image-link' );

  // SMOOTH SCROLL TO INTERNAL LINKS
  var $scroll_root = $( 'html, body' );
  $( '#content' ).find( 'a[href^=#]' ).not( '.filter a' ).click( function () { // don't target external or jquery mobile links
    var href = $.attr( this, 'href' );
    if ( href.length > 1 ) {
      $scroll_root.animate( {
        scrollTop: $( href ).offset().top
      }, 500, function () {
        // Uncomment next line to add hash to URL
        //window.location.hash = href;
      } );
      return false;
    }
  } );

  //autofocus on search box on /search
  jQuery( document ).ready( function () {
    if ( jQuery( 'body' ).is( '.twig-search' ) ) {
      setTimeout( function () {
        jQuery( ".searchui-incontent .wms-navbox-input" ).focus();
        console.log( "working" );
      }, 3000 );
    }
  } );

})( window.jQuery );
