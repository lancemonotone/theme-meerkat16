!function($, common){
    "use strict";

    var url = common.getPageUrl();

// Media query event listener (https://www.sitepoint.com/javascript-media-queries/)
    var $width = "(min-width: 710px)";
    var mq     = window.matchMedia($width);

    $('.accordion-tab').each(function (index) {
        var $li             = $(this);
        var $this_accordion = $li.parent();
        var $tab_content    = $('> .tab-content', $li).hide();
        var $tab_link       = $('> .tab-link', $li).append('<div class="dropdown-toggle"></div>');
        var default_tab = $('.accordion-tab').find('.is-active');

        // If this isn't an expando, listen for width change to display as expando at small widths
        $this_accordion.data('isExpando', $this_accordion.hasClass('is-expando'));
        if (!$this_accordion.data('isExpando')) {
            mq.addListener(widthChange);
            widthChange(mq);
        }

        // Append anchor link
        var anchor_url = url.pageUrl + '#' + $li.attr('id');
        common.appendAnchor($li, anchor_url);

        // Open and show first tab
        if (!$this_accordion.data('isExpando') || default_tab ) { // tabs
            if ( index === 0 && !default_tab) {
                openTab($li);
            } else if ($li.hasClass('is-active')) {
                closeTabs($this_accordion);
                openTab($li);
            }
        }

        $tab_link.click(function (e) {
            e.preventDefault();
            if (!$this_accordion.data('isExpando')) { // tabs
                if (!$li.hasClass('is-active')) {
                    closeTabs($this_accordion);
                    openTab($li);
                }
            } else { // expando
                $li.toggleClass('is-active');
                $tab_content.toggleClass('is-open').slideToggle(100, reloadTab($li));
            }
        });

        // Media query change handler
        function widthChange(mq) {
            $this_accordion.data('isExpando', !mq.matches);
            var $active_tabs = $('> li.is-active', $this_accordion);

            // If no tabs are active, make first active
            if (!$active_tabs.length) {
                openTab($('> li', $this_accordion).first());
            }

            // If more than one tab is active, make only first tab active
            if ($active_tabs.length > 1) {
                closeTabs($this_accordion);
                openTab($active_tabs.first())
            }
        }
    });

// open automatically with ID
    $('.tab-link', $('#' + url.urlObj.attr('fragment'))).click();

// Reload iframes
    function reloadTab($el) {
        var $WmsInclude = $el.find('.WmsInclude');
        if ($WmsInclude.length) {
            $WmsInclude.attr('src', function (i, val) {
                return val;
            });
        }
    }

    function closeTabs($accordion) {
        $('> .is-active', $accordion).removeClass('is-active');
        $('> li > .tab-content.is-open', $accordion).removeClass('is-open').hide();
    }

    function openTab($li) {
        $li.addClass('is-active');
        $('> .tab-content', $li).addClass('is-open').show();
    }
}(jQuery, wms.common);