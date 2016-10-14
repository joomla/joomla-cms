;(function($){
	$(function(){
		document.formvalidator.setHandler('range', function(value, element){
			
			var min  = parseFloat(element.prop('min')),
				max  = parseFloat(element.prop('max')),
				step = parseFloat(element.prop('step'));

			if(!isNaN(min) && min > value){
				return false;
			}

			if(!isNaN(max) && max < value){
				return false;
			}

			if(!isNaN(step) && value % step !== 0){
				return false;
			}

			return true;
		});
	});
})(jQuery);
