// initialization of magnific popup for all slider instances
jQuery(document).ready(function(){
	jQuery('.djslider-loader').each(function() {
		jQuery(this).magnificPopup({
	        delegate: '.image-link', // the selector for gallery item
	        type: 'image',
	        mainClass: 'mfp-img-mobile',
	        gallery: {
	          enabled: true
	        },
			image: {
				verticalFit: true,
				titleSrc: 'data-title'
			}
	    });
	});
});