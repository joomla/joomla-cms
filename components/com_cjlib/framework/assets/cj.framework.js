(function($) {
	var CJFramework = {
		
		load_cjpagination: function(){
			
			if($.isFunction($('.cjpagination').buttonset)){
				
				$('.cjpagination').buttonset();
				$('.cjpagination').find('.current').addClass('ui-state-highlight');
				$('.cjpagination').find('.first').button('option', 'icons', {primary:'ui-icon-seek-start'}).button('option', 'text', false);
				$('.cjpagination').find('.previous').button('option', 'icons', {primary:'ui-icon-triangle-1-w'}).button('option', 'text', false);
				$('.cjpagination').find('.next').button('option', 'icons', {primary:'ui-icon-triangle-1-e'}).button('option', 'text', false);
				$('.cjpagination').find('.last').button('option', 'icons', {primary:'ui-icon-seek-end'}).button('option', 'text', false);
				$('.cjpagination').find('.disabled').button( 'option', 'disabled', true );
			}
		}
	};
	
	window.CJFramework = CJFramework;
})(jQuery);

jQuery(document).ready(function($){
	
	if($.isFunction($('body').tooltip)){
		$('body').tooltip({selector: '.tooltip-hover'});
	} 
	
	CJFramework.load_cjpagination();
});