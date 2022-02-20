/**
 * @package		Joomla.JavaScript
 * @copyright	(C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * gets the help site with ajax
 */
jQuery(document).ready(function() {
	jQuery('#helpsite-refresh').click(function()
	{
		// Uses global variable helpsite_base for bast uri
		var select_id   = jQuery(this).attr('rel');
		var showDefault = jQuery(this).attr('showDefault');

		jQuery.getJSON('index.php?option=com_users&task=profile.gethelpsites&format=json', function(data){
			// The response contains the options to use in help site select field
			var items = [];

			// Build options
			jQuery.each(data, function(key, val) {
				if (val.value !== '' || showDefault === 'true') {
					items.push('<option value="' + val.value + '">' + val.text + '</option>');
				}
			});

			// Replace current select options. The trigger is needed for Chosen select box enhancer
			jQuery("#" + select_id).empty().append(items).trigger("liszt:updated");
		});
	});
});
