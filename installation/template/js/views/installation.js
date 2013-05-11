/**
 * @package Joomla.Installation
 * @subpackage JavaScript
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters. All rights
 *            reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

define([ "jquery", "underscore", "backbone", "uiinit",
         	"template/js/views/languagechooser",
         	"template/js/views/spinner",
         	"template/js/models/site",
         	"template/js/models/body" ],

		function($, _, Backbone, UiInit,
				LanguageChooserView,
				SpinnerView,
				Site,
				Body) {

	// Main Installation - Steps Management
	var InstallationView = Backbone.View.extend({

		// The DOM events specific to an item.
		events : {
			'click .goToPageDatabaseButton' : 'submitForm',

			'click .goToPreviousSiteButton' : 'goToSite',
			'click #goToPageSiteButton' : 'goToSite',

			'click .goToPageSummaryButton' : 'submitForm',

			'click .goToPreviousDatabaseButton' : 'goToDatabase',
			'click #goToPageDatabaseButton' : 'goToDatabase'

		},

		initialize : function() {

			this.spinnerView = new SpinnerView({
				el : $('#container-installation')
			});

			(new LanguageChooserView({
				el : $('#container-installation')
			}));
		},

		submitForm : function () {
			var task = this.$('#task').val(), formdata;

			// Task setup for site query
			this.site = new Site({}, {
				task : task
			});
			this.site.on('change', this.loadSiteResponse, this);

			Joomla.removeMessages();

			// query Site to check current form
			formdata = this.$('#adminForm').serializeObject();
			this.site.save(formdata, {
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

		loadSiteResponse : function (r) {
			var view = r.get('data').view;

			// Just in case AjaxStop fails
			this.spinnerView.stop();
			UiInit.processSiteResponse(r);
			this._loadBody(view);
		},

		_loadBody: function(view) {
			// View setup for body query
			this.body = new Body({}, {
				view: view
			});
			this.body.on('change', this.loadBodyResponse, this);
			this.body.fetch({
				wait: true,
				dataType: 'text',

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

		loadBodyResponse : function (r) {
			var body = r.get('body');

			this.$el.html(body);
			UiInit.initUi();
			UiInit.initMootools();
		},

		goToSite : function() {
			this._loadBody('site');
		},

		goToDatabase: function() {
			this._loadBody('database');
		}

	});

	return InstallationView;
});


/*
'click .goToPageSiteButton' : 'goToPageSiteButton',
'click .goToPageButton' : 'goToPageButton',
'click .removeFolderButon' : 'removeFolder',
'click .submitformButton' : 'submitform',
'click .verifyFtpSettingsButon' : 'verifyFtpSettings',
'click .detectFtpRootButton' : 'detectFtpRoot',
*/

// ??
// 'click input[name=jform[summary_email]]' : 'toggleEmailPasswords'

/*
this.sampleDataLoaded = false;
*/
/**/


/*
goToPageSiteButton : function goToPageSiteButton() {

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
*/