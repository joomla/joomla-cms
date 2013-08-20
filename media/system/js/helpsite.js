/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * gets the help site with ajax
 */
jQuery(document).ready(function() {
	jQuery('#helpsite-refresh').click(function()
	{
		var select_id = jQuery(this).attr('rel');
		jQuery.getJSON('index.php?option=com_config&task=application.refreshHelp&format=json', function(data){
			// The response contains the options to use in help site select field
			var items = [];

			// Build options
			jQuery.each(data, function(key, val) {
				items.push('<option value="' + val.value + '">' + val.text + '</option>');
			});

			// Replace current select options. The trigger is needed for Chosen select box enhancer
			jQuery("#" + select_id).empty().append(items).trigger("liszt:updated");
		});
	})
});
