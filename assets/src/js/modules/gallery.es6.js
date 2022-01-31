'use strict';

!(function () {
  const wp = wp || null;
  if ( !wp || !wp.media ) {
    return false;
  }

  const media = wp.media;

  // Wrap the render() function to append controls
  media.view.Settings.Gallery = media.view.Settings.Gallery.extend( {
    render: function () {
      //alert('this here');
      media.view.Settings.prototype.render.apply( this, arguments );

      // Append the custom template
      this.$el.append( media.template( 'custom-gallery-setting' ) );

      // Save the setting
      media.gallery.defaults.size = 'thumbnail';
      this.update.apply( this, ['size'] );
      return this;
    }
  } );
})();