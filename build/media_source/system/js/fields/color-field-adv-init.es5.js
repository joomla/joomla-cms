/**
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

!(function(document, $) {
	"use strict";

	function initMinicolorsField (event) {
		$(event.target).find('.minicolors').each(function() {
			$(this).minicolors({
				control: $(this).attr('data-control') || 'hue',
				format: $(this).attr('data-validate') === 'color'
					? 'hex'
					: ($(this).attr('data-format') === 'rgba'
					? 'rgb'
					: $(this).attr('data-format'))
					|| 'hex',
				keywords: $(this).attr('data-keywords') || '',
				opacity:  $(this).attr('data-format') === 'rgba',
				position: $(this).attr('data-position') || 'default',
				swatches: $(this).attr('data-colors') ? $(this).attr('data-colors').split(",") : [],
				theme: 'bootstrap'
			});
		});
	}

	/**
	 * Initialize at an initial page load
	 */
	document.addEventListener("DOMContentLoaded", initMinicolorsField);

	/**
	 * Initialize when a part of the page was updated
	 */
	document.addEventListener("joomla:updated", initMinicolorsField);

})(document, jQuery);
