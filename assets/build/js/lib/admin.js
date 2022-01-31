/* 
* admin interface tweaks
*/

/**
 * Helper function for Quick Alt Edit plugin.
 * Add button to copy image title into alt field
 * when bulk editing alt text.
 */
(function($){
	"use strict";
	if (!$('body').hasClass('upload-php')) {
		return;
	}
	$('.wpa_mc_qtx').each( function(i) {
		$(this).parent().append('<span class="button qae-title-copy">copy title text</span>');
	});
	$('.qae-title-copy').on('click', function(){ 
		var inputId = $(this).parent().find('input').attr('id'); 
		// Get image title and add it to the ALT input field
		var title = $(this).parents('tr').find('.title a').first().text().trim(); 
		$(this).parent().find('input').val(title); 
		// Focus the input field
		document.getElementById(inputId).focus();
	});
})(jQuery);

/**
 * Move focused widget or sidebar to top of screen for easy editing.
 */
(function($){
	"use strict";
	// Utility function to get specific query param
	function getParameterByName(name) {
		var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	}
	// Get focused sidebar or widget
	var $el = $('#' + getParameterByName('sidebar')).parents('.widgets-holder-wrap');
	if(!$el.length){
		$el = $('div[id*=' + getParameterByName('widget') + ']');
	}
	// Give it a noticeable color
	if($el.length) {
		$el.css({'border' : '4px solid #7ad03a'});
	}
})(jQuery);

jQuery(document).ready(function($) {

	// STATUS: fade animation for status msg
	$('.fadeout').fadeOut(1800);

	//---------------- WIDGET ADMIN --------------------//

	// on widgets admin page, if we were passed a query string, focus on that sidebar/widget
	$q_string = window.location.search.substring(1);
	$args = $q_string.split("&");

	for (i=0; i < $args.length; i++) {
		$field = $args[i].split("=");

		// we've been given a widget or sidebar
		if ($field[0] == 'sidebar' && $field[1] != ''){
			// this is for the shortcode builder's widgetized area - we can only open the sidebar not the widget
			// close all of the sidebars
            $('#widgets-right .widgets-holder-wrap').addClass('closed');
			// open the widget & sidebar corresponding to the supplied widget id
			$widget_selecter = '#' + $field[1] + '.widgets-sortables';
			$($widget_selecter).parent().removeClass('closed');
		}
		else if ($field[0] == 'widget' && $field[1] != ''){
			// this is for standard (ie not shortcode builder widgetized area) widgets
			// close all of the sidebars
            $('#widgets-right .widgets-holder-wrap').addClass('closed');
			// open the widget & sidebar corresponding to the supplied widget id
			$widget_selecter = '.widget-insides input.widget-id[value="' + $field[1] + '"]';
			$($widget_selecter).closest('.widget-insides').show().closest('.widgets-holder-wrap').removeClass('closed');
		}
	}

	// add edit link to custom menu widgets

	// selector for custom menu widgets
	$cmenu_widgets = $(".widget-insides:has('input.id_base[value=nav_menu]')"); 

	$cmenu_widgets.each (function(index) {
		update_menu_link( $(this) );
		// also, hide callout menu style checkbox from non super admins
		if (! is_super_admin){
			$(this).find('input[id$=callout]').parent().hide();
			$(this).find('input[id$=pic_url]').parent().hide();
		}		
	});

	// event handler for custom menu select, for current and future instances 
	$('.widget-insides').delegate('select', "change", function(){
		update_menu_link( $(this).closest('.widget-insides') ) ;
		
	});

	function update_menu_link($widget) {
		// figure out which menu is selected in the pulldown menu
		$menu_id = $widget.find('option:selected').val();
		// create link to edit that specific menu
		$edit_link = ' <span class="edit_menu">| <a href=' + $menu_id + '"/wp-admin/nav-menus.php?action=edit&menu=" class="edit">Edit Menu</a></span>';
		// put it after the close link
		$widget_control_links = $widget.find('.widget-control-actions .alignleft');
		// remove the edit menu link if we've already put one there
		$widget_control_links.find('.edit_menu').remove();
		// add updated link
		$widget_control_links.append($edit_link);
	}

    // put a "W" icon on widget titles to identify it as a williams widget
    $widget_title = $('.widget-title:contains(. ) h4').each (function(index){
        $widget_title_html = $(this).html();
		$icon_html = '<div class="williams-widget-icon"></div>';
        $new_widget_title_html = $widget_title_html.replace(". ", $icon_html);
        $(this).html($new_widget_title_html);
    });
	
	//----------- END WIDGET ADMIN

	//--------------- FACULTY PROFILE ----------------//

	$poststuff = $('#admin_profile_container').closest('#poststuff');

	// hide everything in the publish box except the trash & publish/update button
	$('#edit-slug-box', $poststuff).hide();

	// preview link is broken
	$('#minor-publishing-actions', $poststuff).hide();

	// change text on featured image box to say "profile" instead
	$('#postimagediv h3.hndle span', $poststuff).html('Profile Image');
	$('#remove-post-thumbnail', $poststuff).html('Remove profile image');
	$default_msg = '<p><b>No custom profile image uploaded.</b><br>Profile will use default directory image.</p>';
	$("#set-post-thumbnail:contains('Set featured image')", $poststuff).html('Upload custom profile image').parent().prepend($default_msg);

    // change title of 'Profile' on editing your user settings so people don't think that it's their Williams profile
    $('body.profile-php #profile-page h2').html('Your User Settings');

    // hide gravity forms button on profile acf wysiwyg fields
    $('body.post-type-profile #add_gform').hide();

	//---------- END PROFILE

	//------------- ACF TWEAKS
	// hide logo upload & site title font size from non-super users so they don't do something awful
	if ( ! is_super_admin ){
		$('#toplevel_page_edit-post_type-acf-field-group').hide(); // hide custom fields menu item
    }
    
    // Hide ACF update nag for non-super-admins and perform upgrade otherwise
    // @todo Make this automatic for all sites and remove
    /*if ( ! is_super_admin ){
        $('#acf-upgrade-notice').hide();
	} else {
        $(".index-php #acf-notice-action").each(function(){
            window.location = $("#acf-notice-action").attr('href');
        });
        $('.custom-fields_page_acf-upgrade').each(function(){
            window.location = '/wp-admin';
        });
    }*/
});


