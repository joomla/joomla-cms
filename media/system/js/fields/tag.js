/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field tag
 */
(function() {
	document.addEventListener('DOMContentLoaded', function () {

		if (Joomla.getOptions('js-choices-tags')) {
			var options = Joomla.getOptions('js-choices-tags');

			if (options['elementId']) {
				var tagElement = new Choices(options['elementId'], {
					addItems: options['addItems'] ? options['addItems'] : true,
					duplicateItems: options['duplicateItems'] ? options['duplicateItems'] : false,
					flip: options['flip'] ? options['flip'] : true,
					shouldSort: options['shouldSort'] ? options['shouldSort'] : false,
					search: options['search'] ? options['search'] : true,
					removeItems: true,
					removeItemButton: true,
					//prependValue: "#new#",
				});

				if (options['url']) {
					tagElement.ajax(function(callback) {
						fetch(options['url'])
							.then(function(response) {
								response.json().then(function(data) {
									callback(data, 'value', 'text');
								});
							})
							.catch(function(error) {
								console.log(error);
							});
					});
				}

			} else {
				throw new Error('Element Id id required, Choices cannot be initiated for the tags field.');
			}
		}


		// if (Joomla.getOptions('field-tag-custom')) {
		//
		// 	var options = Joomla.getOptions('field-tag-custom'),
		// 	    customTagPrefix = '#new#';
		//
		// 	// Method to add tags pressing enter
		// 	$(options.selector + '_chzn input').keyup(function(event) {
		//
		// 		var tagOption;
		//
		// 		// Tag is greater than the minimum required chars and enter pressed
		// 		if (this.value && this.value.length >= options.minTermLength && (event.which === 13 || event.which === 188)) {
		//
		// 			// Search an highlighted result
		// 			var highlighted = $(options.selector + '_chzn').find('li.active-result.highlighted').first();
		//
		// 			// Add the highlighted option
		// 			if (event.which === 13 && highlighted.text() !== '')
		// 			{
		// 				// Extra check. If we have added a custom tag with this text remove it
		// 				var customOptionValue = customTagPrefix + highlighted.text();
		// 				$(options.selector + ' option').filter(function () { return $(this).val() == customOptionValue; }).remove();
		//
		// 				// Select the highlighted result
		// 				tagOption = $(options.selector + ' option').filter(function () { return $(this).html() == highlighted.text(); });
		// 				tagOption.attr('selected', 'selected');
		// 			}
		// 			// Add the custom tag option
		// 			else
		// 			{
		// 				var customTag = this.value;
		//
		// 				// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
		// 				tagOption = $(options.selector + ' option').filter(function () { return $(this).html() == customTag; });
		// 				if (tagOption.text() !== '')
		// 				{
		// 					tagOption.attr('selected', 'selected');
		// 				}
		// 				else
		// 				{
		// 					var option = $('<option>');
		// 					option.text(this.value).val(customTagPrefix + this.value);
		// 					option.attr('selected','selected');
		//
		// 					// Append the option an repopulate the chosen field
		// 					$(options.selector).append(option);
		// 				}
		// 			}
		//
		// 			this.value = '';
		// 			$(options.selector).trigger('liszt:updated');
		// 			event.preventDefault();
		// 		}
		// 	});
		// }
	});
})();

