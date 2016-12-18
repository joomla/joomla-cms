jQuery(function ($) {
	'use strict';

	$('.minicolors').each(function() {
		var $this = $(this),
			format = $this.data('format');

		$this.minicolors({
			control: $this.data('control') || 'hue',
			format: $this.data('validate') === 'color'
				? 'hex'
				: (format === 'rgba' ? 'rgb' : format)
			|| 'hex',
			keywords: $this.data('keywords') || '',
			opacity: format === 'rgba',
			position: $this.data('position') || 'default',
			theme: 'bootstrap'
		});
	});
});
