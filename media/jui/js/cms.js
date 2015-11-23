/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
			var showfield = true, itemval, jsondata = target.data()['showon'];

			// Check if target conditions are satisfied
			$.each(jsondata, function(j, item) {
				itemval = (['checkbox','radio'].indexOf($('[name="' + jsondata[j]['field'] + '"]').attr('type')) != -1) ? $('[name="' + jsondata[j]['field'] + '"]:checked').val() : $('[name="' + jsondata[j]['field'] + '"]').val();
				jsondata[j]['valid'] = (jsondata[j]['values'].indexOf(itemval) != -1) ? 1 : 0;
				if (   (jsondata[j]['op'] == ''    && jsondata[j]['valid'] == 0)
					|| (jsondata[j]['op'] == 'AND' && jsondata[j]['valid'] + jsondata[j-1]['valid'] < 2)
					|| (jsondata[j]['op'] == 'OR'  && jsondata[j]['valid'] + jsondata[j-1]['valid'] < 1))
					{ showfield = false; }
			});

			// If all satisfied show the target field(s), else hide
			(showfield) ? target.slideDown() : target.slideUp();
		};

		$('[data-showon]').each(function() {
			var target = $(this), jsondata = $(this).data()['showon'];

			// Attach events to referenced element
			$.each(jsondata, function(j, item) {
				$('[name="' + jsondata[j]['field'] + '"]').each(function() {
					linkedoptions(target);
				}).bind('change click', function() {
					linkedoptions(target);
				});
			});
		});
	});
}