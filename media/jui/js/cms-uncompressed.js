/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

!(function (document, Joomla) {
	"use strict";

/**
 * Sets the HTML of the container-collapse element
 */
Joomla.setcollapse = function(url, name, height) {
    if (!document.getElementById('collapse-' + name)) {
        document.getElementById('container-collapse').innerHTML = '<div class="collapse fade" id="collapse-' + name + '"><iframe class="iframe" src="' + url + '" height="'+ height + '" width="100%"></iframe></div>';
    }
};

/**
 * IE8 polyfill for indexOf()
 */
if (!Array.prototype.indexOf)
{
	Array.prototype.indexOf = function(elt)
	{
		var len = this.length >>> 0;

		var from = Number(arguments[1]) || 0;
		from = (from < 0) ? Math.ceil(from) : Math.floor(from);

		if (from < 0)
		{
			from += len;
		}

		for (; from < len; from++)
		{
			if (from in this && this[from] === elt)
			{
				return from;
			}
		}
		return -1;
	};
}
	/**
	 * JField 'showon' feature.
	 */
	window.jQuery && (function ($) {

		function linkedoptions (target) {
			var showfield = true, itemval, jsondata = target.data('showon');

			// Check if target conditions are satisfied
			$.each(jsondata, function(j, item) {
				var $fields = $('[name="' + jsondata[j]['field'] + '"], [name="' + jsondata[j]['field'] + '[]"]');
				jsondata[j]['valid'] = 0;

				// Test in each of the elements in the field array if condition is valid
				$fields.each(function() {
					// If checkbox or radio box the value is read from proprieties
					if (['checkbox','radio'].indexOf($(this).attr('type')) != -1)
					{
						itemval = $(this).prop('checked') ? $(this).val() : '';
					}
					else
					{
						itemval = $(this).val();
					}

					// Convert to array to allow multiple values in the field (e.g. type=list multiple) and normalize as string
					if (!(typeof itemval === 'object'))
					{
						itemval = JSON.parse('["' + itemval + '"]');
					}

					// Test if any of the values of the field exists in showon conditions
					for (var i in itemval)
					{
						if (jsondata[j]['values'].indexOf(itemval[i]) != -1)
						{
							jsondata[j]['valid'] = 1;
						}
					}
				});

				// Verify conditions
				// First condition (no operator): current condition must be valid
				if (jsondata[j]['op'] == '')
				{
					if (jsondata[j]['valid'] == 0)
					{
						showfield = false;
					}
				}
				// Other conditions (if exists)
				else
				{
					// AND operator: both the previous and current conditions must be valid
					if (jsondata[j]['op'] == 'AND' && jsondata[j]['valid'] + jsondata[j-1]['valid'] < 2)
					{
						showfield = false;
					}
					// OR operator: one of the previous and current conditions must be valid
					if (jsondata[j]['op'] == 'OR'  && jsondata[j]['valid'] + jsondata[j-1]['valid'] > 0)
					{
						showfield = true;
					}
				}
			});

			// If conditions are satisfied show the target field(s), else hide
			(showfield) ? target.slideDown() : target.slideUp();
		}

		/**
		 * Method for setup the 'showon' feature, for the fields in given container
		 * @param {HTMLElement} container
		 */
		function setUpShowon (container) {
			container = container || document;

			$(container).find('[data-showon]').each(function() {
				var target = $(this), jsondata = target.data('showon') || [],
					field, $fields;

				$.each(jsondata, function(j, item) {
					field   = jsondata[j]['field'];
					$fields = $('[name="' + field + '"], [name="' + field + '[]"]');

					// Attach events to referenced element
					$fields.each(function() {
						linkedoptions(target);
					}).on('change', function() {
						linkedoptions(target);
					});
				});
			});
		}

		$(document).ready(function() {
			setUpShowon();
		});

	})(jQuery);


})(document, Joomla);
