var jQuery;
	(function ($) {
		$(document).ready(function () {
			$('#jform_type').on('change', function (a, params) {
				var v = typeof(params) !== 'object' ? $('#jform_type').val() : params.selected;
				switch (v) {
				case '0':
					// Image
					$('#image, #url').show();
					$('#custom').hide();
					break;
				case '1':
					// Custom
					$('#image, #url').hide();
					$('#custom').show();
					break;
				}
			});
		});
	})(jQuery);