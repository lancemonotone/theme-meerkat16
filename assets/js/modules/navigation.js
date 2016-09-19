// require(['jquery', 'jquery_ui'], function($){
/**
 * Theme functions file.
 *
 * Contains handlers for navigation and widget area.
 *
 * @todo Close mega menu when clicking outside it
 */

/**
 * tooltipX for testing
 * http://jsfiddle.net/BfSz3/
 !function(a){a.widget("custom.tooltipX",a.ui.tooltip,{options:{autoShow:!0,autoHide:!0},_create:function(){this._super(),this.options.autoShow||this._off(this.element,"mouseover focusin")},_open:function(a,b,c){this._superApply(arguments),this.options.autoHide||this._off(b,"mouseleave focusout")}})}(jQuery);
 */

(function ($) {
    "use strict";
    var body,
        $navigationMenus,
        $masthead,
        $menuToggle,
        $navButtonContainer,
        $globalNavigation,
        $socialNavigation,
        $networkHeaderMenu,
        resizeTimer;

    $navigationMenus   = $('.widget_nav_menu').find('.menu');
    $masthead          = $('#masthead');
    $networkHeaderMenu = $masthead.find('#network-header-menu');
    $globalNavigation  = $masthead.find('#global-navigation');
    $socialNavigation  = $masthead.find('#social-navigation');

    $navButtonContainer = $('#nav-button-container');
    $menuToggle         = $('.menu-toggle');

    $navigationMenus.tooltip({
        position: {my: "left top+10", at: "left bottom", collision: "flipfit"}
    });

    (function (container, context) {
        // Add dropdown toggle that displays child menu items.
        var dropdownToggle = $('<div />', {
            'class': 'dropdown-toggle',
            'aria-expanded': false
        }).append($('<span />', {
            'class': 'screen-reader-text test3',
            text: 'Expand child menu'
        }));

        container.find('.menu-item-has-children > a').after(dropdownToggle);

        //prevent clicking top level items in site nav, and toggle, otherwise follow link
        $('#site-navigation .nav-link, .global-navigation .nav-link').each(function (index, value) {
            $(this).on('click touch',function (e) {
                var toggleBtn = $(this).next(".dropdown-toggle");
                // if a toggle btn exists, prevent default and toggle
                if (toggleBtn.length > 0) {
                    e.preventDefault();
                    toggleBtn.trigger('click');
                }
            });
        });


        // Toggle buttons and submenu items with active children menu items.
        context.find(container).find('.current-menu-item > .dropdown-toggle').addClass('toggled-on auto-opened');
        context.find(container).find('.current-menu-item > .nav-drop').addClass('toggled-on auto-opened');
        context.find(container).find('.current-menu-ancestor > .dropdown-toggle').addClass('toggled-on auto-opened');
        context.find(container).find('.current-menu-ancestor').addClass('toggled-on auto-opened');
        context.find(container).find('.current-menu-ancestor > .nav-drop').addClass('toggled-on auto-opened');
        context.find(container).find('.current-menu-parent').addClass('toggled-on auto-opened');
        context.find(container).find('.current-page-ancestor > .dropdown-toggle').addClass('toggled-on auto-opened');
        context.find(container).find('.current-page-ancestor > .nav-drop').addClass('toggled-on auto-opened');

        // Add menu items with submenus to aria-haspopup="true".
        container.find('.menu-item-has-children').attr('aria-haspopup', 'true');

        container.find('.dropdown-toggle').click(function (e) {
            var _this            = $(this),
                screenReaderSpan = _this.find('.screen-reader-text');

            e.preventDefault();
            _this
                .add($(this).parent())
                .toggleClass('toggled-on').removeClass('auto-opened');
            _this.next('.children, .sub-menu, .nav-drop').slideToggle(100).toggleClass('toggled-on').removeClass('auto-opened');

            // jscs:disable
            _this.attr('aria-expanded', _this.attr('aria-expanded') === 'false' ? 'true' : 'false');
            // jscs:enable
            screenReaderSpan.text(screenReaderSpan.text() === 'Expand child menu' ? 'Collapse child menu' : 'Expand child menu');
        });
    })($navigationMenus, $('.site-content'));

    // function initSidebarNavigation(container, context) {}

    // initSidebarNavigation($navigationMenus, $('.site-content'));

    // Enable menuToggle.
    (function () {
        // Return early if menuToggle is missing.
        if (!$menuToggle.length) {
            return;
        }

        // Add an initial values for the attribute.
        $menuToggle.add($globalNavigation).add($socialNavigation).attr('aria-expanded', 'false');

        $menuToggle.on('click.twentysixteen', function () {
            var $me              = $(this);
            var $target          = $($me.data('target'));
            var $targetParent    = $target.parent();
            var $targetContainer = $targetParent.parent();
            var $networkHeader   = $('.network-header');

            // Close other menus
            if ($menuToggle.hasClass('toggled-on') && !$me.hasClass('toggled-on')) {
                closeDrawer();
            } else {
                openDrawer();
            }

            function closeDrawer() {
                $targetParent.slideUp(300, function () {
                    $(this).removeClass('toggled-on auto-opened');
                    $targetContainer.add('.toggled-on', $(this)).removeClass('toggled-on auto-opened');
                    openDrawer();
                });
            }

            function openDrawer() {
                if ($me.hasClass('toggled-on')) { // close
                    $networkHeader.removeClass('toggled-on');
                    $targetParent.slideUp(300, function () {
                        $me.removeClass('toggled-on auto-opened');
                        $targetParent.removeClass('toggled-on auto-opened');
                        $target.removeClass('toggled-on auto-opened');
                        $targetContainer.removeClass('toggled-on auto-opened');
                    });
                } else { // open
                    $networkHeader.addClass('toggled-on');
                    $me.addClass('toggled-on');
                    $target.addClass('toggled-on');
                    $targetContainer.addClass('toggled-on');
                    $targetParent.addClass('toggled-on');
                    $targetParent.slideDown(300);
                }

                // jscs:disable
                $me
                    .add($target)
                    .add($targetParent)
                    .add($targetContainer)
                    .attr('aria-expanded',
                        $(this)
                            .add($target)
                            .attr('aria-expanded') === 'false' ? 'true' : 'false'
                    );
                // jscs:enable
            }


        });
    })();

    // Fix sub-menus for touch devices and better focus for hidden submenu items for accessibility.
    (function () {
        if (!$globalNavigation.length || !$globalNavigation.children().length) {
            return;
        }

        // Toggle `focus` class to allow submenu access on tablets.
        function toggleFocusClassTouchScreen() {
            $globalNavigation.find('.menu-item-has-children > a').unbind('touchstart.twentysixteen');
        }

        if ('ontouchstart' in window) {
            $(window).on('resize.twentysixteen', toggleFocusClassTouchScreen);
            toggleFocusClassTouchScreen();
        }

        $globalNavigation.find('a').on('focus.twentysixteen blur.twentysixteen', function () {
            $(this).parents('.menu-item').toggleClass('focus');
        });
    })();

    // Add the default ARIA attributes for the menu toggle and the navigations.
    function onResizeARIA() {
        if ($menuToggle.hasClass('toggled-on')) {
            $menuToggle.attr('aria-expanded', 'true');
        } else {
            $menuToggle.attr('aria-expanded', 'false');
        }

        if ($networkHeaderMenu.hasClass('toggled-on')) {
            $globalNavigation.attr('aria-expanded', 'true');
            $socialNavigation.attr('aria-expanded', 'true');
        } else {
            $globalNavigation.attr('aria-expanded', 'false');
            $socialNavigation.attr('aria-expanded', 'false');
        }

        $menuToggle.attr('aria-controls', 'navigation-menu social-navigation');
    }

    // Add 'below-entry-meta' class to elements.
    function belowEntryMetaClass(param) {
        if (body.hasClass('page') || body.hasClass('search') || body.hasClass('single-attachment') || body.hasClass('error404')) {
            return;
        }

        $('.entry-content').find(param).each(function () {
            var element              = $(this),
                elementPos           = element.offset(),
                elementPosTop        = elementPos.top,
                entryFooter          = element.closest('article').find('.entry-footer'),
                entryFooterPos       = entryFooter.offset(),
                entryFooterPosBottom = entryFooterPos.top + ( entryFooter.height() + 28 ),
                caption              = element.closest('figure'),
                newImg;

            // Add 'below-entry-meta' to elements below the entry meta.
            if (elementPosTop > entryFooterPosBottom) {

                // Check if full-size images and captions are larger than or equal to 840px.
                if ('img.size-full' === param) {

                    // Create an image to find native image width of resized images (i.e. max-width: 100%).
                    newImg     = new Image();
                    newImg.src = element.attr('src');

                    $(newImg).load(function () {
                        if (newImg.width >= 840) {
                            element.addClass('below-entry-meta');

                            if (caption.hasClass('wp-caption')) {
                                caption.addClass('below-entry-meta');
                                caption.removeAttr('style');
                            }
                        }
                    });
                } else {
                    element.addClass('below-entry-meta');
                }
            } else {
                element.removeClass('below-entry-meta');
                caption.removeClass('below-entry-meta');
            }
        });
    }

    $(document).ready(function () {
        body = $(document.body);

        $(window)
            .on('load.twentysixteen', onResizeARIA)
            .on('resize.twentysixteen', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    belowEntryMetaClass('img.size-full');
                    belowEntryMetaClass('blockquote.alignleft, blockquote.alignright');
                }, 300);
                onResizeARIA();
            });

        belowEntryMetaClass('img.size-full');
        belowEntryMetaClass('blockquote.alignleft, blockquote.alignright');
    });
})(jQuery);
// });