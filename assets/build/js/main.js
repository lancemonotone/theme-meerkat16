/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./web/wp-content/lib/assets/js/src/common.es6.js":
/*!********************************************************!*\
  !*** ./web/wp-content/lib/assets/js/src/common.es6.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Common": () => (/* binding */ Common)
/* harmony export */ });


var Common = function ($) {
  var elements = {
    hasClass: function hasClass(el, className) {
      if (!el) {
        return false;
      }

      return el.classList ? el.classList.contains(className) : new RegExp('\\b' + className + '\\b').test(el.className);
    },
    addClass: function addClass(el, className) {
      if (el.classList) {
        el.classList.add(className);
      } else if (!hasClass(el, className)) {
        el.className += ' ' + className;
      }

      return el;
    },
    removeClass: function removeClass(el, className) {
      if (el.classList) {
        el.classList.remove(className);
      } else {
        el.className = el.className.replace(new RegExp('\\b' + className + '\\b', 'g'), '');
      }

      return el;
    },
    // Wrap wrapper around nodes
    // Just pass a collection of nodes, and a wrapper element
    wrapAll: function wrapAll(nodes, wrapper) {
      // Cache the current parent and previous sibling of the first node.
      var parent = nodes[0].parentNode;
      var previousSibling = nodes[0].previousSibling; // Place each node in wrapper.
      //  - If nodes is an array, we must increment the index we grab from
      //    after each loop.
      //  - If nodes is a NodeList, each node is automatically removed from
      //    the NodeList when it is removed from its parent with appendChild.

      for (var i = 0; nodes.length - i; wrapper.firstChild === nodes[0] && i++) {
        wrapper.appendChild(nodes[i]);
      } // Place the wrapper just after the cached previousSibling,
      // or if that is null, just before the first child.


      var nextSibling = previousSibling ? previousSibling.nextSibling : parent.firstChild;
      parent.insertBefore(wrapper, nextSibling);
      return wrapper;
    },
    // Similar to jQuery closest(). Finds closest element by selector.
    findAncestor: function findAncestor(el, sel) {
      if (!el) {
        return false;
      }

      if (!Element.prototype.matches) {
        Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
      }

      if (!Element.prototype.closest) {
        Element.prototype.closest = function (s) {
          var el = this;

          if (!document.documentElement.contains(el)) {
            return null;
          }

          do {
            if (el.matches(s)) {
              return el;
            }

            el = el.parentElement || el.parentNode;
          } while (el !== null && el.nodeType === 1);

          return null;
        };
      }

      return el.closest(sel);
    }
  };

  var initTooltips = function (extraSelectors) {
    if ($.isFunction('tooltip')) {
      var selectors = extraSelectors ? '.tooltip, ' + extraSelectors : '.tooltip';

      var doTooltip = function doTooltip(selectors) {
        $(selectors).tooltip({
          content: function content() {
            var $el = $(this);
            var $tt = $el.find('.tt').clone();
            $tt.attr('class', ''); // for safety

            return $tt.length ? $tt[0].outerHTML : $el.prop('title');
          }
        });
      };

      doTooltip(selectors);
    }
  }();

  var getPageUrl = function getPageUrl() {
    var $d_url = $.url();
    var page_url = $d_url.attr('protocol') + '://' + $d_url.attr('host') + $d_url.attr('path');
    return {
      urlObj: $d_url,
      pageUrl: page_url
    };
  };

  var appendAnchor = function appendAnchor($el, url) {
    var $anchor = $('<button class="link-anchor" title="Click to copy link" href="' + url + '"><span class="visuallyhidden">Click to copy link</span></button>');

    var copyTextToClipboard = function copyTextToClipboard(event, text) {
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
      textArea.style.top = 0;
      textArea.style.left = 0; // Ensure it has a small width and height. Setting to 1px / 1em
      // doesn't work as this gives a negative w/h on some browsers.

      textArea.style.width = '2em';
      textArea.style.height = '2em'; // We don't need padding, reducing the size if it does flash render.

      textArea.style.padding = 0; // Clean up any borders.

      textArea.style.border = 'none';
      textArea.style.outline = 'none';
      textArea.style.boxShadow = 'none'; // Avoid flash of white box if rendered for any reason.

      textArea.style.background = 'transparent';
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      var msg;

      try {
        var successful = document.execCommand('copy');
        msg = successful ? 'Copied to clipboard' : 'Oops, unable to copy';
        console.log(msg);
      } catch (err) {
        msg = 'Oops, unable to copy';
        console.log(msg);
      } // Cache $target


      var $target = $(event.target); // First close tooltip, which should be open on hover

      $target.tooltip('close'); // Get the original message

      var oldMsg = $target.tooltip("option", "content"); // Set the tool tip to the success/fail message

      $target.tooltip("option", "content", msg); // Open the tooltip with the new message

      $target.tooltip('open'); // For some reason, the tooltip doesn't close automatically
      // unless the hide option is reset

      $target.tooltip("option", "hide", {
        effect: "fade",
        duration: 1000
      }); // Close the tooltip after an interval

      setTimeout(function () {
        $target.tooltip('close');
      }, 1000); // Set message back to original after close.

      $target.on("tooltipclose", function (event, ui) {
        $(this).tooltip("option", "content", oldMsg);
      });
      document.body.removeChild(textArea);
    };

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
      copyTextToClipboard(event, url);
    });
    $el.append($anchor);
  };

  var loadImages = function loadImages(srcs) {
    var loadImage = function loadImage(src) {
      var deferred = when.defer(),
          img = document.createElement('img');

      img.onload = function () {
        deferred.resolve(img);
      };

      img.onerror = function () {
        deferred.reject(new Error('Image not found: ' + src));
      };

      img.src = src; // Return only the promise, so that the caller cannot
      // resolve, reject, or otherwise muck with the original deferred.

      return deferred.promise;
    }; // srcs = array of image src urls
    // Array to hold deferred for each image being loaded


    var deferreds = []; // Call loadImage for each src, and push the returned deferred
    // onto the deferreds array

    for (var i = 0, len = srcs.length; i < len; i++) {
      deferreds.push(loadImage(srcs[i])); // NOTE: We could push only the promise, but since this array never
      // leaves the loadImages function, it's ok to push the whole
      // deferred.  No one can gain access to them.
      // However, if this array were exposed (e.g. via return value),
      // it would be better to push only the promise.
    } // Return a new promise that will resolve only when all the
    // promises in deferreds have resolved.
    // NOTE: when.all returns only a promise, not a deferred, so
    // this is safe to expose to the caller.


    return when.all(deferreds);
  };

  var showAjaxLoading = function () {
    $('#fancybox-loading, .ajax-loading').ajaxStart(function () {
      $(this).show();
    }).ajaxStop(function () {
      $(this).hide();
    });
  }();

  var touchpunch = function () {
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
    !function (a) {
      function e(a, b) {
        if (!(a.originalEvent.touches.length > 1)) {
          a.preventDefault();
          var c = a.originalEvent.changedTouches[0],
              d = document.createEvent("MouseEvents");
          d.initMouseEvent(b, !0, !0, window, 1, c.screenX, c.screenY, c.clientX, c.clientY, !1, !1, !1, !1, 0, null), a.target.dispatchEvent(d);
        }
      }

      if (a.support.touch = "ontouchend" in document, a.support.touch) {
        var d,
            b = a.ui.mouse.prototype,
            c = b._mouseInit;
        b._touchStart = function (a) {
          var b = this;
          !d && b._mouseCapture(a.originalEvent.changedTouches[0]) && (d = !0, b._touchMoved = !1, e(a, "mouseover"), e(a, "mousemove"), e(a, "mousedown"));
        }, b._touchMove = function (a) {
          d && (this._touchMoved = !0, e(a, "mousemove"));
        }, b._touchEnd = function (a) {
          d && (e(a, "mouseup"), e(a, "mouseout"), this._touchMoved || e(a, "click"), d = !1);
        }, b._mouseInit = function () {
          var b = this;
          b.element.bind("touchstart", a.proxy(b, "_touchStart")).bind("touchmove", a.proxy(b, "_touchMove")).bind("touchend", a.proxy(b, "_touchEnd")), c.call(b);
        };
      }
    }($);
  }();

  var addJqueryFilters = function () {
    // creates a .Contains selector for case insensitive contains
    $.expr[':'].Contains = function (a, i, m) {
      return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
    };

    $.fn.setup_filter = function (changeCallback, keydownCallback, clearCallback) {
      return this.each(function () {
        var $input = $(this); // Define placeholder

        if (typeof $input.data('placeholder') === 'undefined') {
          $input.data('placeholder', 'Filter list');
        }

        var placeholder = $input.data('placeholder');
        var $name = $input.attr('name'); // Handle events

        $input.wrap('<span class="filter-wrapper bt-search"></span>').val(function () {
          // if no query string value, we'll use the placeholder text
          var $url = $.url();

          if (!$url.attr('query').length || typeof $url.param($name) === 'undefined') {
            return $input.data('placeholder');
          } else {
            return $url.param($name);
          }
        }).blur(function () {
          if (this.value == '') {
            this.value = placeholder;
          }
        }).focus(function () {
          if (this.value == placeholder) {
            this.value = '';
          }
        }).keydown(function (e) {
          if (typeof keydownCallback === 'function') {
            keydownCallback(e);
          }
        }).keyup(function () {
          $input.change();
        }).change(function () {
          if (typeof changeCallback === 'function') {
            changeCallback();
          }
        }); // Clear filter link functionality

        $('<a class="clear-filter bt-times" href="javascript:void(0)"></a>').insertAfter($input).click(function (e) {
          e.preventDefault(); // reset placeholder text

          $input.val(placeholder);

          if (typeof clearCallback === 'function') {
            clearCallback();
          }
        }); // Perform search on load

        if ($input.val() !== $input.data('placeholder') && typeof changeCallback === 'function') {
          changeCallback();
        }
      });
    };
  }();

  return {
    elements: elements,
    getPageUrl: getPageUrl,
    appendAnchor: appendAnchor,
    loadImages: loadImages
  };
}(window.jQuery);

/***/ }),

/***/ "./web/wp-content/lib/assets/js/src/expando_tabs.es6.js":
/*!**************************************************************!*\
  !*** ./web/wp-content/lib/assets/js/src/expando_tabs.es6.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _common_es6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./common.es6 */ "./web/wp-content/lib/assets/js/src/common.es6.js");

/**
 * Build expando or tab groups.
 *
 * @uses wms.common.getPageUrl
 * @uses wms.common.appendAnchor
 */

!function ($) {
  "use strict";

  $.fn.expando = function (options) {
    return this.each(function () {
      if ($(this).hasClass('expando-js')) {
        return;
      } // These are the defaults.


      var settings = $.extend({
        isExpando: false,
        open: false,
        hideAnchor: false,
        singleOpen: false
      }, options); // Set internal variable and initialize.

      var $expandoGroup = $(this).data({
        'is_init': true
      }).addClass('expando-js'); // Expando group be styled as expandos or tabs?

      var isExpando = settings.isExpando || Boolean($expandoGroup.data('is_expando')); // Hide Copy Link anchor.

      var hideAnchor = settings.hideAnchor || Boolean($expandoGroup.data('hide_anchor')); // If expando, only one item open concurrently.

      var singleOpen = settings.singleOpen || Boolean($expandoGroup.data('single_open')); // Which item to open on load.

      var open = settings.open || parseInt($expandoGroup.data('open')); // Get $.url object for page.

      var url = _common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.getPageUrl(); // Object library containing all items in group.(Not used yet)

      var myItems = {};
      init();
      /**
       * Initialize accordion group
       */

      function init() {
        closeAll();
        initItems();
        addEvents();
        detectExpando();
      }

      function initItems() {
        $expandoGroup.find('> li.expando').each(function (i, el) {
          initItem(i, el);
        });
      }
      /**
       * Initialize tab/expando
       * @param el
       * @param i
       */


      function initItem(i, el) {
        // Not yet used. Store items for easier actions.
        myItems[$(el).attr('id')] = i; // Add toggle control that displays child menu items.

        var $dropdownToggle = $('<span />', {
          'class': 'dropdown-toggle'
        });
        var $button = $(el).find('> button');
        var id = $(el).find('> .expando-content').attr('id');
        $button.append($dropdownToggle).attr({
          'aria-expanded': 'false',
          //'aria-label': 'Toggle section',
          'aria-controls': id ? id : ''
        }).click(function (e) {
          e.preventDefault();
          doClick($(el));
        });

        if (!hideAnchor) {
          appendAnchor($(el));
        }
      }
      /**
       * Media query event listener (https://www.sitepoint.com/javascript-media-queries/)
       */


      function addEvents() {
        //If this isn't an expando, listen for width change to display as expando at small widths.
        $(window).resize(function () {
          detectExpando();
        });
      }
      /**
       * Check to see if collective item width is > main content area and collapse to expando.
       */


      function detectExpando() {
        // Only do this if the accordion is NOT expando by default.
        if (!$expandoGroup.data('is_expando')) {
          // Remove this to get non-expando context tab width
          toggleAsExpando(false); // Minus 5 to add small tolerance for mouse event timing

          var thisWidth = $expandoGroup.width() - 5;
          var tabsWidth = 0; // Add widths of all tabs to get total

          $expandoGroup.find('> li.expando').each(function () {
            tabsWidth += $(this).outerWidth(true);
          }); // Force expando if the accordion is narrower than the tabs

          if (thisWidth <= tabsWidth) {
            toggleAsExpando(true);
          }
        } else {
          toggleAsExpando(true);
        }

        checkActiveItem();
      }
      /**
       * Add/remove expando styling
       * @param which
       */


      function toggleAsExpando(which) {
        switch (which) {
          case false:
            isExpando = false;
            $expandoGroup.removeClass('is-expando');
            break;

          case true:
            isExpando = true;
            $expandoGroup.addClass('is-expando');
            break;
        }
      }
      /**
       * When toggling states (expando to tab), check and init an item state
       */


      function checkActiveItem() {
        var $current = {}; // Only run this on page load.

        if ($expandoGroup.data('is_init')) {
          $expandoGroup.data('is_init', false); // Open if id has been passed by url hash.

          var id = url.urlObj.attr('fragment');

          if (id && $expandoGroup.find('#' + id).length) {
            $current = $expandoGroup.find('> li.expando#' + id);
          } // Open item passed in config


          if (!$current.length && isNumber(open)) {
            // If open is a positive number
            var index = open > 0 ? open - 1 : 0;
            $current = $expandoGroup.find('> li.expando').eq(index);
          }
        } // If this is tabs, open the first active item or first tab


        if (!$current.length && !isExpando) {
          $current = $expandoGroup.find('> li.expando.is-active').first();

          if (!$current.length) {
            $current = $expandoGroup.find('> li.expando').eq(0);
          }
        } // If we have found one of the cases above


        if ($current.length) {
          doClick($current);
        }
      }
      /**
       * Open or toggle item if exists.
       * @param $item
       */


      function doClick($item) {
        if (!$item.length) {
          return false;
        }

        if (!isExpando || singleOpen && !$item.hasClass('is-active')) {
          // If this is a tab group
          openItem($item);
        } else {
          // Open or close tab
          toggleItem($item);
        }
      }

      function openItem($item) {
        closeAll();
        var $button = $item.find(' > button');
        $item.addClass('is-active').find('> .expando-content').each(function (i, el) {
          $(el).addClass('is-open');

          if (isExpando) {
            $(el).slideDown(100, reloadItem($item));
          } else {
            $(el).show(0, function () {
              reloadItem($item);
            });
          }
        });
        $button.attr({
          'aria-expanded': 'true'
        });
      }

      function closeItem($item) {
        var $subsection = $item.find('> .expando-content'),
            $button = $item.find('> button '); // ul.sub-menu

        $item.removeClass('is-active');
        $subsection.each(function (i, el) {
          $(el).removeClass('is-open');

          if (isExpando) {
            $(el).slideUp(50);
          } else {
            $(el).hide();
          }
        });
        $button.attr({
          'aria-expanded': 'false'
        });
      }

      function toggleItem($item) {
        if ($item.hasClass('is-active')) {
          closeItem($item);
        } else {
          openItem($item);
        }
      }

      function closeAll() {
        $expandoGroup.find('> li.expando').each(function () {
          closeItem($(this));
        });
      }

      function openAll() {
        $expandoGroup.find('> li.expando').each(function () {
          openItem($(this));
        });
      }
      /**
       * Reload iframes in items
       * @param $item
       */


      function reloadItem($item) {
        var $WmsInclude = $item.find(' > .WmsInclude');

        if ($WmsInclude.length) {
          $WmsInclude.attr('src', function (i, val) {
            return val;
          });
        }
      }
      /**
       * Append anchor link
       * @param $item
       */


      function appendAnchor($item) {
        var anchor_url = url.pageUrl + '#' + $item.attr('id');
        _common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.appendAnchor($item, anchor_url);
      }
      /**
       * Utility function for determining if a value is numeric.
       * @param n
       * @returns {boolean}
       */


      function isNumber(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
      }
    });
  };

  $('.expandos').expando();
}(jQuery);

/***/ }),

/***/ "./web/wp-content/lib/assets/js/src/modules/class.events.es6.js":
/*!**********************************************************************!*\
  !*** ./web/wp-content/lib/assets/js/src/modules/class.events.es6.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Events": () => (/* binding */ Events)
/* harmony export */ });


var Events = {
  /**
   * @module Pub-Sub Singleton
   * @type {{Events: [], current: '', on: on, off: off, emit: emit}}
   */
  events: [],
  on: function on(eventName, fn) {
    var _this = this;

    var fns = typeof fn === 'function' ? [fn] : fn;
    this.events[eventName] = this.events[eventName] || [];
    fns.map(function (fn) {
      return _this.events[eventName].push(fn);
    });
  },
  off: function off(eventName, fn) {
    if (this.events[eventName]) {
      for (var i = 0; i < this.events[eventName].length; i++) {
        if (this.events[eventName][i] === fn) {
          this.events[eventName].splice(i, 1);
          break;
        }
      }
    }
  },
  emit: function emit(eventName, data) {
    if (this.events[eventName]) {
      for (var i = 0; i < this.events[eventName].length; i++) {
        this.events[eventName][i](data, eventName);
      }
      /*this.events[eventName].forEach( function ( fn ) {
        fn( data, eventName );
      } );*/

    }
  }
};

/***/ }),

/***/ "./web/wp-content/lib/quicklinks/assets/js/src/app.es6.js":
/*!****************************************************************!*\
  !*** ./web/wp-content/lib/quicklinks/assets/js/src/app.es6.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../assets/js/src/modules/class.events.es6.js */ "./web/wp-content/lib/assets/js/src/modules/class.events.es6.js");
/* harmony import */ var _modules_class_tools_es6__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modules/class.tools.es6 */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.tools.es6.js");
/* harmony import */ var _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modules/class.cookies.es6 */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.cookies.es6.js");
/* harmony import */ var _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./modules/class.elements.es6 */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.elements.es6.js");
/* harmony import */ var _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./modules/class.links.es6 */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.links.es6.js");
/* harmony import */ var _modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./modules/class.user.es6 */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.user.es6.js");
/**
 * Entry point of app.
 */








!function ($) {
  // Block IE11-
  if (!!window.MSInputMethodContext && !!document.documentMode) {
    console.log('Quicklinks is not supported');
    return;
  }

  if (!document.getElementById('quicklinks-container')) {
    return;
  } //Touchpunch disables input form.


  $('input, li').on('click', function () {
    $(this).focus();
  });
  /**
   * These are the cookies the app will use.
   */

  var cookiesMap = {
    quicklinks: 'quicklinks',
    username: 'quicklinks_user',
    nohelp: 'quicklinks_nohelp',
    menu: 'quicklinks_toggled'
  };
  /**
   * @requires {$}
   * @requires {Elements}
   * @requires {Cookies}
   * @requires {User}
   * @requires {Tools}
   */

  function init() {
    // Add interface events.
    registerAsyncEvents(); // Hook into ajax events.

    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.attachFrontElements(); // Load cookies before user.

    _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.init(cookiesMap);
    _modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.init({
      'quicklinks': _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.cookieObjectToLinks(_modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.get('quicklinks')),
      'username': _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.get('username'),
      'linksChanged': !!_modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.get('quicklinks'),
      'status': _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.get('username') && !_modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.get('quicklinks') ? 'restore' : 'init'
    });
    _modules_class_tools_es6__WEBPACK_IMPORTED_MODULE_1__.Tools.init();
    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.addGaTracking();
  }
  /**
   * Central switchboard to handle async events.
   *
   * @todo Possible to replace with generators?
   * @requires {Events}
   */


  function registerAsyncEvents() {
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('afterInitUser', afterInitUser);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('afterSaveUser', afterSaveUser);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('updateUserLinks', updateUserLinks);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('afterLoadLinks', afterLoadLinks);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('afterShowOverlay', afterShowOverlay);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('afterLoginFail', afterLoginFail);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('edit', _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.initLinkForm);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('afterSortable', afterSortable);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('prepend', _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.prepend);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('save', _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.save);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('remove', _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.remove);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('insert', _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.insert);
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.on('swap', _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.swap);
  }

  var afterInitUser = function afterInitUser(links) {
    _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.init(links);
  };
  /**
   *
   */


  var updateUserLinks = function updateUserLinks(links) {
    _modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.update({
      'quicklinks': links,
      'status': 'save',
      'linksChanged': true
    });
  };

  var afterSaveUser = function afterSaveUser() {
    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.setStatus(_modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getProperty('status'), 'editorStatus');
    setCookies();
  };

  var afterLoadLinks = function afterLoadLinks(index) {
    setCookies();
    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.initEditor(_modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getUser());
    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.initFront(_modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getUser());
    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.setLoginElements(_modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getUser());
    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.fadeBackground(index);
  };
  /**
   * Init editor interface.
   */


  var afterShowOverlay = function afterShowOverlay() {
    _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.attachEditorElements();
    _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.loadAll();
  };

  var afterLoginFail = function afterLoginFail(user) {
    return _modules_class_elements_es6__WEBPACK_IMPORTED_MODULE_3__.Elements.setLoginElements(user);
  };
  /**
   * Handle droppable/sortable events.
   * @param obj
   */


  var afterSortable = function afterSortable(obj) {
    switch (obj.type) {
      case 'swap':
        var index = obj.current;
        var original = obj.original;
        _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.emit('swap', {
          index: index,
          original: original
        });
        break;

      case 'insert':
        insertLink(obj.item);
    }
  };

  var setCookies = function setCookies() {
    if (_modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getProperty('username')) {
      _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.set('username', _modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getProperty('username'));
    } else {
      _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.unset('username');
    }

    if (_modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getProperty('linksChanged')) {
      _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.set('quicklinks', _modules_class_links_es6__WEBPACK_IMPORTED_MODULE_4__.Links.linksToCookieObject(_modules_class_user_es6__WEBPACK_IMPORTED_MODULE_5__.User.getProperty('quicklinks')));
    } else {
      _modules_class_cookies_es6__WEBPACK_IMPORTED_MODULE_2__.Cookies.unset('quicklinks');
    }
  };
  /**
   * Add static link to user links at position.
   *
   * @requires {Link}
   * @param item
   */


  var insertLink = function insertLink(item) {
    var title = item.text();
    var url = item.find('a')[0].href;
    var link = {
      title: title,
      url: url
    };
    var index = item.index();
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_0__.Events.emit('insert', {
      link: link,
      index: index
    });
  };

  init();
}(window.jQuery);

/***/ }),

/***/ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.cookies.es6.js":
/*!**********************************************************************************!*\
  !*** ./web/wp-content/lib/quicklinks/assets/js/src/modules/class.cookies.es6.js ***!
  \**********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Cookies": () => (/* binding */ Cookies)
/* harmony export */ });
/**
 * @module Cookies
 *
 * Cookie store and utilities
 */


var Cookies = function () {
  var expires = new Date(2030, 10, 30);
  var cookies = {};
  /**
   * 1. Initialize cookies property with map object in this form:
   *    {
   *      easyName0: 'longAndComplicatedName0',
   *      easyName1: 'longAndComplicatedName1',
   *      easyName2: 'longAndComplicatedName2'
   *    }
   *
   * 2. Initialize status cookie.
   * 3. Load cookie values into passed in object.
   *
   * @param cookiesMap {Object}
   */

  var init = function init(cookiesMap) {
    cookies = cookiesMap;
  };
  /**
   * Return single cookie value.
   * @param key
   * @returns {*}
   */


  var get = function get(key) {
    return readCookie(cookies[key]);
  };
  /**
   * Set single cookie value.
   * @param key
   * @param value
   * @param persistent
   * @private
   */


  var set = function set(key, value) {
    var persistent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

    if (value !== false) {
      var exp = persistent ? "expires=".concat(expires, ";") : "";
      document.cookie = "".concat(cookies[key], "=").concat(value, ";domain=").concat(myAjax.domain, ";path=/;").concat(exp);
    } //console.log( "Set", cookies[key] + ":", get( key ) );

  };
  /**
   * Set all object property values to matching cookie values.
   * @private
   */


  var mapPropertiesToObj = function mapPropertiesToObj(obj) {
    Object.keys(obj).map(function (key) {
      obj[key] = get(key);
    });
  };
  /**
   * Set cookie values to object property values.
   * @param updated
   * @private
   */


  var updateAll = function updateAll(updated) {
    Object.keys(updated).map(function (key) {
      return set(key, updated[key]);
    });
  };
  /**
   * Unset single cookie value.
   * @param key
   * @private
   */


  var unset = function unset(key) {
    return set(key, '');
  };
  /**
   * Unset all cookie values.
   * @private
   */


  var unsetAll = function unsetAll() {
    return Object.keys(cookies).map(function (key) {
      return unset(key);
    });
  };
  /**
   * Utility function to retrieve single cookie value.
   * @param key
   * @returns {*}
   * @private
   */


  var readCookie = function readCookie(key) {
    var nameEQ = key + "=";
    var ca = document.cookie.split(';');

    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];

      while (c.charAt(0) == ' ') {
        c = c.substring(1, c.length);
      }

      if (c.indexOf(nameEQ) == 0) {
        return c.substring(nameEQ.length, c.length);
      }
    }

    return false;
  };
  /**
   * Output cookie values to console.
   * @private
   */


  var logAll = function logAll() {
    Object.keys(cookies).map(function (key) {
      return console.log(cookies[key], ":", get(key));
    });
  };

  return {
    init: init,
    get: get,
    set: set,
    unset: unset,
    mapPropertiesToObj: mapPropertiesToObj,
    updateAll: updateAll,
    unsetAll: unsetAll,
    logAll: logAll
  };
}();

/***/ }),

/***/ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.elements.es6.js":
/*!***********************************************************************************!*\
  !*** ./web/wp-content/lib/quicklinks/assets/js/src/modules/class.elements.es6.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Elements": () => (/* binding */ Elements)
/* harmony export */ });
/* harmony import */ var _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../assets/js/src/common.es6 */ "./web/wp-content/lib/assets/js/src/common.es6.js");
/* harmony import */ var _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../../../assets/js/src/modules/class.events.es6.js */ "./web/wp-content/lib/assets/js/src/modules/class.events.es6.js");
/* harmony import */ var _assets_js_src_expando_tabs_es6__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../assets/js/src/expando_tabs.es6 */ "./web/wp-content/lib/assets/js/src/expando_tabs.es6.js");
/**
 * @module Elements Object
 */





var Elements = function ($) {
  var front = {
    // Mega menu
    menu: null,
    // Inside QL section of public menu
    container: null,
    // User's default or cookie-saved links
    userLinks: null,
    // Action buttons
    quickActions: null,
    // User logged-in icon
    userIcon: null,
    // Edit button
    quickLaunch: null
  };
  var editor = {
    // Inside editor in lightbox overlay
    container: null,
    // Status messages
    status: null,
    // Initialize static-links filter
    filterInput: null,
    // Container for Help and Login form.
    utilityContainer: null,
    // Info content for new users.
    helpContainer: null,
    // User's default or cookie-saved links
    userLinks: null,
    // All available links: null, generated in lib/inc/class.quicklinks.php
    staticLinks: null,
    // Custom Item form
    customItemForm: null,
    // Divider form
    dividerForm: null
  };
  var login = {
    // Login form container and elements
    container: null,
    form: null,
    // Status messages
    status: null
  };

  var getLoc = function getLoc(where) {
    switch (where) {
      case 'locFront':
        return front.userLinks;

      case 'locEditor':
        return editor.userLinks;

      case 'locStatic':
        return editor.staticLinks;

      case 'loginStatus':
        return login.status;

      case 'editorStatus':
        return editor.status;
    }
  };
  /**
   * Input filter listener
   */


  var change = function change() {
    var search_term = editor.filterInput.val();

    if (search_term) {
      [editor.userLinks, editor.staticLinks].map(function ($list) {
        // Hide non-matches
        $list.find('.quick-item').not(':Contains(' + search_term + ')').hide(); // Show matches

        $list.find('.quick-item' + ':Contains(' + search_term + ')').show();
      });
    } else {
      // Show all.
      clear();
    }
  };
  /**
   * Input filter listener
   */


  var clear = function clear() {
    // clear callback: shows all items in associated list
    [editor.userLinks, editor.staticLinks].map(function ($list) {
      $list.children().show();
    });
  };

  var attachFrontElements = function attachFrontElements() {
    front.menu = $('#network-header-menu');
    front.container = $('#quicklinks-container');
    front.quickActions = front.container.find('#quick-actions');
    front.quickLaunch = front.quickActions.find('#quick-launch');
    front.userLinks = front.container.find('#menu-links'); // Other actions

    showSpinners(['locFront']);
  };
  /**
   *
   * @param user
   */


  var initFront = function initFront(user) {
    front.quickLaunch.prop('disabled', false); //setLoginElements( user );
  };
  /**
   *
   * @param user
   * @return {boolean}
   */


  var initEditor = function initEditor(user) {
    if (!editor.container) {
      return false;
    }

    hideDismissibles();
    setActiveForTouch();
    initExpandos();
    initDragDropSort();
    window.addEventListener('resize', initDragDropSort);
    $('.ui-tooltip').remove();
  };
  /**
   *
   */


  var attachEditorElements = function attachEditorElements() {
    // Cache DOM
    editor.container = $('#quicklinks');
    editor.status = editor.container.find('.col-header .quick-status'); // Lists and filters

    editor.userLinks = editor.container.find('#your-links');
    editor.staticLinks = editor.container.find('#static-links'); // Login form

    login.container = editor.container.find('#quick-login');
    login.form = login.container.find('#quick-login-form');
    login.status = login.container.find('.quick-status'); // Filter staticLinks

    editor.filterInput = editor.container.find('#quick-filter-input');
    editor.filterInput.setup_filter(change, null, clear);
  };

  var setLoginElements = function setLoginElements(user) {
    doUserIcon(user);

    if (editor.container) {
      editor.container.find('#user-tab button').data('tool', user.username ? "logout" : "show").text(user.username ? "Log out ".concat(user.username) : "Log in");

      if (login.form && login.form.hasClass('submitting')) {
        // If login form is open
        if (user.username) {
          // Saved status icon
          login.form[0].reset();
          hideDismissibles();
          setStatus(user.status, 'editorStatus');
        } else {
          setStatus(user.status, 'loginStatus', true);
        }

        login.form.removeClass('submitting');
      }
    }
  };
  /**
   * Set user icon depending on login state.
   * @param user
   */


  var doUserIcon = function doUserIcon(user) {
    var $icon = document.querySelectorAll('.quick-user-icon');
    var title = '';

    for (var i = 0; i < $icon.length; i++) {
      _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.removeClass($icon[i], 'bts');
      _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.removeClass($icon[i], 'btb');
      _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.removeClass($icon[i], 'unsaved');
      _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.removeClass($icon[i], 'saved');

      if (user.username) {
        title = "You are logged into Quick Links as ".concat(user.username, ". Your links will be saved automatically.");
        _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.addClass($icon[i], 'bts');
        _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.addClass($icon[i], 'saved');
      } else if (user.linksChanged) {
        title = "You have custom Quick Links and you are not logged in. Log in to save or retrieve your Quick Links from your Williams user account.";
        _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.addClass($icon[i], 'bts');
        _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.addClass($icon[i], 'unsaved');
      } else {
        title = "You are not logged into Quick Links. Log in to save changes.";
        _assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.addClass($icon[i], 'btb');
      }

      $icon[i].setAttribute('title', title);
    }
  };
  /**
   * Build form for adding a custom label/link.
   * @param {jQuery} $linkElement
   * @param link
   */


  var initLinkForm = function initLinkForm(_ref) {
    var $linkElement = _ref.$linkElement,
        link = _ref.link;
    closeAllLinkForms();
    var title = link.title,
        url = link.url;
    var $form = $linkElement.find('form');

    if (url && url !== 'undefined') {
      $form.find('.custom-item-url').val(url);
    }

    $form.find('.custom-item-title').val(title);
    $linkElement.addClass('editing');
  };

  var closeAllLinkForms = function closeAllLinkForms() {
    editor.userLinks.find('li.editing').removeClass('editing');
  };
  /**
   * Helper function to convert touch to click for link utility buttons.
   */


  var setActiveForTouch = function setActiveForTouch() {
    [front.userLinks, editor.userLinks, editor.staticLinks].filter(function ($links) {
      return !!$links;
    }).map(function ($links) {
      $links[0].addEventListener('touchend', function (e) {
        var $item = $(e.target).closest('.quick-item');
        $(this).find('.quick-item').removeClass('active');
        $item.toggleClass('active');
      });
    });
  };
  /**
   * Show/hide .dismissible elements
   * @param $target
   */


  var showDismissible = function showDismissible($target) {
    var $parent = $target.parent('.dismissible');
    $target.add($parent).show();
  };

  var hideDismissibles = function hideDismissibles() {
    return $('.dismissible').add('.dismissible > *').hide();
  };
  /**
   * Show and hide ajax spinners.
   * The spinner will be replaced by the loaded content.
   * @todo Fade content.
   *
   * @param locations { Array }
   */


  var showSpinners = function showSpinners(locations) {
    var spinner = $('#quicklinks-spinner-template').html();
    locations.map(function (where) {
      if (Elements.getLoc(where).length) {
        Elements.getLoc(where).html(spinner);
      }
    });
  };
  /**
   * Add Draggable/Droppable behaviors to link lists
   *
   * @requires {$}
   */


  var initDragDropSort = function initDragDropSort() {
    // Create sortable/draggable lists.
    // 'draggable' and 'dragstop' must be separate.
    if (!editor || !editor.staticLinks.find('.quick-item').length) {
      return;
    }

    editor.staticLinks.find('.quick-item').draggable({
      connectToSortable: editor.userLinks,
      helper: 'clone',
      revert: 'invalid',
      containment: editor.container.find('.quick-content')
    });
    editor.staticLinks.find('.quick-item').on('dragstart', function (event, ui) {
      editor.userLinks.children().show(); // Set height & width to match first li

      var $li = ui.helper.parent().find('li:first-child');
      ui.helper.css({
        width: $li.css('width'),
        height: $li.css('height')
      });
    });
    editor.userLinks.sortable({
      start: function start(e, ui) {
        _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit('beforeSortable', {
          item: ui.item
        }); // If this link is from the same list, create a
        // temporary attribute on the link with the original index.

        if (!ui.item.hasClass('ui-draggable')) {
          ui.item.data('original', ui.item.index());
        }
      },
      update: function update(e, ui) {
        var original = ui.item.data('original');

        if (typeof original !== 'number') {
          // We're inserting.
          _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit('afterSortable', {
            type: 'insert',
            item: ui.item
          });
        } else {
          // We're swapping.
          var current = ui.item.index();
          ui.item.removeData('original');
          _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit('afterSortable', {
            type: 'swap',
            current: current,
            original: original,
            item: ui.item
          });
        }
      }
    });
  };

  var initExpandos = function initExpandos() {
    $('#ql-expandos').expando({
      hideAnchor: true,
      singleOpen: true
      /*,
      open: 3*/

    });
  };
  /**
   * Replace mega menu with Quicklinks.
   * @param menuToggled {Boolean}
   */


  var toggleMenu = function toggleMenu(menuToggled) {
    front.menu.toggleClass('quicklinks-only', menuToggled);
  };

  var setStatus = function setStatus(message, where) {
    var noFade = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

    if (getLoc(where)) {
      if (noFade) {
        getLoc(where).html(message);
      } else {
        getLoc(where).find('.fade-out').remove();
        getLoc(where).html("<span class=\"fade-out\"> ".concat(message, " </span>"));
      }
    }
  };

  var fadeBackground = function fadeBackground(index) {
    if (index === false) {
      return;
    }

    var userLinksArr = [front.userLinks];

    if (editor.userLinks) {
      userLinksArr.push(editor.userLinks);
    }

    $(userLinksArr).each(function () {
      $(this).find('li').eq(index).removeClass('fade-background').addClass('fade-background');
    });
  };
  /**
   * Add google analytics event tracking to Landing Page links
   */


  var addGaTracking = function addGaTracking() {
    if (typeof _gaq == 'undefined') {
      // Tracking handled by Google Tag Manger (GTM)
      return;
    }

    $(document).on('click', 'li.quick-item a', function (e) {
      var label = e.target.text;
      var url = e.target.href;
      var event_label = "".concat(label, ": ").concat(url);

      _gaq.push(['_trackEvent', 'Quick Links', 'Menu Click', event_label]);
    });
    $(document).on('click', '.quick-tool', function (e) {
      var tool = e.target.getAttribute('data-tool');

      _gaq.push(['_trackEvent', 'Quick Links', 'Tool Click', tool]);
    });
  };

  return {
    getLoc: getLoc,
    toggleMenu: toggleMenu,
    attachFrontElements: attachFrontElements,
    attachEditorElements: attachEditorElements,
    showSpinners: showSpinners,
    initEditor: initEditor,
    initDragDropSort: initDragDropSort,
    initFront: initFront,
    initLinkForm: initLinkForm,
    closeAllLinkForms: closeAllLinkForms,
    setStatus: setStatus,
    showDismissible: showDismissible,
    hideDismissibles: hideDismissibles,
    setLoginElements: setLoginElements,
    fadeBackground: fadeBackground,
    addGaTracking: addGaTracking
  };
}(window.jQuery);

/***/ }),

/***/ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.links.es6.js":
/*!********************************************************************************!*\
  !*** ./web/wp-content/lib/quicklinks/assets/js/src/modules/class.links.es6.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Links": () => (/* binding */ Links)
/* harmony export */ });
/* harmony import */ var _utils_es6_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.es6.js */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/utils.es6.js");
/* harmony import */ var _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../../../assets/js/src/modules/class.events.es6.js */ "./web/wp-content/lib/assets/js/src/modules/class.events.es6.js");
/* harmony import */ var _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./class.elements.es6.js */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.elements.es6.js");
/**
 * @todo Check for duplicate links.
 */





var Links = function ($) {
  var locations = {
    locFront: null,
    locEditor: null,
    locStatic: null
  };
  var linkTypes = {
    locStatic: $('#link-static'),
    locEditor: $('#link-editor'),
    locEditorCat: $('#link-editor-cat'),
    locFront: $('#link-front'),
    locFrontCat: $('#link-front-cat')
  };
  /**
   * 1. Initialize user.
   * 2. Fetch user links.
   * 3. Convert link strings to html.
   * 4. Load into page.
   */

  var init = function init(links) {
    update(links);

    if (!locations.locStatic) {
      buildStaticLinks();
    }

    loadAll();
  };

  var update = function update(links) {
    buildUserLinks(function (where) {
      buildLinks(links, where);
    });
  };
  /**
   * Generate flexiform links
   */


  var buildStaticLinks = function buildStaticLinks() {
    return (0,_utils_es6_js__WEBPACK_IMPORTED_MODULE_0__.fetchApi)('quicklinks_get_static_links').then(function (links) {
      return buildLinks(links, 'locStatic');
    });
  };

  var loadAll = function loadAll() {
    var index = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    Object.keys(locations).filter(function (key) {
      return locations[key];
    }).map(function (where) {
      return loadLinksByLocation(where);
    });

    if (index !== false) {
      _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit('updateUserLinks', allToArray());
    }

    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit('afterLoadLinks', index);
  };

  var loadLinksByLocation = function loadLinksByLocation(where) {
    var $el = _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_2__.Elements.getLoc(where);

    if ($el && $el.length) {
      var linksHtml = locations[where].join('\n');
      var htmlEncodedLinks = htmlEntitiesEncode(linksHtml);
      var htmlDecodedLinks = htmlEntitiesDecode(htmlEncodedLinks);
      $el.html(htmlDecodedLinks);
    }
  };

  var htmlEntitiesEncode = function htmlEntitiesEncode(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  };

  var htmlEntitiesDecode = function htmlEntitiesDecode(str) {
    var txt = document.createElement('textarea');
    txt.innerHTML = str;
    return txt.value;
  };
  /**
   * Populate link arrays with HTML as per callback.
   * Editor links have extra markup for editing interface.
   * @param callback
   */


  var buildUserLinks = function buildUserLinks(callback) {
    ['locFront', 'locEditor'].map(function (where) {
      if (typeof callback === 'function') {
        callback(where);
      }
    });
  };
  /**
   * Split complete cookie string and map each HTML link to locations.
   * @param links
   * @param where
   */


  var buildLinks = function buildLinks(links, where) {
    locations[where] = [];
    links.map(function (link) {
      return addLinkToLocation(link, where);
    });
    /*Object.keys( links ).map( link => {
     const linkObj = {title: link, url: links[link]};
     addLinkToLocation( linkObj, where );
     } );*/
  };
  /**
   * Map links to specified locations.
   * @param link { Object }
   * @param where { String }
   * @param index { Number | null }
   */


  var addLinkToLocation = function addLinkToLocation(link, where) {
    var index = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    // Where to append, prepend, or insert?
    index = index === null ? locations[where].length : index; // Generate link HTML string and add to location.

    spliceLinks(where, index, buildLinkHtml(link, where));
  };
  /**
   * Construct link HTML from script template HTML
   * @param title {String}
   * @param url {String}
   * @param where {String}
   */


  var buildLinkHtml = function buildLinkHtml(_ref, where) {
    var title = _ref.title,
        url = _ref.url;
    var type = '';

    if (url === null || url === "undefined" || url === undefined || url === '') {
      type = 'Cat';
    }

    return linkTypes[where + type].html().replace('##title##', title).replace('##url##', encodeURI(url));
  };
  /**
   * Return array of link objects { title, url } for saving to server.
   * Use Front because it always exists.
   * @returns {Array}
   */


  var allToArray = function allToArray() {
    var links = [];
    locations['locFront'].map(function (link) {
      links.push(extractLink(link));
    });
    return links;
  };
  /**
   * Converts array of links into JSON compatible cookie object.
   * @returns {{}}
   */


  var linksToCookieObject = function linksToCookieObject(links) {
    var linksObj = {};
    var count = 0;
    links.map(function (_ref2) {
      var title = _ref2.title,
          url = _ref2.url;

      // Need parentheses
      if (!(title in linksObj)) {
        linksObj[(count++).toString()] = {
          title: title,
          url: url
        };
      }
    });
    return encodeURIComponent(JSON.stringify(linksObj));
  };
  /**
   *
   * @param link
   * @return {{title: *, url: *}}
   */


  function extractLink(link) {
    var title, url;
    title = $(link)[0].textContent.trim();
    var anchor = $(link).find('a');

    if (anchor.length) {
      url = anchor[0].href;
    }

    return {
      title: title,
      url: url
    };
  }
  /**
   * Converts links string to a compatible array for Links class.
   * @param links
   * @return {*}
   */


  function cookieObjectToLinks(links) {
    if (!links) {
      return false;
    }

    links = decodeURIComponent(links); // If it's not already an

    if (!isArray(links) && !isObject(links)) {
      // Is it a JSON string?
      try {
        links = JSON.parse(links);
      } // Is it legacy quicklinks with heart and spade separators
      catch (e) {
        links = legacyLinksToArray(links);
      }
    }

    if (isObject(links)) {
      links = objToArray(links);
    }

    return links;
  }

  var objToArray = function objToArray(obj) {
    var arr = [];
    Object.keys(obj).map(function (key) {
      var link = obj[key]; //{title, url};

      arr.push(link);
    });
    return arr;
  };

  var pair_sep = "\u2660"; // spade

  var name_val_sep = "\u2665"; // heart

  /**
   * Convert old-school Meerkat QL links string to array.
   * @param links
   * @return {Array}
   */

  var legacyLinksToArray = function legacyLinksToArray(links) {
    var linksArr = [];
    links.split(pair_sep).map(function (link) {
      var parts = legacyLinkToObj(link);

      if (parts.title) {
        linksArr.push(parts);
      }
    });
    return linksArr.length ? linksArr : '';
  };
  /**
   * Convert single link string into object.
   * @param link
   * @returns {{title: *, url: *}}
   */


  var legacyLinkToObj = function legacyLinkToObj(link) {
    var linkParts = link.split(name_val_sep);
    return {
      title: linkParts[0],
      url: linkParts[1]
    };
  };

  var isObject = function isObject(o) {
    return !!o && o.constructor === Object;
  };

  var isArray = function isArray(a) {
    return !!a && a.constructor === Array;
  };
  /**
   * Add or remove links (if no added) from location.
   *
   * Add: arr.splice(index, 0, added);
   * Remove: arr.splice(index, 1);
   *
   * @param where {String}
   * @param index {Number}
   * @param added {String}
   */


  var spliceLinks = function spliceLinks(where, index, added) {
    var loc = locations[where];
    return added ? loc.splice(index, 0, added) : loc.splice(index, 1);
  };
  /**
   * Remove user link at index.
   * @param index {Number}
   */


  var remove = function remove(index) {
    buildUserLinks(function (where) {
      return spliceLinks(where, index);
    });
    loadAll(index);
  };
  /**
   * Insert/Swap positions of links in locations array.
   *
   * @param index
   * @param original
   */


  var swap = function swap(_ref3) {
    var index = _ref3.index,
        original = _ref3.original;
    buildUserLinks(function (where) {
      return spliceLinks(where, index, spliceLinks(where, original)[0]);
    });
    loadAll(index);
  };
  /**
   * Append new link to quicklinks string.
   * @param link
   * @param index
   */


  var insert = function insert(_ref4) {
    var link = _ref4.link,
        index = _ref4.index;
    buildUserLinks(function (where) {
      return addLinkToLocation(link, where, index);
    });
    loadAll(index);
  };
  /**
   * Prepend new link to quicklinks string.
   *
   * @param link
   */


  var prepend = function prepend(link) {
    var index = 0;
    insert({
      link: link,
      index: index
    });
  };

  var save = function save(_ref5) {
    var link = _ref5.link,
        index = _ref5.index;
    // Insert at index.
    buildUserLinks(function (where) {
      return addLinkToLocation(link, where, index);
    }); // Remove index + 1.

    remove(index + 1);
  };

  return {
    init: init,
    update: update,
    insert: insert,
    prepend: prepend,
    remove: remove,
    swap: swap,
    loadAll: loadAll,
    save: save,
    cookieObjectToLinks: cookieObjectToLinks,
    linksToCookieObject: linksToCookieObject
  };
}(window.jQuery);

/***/ }),

/***/ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.tools.es6.js":
/*!********************************************************************************!*\
  !*** ./web/wp-content/lib/quicklinks/assets/js/src/modules/class.tools.es6.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "dont_need_this": () => (/* binding */ dont_need_this),
/* harmony export */   "Tools": () => (/* binding */ Tools)
/* harmony export */ });
/* harmony import */ var _class_cookies_es6_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./class.cookies.es6.js */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.cookies.es6.js");
/* harmony import */ var _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./class.elements.es6.js */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.elements.es6.js");
/* harmony import */ var _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../assets/js/src/modules/class.events.es6.js */ "./web/wp-content/lib/assets/js/src/modules/class.events.es6.js");
/* harmony import */ var _class_links_es6_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./class.links.es6.js */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.links.es6.js");
/* harmony import */ var _class_user_es6_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./class.user.es6.js */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.user.es6.js");
/**
 * @module Tools
 *
 * Handles interface events.
 * (Add Williams link, Add custom link/label, etc.)
 */







/**
 * This function should be removed by RollupJS.
 * Q: Does it remove jsdoc comments too?
 * A: It does!
 */

var dont_need_this = function dont_need_this() {
  return 42;
};
var Tools = function ($) {
  var init = function init() {
    registerEvents();
    doMenuDisplay(true);
  };
  /**
   * @requires window.jQuery
   */


  var registerEvents = function registerEvents() {
    $(document).on('click', '.quick-tool', function (e) {
      e.preventDefault();
      e.stopPropagation();
      doTool($(e.target));
    }); //      $( document ).on( 'submit', '#quicklinks form', e => e.preventDefault() );
  };
  /**
   * Event handlers for <element class="quick-tool" data-tool="action"/>
   * @param $tool
   *
   * @requires {Cookies}
   */


  var doTool = function doTool($tool) {
    switch ($tool.data('tool')) {
      case 'add-current-page':
        addCurrentPage();
        break;

      case 'add-link':
        addLink($tool);
        break;

      case 'add-custom':
        addCustom($tool);
        break;

      case 'delete':
        deleteLink($tool);
        break;

      case 'save-link':
        saveLink($tool);
        break;

      case 'delete-cookies':
        _class_cookies_es6_js__WEBPACK_IMPORTED_MODULE_0__.Cookies.unsetAll();
        break;

      case 'dismiss':
        _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_1__.Elements.hideDismissibles();
        break;

      case 'dismiss-forms':
        dismissLinkForms($tool);
        break;

      case 'edit-link':
        editLink($tool);
        break;

      case 'login':
        doLogin($tool);
        break;

      case 'logout':
        doLogout();
        break;

      case 'hide-menu':
        doMenuDisplay();
        break;

      case 'quick-launch':
        showOverlay();
        break;

      case 'restore-default-links':
        restoreDefaultLinks();
        break;

      case 'show':
        showDismissible($tool);
        break;

      case 'show-cookies':
        _class_cookies_es6_js__WEBPACK_IMPORTED_MODULE_0__.Cookies.logAll();
        break;
    }
  };
  /**
   * Authenticate user via LDAP
   */


  var doLogin = function doLogin($tool) {
    // Get form variables.
    var $form = $tool.parents('form');
    $form.find('.login-status').html(''); // Show spinner

    $form.addClass('submitting');
    var username = $form.find('input[name="username"]').val();
    var password = $form.find('input[name="password"]').val();
    var method = $form.find("input:radio[name='method']:checked").val(); //    const save = $form.find( 'input[name="save"]' ).is( ':checked' ) ? 'save' : 'init';

    if (username && password) {
      _class_user_es6_js__WEBPACK_IMPORTED_MODULE_4__.User.setProperties({
        'status': method
      });
      var user = _class_user_es6_js__WEBPACK_IMPORTED_MODULE_4__.User.getUser();
      _class_user_es6_js__WEBPACK_IMPORTED_MODULE_4__.User.doLogin({
        username: username,
        password: password,
        user: user
      });
    } else {
      _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_1__.Elements.setStatus('Please enter both a user and a password', 'loginStatus', true);
      $form.removeClass('submitting');
    }
  };

  var doLogout = function doLogout() {
    return _class_user_es6_js__WEBPACK_IMPORTED_MODULE_4__.User.init({
      'status': 'You are now logged out.',
      'username': false
    });
  };

  var dismissLinkForms = function dismissLinkForms() {
    return _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_1__.Elements.closeAllLinkForms();
  };
  /**
   * Show Dismissible
   */


  var showDismissible = function showDismissible($tool) {
    _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_1__.Elements.hideDismissibles();
    _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_1__.Elements.showDismissible($($tool.data('target')));
  };

  var deleteCookies = function deleteCookies() {
    if (confirm('All cookies will be deleted. Continue?')) {
      _class_cookies_es6_js__WEBPACK_IMPORTED_MODULE_0__.Cookies.unsetAll();
      _class_user_es6_js__WEBPACK_IMPORTED_MODULE_4__.User.update({
        'quicklinks': false,
        'username': false,
        'linksChanged': false
      });
    }
  };

  var restoreDefaultLinks = function restoreDefaultLinks() {
    if (confirm('All customized links will be deleted. Continue?')) {
      _class_user_es6_js__WEBPACK_IMPORTED_MODULE_4__.User.init({
        'quicklinks': false,
        'linksChanged': false,
        'status': 'restore'
      });
    }
  };
  /**
   * Retrieve link title and url.
   * @param $tool
   * @returns {{$linkElement, url, title}}
   */


  var getLinkData = function getLinkData($tool) {
    var $linkElement = $tool.parents('li.quick-item');
    var title = $linkElement.find('.title').text();
    var url;

    if (!$linkElement.hasClass('quick-cat')) {
      url = $linkElement.find('.bt-external-link')[0].href;
    }

    var link = {
      title: title,
      url: url
    };
    return {
      $linkElement: $linkElement,
      link: link
    };
  };

  function getFormData($tool) {
    var link;
    var $form = $tool.parents('form');
    var title = $form.find('[name="custom-item-title"]').val();

    switch ($form.find('[name="custom-form"]').val()) {
      case 'link':
        var url = $form.find('[name="custom-item-url"]').val();
        link = validateForm(title, url);
        break;

      case 'divider':
        link = validateForm(title);
    }

    if (link) {
      $form[0].reset();
      return link;
    }

    return false;
  }
  /**
   * Check for existence of title, and correctly-formed url if this is a link.
   * @param title
   * @param url
   * @return {boolean}
   */


  var validateForm = function validateForm(title) {
    var url = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

    if (title === '') {
      title = prompt('Please enter a title');

      if (!title) {
        return false;
      }
    }

    if (url !== null) {
      if (url === '') {
        url = prompt('Please enter a URL');

        if (!url) {
          return false;
        }
      }

      if (url && url.indexOf("://") === -1) {
        // format url if they didn't
        url = 'http://' + url;
      }
    }

    url = encodeURI(url);
    return {
      title: title,
      url: url
    };
  };
  /**
   * Bookmark.
   *
   * @requires {Links}
   */


  var addCurrentPage = function addCurrentPage() {
    var title = document.title;
    var url = window.location.href;
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__.Events.emit('prepend', {
      title: title,
      url: url
    });
  };
  /**
   * @requires {Events}
   * @param $tool
   */


  var saveLink = function saveLink($tool) {
    var link = getFormData($tool);
    var index = $tool.parents('li').index();
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__.Events.emit('save', {
      link: link,
      index: index
    });
  };
  /**
   * Populate link edit form.
   * @requires {Events}
   * @param $tool
   */


  var editLink = function editLink($tool) {
    return _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__.Events.emit('edit', getLinkData($tool));
  };
  /**
   * Process custom link and divider submissions.
   * @requires {Events}
   * @param $tool
   */


  var addCustom = function addCustom($tool) {
    return _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__.Events.emit('prepend', getFormData($tool));
  };
  /**
   * Add Williams link using + button.
   * @requires {Events}
   * @param $tool
   */


  var addLink = function addLink($tool) {
    var _getLinkData = getLinkData($tool),
        link = _getLinkData.link;

    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__.Events.emit('prepend', link);
  };
  /**
   * @requires {Links}
   * @param $tool
   */


  var deleteLink = function deleteLink($tool) {
    var $item = $tool.parents('.quick-item').hide();
    var index = $item.index();
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__.Events.emit('remove', index);
  };
  /**
   * Hide or show mega menu links.
   *
   * @param isInit
   *
   * @requires window.jQuery
   * @requires {Elements}
   * @requires {Cookies}
   */


  var doMenuDisplay = function doMenuDisplay(isInit) {
    var menuToggled = _class_cookies_es6_js__WEBPACK_IMPORTED_MODULE_0__.Cookies.get('menu') === 'true';

    if (!isInit) {
      if (!menuToggled) {
        menuToggled = true;
        _class_cookies_es6_js__WEBPACK_IMPORTED_MODULE_0__.Cookies.set('menu', 'true');
      } else {
        menuToggled = false;
        _class_cookies_es6_js__WEBPACK_IMPORTED_MODULE_0__.Cookies.unset('menu');
      }

      $('html, body').animate({
        scrollTop: $('body').offset().top
      }, 500);
    }

    _class_elements_es6_js__WEBPACK_IMPORTED_MODULE_1__.Elements.toggleMenu(menuToggled);
  };
  /**
   * Show QL editing interface in lightbox overlay
   *
   * @requires window.jQuery
   * @requires {Events}
   * @requires {Elements}
   */


  var showOverlay = function showOverlay() {
    $.featherlight(myAjax.siteurl + '/ql/?iframe', {
      afterContent: function afterContent() {
        var $self = this;
        var $instance = $self.$instance.find('.' + $self.namespace + '-content'); // set our custom close icon

        $instance.find('.featherlight-close').html('<span class="bts bt-times"></span>'); // init and load editor interface and links

        _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_2__.Events.emit('afterShowOverlay');
      },
      afterOpen: function afterOpen() {
        // isIos() is in featherlight-config
        if (!isIos()) {
          $('html').css('overflow', 'hidden');
        }
      },
      afterClose: function afterClose() {
        if (!isIos()) {
          $('html').css('overflow', 'auto');
        }
      }
    });
  };

  return {
    init: init,
    registerEvents: registerEvents,
    doTool: doTool,
    deleteCookies: deleteCookies,
    restoreDefaultLinks: restoreDefaultLinks,
    addCurrentPage: addCurrentPage,
    addLink: addLink,
    editLink: editLink,
    validateForm: validateForm,
    deleteLink: deleteLink,
    doMenuDisplay: doMenuDisplay,
    showOverlay: showOverlay
  };
}(window.jQuery);

/***/ }),

/***/ "./web/wp-content/lib/quicklinks/assets/js/src/modules/class.user.es6.js":
/*!*******************************************************************************!*\
  !*** ./web/wp-content/lib/quicklinks/assets/js/src/modules/class.user.es6.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "User": () => (/* binding */ User)
/* harmony export */ });
/* harmony import */ var _utils_es6_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.es6.js */ "./web/wp-content/lib/quicklinks/assets/js/src/modules/utils.es6.js");
/* harmony import */ var _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../../../assets/js/src/modules/class.events.es6.js */ "./web/wp-content/lib/assets/js/src/modules/class.events.es6.js");
/**
 * @module User Singleton
 *
 * Holds state of login, personal links, etc.
 */




/**
 *
 * @type {{defaultChanged, getUser, getProperty, setProperties, init, save, doLogin, doLogout}}
 */

var User = function () {
  var user = {
    status: 'init',
    username: false,
    isSuper: false,
    quicklinks: false,
    linksChanged: false
  };
  /**
   *
   * @param action
   */

  var doEmit = function doEmit(action) {
    //console.log( 'Status:', getProperty( 'status' ) );
    _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit(action, getProperty('quicklinks'));
  };
  /**
   * Send user to server.
   * If the user has links in the db, get them.
   * If not, we'll use the cookie links.
   * If still not, fall back on the default.
   *
   * @requires {Events}
   * @requires {fetchApi}
   */


  var init = function init(user) {
    setProperties(user);
    doFetch().then(function (user) {
      if (user) {
        setProperties(user);
        doEmit('afterInitUser');
      }
    });
  };
  /**
   *
   * @param user
   * @param login
   */


  var update = function update(user) {
    var login = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    setProperties(user); // Save if logged in.

    if (login) {
      doEmit('afterInitUser');
    } else if (getProperty('username')) {
      save();
    }
  };
  /**
   *
   */


  var save = function save() {
    setProperty('status', 'put');
    doFetch().then(function (user) {
      setProperties(user);
      _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit('afterSaveUser', user);
    });
  };
  /**
   *
   */


  var doFetch = function doFetch() {
    return (0,_utils_es6_js__WEBPACK_IMPORTED_MODULE_0__.fetchApi)('quicklinks_update_user', user);
  };
  /**
   *
   * @param login
   */


  var doLogin = function doLogin(login) {
    return (0,_utils_es6_js__WEBPACK_IMPORTED_MODULE_0__.fetchApi)('quicklinks_login', login).then(function (user) {
      if (!user.username) {
        _assets_js_src_modules_class_events_es6_js__WEBPACK_IMPORTED_MODULE_1__.Events.emit('afterLoginFail', user);
        return;
      }

      update(user, true);
    });
  };
  /**
   * Set all user properties.
   *
   * @param user
   * @returns {boolean}
   * @private
   * @requires {Events}
   */


  var setProperties = function setProperties(user) {
    Object.keys(user).map(function (key) {
      setProperty(key, user[key]);
    });
  };
  /**
   * Return _user object.
   *
   * @returns {{logged: boolean, status: string, user: string, isSuper: boolean, quicklinks: string}}
   * @private
   */


  var getUser = function getUser() {
    return user;
  };
  /**
   * Return single user property.
   *
   * @param key
   * @returns {*}
   * @private
   */


  var getProperty = function getProperty(key) {
    return user[key];
  };
  /**
   * Return single user property.
   *
   * @param key
   * @param value
   * @returns {*}
   * @private
   */


  var setProperty = function setProperty(key, value) {
    return user[key] = value;
  };

  return {
    init: init,
    update: update,
    getUser: getUser,
    getProperty: getProperty,
    setProperties: setProperties,
    doLogin: doLogin
  };
}();

/***/ }),

/***/ "./web/wp-content/lib/quicklinks/assets/js/src/modules/utils.es6.js":
/*!**************************************************************************!*\
  !*** ./web/wp-content/lib/quicklinks/assets/js/src/modules/utils.es6.js ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "fetchApi": () => (/* binding */ fetchApi)
/* harmony export */ });
/* harmony import */ var whatwg_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! whatwg-fetch */ "./node_modules/whatwg-fetch/fetch.js");
/**
 * @requires 'url-search-params-polyfill'
 * @requires 'whatwg-fetch'
 * @param action {String}
 * @param obj {Object}
 * @return Promise
 *
 * fetchApi( 'ajax_action', user )
 * .then( function ( responseObj ) {
 *    // This could be another fetchApi call.
 *    someCallback( responseObj );
 *  } )
 *  .then( function () {
 *    someOtherCallback();
 *  } );
 *
 */
 //import 'url-search-params-polyfill';


var fetchApi = function fetchApi(action) {
  var obj = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  //console.log( 'Begin fetch:', action, obj );
  var payload = {
    action: action,
    obj: JSON.stringify(obj)
  };
  var data = new FormData(); //data.append( 'json', JSON.stringify( payload ) );

  Object.keys(payload).map(function (key) {
    data.append(key, payload[key]);
  });
  var init = {
    method: 'POST',
    body: data,
    credentials: 'same-origin'
  };
  /* const params = new URLSearchParams();
     Object.keys( payload ).map( key => {
   params.set( key, payload[key] )
   } );*/

  /*const request = new Request( myAjax.ajaxurl,  {
   method: 'POST',
   body: params,
   credentials: 'same-origin'
   });*/
  //return fetch( request )

  return fetch(myAjax.ajaxurl, init).then(function (response) {
    if (response.ok) {
      //console.log( 'End fetch:', action );
      return response.json();
    }
  }) // Uncomment to test response.json. This will cause the Promise to return undefined.
  //.then( data => console.log( JSON.stringify( data ) ) )
  .catch(function (error) {
    console.log('Fetch error:', error.message);
    return false;
  });
};

/***/ }),

/***/ "./web/wp-content/themes/meerkat16/assets/src/js/main.js":
/*!***************************************************************!*\
  !*** ./web/wp-content/themes/meerkat16/assets/src/js/main.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _lib_assets_js_src_expando_tabs_es6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../lib/assets/js/src/expando_tabs.es6 */ "./web/wp-content/lib/assets/js/src/expando_tabs.es6.js");
/* harmony import */ var _modules_quad_es6__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modules/quad.es6 */ "./web/wp-content/themes/meerkat16/assets/src/js/modules/quad.es6.js");
/* harmony import */ var _modules_gallery_es6__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modules/gallery.es6 */ "./web/wp-content/themes/meerkat16/assets/src/js/modules/gallery.es6.js");
/* harmony import */ var _modules_gallery_es6__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_modules_gallery_es6__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _lib_quicklinks_assets_js_src_app_es6__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../lib/quicklinks/assets/js/src/app.es6 */ "./web/wp-content/lib/quicklinks/assets/js/src/app.es6.js");






!function ($) {
  /**
   * Email address wrap on @ and . characters.
   *
   * http://stackoverflow.com/questions/27419127/can-i-break-the-line-at-special-characters-using-css
   */
  $('a[href^="mailto:"]').each(function () {
    var content = $(this).text().trim();
    var replaced = content.replace('@', '&#x200b;@').replace('.', '&#x200b;.');
    $(this).html(replaced);
  }); //--- Gravity Forms Likert Scale - requires .gf_likert styles (in forms.css) ---//

  $(window).load(function () {
    if ($(".gf_likert ul.gfield_radio li input").is(":checked")) {
      $(".gf_likert ul.gfield_radio li input:checked").parent().addClass("mychoice");
    }
  }); // add some extra classes and markup to make our likert-style radio choices

  $(".gf_likert ul.gfield_radio li:first-child").addClass("likert-first");
  $(".gf_likert ul.gfield_radio li:last-child").addClass("likert-last");
  $(".gf_likert ul.gfield_radio li input").addClass("likert-choice");
  $(".gf_likert ul.gfield_radio li label").wrap("<div class='likert-label'></div>"); // add space to pad label.

  $('.likert-label label:empty').html('&nbsp;'); // add a hover state

  $(".gf_likert ul.gfield_radio li").hover(function () {
    $(this).addClass("likert-hover");
  }, function () {
    $(this).removeClass("likert-hover");
  }); // add a selected class to the parent list item

  $(".likert-choice").change(function () {
    if ($(this).is(":checked")) {
      $(this).parent().parent().parent().find(".mychoice").removeClass("mychoice");
      $(this).parent().addClass("mychoice");
    }
  }); //--- GOOGLE SEARCH RESULTS ---//

  (function () {
    var cx = '010875475703748055486:qrlq8khyohw';
    var gcse = document.createElement('script');
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + '//www.google.com/cse/cse.js?cx=' + cx;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gcse, s);
  })(); //---- IPAD ----//


  var $is_ipad = navigator.userAgent.match(/iPad/i) != null;

  if ($is_ipad) {
    $('body').addClass('ipad');
  }

  var magSearchText = 'Search Magazine';

  if (location.href.indexOf('alumni-news') > 0) {
    magSearchText = 'Search Alumni News';
  }

  $('body.meerkat-magazine #s').val(magSearchText).blur(function () {
    if (this.value == '') {
      this.value = magSearchText;
    }
  }).focus(function () {
    if (this.value == magSearchText) {
      this.value = '';
    }
  }); //---- tab filters
  // display correct tab on page load & on hash change

  if (location.hash) {
    set_button_tab(location.hash);
  } else {
    set_button_tab(false);
  }

  $(window).on('hashchange', function () {
    set_button_tab(location.hash);
  }); // click action for tab

  $('.button-tabs > a').click(function (e) {
    var $target = $(this).attr('data-target');
    set_button_tab($target);
    e.preventDefault();
  });

  function set_button_tab($target) {
    // get rid of hash mark from things coming from url
    if ($target) {
      $target = $target.replace('#', '');
    } // hide all content & unselect all tabs


    $('.button-tabs').find('.meerkat-tab').hide();
    $('.button-tabs > a').removeClass('selected-button');

    if ($target) {
      // reveal & highlight active tab
      var $target_div = $('.button-tabs').find('div[data-target="' + $target + '"]');

      if ($target_div.length == 0) {
        // non-existant tab called, show default
        $('.button-tabs').find('.meerkat-tab:first-child').show();
        $('.button-tabs > a:first-child').addClass('selected-button');
      } else {
        $target_div.show();
        $('.button-tabs > a[data-target="' + $target + '"]').addClass('selected-button');
      }
    } else {
      // show & highlight default (first) tab
      $('.button-tabs').find('.meerkat-tab:first-child').show();
      $('.button-tabs > a:first-child').addClass('selected-button');
    }
  } //---- category filters


  $('.category-filter').click(function (e) {
    var $desired_cat = $(this).attr('data-slug');
    var $show_all = $(this).hasClass('show-all'); // apply selected filter styles

    $(this).siblings().removeClass('selected-filter');
    $(this).addClass('selected-filter'); // hide all posts on cat page that aren't of this type
    // category view

    $('body.category #content').find('div.status-publish').each(function () {
      if ($show_all || $(this).hasClass('category-' + $desired_cat)) {
        $(this).show();
      } else {
        $(this).hide();
      }
    }); // page view (used as a mk_calendar shortcode on a page)

    $('body.page #cal-grid').find('div.event-container').each(function () {
      if ($show_all || $(this).hasClass($desired_cat)) {
        $(this).show();

        if ($('html').hasClass('ui-mobile')) {
          $(this).parents('.cal-day').show();
        }
      } else {
        $(this).hide();

        if ($('html').hasClass('ui-mobile')) {
          if (!$(this).parents('.cal-day').find('div.event-container:visible').length) {
            $(this).parents('.cal-day').hide();
          }
        }
      }
    });
  }); //---- MEDIA ----//
  // animated slideshow stop-go controls

  $('.cycle-container .cycle-control').click(function (e) {
    // get current state
    var $paused = $(this).parent().find('.meerkat-image-gallery').is('.cycle-paused');

    if ($paused) {
      // go (show pause button)
      $(this).parent().find('.meerkat-image-gallery').cycle('resume');
      $(this).removeClass('cycle-resume');
      $(this).addClass('cycle-pause');
    } else {
      // stop (show go button)
      $(this).parent().find('.meerkat-image-gallery').cycle('pause');
      $(this).removeClass('cycle-pause');
      $(this).addClass('cycle-resume');
    }
  }); // gallery widget image sizing

  $('#sidebar .gallery_widget_layout').closest('.widget').addClass('widget_meerkat_gallery');
  var $gallery_widgets = $('#sidebar .widget_meerkat_gallery'); // window resize events

  $(window).resize(function () {
    mk_gallery_widget_size();
    mk_filmstrip_gallery_size();
  });
  $gallery_widgets.ready(function () {
    mk_gallery_widget_size();
  });

  function mk_gallery_widget_size() {
    // responsive sizing of images in gallery widget
    var $pad = 8;
    var $border = 2;
    $gallery_widgets.each(function () {
      var $gallery_container = $(this).find('.meerkat-gallery');
      var $gallery_container_limit = $gallery_container.attr('limit');
      var $gallery_widget_w = $(this).width() - 1;
      var $gall_item_w = ($gallery_widget_w - $pad * 3) / 2;
      var $gall_item_w = Math.floor($gall_item_w) - $border;
      $(this).find('img').each(function () {
        $(this).width($gall_item_w + 'px');
        $(this).height($gall_item_w + 'px');
      });
      var $rows = $gallery_container_limit / 2;
      var $h = $gall_item_w * $rows + $rows * $pad + $rows * $border;
      $gallery_container.height($h + 'px');
    });
  } // Video Fancybox
  // video pops up in fancybox


  var $typeVideo = $('.type-video,.fancybox-video');
  $typeVideo.attr('rel', 'media-gallery').attr('data-featherlight', 'iframe').featherlight({
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
      title: {
        type: 'inside'
      }
    }
  });
  $typeVideo.not(":has(img)").addClass('no-img'); //---- GALLERY FILMSTRIP ----//

  $('.gallery-filmstrip').ready(function () {
    mk_filmstrip_gallery_size();
  }); // default caption

  $('.gallery-filmstrip').each(function () {
    var $first_caption = $(this).find('.strip-container a:eq(0) img').attr('alt');
    $(this).find('.filmstrip-caption').html($first_caption);
  }); // click or hover to change large picture

  $('.gallery-filmstrip .strip-container a').click(function (e) {
    filmstrip_select_pic($(this));
  });
  $('.gallery-filmstrip .strip-container a').hover(function (e) {
    filmstrip_select_pic($(this));
  });

  function mk_filmstrip_gallery_size() {
    // responsive sizing of images in filmstrip gallery
    var $filmstrip = $('.gallery-filmstrip');
    var $backdrop = $filmstrip.find('.filmstrip-backdrop');
    var $img_w = $backdrop.attr('data-img-width');
    var $avail_space = $filmstrip.width(); // subtract padding

    $avail_space = $avail_space - 30;

    if ($img_w < $avail_space) {
      $backdrop.css({
        'width': $img_w,
        'margin-right': 'auto'
      });
    } else {
      // shrink to fit!
      $backdrop.css({
        'width': '95%'
      });
    }
  } // clicking/hovering on preview thumb enlarges is


  function filmstrip_select_pic($pic_link) {
    var $fullpic = $pic_link.attr('fullpic');
    var $caption = $pic_link.find('img').attr('alt');
    var $filmstrip = $pic_link.closest('.gallery-filmstrip');
    $filmstrip.find('.filmstrip-current img').attr('src', $fullpic);
    $filmstrip.find('.filmstrip-caption').html($caption);
  }

  var $filmstrip_hover = false;
  var $filmstrip_click = false; // next/prev arrows scrolls preview container

  $('.gallery-filmstrip .filmstrip-nav').click(function (e) {
    $filmstrip_click = true;
    scroll_filmstrip_preview($(this));
  });
  $('.gallery-filmstrip .filmstrip-nav').hover(function () {
    if ($is_ipad) {
      return;
    }

    $filmstrip_hover = true;
    scroll_filmstrip_preview($(this));
  }, function () {
    if ($is_ipad) {
      return;
    }

    $filmstrip_hover = false;
    scroll_filmstrip_preview($(this));
  });

  function scroll_filmstrip_preview($strip) {
    // hover is recursive, we use a global var $filmstrip_hover to bail
    if (!$filmstrip_hover && !$filmstrip_click) {
      return;
    } // calculate some dimensions


    var $strip_pics = $strip.parent().find('.strip-pics');
    var $num_pics = $strip_pics.find('img').size();
    var $unit_w = 110; // 1 pic plus margin-right

    var $full_w = $num_pics * $unit_w;
    var $nav_w = 90; // next/prev arrows + margin

    var $big_pic_w = $strip_pics.closest('.gallery-filmstrip').find('.filmstrip-current img').width();
    var $view_w = $big_pic_w - $nav_w; // fiture out which direction we're going, and if we're out of bounds

    var $cur_left = parseInt($strip_pics.css('left'));
    var $direction = '-=';
    var $out_of_bounds = false;

    if ($strip.hasClass('filmstrip-prev')) {
      $direction = '+=';

      if ($cur_left >= 0) {
        // don't allow previous if we're at the first pic
        $out_of_bounds = true;
      }
    } else {
      if (Math.abs($cur_left) + $view_w >= $full_w) {
        // don't allow next if it would take you to blank space
        $out_of_bounds = true;
      }
    }

    var $increment = 10;

    if ($filmstrip_click) {
      // click advances full pic
      $increment = $unit_w;
    }

    $filmstrip_click = false; // prevent recursion on click
    // do the actual animation

    if (!$out_of_bounds) {
      $strip_pics.animate({
        'left': $direction + $increment
      }, 40, function () {
        scroll_filmstrip_preview($strip);
      });
    }
  } //---- PRINTFRIENDLY ----------//


  $('.printfriendly > a').click(function (e) {
    $('.wms-details .wms-summary').each(function () {
      $(this).parent().addClass('expanded');
      $(this).siblings('.summary-detail').show();
    });
    window.print();
  }); //---- PAGE FORMAT SUPPORT ----//

  $('#content:has(".post-content.wide")').addClass('wide');
  $('#content:has(".post-content.mediawall")').addClass('mediawall'); //---- PAGES/CUSTOM MENUS WIDGET ----//
  // menus with children get an icon hinting at more content

  var $menus = $('#sidebar').find('ul.menu');
  $menus.find('li.depth-3').prev('li.depth-2').addClass('has-children').append('<span class="menu-arrow"></span>');
  $menus.find('li.depth-2').prev('li.depth-1').addClass('has-children').append('<span class="menu-arrow"></span>');
  $menus.find('li.depth-1').prev('li.top-item').addClass('has-children').append('<span class="menu-arrow"></span>'); // collapse all submenus

  var $sub_items = $menus.find('li.sub-item');
  $sub_items.hide(); // clicking on parent menu items (li, not link itself) toggles submenu items

  var $parent_items = $menus.find('li.has-children'); // clicking on link does not trigger toggle

  $menus.find('a').click(function (e) {
    e.stopPropagation();
  }); // toggle menu children open/closed

  $parent_items.click(function (e) {
    toggle_children($(this));
  }); // expanding current menu item & ancestors

  $menus.each(function () {
    show_menu_context($(this));
  });

  function show_menu_context($menu) {
    // expand correct menu items to show the context of current page/item
    // show children of this item
    var $curr_item = $menu.find('.current-menu-item');
    toggle_children($curr_item); // current menu ancestor class not properly applied to category menu item
    // parents, do it (note: this does not work at all depths)

    if ($curr_item.hasClass('menu-item-object-category')) {
      var $curr_depth = $curr_item.attr('data-depth');

      if ($curr_depth > 0) {
        $curr_item.prevUntil('li.top-item').prev().addClass('current-menu-ancestor');
      }
    } // expand ancestry


    var $ancestors = $menu.find('.current-menu-ancestor');
    $ancestors.each(function () {
      toggle_children($(this));
    });
  }

  function toggle_children($item) {
    // toggle immediate "children" of this parent item
    if (!$item.hasClass('has-children')) {
      return;
    }

    var $depth = $item.attr('data-depth');
    var $next_depth = parseInt($depth) + 1;

    if ($item.hasClass('expanded-parent')) {
      // hide
      $item.nextUntil('li[data-depth="' + $depth + '"]').hide();
      $item.removeClass('expanded-parent'); // remove expanded parent icon from "children" too

      $item.nextUntil('li[data-depth="' + $depth + '"]', 'li.expanded-parent').removeClass('expanded-parent');
    } else {
      // show only immediate "children" but not grandchilden, etc.
      // nextUntil param 1: items up to but not including next item of same level as this one
      // nextUntil param 2: match only items that are 1 level deeper than this one
      $item.nextUntil('li[data-depth="' + $depth + '"]', 'li[data-depth="' + $next_depth + '"]').show();
      $item.addClass('expanded-parent');
    }
  } //---- EDIT WIDGET LINKS ----//


  $('.widget:not(.widget_nav_menu) a.edit-me').each(function (index) {
    var $widget = $(this).parents('.widget'); // To skip rule, apply '.widgetized_area' class to widget

    if (!$widget.hasClass('widgetized_area')) {
      // modify the edit widget links to make them use the widget's id
      var $widget_id = $widget.attr('id');
      $(this).attr('href', myAjax.siteurl + '/wp-admin/widgets.php?widget=' + $widget_id);
    }
  }); //---- RSS WIDGETS ----//
  // use our own rss icon at the bottom

  $('.widget_rss').each(function () {
    var rss_url = $(this).find('.widgettitle a:first-child').attr('href') || $(this).find('.title a:first-child').attr('href'); // http://communications.williams.edu/category/news-releases/feed/

    var site_url = $(this).find('.widgettitle a:last-child').attr('href') || $(this).find('.title a:last-child').attr('href'); // for wp category feeds, be "smarter" than the wp widget that just sends you to the top level site

    if (typeof rss_url !== 'undefined' && rss_url.indexOf('.williams.edu') !== -1 && rss_url.indexOf('/category/') !== -1) {
      var url_bits = rss_url.split('/');
      var cat_name = '';

      for (var i = 0; i < url_bits.length; i++) {
        if (url_bits[i] == 'category') {
          cat_name = url_bits[i + 1];
          break;
        }
      }

      site_url += 'category/' + cat_name + '/'; // reset title url

      $(this).find('.widgettitle a.rsswidget').attr('href', site_url);
    }

    $(this).find('.widgettitle a.rsswidget:first-child').remove();
    $(this).find('.title a.rsswidget:first-child').remove();
    var rss_icon = '<div class="wms-cal-rss"><a href="' + rss_url + '"><div class="sprite icon-16 rss"></div>Subscribe</a></div>';
    var widgetTitle = $(this).find('.widgettitle a').text();
    var site_link = '<a class="wms-cal-link" href="' + site_url + '">More ' + widgetTitle + ' &raquo;</a>';
    $(this).append(rss_icon + site_link);
  }); //---- CATEGORY POSTS WIDGET ----//
  // assign a marker class to category posts widgets that display ONLY the title, and style it like a menu

  $('#sidebar').find('.widget_cat_loop_simple ul.post-loop-wrap').each(function () {
    var $extras = $(this).find('li > *:not("p.post-title")');

    if ($extras.length == 0) {
      $(this).closest('.widget_cat_loop_simple').addClass('ultra-simple-cat');
    } // if this is the current page, highlight it


    $(this).find('li').each(function () {
      var $post_url = $(this).find('a').attr('href');

      if ($post_url == location.href) {
        $(this).addClass('current-menu-item');
      }
    });
  }); //---- LOCALIST CALENDAR ----//
  // localist markup is not human readable, add some classes so we don't go crazy

  $('div#lw').addClass('localist-outer-wrapper');
  $('div.lw').addClass('localist-inner-wrapper');
  $('ul#lwe').addClass('localist-events');
  $('ul#lwe > li.lwe').addClass('localist-event');
  $('div.lwn').addClass('localist-basic-info');
  $('span.lwn0').addClass('localist-date');
  $('div.lwn > a').addClass('localist-title');
  $('div.lwd').addClass('localist-details');
  $('span.lwi0').addClass('localist-thumb-container');
  $('span.lwi0 > a').addClass('localist-thumb-link');
  $('span.lwi0 > a > img').addClass('localist-thumb-img');
  $('div.lwl').addClass('localist-where');
  $('span.lwl0').addClass('localist-location');
  $('div.lwl > a').addClass('localist-venue');
  $('.localist-details').contents().filter(function () {
    return this.nodeType == 3;
  }).wrap('<div class="cal-rollover-bot" />');
  $('.localist-details').wrap('<div class="cal-rollover" />');
  var $dow_names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  var $is_math = location.href.substring(0, 12) == 'http://math.';
  $('.localist-event').each(function (index) {
    // for each event
    // duplicate event name & date so it can show in the rollover too
    var $e_name = $(this).find('.lwn').clone().addClass('cal-rollover-top');
    $(this).find('.cal-rollover .lwi0').after($e_name); // date calculations

    var $date_html = $(this).find('.lwn0:first');
    var $date = jQuery.trim($date_html.text());
    var $date_copy = $date; // save for later

    var $date_bits = $date.split(' '); // $date_bits = ["Sep", "25", "", "7pm"...]

    var $mon = $date_bits[0].toLowerCase();
    var $day = $date_bits[1].replace(',', '');
    var $time;

    if ($date_bits[2] != "") {
      $time = $date_bits[2];
    } else {
      $time = $date_bits[3];
    }

    var $is_date_span = false;

    if ($date_bits.length > 4) {
      $is_date_span = true;
    } // calculate day of week. year is not included in localist feed :<


    var $today_obj = new Date();
    var $cur_year = $today_obj.getFullYear();
    var $cur_mon = $today_obj.getMonth();
    var $event_date_obj = new Date($mon + " " + $day + ", " + $cur_year);
    var $event_mon = $event_date_obj.getMonth(); // check to see if we're crossing into the next year

    if ($event_mon < $cur_mon) {
      // yup, increment year
      $event_date_obj.setFullYear($cur_year + 1);
    }

    var $event_dow = $dow_names[$event_date_obj.getDay()]; // move datetime to after title

    var $roll_date = $(this).find('.cal-rollover-top span.lwn0').prepend($event_dow + ', ').remove();
    $(this).find('.cal-rollover-top').append($roll_date); // add month sprite

    var $all_html = '<div class="localist-fancy-date">' + '<div class="sprite icon-month ' + $mon + '"></div>' + '<div class="day-big">' + $day + '</div>';
    $(this).find('.lwn:first').prepend($all_html); // move location into rollover

    var $lwl = $(this).find('.lwl').remove();
    $(this).find('.cal-rollover .lwn a').after($lwl); // replace date/time in main list with just time

    $date_html.remove();

    if ($is_math) {
      if ($(this).closest('#content').length) {
        // copy thumb & link to main display
        var $math_thumb = $(this).find('.localist-thumb-container');
        $(this).find('.localist-basic-info').prepend($math_thumb); // copy day of week into main display

        var $math_dow = '<div class="math-cal-dow">' + $event_dow + '</div>';
        $(this).find('.localist-fancy-date').append($math_dow); // add time of day to main display

        var $math_time = '<span class="math-cal-time">' + $time + '</span>: ';
        $(this).find('.localist-fancy-date').next().prepend($math_time);
      }
    } // add more link


    $url = $(this).find('.lwn > a').attr('href');
    var $more = '<a class="more" href="' + $url + '">[ more ]</a>';
    $(this).find('.cal-rollover-bot').append($more); // fix thumb image url - they started giving us dinky ones :<

    var $thumb = $(this).find('.cal-rollover img.localist-thumb-img');
    var $too_small = $thumb.attr('src');
    var $bigger = $too_small.replace("/small/", "/big/");
    $thumb.attr('src', $bigger);
  }); //add classes to links that wrap images

  $('a:has(img)').addClass('image-link'); // SMOOTH SCROLL TO INTERNAL LINKS

  var $scroll_root = $('html, body');
  $('#content').find('a[href^=#]').not('.filter a').click(function () {
    // don't target external or jquery mobile links
    var href = $.attr(this, 'href');

    if (href.length > 1) {
      $scroll_root.animate({
        scrollTop: $(href).offset().top
      }, 500, function () {// Uncomment next line to add hash to URL
        //window.location.hash = href;
      });
      return false;
    }
  }); //autofocus on search box on /search

  jQuery(document).ready(function () {
    if (jQuery('body').is('.twig-search')) {
      setTimeout(function () {
        jQuery(".searchui-incontent .wms-navbox-input").focus();
        console.log("working");
      }, 3000);
    }
  });
}(window.jQuery);

/***/ }),

/***/ "./web/wp-content/themes/meerkat16/assets/src/js/modules/gallery.es6.js":
/*!******************************************************************************!*\
  !*** ./web/wp-content/themes/meerkat16/assets/src/js/modules/gallery.es6.js ***!
  \******************************************************************************/
/***/ (() => {



!function () {
  var wp = wp || null;

  if (!wp || !wp.media) {
    return false;
  }

  var media = wp.media; // Wrap the render() function to append controls

  media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
    render: function render() {
      //alert('this here');
      media.view.Settings.prototype.render.apply(this, arguments); // Append the custom template

      this.$el.append(media.template('custom-gallery-setting')); // Save the setting

      media.gallery.defaults.size = 'thumbnail';
      this.update.apply(this, ['size']);
      return this;
    }
  });
}();

/***/ }),

/***/ "./web/wp-content/themes/meerkat16/assets/src/js/modules/quad.es6.js":
/*!***************************************************************************!*\
  !*** ./web/wp-content/themes/meerkat16/assets/src/js/modules/quad.es6.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _lib_assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../../lib/assets/js/src/common.es6 */ "./web/wp-content/lib/assets/js/src/common.es6.js");


 //---- QUAD/Masonry ----//

/**
 * Example:
 <div class="quad-container">
 <figure class="quad-image landscape ratio_4_3">
 <a href="http://google.com" title="Overlay">
 <div class="quad-inner" style="background-image: url(http://library.local.williams.edu/files/2017/05/galleries-ba1e52147cea983d482f6d4cddb6e9f2-11317214390-e1495209790914.jpg);">
 <img src="http://library.local.williams.edu/files/2017/05/galleries-ba1e52147cea983d482f6d4cddb6e9f2-11317214390-e1495209790914.jpg" alt="">
 <div class="custom-caption-title">Overlay</div>
 </div>
 <div class="custom-caption-text">Blurb</div>
 </a>
 <a class="edit-me" href="/wp-admin/upload.php?item=1418">Edit Image</a>
 </figure>
 ...
 </div>
 */

!function () {
  var doQuadContainer = function doQuadContainer() {
    // Get all quads (all-or-nothing, atm...all quads are placed together on page)
    var $quads = Array.prototype.slice.apply(document.querySelectorAll('.quad-image:not(.no-quad)')); //const $quads = document.querySelectorAll( '.quad-image:not(.no-quad)' );

    var len = $quads.length;

    if (!len) {
      return;
    }

    var $toContain = [];
    $quads.forEach(function (el) {
      if (!_lib_assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.hasClass(el.parentNode, 'quad-container')) {
        $toContain.push(el);
      }
    }); // Wrap in container

    var $wrapper = document.createElement('div');
    $wrapper.setAttribute('class', 'quad-container');
    _lib_assets_js_src_common_es6__WEBPACK_IMPORTED_MODULE_0__.Common.elements.wrapAll($toContain, $wrapper);
  };

  doQuadContainer();
}();

/***/ }),

/***/ "./node_modules/whatwg-fetch/fetch.js":
/*!********************************************!*\
  !*** ./node_modules/whatwg-fetch/fetch.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Headers": () => (/* binding */ Headers),
/* harmony export */   "Request": () => (/* binding */ Request),
/* harmony export */   "Response": () => (/* binding */ Response),
/* harmony export */   "DOMException": () => (/* binding */ DOMException),
/* harmony export */   "fetch": () => (/* binding */ fetch)
/* harmony export */ });
var global =
  (typeof globalThis !== 'undefined' && globalThis) ||
  (typeof self !== 'undefined' && self) ||
  (typeof global !== 'undefined' && global)

var support = {
  searchParams: 'URLSearchParams' in global,
  iterable: 'Symbol' in global && 'iterator' in Symbol,
  blob:
    'FileReader' in global &&
    'Blob' in global &&
    (function() {
      try {
        new Blob()
        return true
      } catch (e) {
        return false
      }
    })(),
  formData: 'FormData' in global,
  arrayBuffer: 'ArrayBuffer' in global
}

function isDataView(obj) {
  return obj && DataView.prototype.isPrototypeOf(obj)
}

if (support.arrayBuffer) {
  var viewClasses = [
    '[object Int8Array]',
    '[object Uint8Array]',
    '[object Uint8ClampedArray]',
    '[object Int16Array]',
    '[object Uint16Array]',
    '[object Int32Array]',
    '[object Uint32Array]',
    '[object Float32Array]',
    '[object Float64Array]'
  ]

  var isArrayBufferView =
    ArrayBuffer.isView ||
    function(obj) {
      return obj && viewClasses.indexOf(Object.prototype.toString.call(obj)) > -1
    }
}

function normalizeName(name) {
  if (typeof name !== 'string') {
    name = String(name)
  }
  if (/[^a-z0-9\-#$%&'*+.^_`|~!]/i.test(name) || name === '') {
    throw new TypeError('Invalid character in header field name')
  }
  return name.toLowerCase()
}

function normalizeValue(value) {
  if (typeof value !== 'string') {
    value = String(value)
  }
  return value
}

// Build a destructive iterator for the value list
function iteratorFor(items) {
  var iterator = {
    next: function() {
      var value = items.shift()
      return {done: value === undefined, value: value}
    }
  }

  if (support.iterable) {
    iterator[Symbol.iterator] = function() {
      return iterator
    }
  }

  return iterator
}

function Headers(headers) {
  this.map = {}

  if (headers instanceof Headers) {
    headers.forEach(function(value, name) {
      this.append(name, value)
    }, this)
  } else if (Array.isArray(headers)) {
    headers.forEach(function(header) {
      this.append(header[0], header[1])
    }, this)
  } else if (headers) {
    Object.getOwnPropertyNames(headers).forEach(function(name) {
      this.append(name, headers[name])
    }, this)
  }
}

Headers.prototype.append = function(name, value) {
  name = normalizeName(name)
  value = normalizeValue(value)
  var oldValue = this.map[name]
  this.map[name] = oldValue ? oldValue + ', ' + value : value
}

Headers.prototype['delete'] = function(name) {
  delete this.map[normalizeName(name)]
}

Headers.prototype.get = function(name) {
  name = normalizeName(name)
  return this.has(name) ? this.map[name] : null
}

Headers.prototype.has = function(name) {
  return this.map.hasOwnProperty(normalizeName(name))
}

Headers.prototype.set = function(name, value) {
  this.map[normalizeName(name)] = normalizeValue(value)
}

Headers.prototype.forEach = function(callback, thisArg) {
  for (var name in this.map) {
    if (this.map.hasOwnProperty(name)) {
      callback.call(thisArg, this.map[name], name, this)
    }
  }
}

Headers.prototype.keys = function() {
  var items = []
  this.forEach(function(value, name) {
    items.push(name)
  })
  return iteratorFor(items)
}

Headers.prototype.values = function() {
  var items = []
  this.forEach(function(value) {
    items.push(value)
  })
  return iteratorFor(items)
}

Headers.prototype.entries = function() {
  var items = []
  this.forEach(function(value, name) {
    items.push([name, value])
  })
  return iteratorFor(items)
}

if (support.iterable) {
  Headers.prototype[Symbol.iterator] = Headers.prototype.entries
}

function consumed(body) {
  if (body.bodyUsed) {
    return Promise.reject(new TypeError('Already read'))
  }
  body.bodyUsed = true
}

function fileReaderReady(reader) {
  return new Promise(function(resolve, reject) {
    reader.onload = function() {
      resolve(reader.result)
    }
    reader.onerror = function() {
      reject(reader.error)
    }
  })
}

function readBlobAsArrayBuffer(blob) {
  var reader = new FileReader()
  var promise = fileReaderReady(reader)
  reader.readAsArrayBuffer(blob)
  return promise
}

function readBlobAsText(blob) {
  var reader = new FileReader()
  var promise = fileReaderReady(reader)
  reader.readAsText(blob)
  return promise
}

function readArrayBufferAsText(buf) {
  var view = new Uint8Array(buf)
  var chars = new Array(view.length)

  for (var i = 0; i < view.length; i++) {
    chars[i] = String.fromCharCode(view[i])
  }
  return chars.join('')
}

function bufferClone(buf) {
  if (buf.slice) {
    return buf.slice(0)
  } else {
    var view = new Uint8Array(buf.byteLength)
    view.set(new Uint8Array(buf))
    return view.buffer
  }
}

function Body() {
  this.bodyUsed = false

  this._initBody = function(body) {
    /*
      fetch-mock wraps the Response object in an ES6 Proxy to
      provide useful test harness features such as flush. However, on
      ES5 browsers without fetch or Proxy support pollyfills must be used;
      the proxy-pollyfill is unable to proxy an attribute unless it exists
      on the object before the Proxy is created. This change ensures
      Response.bodyUsed exists on the instance, while maintaining the
      semantic of setting Request.bodyUsed in the constructor before
      _initBody is called.
    */
    this.bodyUsed = this.bodyUsed
    this._bodyInit = body
    if (!body) {
      this._bodyText = ''
    } else if (typeof body === 'string') {
      this._bodyText = body
    } else if (support.blob && Blob.prototype.isPrototypeOf(body)) {
      this._bodyBlob = body
    } else if (support.formData && FormData.prototype.isPrototypeOf(body)) {
      this._bodyFormData = body
    } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
      this._bodyText = body.toString()
    } else if (support.arrayBuffer && support.blob && isDataView(body)) {
      this._bodyArrayBuffer = bufferClone(body.buffer)
      // IE 10-11 can't handle a DataView body.
      this._bodyInit = new Blob([this._bodyArrayBuffer])
    } else if (support.arrayBuffer && (ArrayBuffer.prototype.isPrototypeOf(body) || isArrayBufferView(body))) {
      this._bodyArrayBuffer = bufferClone(body)
    } else {
      this._bodyText = body = Object.prototype.toString.call(body)
    }

    if (!this.headers.get('content-type')) {
      if (typeof body === 'string') {
        this.headers.set('content-type', 'text/plain;charset=UTF-8')
      } else if (this._bodyBlob && this._bodyBlob.type) {
        this.headers.set('content-type', this._bodyBlob.type)
      } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
        this.headers.set('content-type', 'application/x-www-form-urlencoded;charset=UTF-8')
      }
    }
  }

  if (support.blob) {
    this.blob = function() {
      var rejected = consumed(this)
      if (rejected) {
        return rejected
      }

      if (this._bodyBlob) {
        return Promise.resolve(this._bodyBlob)
      } else if (this._bodyArrayBuffer) {
        return Promise.resolve(new Blob([this._bodyArrayBuffer]))
      } else if (this._bodyFormData) {
        throw new Error('could not read FormData body as blob')
      } else {
        return Promise.resolve(new Blob([this._bodyText]))
      }
    }

    this.arrayBuffer = function() {
      if (this._bodyArrayBuffer) {
        var isConsumed = consumed(this)
        if (isConsumed) {
          return isConsumed
        }
        if (ArrayBuffer.isView(this._bodyArrayBuffer)) {
          return Promise.resolve(
            this._bodyArrayBuffer.buffer.slice(
              this._bodyArrayBuffer.byteOffset,
              this._bodyArrayBuffer.byteOffset + this._bodyArrayBuffer.byteLength
            )
          )
        } else {
          return Promise.resolve(this._bodyArrayBuffer)
        }
      } else {
        return this.blob().then(readBlobAsArrayBuffer)
      }
    }
  }

  this.text = function() {
    var rejected = consumed(this)
    if (rejected) {
      return rejected
    }

    if (this._bodyBlob) {
      return readBlobAsText(this._bodyBlob)
    } else if (this._bodyArrayBuffer) {
      return Promise.resolve(readArrayBufferAsText(this._bodyArrayBuffer))
    } else if (this._bodyFormData) {
      throw new Error('could not read FormData body as text')
    } else {
      return Promise.resolve(this._bodyText)
    }
  }

  if (support.formData) {
    this.formData = function() {
      return this.text().then(decode)
    }
  }

  this.json = function() {
    return this.text().then(JSON.parse)
  }

  return this
}

// HTTP methods whose capitalization should be normalized
var methods = ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT']

function normalizeMethod(method) {
  var upcased = method.toUpperCase()
  return methods.indexOf(upcased) > -1 ? upcased : method
}

function Request(input, options) {
  if (!(this instanceof Request)) {
    throw new TypeError('Please use the "new" operator, this DOM object constructor cannot be called as a function.')
  }

  options = options || {}
  var body = options.body

  if (input instanceof Request) {
    if (input.bodyUsed) {
      throw new TypeError('Already read')
    }
    this.url = input.url
    this.credentials = input.credentials
    if (!options.headers) {
      this.headers = new Headers(input.headers)
    }
    this.method = input.method
    this.mode = input.mode
    this.signal = input.signal
    if (!body && input._bodyInit != null) {
      body = input._bodyInit
      input.bodyUsed = true
    }
  } else {
    this.url = String(input)
  }

  this.credentials = options.credentials || this.credentials || 'same-origin'
  if (options.headers || !this.headers) {
    this.headers = new Headers(options.headers)
  }
  this.method = normalizeMethod(options.method || this.method || 'GET')
  this.mode = options.mode || this.mode || null
  this.signal = options.signal || this.signal
  this.referrer = null

  if ((this.method === 'GET' || this.method === 'HEAD') && body) {
    throw new TypeError('Body not allowed for GET or HEAD requests')
  }
  this._initBody(body)

  if (this.method === 'GET' || this.method === 'HEAD') {
    if (options.cache === 'no-store' || options.cache === 'no-cache') {
      // Search for a '_' parameter in the query string
      var reParamSearch = /([?&])_=[^&]*/
      if (reParamSearch.test(this.url)) {
        // If it already exists then set the value with the current time
        this.url = this.url.replace(reParamSearch, '$1_=' + new Date().getTime())
      } else {
        // Otherwise add a new '_' parameter to the end with the current time
        var reQueryString = /\?/
        this.url += (reQueryString.test(this.url) ? '&' : '?') + '_=' + new Date().getTime()
      }
    }
  }
}

Request.prototype.clone = function() {
  return new Request(this, {body: this._bodyInit})
}

function decode(body) {
  var form = new FormData()
  body
    .trim()
    .split('&')
    .forEach(function(bytes) {
      if (bytes) {
        var split = bytes.split('=')
        var name = split.shift().replace(/\+/g, ' ')
        var value = split.join('=').replace(/\+/g, ' ')
        form.append(decodeURIComponent(name), decodeURIComponent(value))
      }
    })
  return form
}

function parseHeaders(rawHeaders) {
  var headers = new Headers()
  // Replace instances of \r\n and \n followed by at least one space or horizontal tab with a space
  // https://tools.ietf.org/html/rfc7230#section-3.2
  var preProcessedHeaders = rawHeaders.replace(/\r?\n[\t ]+/g, ' ')
  // Avoiding split via regex to work around a common IE11 bug with the core-js 3.6.0 regex polyfill
  // https://github.com/github/fetch/issues/748
  // https://github.com/zloirock/core-js/issues/751
  preProcessedHeaders
    .split('\r')
    .map(function(header) {
      return header.indexOf('\n') === 0 ? header.substr(1, header.length) : header
    })
    .forEach(function(line) {
      var parts = line.split(':')
      var key = parts.shift().trim()
      if (key) {
        var value = parts.join(':').trim()
        headers.append(key, value)
      }
    })
  return headers
}

Body.call(Request.prototype)

function Response(bodyInit, options) {
  if (!(this instanceof Response)) {
    throw new TypeError('Please use the "new" operator, this DOM object constructor cannot be called as a function.')
  }
  if (!options) {
    options = {}
  }

  this.type = 'default'
  this.status = options.status === undefined ? 200 : options.status
  this.ok = this.status >= 200 && this.status < 300
  this.statusText = 'statusText' in options ? options.statusText : ''
  this.headers = new Headers(options.headers)
  this.url = options.url || ''
  this._initBody(bodyInit)
}

Body.call(Response.prototype)

Response.prototype.clone = function() {
  return new Response(this._bodyInit, {
    status: this.status,
    statusText: this.statusText,
    headers: new Headers(this.headers),
    url: this.url
  })
}

Response.error = function() {
  var response = new Response(null, {status: 0, statusText: ''})
  response.type = 'error'
  return response
}

var redirectStatuses = [301, 302, 303, 307, 308]

Response.redirect = function(url, status) {
  if (redirectStatuses.indexOf(status) === -1) {
    throw new RangeError('Invalid status code')
  }

  return new Response(null, {status: status, headers: {location: url}})
}

var DOMException = global.DOMException
try {
  new DOMException()
} catch (err) {
  DOMException = function(message, name) {
    this.message = message
    this.name = name
    var error = Error(message)
    this.stack = error.stack
  }
  DOMException.prototype = Object.create(Error.prototype)
  DOMException.prototype.constructor = DOMException
}

function fetch(input, init) {
  return new Promise(function(resolve, reject) {
    var request = new Request(input, init)

    if (request.signal && request.signal.aborted) {
      return reject(new DOMException('Aborted', 'AbortError'))
    }

    var xhr = new XMLHttpRequest()

    function abortXhr() {
      xhr.abort()
    }

    xhr.onload = function() {
      var options = {
        status: xhr.status,
        statusText: xhr.statusText,
        headers: parseHeaders(xhr.getAllResponseHeaders() || '')
      }
      options.url = 'responseURL' in xhr ? xhr.responseURL : options.headers.get('X-Request-URL')
      var body = 'response' in xhr ? xhr.response : xhr.responseText
      setTimeout(function() {
        resolve(new Response(body, options))
      }, 0)
    }

    xhr.onerror = function() {
      setTimeout(function() {
        reject(new TypeError('Network request failed'))
      }, 0)
    }

    xhr.ontimeout = function() {
      setTimeout(function() {
        reject(new TypeError('Network request failed'))
      }, 0)
    }

    xhr.onabort = function() {
      setTimeout(function() {
        reject(new DOMException('Aborted', 'AbortError'))
      }, 0)
    }

    function fixUrl(url) {
      try {
        return url === '' && global.location.href ? global.location.href : url
      } catch (e) {
        return url
      }
    }

    xhr.open(request.method, fixUrl(request.url), true)

    if (request.credentials === 'include') {
      xhr.withCredentials = true
    } else if (request.credentials === 'omit') {
      xhr.withCredentials = false
    }

    if ('responseType' in xhr) {
      if (support.blob) {
        xhr.responseType = 'blob'
      } else if (
        support.arrayBuffer &&
        request.headers.get('Content-Type') &&
        request.headers.get('Content-Type').indexOf('application/octet-stream') !== -1
      ) {
        xhr.responseType = 'arraybuffer'
      }
    }

    if (init && typeof init.headers === 'object' && !(init.headers instanceof Headers)) {
      Object.getOwnPropertyNames(init.headers).forEach(function(name) {
        xhr.setRequestHeader(name, normalizeValue(init.headers[name]))
      })
    } else {
      request.headers.forEach(function(value, name) {
        xhr.setRequestHeader(name, value)
      })
    }

    if (request.signal) {
      request.signal.addEventListener('abort', abortXhr)

      xhr.onreadystatechange = function() {
        // DONE (success or failure)
        if (xhr.readyState === 4) {
          request.signal.removeEventListener('abort', abortXhr)
        }
      }
    }

    xhr.send(typeof request._bodyInit === 'undefined' ? null : request._bodyInit)
  })
}

fetch.polyfill = true

if (!global.fetch) {
  global.fetch = fetch
  global.Headers = Headers
  global.Request = Request
  global.Response = Response
}


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		if(__webpack_module_cache__[moduleId]) {
/******/ 			return __webpack_module_cache__[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	// startup
/******/ 	// Load entry module
/******/ 	__webpack_require__("./web/wp-content/themes/meerkat16/assets/src/js/main.js");
/******/ 	// This entry module used 'exports' so it can't be inlined
/******/ })()
;
//# sourceMappingURL=main.js.map