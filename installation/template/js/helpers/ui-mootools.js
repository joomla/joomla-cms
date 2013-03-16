/**
 * @package Joomla.Installation
 * @subpackage JavaScript
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters. All rights
 *            reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

define([ "domready" ], function(domReady) {

	domReady(function() {

		(new Fx.Accordion($$('h4.moofx-toggler'), $$('div.moofx-slider'), {
			onActive : function(toggler, i) {
				toggler.addClass('moofx-toggler-down');
			},
			onBackground : function(toggler, i) {
				toggler.removeClass('moofx-toggler-down');
			},
			duration : 300,
			opacity : false,
			alwaysHide : true,
			show : 1
		}));

		// Attach the validator
		$$('form.form-validate').each(function(form) {
			this.attachToForm(form);
		}, document.formvalidator);

	});

});
