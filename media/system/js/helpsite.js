/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * gets the help site with ajax
 */
jQuery(function($) {
	'use strict';

	$('#helpsite-refresh').click(function()
	{
		// Uses global variable helpsite_base for bast uri
		var $this = $(this);
		var select_id   = $this.attr('rel');
		var showDefault = $this.attr('showDefault');

		$.getJSON('index.php?option=com_users&task=profile.gethelpsites&format=json', function(data){
			// The response contains the options to use in help site select field
			var items = [];

			// Build options
			$.each(data, function(key, val) {
				if (val.value !== '' || showDefault === 'true') {
					items.push('<option value="' + val.value + '">' + val.text + '</option>');
				}
			});

			// Replace current select options. The trigger is needed for Chosen select box enhancer
			$("#" + select_id).empty().append(items).trigger("liszt:updated");
		});
	});
});
