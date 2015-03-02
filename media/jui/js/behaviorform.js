/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Form related behaviors
 */
!(function($){
	'use strict';

	/**
	 * Chosen Behavior
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('chosen', 'ready update', function(event){
		var $target = $(event.target), options, selector,
			defaultOptions = {
				disable_search_threshold: 10,
				allow_single_deselect: true,
				placeholder_text_multiple: Joomla.JText._('JGLOBAL_SELECT_SOME_OPTIONS'),
				placeholder_text_single: Joomla.JText._('JGLOBAL_SELECT_AN_OPTION'),
				no_results_text: Joomla.JText._('JGLOBAL_SELECT_NO_RESULTS_MATCH')
			};

		for (selector in event.options) {
			// Prepare options
			options = event.options[selector] || {};
			options = Joomla.extend(defaultOptions, options);

			$target.find(selector).chosen(options);
		}
	});

	Joomla.Behavior.add('chosen', 'remove', function(event){
		$(event.target).find('.chzn-done').chosen('destroy');
	});

	/**
	 * AJAX Chosen Behavior
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('ajaxchosen', 'ready update', function(event){
		var $target = $(event.target), options, selector,
			defaultOptions = {
				type: 'GET',
				url: null,
				dataType: 'json',
				jsonTermKey: 'term',
				afterTypeDelay: 500,
				minTermLength: 3
			};

		for (selector in event.options) {
			// Prepare options
			options = event.options[selector] || {};
			options = Joomla.extend(defaultOptions, options);

			if(!options.url) {
				continue;
			}

			$target.find(selector).ajaxChosen(options, function (data) {
				var results = [];

				$.each(data, function (i, val) {
					results.push({ value: val.value, text: val.text });
				});

				return results;
			});
		}
	});

})(jQuery);
