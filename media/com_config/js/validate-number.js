;(function($){
	$(function(){

		document.formvalidator.setHandler('number', function(value, element){
			var min = parseInt(element.prop('min')),
				max = parseInt(element.prop('max'));

			if(!isNaN(min) && min > value)
			{
				return false;
			}

			if(!isNaN(max) && max < value)
			{
				return false;
			}

			return true;
		});
	});
})(jQuery);