/**
 * @package Extly.Components
 * @subpackage com_autotweet - AutoTweet posts content to social channels
 *             (Twitter, Facebook, LinkedIn, etc).
 *
 * @author Prieco S.A.
 * @copyright Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://www.extly.com http://support.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

define([ "jquery", "underscore", "backbone"],
	function($, _, Backbone) {
	"use strict";

	// To define the spinner
	var SpinnerView = Backbone.View.extend({

		initialize : function() {
			var thisView = this;
			this.busy = false;

			// Still using the global spinner
			this.spinner = new Spinner(this.$el.get(0));
			$(document).ajaxStart(function() {
				thisView.spinner.show(true);
				thisView.busy = true;
				Joomla.removeMessages();
			}).ajaxStop(function() {
				thisView.spinner.hide(true);
				thisView.busy = false;
			});
		},

		// Just in case AjaxStop fails
		stop: function() {
			this.spinner.hide(true);
			this.busy = false;
		}

	});

	return SpinnerView;

});