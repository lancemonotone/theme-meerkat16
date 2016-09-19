"use strict";
/*!  WILLIAMS QUICKLINKS */
!function ($) {
// all public ajax calls go through wp-admin/admin-ajax.php
    var nonSSLajaxURL = myAjax.ajaxurl.replace('https', 'http');

// touchpunch disables input form :<
    $('input').on('click', function () {
        $(this).focus();
    });

// html constants
    var $external_link = '<div class="sprite icon-16 external-link"></div>';
    var $edit_link = '<a class="sprite icon-16 edit"></a>';
    var $delete_link = '<a class="sprite icon-16 delete"></a>';

// string separators for concatenating quick link names/labels for cookie or db storage
// http://domstyled.saila.com/usage/tips/examples/special_characters.html
    var $name_val_sep = '\u2665'; // heart
    var $pair_sep = '\u2660'; // spade

    var $cookie_expires = new Date(2030, 10, 30);

    /*-----------------------------------------------------------------------------------------
     * HEADER
     * pulldown menu from quick links utility link
     *-----------------------------------------------------------------------------------------*/

    var $yourLinks = $('#your-links');
    var $quicklinks = $('#quicklinks');
    var $linkOptionsLi = $('#link-options').find('li');

    if ($('#quick-container').length) {
        $('.quick-list li').on('click', function (e) {
            // prevent clicking on quick links & section titles from triggering quicklink edit fancybox
            e.stopPropagation();
            if (e.target.tagName == 'SPAN') {
                e.preventDefault();
            }
        });

        // utility nav hover - build list for dropdown
        ql_prepopulate_links($('.quick-list ul#your-links'), 'menu');
        $('#header.quick-list').hide();
        $('.quick-nav-link').hover(
            function () {
                ql_prepopulate_links($('.quick-list ul#your-links'), 'menu');
            },
            function () {
                $('#header.quick-list').hide();
            }
        );

        // bookmark
        $('.quick-bookmark').click(function (e) {
            $url = window.location.href;
            var $title = document.title;
            save_custom_item($title, $url, 'link');
        });
        
        $('.quick-launch').featherlight();
        
        // Add google analytics event tracking to Landing Page links
        $yourLinks.find('li a').on('click', function (e) {
            var $label = $(this).text();
            $url = $(this).attr('href');
            var $event_label = $label + ' : ' + $url;
            _gaq.push(['_trackEvent', 'Quick Links', 'Menu Click', $event_label]);
        });
    }

    /*-----------------------------------------------------------------------------------------
     * FANCYBOX
     * tools for fancybox popup triggered by clicking on "customize my links" in utility nav
     *-----------------------------------------------------------------------------------------*/

    else if ($quicklinks.length) {
        //---- SETUP ----//
        (function ($, window, document) {
            // cache elements
            var $input = $('#quick-filter-input');
            var $data_list = $('#link-options');
            $(function () {
                // Set up filter on .filter elements.
                if ($input.length) {
                    //params: change, keydown, clearfilter
                    // set up filter for college links
                    $input.setup_filter(change, null, clear);
                }
            });

            function change() {
                var list_unit = 'li';
                var sub_unit = '.quick-item';
                var search_term = $input.val();
                if (search_term) {
                    // hide non-matches
                    $data_list.find(sub_unit).not(':Contains(' + search_term + ')').closest(list_unit).hide();
                    // show matches
                    $data_list.find(sub_unit + ':Contains(' + search_term + ')').closest(list_unit).show();
                }
                else {
                    // default state- expand all
                    $data_list.children().show();
                }
            }

            function clear() {
                // clear callback: shows all items in associated list
                $('#link-options').children().show();
            }

        }(window.jQuery, window, document));

        var $unsaved_changes = false;  // unsaved to database, cookie is always automatic

        // initialize link list & set login message
        var $status = $.cookie('quicklinks_login_status');

        var $links_loaded = false;
        var $login_message = '';
        if ($status) {
            if ($status == 'load') {
                // successful login for loading custom links
                ql_get_user_links();
                $login_message = '<span class="login-success">Login successful - links loaded.</span>';
                $links_loaded = true;
            }
            else if ($status == 'save') {
                // successful login to save links
                $.get(nonSSLajaxURL, {'action': 'save'},
                    function (data) {
                    }
                );
                $('.quick-save').removeClass('unsaved-changes');
                $login_message = '<span class="login-success">Login successful - links saved.</span>';
            }
            else {
                $login_message = $status.replace(/\+/g, ' ');
            }
            ql_login_message($login_message);
            // unset cookie
            $.cookie('quicklinks_login_status', null, {domain: 'williams.edu', path: '/'});
        }

        if (!$links_loaded) {
            ql_prepopulate_links($yourLinks, 'popup');
        }
        ql_user_info();
        ql_set_size();

        // create sortable/draggable lists
        $linkOptionsLi.draggable({
            connectToSortable: $yourLinks,
            helper: 'clone',
            revert: 'invalid',
            //containment: $('#your-links')
            containment: $('div.quick-content')
        });
        $linkOptionsLi.on("dragstop", function (event, ui) {
            // unset height & width which screws up layout
            $(ui.helper).css({width: "", height: ""});
        });
        $yourLinks.sortable({
            update: function (e, ui) {
                // add a delete link to the quick link if one does not already exist
                var $has_delete = $(ui.item).has('a.delete').length;
                if ($has_delete == 0) {
                    ui.item.append($delete_link + $edit_link);
                }
                ql_set_cookie();
                ql_set_size();
            }
        });

        // tool toggle : buttons to switch between tools (add williams link, add custom link/label)
        $quicklinks.find('.quick-tool').click(function (e) {
            hide_tools();
            var $tool = $(this).data('tool');
            show_tool($tool);
            ql_set_size();
            e.preventDefault();
        });

        //---- ADD / EDIT / DELETE LINKS ----//

        // delete
        $yourLinks.find('.delete').on('click', function (e) {
            $(this).parent().remove();
            ql_set_cookie();
            e.preventDefault();
        });

        // add custom link/label form
        $('.custom-item-form-goes-here').each(function () {
            var $type = '';
            if ($(this).hasClass('cat-only')) {
                $type = 'cat';
            }
            // build & attach form
            var $form = custom_item_form($type);
            $(this).html($form);
        });

        // if template version of form gets submitted, ignore it
        $('#custom-item-form-template').submit(function (e) {
            e.preventDefault();
        });

        // edit existing link form
        $yourLinks.find('a.edit').on('click', function (e) {
            // get link info
            var $ql_item = $(this).parent().find('.quick-item');
            var $title = $ql_item.text();
            $url = $(this).parent().find('a.link-goes-to').attr('href');

            // get appropriate version of form (inline-cat, inline)
            var $type = 'inline';
            if ($(this).parent().hasClass('quick-cat')) {
                $type = 'inline-cat';
            }
            var $form = custom_item_form($type, $title, $url);

            // hide all inline forms (can only have 1 open at a time)
            $('.inline-edit').hide();
            $('#your-links').find('li').removeClass('editing');

            // place form after list item
            $ql_item.after($form);

            // css tweaks for li while in editing mode
            $ql_item.parent().addClass('editing');

            e.preventDefault();
        });

        // cancel edit
        $('.custom-item-form a.cancel-edit').on('click', function (e) {
            // hide form & modify styles
            $(this).parent().hide();
            $ql_item.closest('li').removeClass('editing');
            e.preventDefault();
        });

        // save item
        $('.custom-item-form').on('submit', function (e) {
            var $type = '';
            var $attach = '';
            // get link data
            var $title = $(this).find('.custom-item-title').val();
            $url = $(this).find('.custom-item-url').val();

            // what type of form? (cat, inline-cat, inline)
            if ($(this).hasClass('cat-only')) {
                $type = 'cat';
            }
            if ($(this).hasClass('inline-edit')) {
                if ($(this).hasClass('cat-only')) {
                    $type = 'inline-cat';
                }
                else {
                    $type = 'inline';
                }
                // where to attach form
                $attach = $(this).parent();
                $attach.removeClass('editing');
            }
            // save
            save_custom_item($title, $url, $type, $attach);

            if ($type != 'inline') {
                // reset form input
                $(this).find('input[type="text"]').val('');
            }
            e.preventDefault();
        });

        //---- SAVE / LOAD / RESET LINKS ----//

        // logout
        $('a.logout').click(function (e) {
            ql_logout();
            // Allow page to reload after ajax logout
            //e.preventDefault();
        });

        // load saved data
        $quicklinks.find('a.login-form-load').click(function (e) {
            show_tool('login-form');
            $('#login-form').find('#fx').attr('value', 'load');
            $('.login-form .quick-instr').addClass('hidden');
            $('.login-instr-load').removeClass('hidden');
            ql_set_size();
            e.preventDefault();
        });

        // unsaved changes alert
        $('a.quick-save').click(function (e) {
            if ($(this).hasClass('autosave')) {
                // they are logged in, everything is automatically saved.
                show_tool('college-links');
                e.preventDefault();
                return;
            }
            $('#login-form').find('#fx').attr('value', 'save');
            $('.login-form .quick-instr').addClass('hidden');
            $('.login-instr-save').removeClass('hidden');
            show_tool('login-form');
            e.preventDefault();
        });

        // restore defaults
        $('.restore-default-links').click(function (e) {
            $.cookie('quicklinks', null, {domain: 'williams.edu', path: '/'});
        });

        // dismiss helpful hints
        $('a.dismiss-help').click(function (e) {
            $.cookie('quicklinks_nohelp', 'true', {
                expires: $cookie_expires,
                domain: 'williams.edu',
                path: '/'
            });
            $('.quick-user').addClass('hidden');
            ql_set_size();
            e.preventDefault();
        });

        // show helpful hints
        $('a.show-help').click(function (e) {
            $('.quick-user').removeClass('hidden');
            $('.user-new').removeClass('hidden');
            ql_set_size();
            e.preventDefault();
        });

        // utility links [testing only]
        $('.show-cookie').click(function (e) {
            alert("links are: " + $.cookie('quicklinks'));
            alert("user is: " + $.cookie('quicklinks_user'));
            alert("nohelp is: " + $.cookie('quicklinks_nohelp'));
            alert("login status: " + $.cookie('quicklinks_login_status'));
            e.preventDefault();
        });
        $('.nuke-cookie').click(function (e) {
            $.cookie('quicklinks', null, {domain: 'williams.edu', path: '/'});
            $.cookie('quicklinks_nohelp', null, {domain: 'williams.edu', path: '/'});
            $.cookie('quicklinks_user', null, {domain: 'williams.edu', path: '/'});
            $.cookie('quicklinks_login_status', null, {domain: 'williams.edu', path: '/'});
        });

        // screen resize
        $(window).resize(function () {
            ql_set_size();
        });
    }

    /*----FUNCTIONS----*/

//---- FORMS  ----//

    function custom_item_form($type, $title, $url) {
        // build form for adding a custom label/link
        // copy template from hidden html in template-quicklinks.php
        var $form = $('#custom-item-form-template').clone();
        // prepopulate values
        if ($title) {
            $form.find('.custom-item-title').val($title);
        }
        if ($url && $url != 'undefined') {
            $form.find('.custom-item-url').val($url);
        }
        // tweak form depending on type
        if ($type) {
            if ($type == 'cat' || $type == 'inline-cat') {
                // remove url input- only interested in title
                $form.find('label:eq(1)').remove();
                $form.find('.custom-item-url').remove();
            }
            if ($type == 'inline' || $type == 'inline-cat') {
                $form.addClass('inline-edit');
                $form.find('a.cancel-edit').removeClass('hidden');
                $form.find('.button').attr('value', 'save');
            }
            if ($type == 'inline-cat') {
                $form.addClass('cat-only');
            }
        }
        // unhide
        $form.removeClass('hidden').attr('id', '');
        return $form;
    }

    function save_custom_item($title, $url, $type, $attach) {
        // save custom label/link
        // check for title
        if ($title == '') {
            alert('Please enter a title');
            return;
        }
        // check for url existence & format
        if ($type != 'cat' && $type != 'inline-cat') {
            if ($url == '') {
                alert('Please enter a URL');
                return;
            }
            if ($url && $url.indexOf("://") == -1) {
                // format url if they didn't
                $url = 'http://' + $url;
            }
        }
        var $link_info = [$title, $url];
        // generate list item
        if ($type == 'inline' || $type == 'inline-cat') {
            var $html = ql_generate_link($link_info, 'edit');
            // replace list item with updated info
            $attach.html($html);
        }
        else {
            $html = ql_generate_link($link_info);
            // add link to end of list
            $('#your-links').prepend($html);
        }
        // save to cookie
        ql_set_cookie();
    }

//---- HTML BUILDERS ----//

    function ql_prepopulate_links($target, $type, $saved_data) {
        // build user's list of quicklinks
        var $data = '';
        var $list = '';
        if ($saved_data) {
            // use info grabbed from database
            $data = $saved_data;
        }
        else {
            // use info from cookie
            $data = $.cookie('quicklinks');
        }
        if ($data == '' || $data == null) {
            // default link list
            $.ajax({
                url: nonSSLajaxURL,
                data: {action: 'load_default_links'},
                dataType: 'json',
                success: function (data) {
                    var $html = '';
                    $.each(data, function (i, item) {
                        var $item_info = [this.title, this.url];
                        $html += ql_generate_link($item_info, $type);
                    });
                    $target.html($html);
                },
                error: function (data) {
                    console.log(data);
                }
            });
        }
        else {
            // custom links
            var $items = $data.split($pair_sep);
            for (var $n = 0; $n <= $items.length; $n++) {
                if ($items[$n] == undefined || $items[$n] == '') {
                    break;
                }
                var $item_info = $items[$n].split($name_val_sep);
                var $html = ql_generate_link($item_info, $type);
                $list += $html;
            }
            $target.html($list);
        }
    }

    function ql_generate_link($item_info, $type) {
        // build html for a single label/link
        // $item_info = array (title, url)
        // $type is 'menu', 'popup', or 'edit'
        var $html, $class = '';
        var $title = $item_info[0];
        var $url = $item_info[1];
        var $is_cat = false;

        if (typeof($url) == 'undefined' || $url == '' || $url == 'undefined') {
            // no url, it's a label
            $is_cat = true;
            $class = ' class="quick-cat" ';
        }

        // start wrapper
        if ($type != 'edit') {
            $html = '<li' + $class + '>';
        }

        // do (linked) title
        $html += '<span class="quick-item">';
        if ($type == 'menu') {
            $html += '<a class="link-goes-to" href="' + $url + '">' + $title + '</a>';
        }
        else {
            $html += $title;
        }
        $html += '</span>';

        // do icon/links
        if ($type != 'menu') {
            if ($is_cat) {
                $html += $delete_link + $edit_link;
            }
            else {
                $html += '<a target="_new" class="link-goes-to" href="' + $url + '">'
                    + $external_link + '</a>' + $delete_link + $edit_link;
            }
        }

        // finish wrapper
        if ($type != 'edit') {
            $html += '</li>';
        }

        return $html;
    }

//---- SAVE / STATE RELATED ----//

    function ql_set_cookie() {
        // save current link selection to cookie
        var $current, $title, $url = '';
        $('#your-links').find('> li').each(function () {
            $title = $(this).find('.quick-item').text();
            $url = $(this).find('.link-goes-to').attr('href');
            if ($title && $title != undefined) {
                $current += $title + $name_val_sep + $url + $pair_sep;
            }
        });
        $.cookie('quicklinks', $current, {expires: $cookie_expires, domain: 'williams.edu', path: '/'});
        if ($.cookie('quicklinks_user')) {
            // if user is known, save updates to database as well
            $.get(nonSSLajaxURL, {action: 'save'},
                function (data) {
                }
            );
        }
        else {
            // alert that unsaved changes have occurred
            $('.quick-save').addClass('unsaved-changes');
        }
    }

    function ql_get_user_links() {
        var $user = $.cookie('quicklinks_user');
        if (!$user) return false;

        $.ajax({
            type: 'POST',
            url: nonSSLajaxURL,
            data: {
                'action': 'load_custom',
                'username': $user
            },
            dataType: 'json',
            success: function (data) {
                // var $yourLinks = $('#your-links');
                $yourLinks.find('li').remove();
                ql_prepopulate_links($yourLinks, 'popup', data.links);
                ql_set_cookie();
            }
        });
    }

    function ql_logout() {
        $.get(nonSSLajaxURL, {action: 'logout'},
            function (data) {
            }
        );
        // unset cookie
        $.cookie('quicklinks_user', null, {domain: 'williams.edu', path: '/'});
        // Page will reload. Don't need to mess with it now.
        /* $('.quick-user').addClass('hidden');
         ql_set_user_tab(0);
         ql_set_size();
         */
    }

    function ql_user_info() {
        // get identifying info about user
        var $user;
        if ( $user = $.cookie('quicklinks_user')) {
            // user has logged in at some point and we know who they are
            $('.quick-user .user-new').addClass('hidden').removeClass('active');
            ql_set_user_tab($user);
            $('.quick-tool[data=quick-save]').addClass('autosave').attr('title', 'Changes are automatically saved while logged in.');
            show_tool('college-links');
        }
        else if (!$.cookie('quicklinks') && $.cookie('quicklinks_nohelp') != 'true') {
            // user has not made any customizations to quicklinks, so give them info
            $('.quick-user').removeClass('hidden');
            $('.quick-user .user-new').removeClass('hidden').addClass('active');
            $('.quick-tool[data=quick-save]').removeClass('autosave').attr('title', 'Save links across all browsers.');
        }
    }

    function ql_set_user_tab(user) {
        if (user) {
            $('#user-tab').html('<a class="logout" href="">Welcome, ' + user + ' <span id="logout">logout</span></a>');
        } else {
            // Would be better to get this string from a variable.
            $('#user-tab').html('<a href="" class="login-form-load">Load Saved Quick Links</a>');
        }
    }

    function ql_login_message($message) {
        if (!$message) return false;
        // show status
        $('.login-status').removeClass('hidden').html($message);

        // make login screen the visible tool instead of williams links
        show_tool('login-form');
        $('.login-form #fx').attr('value', 'load');
        $('.login-form .quick-instr').addClass('hidden');
        $('.login-instr-load').removeClass('hidden');
    }

//---- OTHER ----//

    function show_tool($tool) {
        // hide all so we don't double up
        $('.left-col').addClass('hidden');
        $('.left-col#' + $tool).removeClass('hidden');
    }

    function hide_tools() {
        $('.left-col').addClass('hidden');
    }

    function ql_set_size() {
        // for fancybox, resizes sortable lists, deals with vertical scrolling
        var $parent_doc = window.parent.document;
        var $iframe = $($parent_doc).find('iframe.fancybox-iframe');
        var $iframe_height = $iframe.height();
        var $header_height = $('.quick-header').outerHeight();
        var $user_height = 0;

        if (!$('.quick-user').hasClass('hidden')) {
            $user_height = $('.quick-user').outerHeight();
        }
        // set content height
        $('.quick-content').height($iframe_height - $header_height - $user_height);
        var $content_height = $('.quick-content').height();

        // calculate space inside content in each col that is not the list
        var $above_height_l = $('.quick-content .left-col:not(".hidden") .above-list').outerHeight() + 25;
        var $above_height_r = $('.quick-content .right-col .above-list').outerHeight() + 25;

        // set list heights
        $('.quick-content #link-options').height($content_height - $above_height_l);
        $('.quick-content #your-links').height($content_height - $above_height_r);
    }

}(jQuery);