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
  ("use strict");

  const $navigationMenus = $(".widget_nav_menu").find("ul.menu");
  const $toggleBtns = $(".menu-toggle");
  let resizeTimer;

  init();

  function init() {
    initNavToggle($navigationMenus);
    openCurrentItems($navigationMenus);
    fixTouch($navigationMenus);
    initSectionToggle($toggleBtns);
    initResize();
    initCloseAll();
  }

  /**
   * Initialize accordion-style submenus
   * @param $menus
   */
  function initNavToggle($menus) {
    if (!$menus.length) {
      return false;
    }

    const $submenus = $menus.find(".menu-item-has-children");

    $submenus.each(function (i, el) {
      // Add toggle control that displays child menu items.
      const $dropdownToggle = $("<span />", {
        class: "dropdown-toggle",
      });

      // Find anchor
      const $anchor = $(el).find("> a");

      // Find id of control target and append aria atts.
      // Have to use index because of encapsulation.
      const id = $(el).find("> ul").attr("id");

      $anchor
        .append($dropdownToggle)
        .attr({
          "aria-expanded": "false",
          "aria-controls": id ? id : "",
        })
        .click(function (e) {
          e.preventDefault();
          doClick($(el));
        });
    });
  }

  function openCurrentItems($menus) {
    $menus = $menus.not(".load_collapsed");

    if (!$menus.length) {
      return false;
    }

    // Toggle buttons and submenu items with active children menu items.
    const $currentMenu = $menus.find(".current-menu-ancestor");
    if ($currentMenu.length) {
      // Page appears in menu
      //const $current = $menus.find( '.current-menu-item, .current-menu-ancestor, .current-menu-parent,
      // .current-page-ancestor' );
      $currentMenu.each(function (i, el) {
        doClick($(el));
      });
    } else {
      // Page parent appears in menu but page doesn't
      const $currentPage = $menus
        .find(".current-page-ancestor")
        .parents(".menu-item-has-children");
      //const $current = $menus.find( '.current-menu-item, .current-menu-ancestor, .current-menu-parent,
      // .current-page-ancestor' );
      $currentPage.each(function (i, el) {
        doClick($(el));
      });
    }
  }

  /**
   * Submenu item click handler
   */
  function doClick($item) {
    if (!$item.length) {
      return false;
    }
    var $parentUl = $item.parent(); //the parent menu ul

    //for the horizontal site nav, check for #heads-up and then close other menu items
    // else for all other menus, just toggle the one clicked
    if ($item.parents().is("#heads-up")) {
      //loop through and toggle off all that are toggled on
      $selfOpen = $item.hasClass("toggled-on");
      //build list of items to click adding target item when not closing itself
      $itemsToToggle = $selfOpen
        ? $parentUl.find(".nav-item.toggled-on")
        : $parentUl.find(".nav-item.toggled-on").add($item);
      $itemsToToggle.each(function (index) {
        doClickItem($(this)); //close these
      });
    } else {
      //if the menu is not horizontal, then just run one time on the item
      doClickItem($item);
    }
  }

  /**
   * single menu item toggle
   */
  function doClickItem($item) {
    if (!$item.length) {
      return false;
    }

    const $toggle = $item.find(".dropdown-toggle").eq(0), // first dropdown descendant
      $submenu = $item.find("> .children, > .sub-menu, > .nav-drop"),
      $anchor = $item.find("> a "); // ul.sub-menu

    $item.add($toggle).toggleClass("toggled-on").removeClass("auto-opened");

    $submenu.slideToggle(100, function () {
      $(this).toggleClass("toggled-on").removeClass("auto-opened");
    });

    // Set Aria attributes
    const isToggled = $item.hasClass("toggled-on");
    $anchor.attr({
      "aria-expanded": isToggled,
    });
  }

  /**
   * Initialize submenu button  to show toggles
   */
  function initSectionToggle($toggleBtns) {
    // Return early if menuToggle is missing.
    if (!$toggleBtns.length) {
      return;
    }

    // Add an initial values for the attribute.
    $toggleBtns
      .each(function () {
        const $me = $(this);
        const $target = $($(this).data("target"));
        const $targetParent = $target.parent();
        const $targetContainer = $targetParent.parent();
        const is_visible = $targetParent.is(":visible");

        if (is_visible) {
          $me
            .add($target)
            .add($targetParent)
            .add($targetContainer)
            .removeClass("toggled-off")
            .addClass("toggled-on");
        } else {
          $me
            .add($target)
            .add($targetParent)
            .add($targetContainer)
            .removeClass("toggled-on")
            .addClass("toggled-off");
        }
      })
      .on("click", function () {
        var $me = $(this);
        var $target = $($me.data("target"));
        var $targetParent = $target.parent();
        var $targetContainer = $targetParent.parent();

        // Close other menus
        if ($me.hasClass("toggled-on")) {
          closeDrawer();
        } else {
          openDrawer();
        }

        function closeDrawer() {
          $targetParent.slideUp(100, function () {
            $(this)
              .add($target)
              .add($targetContainer)
              .removeClass("toggled-on")
              .addClass("toggled-off");
            openDrawer();
          });
        }

        function openDrawer() {
          if ($me.hasClass("toggled-on")) {
            // close
            $targetParent.slideUp(100, function () {
              $me.attr("aria-expanded", "false");
              $me
                .add($target)
                .add($targetParent)
                .add($targetContainer)
                .removeClass("toggled-on")
                .addClass("toggled-off");

              $(document).trigger("menu-toggled-off");
            });
          } else {
            // open
            $me.attr("aria-expanded", "true");
            $me
              .add($target)
              .add($targetParent)
              .add($targetContainer)
              .removeClass("toggled-off")
              .addClass("toggled-on");

            $targetParent.slideDown(100, function () {
              $(document).trigger("menu-toggled-on");
            });
          }
        }
      });
  }

  // Fix sub-menus for touch devices and better focus for hidden submenu items for accessibility.
  function fixTouch($menus) {
    if (!$menus.length || !$menus.children().length) {
      return;
    }

    // Toggle `focus` class to allow submenu access on tablets.
    function toggleFocusClassTouchScreen() {
      $menus
        .find(".menu-item-has-children > a")
        .unbind("touchstart.twentysixteen");
    }

    if ("ontouchstart" in window) {
      $(window).on("resize.twentysixteen", toggleFocusClassTouchScreen);
      toggleFocusClassTouchScreen();
    }

    $menus.find("a").on("focus.twentysixteen blur.twentysixteen", function () {
      $(this).parents(".menu-item").toggleClass("focus");
    });
  }

  // Add 'below-entry-meta' class to elements.
  function belowEntryMetaClass(param) {
    const body = $(document.body);
    if (
      body.hasClass("page") ||
      body.hasClass("search") ||
      body.hasClass("single-attachment") ||
      body.hasClass("error404")
    ) {
      return;
    }

    $(".entry-content")
      .find(param)
      .each(function () {
        var element = $(this),
          elementPos = element.offset(),
          elementPosTop = elementPos.top,
          entryFooter = element.closest("article").find(".entry-footer"),
          entryFooterPos = entryFooter.offset(),
          entryFooterPosBottom =
            entryFooterPos.top + (entryFooter.height() + 28),
          caption = element.closest("figure"),
          newImg;

        // Add 'below-entry-meta' to elements below the entry meta.
        if (elementPosTop > entryFooterPosBottom) {
          // Check if full-size images and captions are larger than or equal to 840px.
          if ("img.size-full" === param) {
            // Create an image to find native image width of resized images (i.e. max-width: 100%).
            newImg = new Image();
            newImg.src = element.attr("src");

            $(newImg).load(function () {
              if (newImg.width >= 840) {
                element.addClass("below-entry-meta");

                if (caption.hasClass("wp-caption")) {
                  caption.addClass("below-entry-meta");
                  caption.removeAttr("style");
                }
              }
            });
          } else {
            element.addClass("below-entry-meta");
          }
        } else {
          element.removeClass("below-entry-meta");
          caption.removeClass("below-entry-meta");
        }
      });
  }

  function initResize() {
    $(window).on("resize.twentysixteen", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        belowEntryMetaClass("img.size-full");
        belowEntryMetaClass("blockquote.alignleft, blockquote.alignright");
      }, 300);
    });

    belowEntryMetaClass("img.size-full");
    belowEntryMetaClass("blockquote.alignleft, blockquote.alignright");
  }
  //close all accordions when clicked outside
  function initCloseAll() {
    $(document).bind("click", function (e) {
      if (!e.target.closest("#heads-up")) {
        closeAll();
      } //end if outside
    });
  }
  //function close all
  function closeAll() {
    $itemsToToggle = $("#heads-up ").find(".nav-item.toggled-on");
    $itemsToToggle.each(function (index) {
      doClickItem($(this));
    });
  }
})(jQuery);
