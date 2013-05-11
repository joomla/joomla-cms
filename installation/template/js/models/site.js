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

define([ "backbone" ],
		function(Backbone) {
	"use strict";

	// To define the Site access for Form Validation
	var Site = Backbone.Model.extend({
		initialize : function(attributes, options) {
			this.set('task', options.task);
		},

		url : function() {
			return base + '?task=' + this.get('task') + '&format=json';

		}

	});

	return Site;

});