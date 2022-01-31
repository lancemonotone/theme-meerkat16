jQuery(document).ready(function($){
	//--------------- ALUMNI NEWS ---------------------//
	
	// class year picker widget
	$('form#my-class-year select').change( function(){
		$year = $(this).find('option:selected').val();
		window.location.href = '/class_year/' + $year + '/';
	});

	// logout
	$('#harris-logout').click( function (e){
		// unset our cookie
		$.cookie('harris_alum_id', null, { domain: 'williams.edu', path: '/' });
		$.cookie('harris_alum_sig', null, { domain: 'williams.edu', path: '/' });
		// redirect to their logout page
		window.location.href = 'https://www.alumniconnections.com/olc/pub/WLC/login/app.sph/olclogin.app';
		e.preventDefault();
	});

	//--------------- WILLIAMS MAGAZINE ---------------------//

	/*-- FEATURE ARTICLES --*/
	
	if ($('body').hasClass('single-feature')){
		// if feature article's first image is sufficiently large, swap to the feature-georgia layout
		$feat_img = $('div.post-content img').first();
		$feat_w = $feat_img.attr('width');
		if ($feat_w == '100%' || $feat_w > 740){
			
			applyGeorgiaFormat($feat_img);
		}
		else {
			// check for filmstrip as first element
			$first_elem = $('.post-content').children().first();
			if ($first_elem.hasClass('gallery-filmstrip')){
				$film_w = $first_elem.width();
				if ($film_w > 740){
					applyGeorgiaFormat($first_elem);
				}
			}
		}
		
		// dynamically apply byline style.... how to do this reliably??
		//$('.post-content strong').each( function(index){
			//console.log($(this).text());
		//});
		
		// get rid of stupid empty paragraphs messing up our layout
		$('.post-content p').each( function(index){
			$test = $(this).html().trim();
			if ($test == ''){
				$(this).remove();
			}
		});
		
	}
	
	function applyGeorgiaFormat($feat_img){
		$feat_img.addClass('feature-img');
		$('body').addClass('feature-georgia');		
		// move feature image to top
		$feat_img.prependTo("#print-only");
		$feat_img.removeClass('aligncenter');	
	}
	
	/*-- SIDEBAR MENU --*/
	
	// highlight current section in sidebar
	$( ".widget_meerkat_edition_toc h3.feature-header a, .widget_meerkat_edition_toc h3.department-header a.issue-link").each(function( index ) {
		$url = $(this).attr('href');
		if (location.href.indexOf($url) == 0){
			$(this).closest('li').css('background-color', '#eef8fd');
		}
	}); 
});