/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Copy title to the menu tile and alias to menu alias respectively
 */
(function ($) {
	$(document).ready(function() {
		$('#jform_menutype').change(function(){
			var menutype = $(this).val();
			$.ajax({
				url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
				dataType: 'json'
			}).done(function(data) {
				$('#jform_parent_id option').each(function() {
					if ($(this).val() != '1') {
						$(this).remove();
					}
				});

				$.each(data, function (i, val) {
					var option = $('<option>');
					option.text(val.title).val(val.id);
					$('#jform_parent_id').append(option);
				});
				$('#jform_parent_id').trigger('chosen:updated');
			});
		});
	});
	}(jQuery));
