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
			var showfield = true, itemval;
			
			// Check if all target conditions are satisfied
			$.each(target.data(), function(i, items) {
				$.each(items, function(j, item) {
					itemval = (['checkbox','radio'].indexOf($('[name="' + item['field'] + '"]').attr('type')) != -1) ? $('[name="' + item['field'] + '"]:checked').val() : $('[name="' + item['field'] + '"]').val();
					showfield = (item['values'].indexOf(itemval) == -1) ? false : true;
				});
			});

			// If all satisfied show the target field(s), else hide
			(showfield) ? target.slideDown() : target.slideUp();
		};

		$('[data-showon]').each(function() {
			var target = $(this);
			
			// Attach events to referenced element
			$.each($(this).data(), function(i, items) {
				$.each(items, function(j, item) {
					$('[name="' + item['field'] + '"]').each(function() {
						linkedoptions(target);
					}).bind('change click', function() {
						linkedoptions(target);
					});
				});
			});
		});
	});
}
