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

define([ "jquery", "underscore", "backbone",
         "template/js/models/language",
         "uiinit",
         "serialize" ],
	function($, _, Backbone, Language, UiInit) {
	"use strict";

	// To define the Language Chooser View
	var LanguageChooserView = Backbone.View.extend({

		// The DOM events specific to an item.
		events : {
			'change #jform_language' : 'setLanguage'
		},

		initialize : function() {
			this.language = new Language();
			this.language.on('change', this.loadLanguage, this);
		},

		setLanguage : function setLanguage() {
			var formdata;

			formdata = this.$('#languageForm').serializeObject();
			this.language.save(formdata, {
				wait: true,

				// TO-DO: Improve error processing
				error : function(model, fail, xhr) {
					var r = JSON.decode(fail.responseText);
					if (r) {
						Joomla.replaceTokens(r.token);
						alert(r.message);
					}
				}
			});
		},

		loadLanguage : function loadLanguage(r) {
			UiInit.processSiteResponse(r);
		},

	});

	return LanguageChooserView;

});
