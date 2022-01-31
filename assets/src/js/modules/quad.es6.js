'use strict';

import { Common } from '../../../../../../lib/assets/js/src/common.es6';

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
  const doQuadContainer = () => {
    // Get all quads (all-or-nothing, atm...all quads are placed together on page)
    const $quads = Array.prototype.slice.apply(
      document.querySelectorAll( '.quad-image:not(.no-quad)' )
    );
    //const $quads = document.querySelectorAll( '.quad-image:not(.no-quad)' );
    const len = $quads.length;
    if ( !len ) {
      return;
    }

    let $toContain = [];

    $quads.forEach( el => {
      if ( !Common.elements.hasClass( el.parentNode, 'quad-container' ) ) {
        $toContain.push( el );
      }
    } );

    // Wrap in container
    const $wrapper = document.createElement( 'div' );
    $wrapper.setAttribute( 'class', 'quad-container' );
    Common.elements.wrapAll( $toContain, $wrapper );
  };

  doQuadContainer();
}();
