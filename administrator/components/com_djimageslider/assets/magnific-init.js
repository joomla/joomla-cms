// initialization of magnific popup for all album instances
!function($){

$(document).ready(function(){
	$('#adminForm').each(function() {
		
		$(this).magnificPopup({
	        delegate: '.mf-popup', // the selector for gallery item
	        type: 'image',
	        mainClass: 'mfp-img-mobile',
	        gallery: {
	          enabled: true
	        },
			image: {
				verticalFit: true
			},
			iframe: {
				patterns: {
					youtube: null,
					vimeo: null,
					link: {
						index: '/',
						src: '%id%'
					}
				}
			}
	    });
	});
});

}(jQuery);