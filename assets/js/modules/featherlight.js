//define(['jquery','featherlight_src'], function($){
!(function ($) {
  $('.fancybox')
          .featherlightGallery({
              nextIcon: '',
              previousIcon: '',
			  variant: 'featherlight-gallery2',
			  //always add arrows
			  beforeOpen: function(event){
				var self = this;
				self.$instance.find('.'+self.namespace+'-content')
				.append(self.createNavigation('previous'))
				.append(self.createNavigation('next'));
				}
			  
   });
//    wp galleries init gallery lightbox
     $('.gallery-grid ul li a')
          .featherlightGallery({
          	nextIcon: '',
            previousIcon: '',
            variant: 'featherlight-wp-gallery'
          });
})(jQuery);
//});