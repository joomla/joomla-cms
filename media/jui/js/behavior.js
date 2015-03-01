/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Common behaviors
 */
!(function(){
	'use strict';

	/**
	 * Image captions
	 */
	Joomla.Behavior.add('caption', 'load update', function(event){
		var selector;

		for (var i = 0, l = event.options.length; i < l; i++ ) {
			selector = event.options[i];
			new JCaption(selector);
		}
	});

	/**
	 * Submenu switcher
	 */
	Joomla.Behavior.add('switcher', 'ready', function(event){
		document.switcher = null;
		var toggler = document.getElementById('submenu');
		var element = document.getElementById('config-document');
		if (element) {
			document.switcher = new JSwitcher(toggler, element);
		}
	});

	/**
	 * Support for a hover tooltips.
	 *
	 * @TODO: is it still used somwhere ?
	 */
	//Joomla.Behavior.add('tooltip', 'ready update', function(event){});

	/**
	 * Modal
	 */
	Joomla.Behavior.add('modal', 'ready update', function(event){
		var $target = jQuery(event.target), selector,
			options;

		if (!window.jModalClose) {
    		SqueezeBox.initialize({});

    		window.jModalClose = function () {
    			SqueezeBox.close();
    		}
		}

		for (var selector in event.options) {
			// Prepare options
			options = event.options[selector] || {};
			options.parse = options.parse ? options.parse : 'rel';

			if (options.fullScreen){
				options.size = {
					x: jQuery(window).width() - 80,
					y: jQuery(window).height() - 80
				};
			}

			SqueezeBox.assign($target.find(selector).get(), options);
		}
	});

})();
