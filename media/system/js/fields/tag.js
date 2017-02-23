/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field user
 */
jQuery(document).ready(function ($) {

	if (Joomla.getOptions('field-tag-custom')) {

		var options = Joomla.getOptions('field-tag-custom'),
			customTagPrefix = '#new#';

		// Method to add tags pressing enter
		$(options.selector + '_chzn input').keyup(function(event) {

			var tagOption;

			// Tag is greater than the minimum required chars and enter pressed
			if (this.value && this.value.length >= options.minTermLength && (event.which === 13 || event.which === 188)) {

				// Search an highlighted result
				var highlighted = $(options.selector + '_chzn').find('li.active-result.highlighted').first();

				// Add the highlighted option
				if (event.which === 13 && highlighted.text() !== '')
				{
					// Extra check. If we have added a custom tag with this text remove it
					var customOptionValue = customTagPrefix + highlighted.text();
					$(options.selector + ' option').filter(function () { return $(this).val() == customOptionValue; }).remove();

					// Select the highlighted result
					tagOption = $(options.selector + ' option').filter(function () { return $(this).html() == highlighted.text(); });
					tagOption.attr('selected', 'selected');
				}
				// Add the custom tag option
				else
				{
					var customTag = this.value;

					// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
					tagOption = $(options.selector + ' option').filter(function () { return $(this).html() == customTag; });
					if (tagOption.text() !== '')
					{
						tagOption.attr('selected', 'selected');
					}
					else
					{
						var option = $('<option>');
						option.text(this.value).val(customTagPrefix + this.value);
						option.attr('selected','selected');

						// Append the option an repopulate the chosen field
						$(options.selector).append(option);
					}
				}

				this.value = '';
				$(options.selector).trigger('liszt:updated');
				event.preventDefault();
			}
		});
	}
});
