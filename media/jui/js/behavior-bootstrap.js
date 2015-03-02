/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Bootstrap behaviors
 */
!(function($){
	'use strict';

	/**
	 * Bootstrap affix
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('bootstrap.affix', 'ready update', function(event){
		var $target = $(event.target), selector, options;

		for (selector in event.options) {
			options = event.options[selector] || {};

			$target.find(selector).affix(options);
		}
	});

	/**
	 * Bootstrap alert
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Array of selectors
	 */
	Joomla.Behavior.add('bootstrap.alert', 'ready update', function(event){
		var $target = $(event.target),
			selector = event.options.join(', ');

		$target.find(selector).alert();
	});

})(jQuery);
