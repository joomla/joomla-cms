/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

/**
 * Sets the HTML of the container-collapse element
 */
Joomla.setcollapse = function(url, name, height) {
    if (!document.getElementById('collapse-' + name)) {
        document.getElementById('container-collapse').innerHTML = '<div class="collapse fade" id="collapse-' + name + '"><iframe class="iframe" src="' + url + '" height="'+ height + '" width="100%"></iframe></div>';
    }
}

if (jQuery) {
	jQuery(document).ready(function($) {
		var linkedoptions = function(target) {
			var showfield = true, itemval, jsondata = target.data('showon');

			// Check if target conditions are satisfied
			$.each(jsondata, function(j, item) {
				$fields = $('[name="' + jsondata[j]['field'] + '"], [name="' + jsondata[j]['field'] + '[]"]');
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
		};

		$('[data-showon]').each(function() {
			var target = $(this), jsondata = $(this).data('showon');

			// Attach events to referenced element
			$.each(jsondata, function(j, item) {
				$fields = $('[name="' + jsondata[j]['field'] + '"], [name="' + jsondata[j]['field'] + '[]"]');
				// Attach events to referenced element
				$fields.each(function() {
					linkedoptions(target);
				}).bind('change', function() {
					linkedoptions(target);
				});
			});
		});
	});
}