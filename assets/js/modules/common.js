"use strict";
var Wms = Wms || function () {};
var wms = new Wms();

!function ($) {
    // creates a .Contains selector for case insensitive contains
    $.expr[':'].Contains = function (a, i, m) {return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase()) >= 0;};
    $.fn.setup_filter = function(changeCallback, keydownCallback, clearCallback){
        return this.each(function(){
            var $input = $(this);

            // Define placeholder
            if(typeof($input.data('placeholder')) === 'undefined'){
                $input.data('placeholder','Type to filter...');
            }

            var placeholder = $input.data('placeholder');
            var $name = $input.attr('name');

            // Handle events
            $input
                .wrap('<span class="filter-wrapper bt-search"></span>')
                .val(function(){
                    // if no query string value, we'll use the placeholder text
                    var $url = $.url();
                    if(!$url.attr('query').length || typeof($url.param($name)) === 'undefined'){
                        return $input.data('placeholder');
                    }else{
                        return $url.param($name);
                    }
                })
                .blur( function(){
                    if (this.value == ''){
                        this.value = placeholder;
                    }
                })
                .focus(function(){
                    if (this.value == placeholder){
                        this.value = '';
                    }
                })
                .keydown(function(e){
                    if(typeof(keydownCallback) === 'function'){
                        keydownCallback(e);
                    }
                })
                .keyup(function(){
                    $input.change();
                })
                .change(function(){
                    if(typeof(changeCallback) === 'function'){
                        changeCallback();
                    }
                });

            // Clear filter link functionality
            $('<a class="clear-filter bt-times" href="javascript:void(0)"></a>')
                .insertAfter($input)
                .click( function(e){
                    e.preventDefault();
                    // reset placeholder text
                    $input.val(placeholder);
                    if(typeof(clearCallback) === 'function'){
                        clearCallback();
                    }
                });

            // Show loading icon when an Ajax request is  made
            /*$('#fancybox-loading').ajaxStart(function(){
             $(this).show();
             }).ajaxStop(function(){
             $(this).hide();
             });*/

            // Perform search on load
            if($input.val() != $input.data('placeholder') && typeof(changeCallback) == 'function'){
                changeCallback();
            }

        });
    };

    Wms.prototype.common = {
        getPageUrl: function () {
            var $d_url   = $.url();
            var page_url = $d_url.attr('protocol') + '://' + $d_url.attr('host') + $d_url.attr('path');
            return {
                urlObj: $d_url,
                pageUrl: page_url
            };
        },
        appendAnchor: function ($el, url) {
            var _self = this;
            var $anchor = $('<a class="link-anchor" title="Click to copy link" href="' + url + '"></a>');
            $anchor.tooltip({
                position: {
                    my: "left top+20",
                    at: "left bottom",
                    collision: "flipfit",
                    track: true
                }
            }).on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                _self.copyTextToClipboard(event, url);
            });
            $el.prepend($anchor);
        },
        copyTextToClipboard: function (event, text) {
            var textArea = document.createElement("textarea");
            /**** This styling is an extra step which is likely not required. ***

             Why is it here? To ensure:
             1. the element is able to have focus and selection.
             2. if element was to flash render it has minimal visual impact.
             3. less flakyness with selection and copying which **might** occur if
             the textarea element is not visible.

             The likelihood is the element won't even render, not even a flash,
             so some of these are just precautions. However in IE the element
             is visible whilst the popup box asking the user for permission for
             the web page to copy to the clipboard.

             Place in top-left corner of screen regardless of scroll position.
             */

            textArea.style.position = 'fixed';
            textArea.style.top  = 0;
            textArea.style.left = 0;
            // Ensure it has a small width and height. Setting to 1px / 1em
            // doesn't work as this gives a negative w/h on some browsers.
            textArea.style.width      = '2em';
            textArea.style.height     = '2em';
            // We don't need padding, reducing the size if it does flash render.
            textArea.style.padding    = 0;
            // Clean up any borders.
            textArea.style.border     = 'none';
            textArea.style.outline    = 'none';
            textArea.style.boxShadow  = 'none';
            // Avoid flash of white box if rendered for any reason.
            textArea.style.background = 'transparent';
            textArea.value            = text;
            document.body.appendChild(textArea);
            textArea.select();
            var msg;
            try {
                var successful = document.execCommand('copy');
                msg            = successful ? 'Copied to clipboard' : 'Oops, unable to copy';
                console.log(msg);
            } catch (err) {
                msg = 'Oops, unable to copy';
                console.log(msg);
            }
            // Cache $target
            var $target = $(event.target);
            // First close tooltip, which should be open on hover
            $target.tooltip('close');
            // Get the original message
            var oldMsg = $target.tooltip("option", "content");
            // Set the tool tip to the success/fail message
            $target.tooltip("option", "content", msg);
            // Open the tooltip with the new message
            $target.tooltip('open');
            // For some reason, the tooltip doesn't close automatically
            // unless the hide option is reset
            $target.tooltip("option", "hide", {
                effect: "fade",
                duration: 1000
            });
            // Close the tooltip after an interval
            setTimeout(function(){
                $target.tooltip('close');
            }, 1000);
            // Set message back to original after close.
            $target.on("tooltipclose", function (event, ui) {
                $(this).tooltip("option", "content", oldMsg);
            });
            document.body.removeChild(textArea);
        }
    };
}(jQuery);

/*!
 * jQuery UI Touch Punch 0.2.2
 *
 * Copyright 2011, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
!function(a){function e(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var d,b=a.ui.mouse.prototype,c=b._mouseInit;b._touchStart=function(a){var b=this;!d&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(d=!0,b._touchMoved=!1,e(a,"mouseover"),e(a,"mousemove"),e(a,"mousedown"))},b._touchMove=function(a){d&&(this._touchMoved=!0,e(a,"mousemove"))},b._touchEnd=function(a){d&&(e(a,"mouseup"),e(a,"mouseout"),this._touchMoved||e(a,"click"),d=!1)},b._mouseInit=function(){var b=this;b.element.bind("touchstart",a.proxy(b,"_touchStart")).bind("touchmove",a.proxy(b,"_touchMove")).bind("touchend",a.proxy(b,"_touchEnd")),c.call(b)}}}(jQuery);