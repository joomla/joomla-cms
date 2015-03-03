/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Bootstrap behaviors
 *
 * @link http://getbootstrap.com/2.3.2/javascript.html
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
		var $target = $(event.target), options;

		for (var selector in event.options) {
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

	/**
	 * Bootstrap button
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Array of selectors
	 */
	Joomla.Behavior.add('bootstrap.button', 'ready update', function(event){
		var $target = $(event.target),
			selector = event.options.join(', ');

		$target.find(selector).button();
	});

	/**
	 * Bootstrap carousel
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('bootstrap.carousel', 'ready update', function(event){
		var $target = $(event.target), options;

		for (var selector in event.options) {
			options = event.options[selector] || {};
			options.interval = options.interval || 5000;
			options.pause    = options.pause || 'hover';

			$target.find(selector).carousel(options);
		}
	});

	/**
	 * Bootstrap dropdown
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Array of selectors
	 */
	Joomla.Behavior.add('bootstrap.dropdown', 'ready update', function(event){
		var $target = $(event.target),
			selector = event.options.join(', ');

		$target.find(selector).dropdown();
	});

	/**
	 * Bootstrap modal
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('bootstrap.modal', 'ready update', function(event){
		var $target = $(event.target), options;

		for (var selector in event.options) {
			options = event.options[selector] || {};
			//options.show = options.show === undefined ? false : options.show;

			$target.find(selector).modal(options);
		}
	});

	/**
	 * Bootstrap popover
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('bootstrap.popover', 'ready update', function(event){
		var $target = $(event.target), options;

		for (var selector in event.options) {
			options = event.options[selector] || {};
			options.trigger   = options.trigger || 'hover focus';
			options.container = options.container || 'body';

			$target.find(selector).popover(options);
		}
	});

	/**
	 * Bootstrap scrollspy
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('bootstrap.scrollspy', 'ready update', function(event){
		var $target = $(event.target), options;

		for (var selector in event.options) {
			options = event.options[selector] || {};

			$target.find(selector).scrollspy(options);
		}
	});

	/**
	 * Bootstrap tooltip
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('bootstrap.tooltip', 'ready update', function(event){
		var $target = $(event.target), options, $items;

		for (var selector in event.options) {
			options = event.options[selector] || {};
			options.container = options.container || 'body';

			$items = $target.find(selector);
			$items.tooltip(options);

			// To be b/c @see JHtmlBootstrap::tooltip()
			if(Joomla.CallbacksBsTooltip){
				if(options.onShow && Joomla.CallbacksBsTooltip[options.onShow]){
					$items.on('show.bs.tooltip', Joomla.CallbacksBsTooltip[options.onShow]);
				}
				if(options.onShown && Joomla.CallbacksBsTooltip[options.onShown]){
					$items.on('shown.bs.tooltip', Joomla.CallbacksBsTooltip[options.onShown]);
				}
				if(options.onHide && Joomla.CallbacksBsTooltip[options.onHide]){
					$items.on('hide.bs.tooltip', Joomla.CallbacksBsTooltip[options.onHide]);
				}
				if(options.onHiden && Joomla.CallbacksBsTooltip[options.onHiden]){
					$items.on('hidden.bs.tooltip', Joomla.CallbacksBsTooltip[options.onHiden]);
				}
			}
		}
	});

	/**
	 * Bootstrap typeahead
	 *
	 * @param JoomlaEvent event Event object where:
	 *
	 * 		event.name    Name of active event
	 * 		event.target  Affected DOM container
	 * 		event.options Possible options, in format {selector1 : options1, selector2 : options2}
	 */
	Joomla.Behavior.add('bootstrap.typeahead', 'ready update', function(event){
		var $target = $(event.target), options;

		for (var selector in event.options) {
			options = event.options[selector] || {};

			$target.find(selector).typeahead(options);
		}
	});

})(jQuery);
