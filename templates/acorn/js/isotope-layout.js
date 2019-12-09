jQuery(window).load(function() {

	var $container = jQuery('#isotope-container');
	
		$container.isotope({
			itemSelector: '.portfolio-element',
			percentPosition: true,
			masonry: {
			columnWidth: '.portfolio-element'
			}
		});	

	jQuery('#filters a').click(function () {
		var selector = jQuery(this).attr('data-option-value');
		$container.isotope({
			filter: selector
		});
	    
		var $this = jQuery(this);
        if ( $this.hasClass('selected') ) {
          return false;
        }
		
        var $optionSet = $this.parents('.option-set');
        $optionSet.find('.selected').removeClass('selected');
        $this.addClass('selected');
		return false;
	});

});