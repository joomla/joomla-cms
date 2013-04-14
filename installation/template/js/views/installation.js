/**
 * @package Joomla.Installation
 * @subpackage JavaScript
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters. All rights
 *            reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

define([ 'jquery', 'underscore', 'backbone', 'serialize' ], function($, _,
		Backbone) {

	var Language = Backbone.Model
			.extend({
				initialize : function(attributes, options) {
					this.set('urlBase', options.urlBase);
				},

				url : function() {

					return this.get('urlBase')
							+ '?&task=setup.setlanguage&format=json';
				}
			});

	var InstallationView = Backbone.View.extend({

		// The DOM events specific to an item.
		events : {
			'click .goToPageSiteButton' : 'goToPageSiteButton',
			'click .goToPageDatabaseButton' : 'goToPageDatabaseButton',
			'click .goToPageButton' : 'goToPageButton',
			'click .removeFolderButon' : 'removeFolder',
			'click .submitformButton' : 'submitform',
			'click .verifyFtpSettingsButon' : 'verifyFtpSettings',
			'click .detectFtpRootButton' : 'detectFtpRoot',

			// ??
			'click input[name=jform[summary_email]]' : 'toggleEmailPasswords',

			'change #jform_language' : 'setLanguage'
		},

		initialize : function() {
			var theInstaller = this;

			this.sampleDataLoaded = false;
			this.busy = false;
			this.spinner = new Spinner(this.$el.get(0));
			this.baseUrl = base;

			this.$el.ajaxStart(function() {
				theInstaller.spinner.show(true);
				theInstaller.busy = true;
				Joomla.removeMessages();
			}).ajaxStop(function() {
				theInstaller.spinner.hide(true);
				theInstaller.busy = false;
			});

			this.language = new Language({}, {
				urlBase : base
			});
			this.language.on('change', this.loadLanguage, this);
		},

		setLanguage : function setLanguage() {
			var formdata;

			formdata = this.$('#languageForm').serializeObject();
			this.language.save(formdata, {
				wait : true,
				error : function(model, fail, xhr) {
					var r = JSON.decode(xhr.responseText);
					if (r) {
						Joomla.replaceTokens(r.token);
						alert(r.message);
					}
				}
			});
		},

		loadLanguage : function loadLanguage(response) {
			console.log(response);

			/*
			 * Joomla.replaceTokens(r.token); if (r.messages) {
			 * Joomla.renderMessages(r.messages); } var lang =
			 * $$('html').getProperty('lang')[0]; if (lang.toLowerCase() ===
			 * r.lang.toLowerCase()) { Install.goToPage(r.data.view, true); }
			 * else { window.location = this.baseUrl+'?view='+r.data.view; }
			 */
		},

		goToPageSiteButton : function goToPageSiteButton() {

		},

		goToPageDatabaseButton : function goToPageDatabaseButton() {

		},

		goToPageButton : function goToPageButton() {

		},

		removeFolder : function removeFolder() {

		},

		submitform : function submitform() {

		},

		verifyFtpSettings : function verifyFtpSettings() {

		},

		detectFtpRoot : function detectFtpRoot() {

		},

		// ??
		doInstall : function doInstall() {

		},

		toggleEmailPasswords : function toggle() {
			this.toggle('email_passwords', 'summary_email', 1);
		},

		toggle : function toggle(id, el, value) {
			var val = $('input[name=jform[' + el + ']]:checked').value;
			if (val == value) {
				$(id).removeClass('hide');
			} else {
				$(id).addClass('hide');
			}
		}

	});

	return InstallationView;
});