jQuery(function ($){
	initMinicolors();
	$('body').on('subform-row-add', initMinicolors);

	function initMinicolors(event, container)
	{
		container = container || document;

		$(container).find('.minicolors').each(function() {
			var $this = $(this);
			var format = $this.data('validate') === 'color' ? 'hex' : $this.data('format') || 'hex';

			$this.minicolors({
				control: $this.data('control') || 'hue',
				format: format === 'rgba' ? 'rgb' : format,
				keywords: $this.data('keywords') || '',
				opacity: format === 'rgba',
				position: $this.data('position') || 'default',
				theme: $this.data('theme') || 'bootstrap',
			});
		});
	}
});
