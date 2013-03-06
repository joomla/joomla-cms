/**
 * @package		Joomla.Installation
 * @subpackage	JavaScript
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

var Installation = new Class({
	/**
	 * Initializes the Installation class
	 *
	 * @param container  The name of the container which the view is rendered in
	 * @param base       The URL of the current page
	 */
	initialize: function(container, base) {
		this.busy = false;
		this.container = container;
		this.spinner = new Spinner(this.container);
		this.baseUrl = base;
		this.view = '';

		this.pageInit();
	},

	/**
	 * Initializes JavaScript events on each request, required for AJAX
	 */
	pageInit: function() {
		this.addToggler();
		// Attach the validator
		$$('form.form-validate').each(function(form) {
			this.attachToForm(form);
		}, document.formvalidator);
	},

	/**
	 * Method to submit a form from the installer via AJAX
	 *
	 * @return {Boolean}
	 */
	submitform: function() {
		var form = document.id('adminForm');

		if (this.busy) {
			alert(Joomla.JText._('INSTL_PROCESS_BUSY', 'Process is in progress. Please wait...'));
			return false;
		}

		var req = new Request.JSON({
			url      : this.baseUrl,
			data     : form,
			format   : 'json',
			onRequest: function() {
				this.spinner.show(true);
				this.busy = true;
				Joomla.removeMessages();
			}.bind(this),
			onSuccess: function(r) {
				Joomla.replaceTokens(r.token);
				if (r.messages) {
					Joomla.renderMessages(r.messages);
				}
				var lang = $$('html').getProperty('lang')[0];
				if (r.lang !== null && lang.toLowerCase() === r.lang.toLowerCase()) {
					Install.goToPage(r.data.view, true);
				} else {
					window.location = this.baseUrl + '?view=' + r.data.view;
				}
			}.bind(this),
			onFailure: function(xhr) {
				this.spinner.hide(true);
				this.busy = false;
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token);
					alert(r.message);
				}
			}.bind(this)
		}).send();

		return false;
	},

	/**
	 * Method to set the language for the installation UI via AJAX
	 *
	 * @return {Boolean}
	 */
	setlanguage: function() {
		var form = document.id('languageForm');

		if (this.busy) {
			alert(Joomla.JText._('INSTL_PROCESS_BUSY', 'Process is in progress. Please wait...'));
			return false;
		}

		var req = new Request.JSON({
			url      : this.baseUrl,
			data     : form,
			format   : 'json',
			onRequest: function() {
				this.spinner.show(true);
				this.busy = true;
				Joomla.removeMessages();
			}.bind(this),
			onSuccess: function(r) {
				Joomla.replaceTokens(r.token);
				if (r.messages) {
					Joomla.renderMessages(r.messages);
				}
				var lang = $$('html').getProperty('lang')[0];
				if (lang.toLowerCase() === r.lang.toLowerCase()) {
					Install.goToPage(r.data.view, true);
				} else {
					window.location = this.baseUrl + '?view=' + r.data.view;
				}
			}.bind(this),
			onFailure: function(xhr) {
				this.spinner.hide(true);
				this.busy = false;
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token);
					alert(r.message);
				}
			}.bind(this)
		}).send();

		return false;
	},

	/**
	 * Method to request a different page via AJAX
	 *
	 * @param  page        The name of the view to request
	 * @param  fromSubmit  Unknown use
	 *
	 * @return {Boolean}
	 */
	goToPage: function(page, fromSubmit) {
		var req = new Request.HTML({
			method   : 'get',
			url      : this.baseUrl + '?tmpl=body&view=' + page,
			update   : this.container,
			onRequest: function() {
				if (!fromSubmit) {
					Joomla.removeMessages();
					this.spinner.show(true);
				}
			}.bind(this),
			onSuccess: function(r) {
				this.view = page;

				// Attach JS behaviors to the newly loaded HTML
				this.pageInit();

				this.spinner.hide(true);
				this.busy = false;

				initElements();
			}.bind(this)
		}).send();

		return false;
	},

	/**
	 * Executes the required tasks to complete site installation
	 *
	 * @param tasks       An array of install tasks to execute
	 * @param step_width  The width of the progress bar element
	 */
	install: function(tasks, step_width) {
		var progress = document.id('install_progress').getElement('div.bar');

		if (!tasks.length) {
			progress.setStyle('width', (progress.getStyle('width').toFloat() + (step_width * 3)) + '%');
			this.goToPage('complete');
			return;
		}

		if (!step_width) {
			var step_width = (100 / tasks.length) / 11;
		}

		var task = tasks.shift();
		var form = document.id('adminForm');
		var tr = document.id('install_' + task);
		var taskSpinner = tr.getElement('div.spinner');

		var req = new Request.JSON({
			url      : this.baseUrl + '?task=Install' + task,
			data     : form,
			format   : 'json',
			onRequest: function() {
				progress.setStyle('width', (progress.getStyle('width').toFloat() + step_width) + '%');
				tr.addClass('active');
				taskSpinner.setStyle('visibility', 'visible');
			}.bind(this),
			onSuccess: function(r) {
				Joomla.replaceTokens(r.token);
				if (r.messages) {
					Joomla.renderMessages(r.messages);
					Install.goToPage(r.data.view, true);
				} else {
					progress.setStyle('width', (progress.getStyle('width').toFloat() + (step_width * 10)) + '%');
					tr.removeClass('active');
					taskSpinner.setStyle('visibility', 'hidden');

					this.install(tasks, step_width);
				}
			}.bind(this),
			onError  : function(text, error) {
				Joomla.renderMessages([
					['', Joomla.JText._('JLIB_DATABASE_ERROR_DATABASE_CONNECT', 'A Database error occurred.')]
				]);
				Install.goToPage('summary');
			}.bind(this),
			onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token);
					alert(r.message);
				}
			}.bind(this)
		}).send();
	},

	/**
	 * Method to detect the FTP root via AJAX request.
	 *
	 * @param el  The page element requesting the event
	 */
	detectFtpRoot: function(el) {
		el = document.id(el);
		var req = new Request.JSON({
			url      : this.baseUrl + '?task=detectftproot',
			data     : document.id(el.form),
			format   : 'json',
			onRequest: function() {
				el.set('disabled', 'disabled');
			},
			onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token)
					alert(xhr.status + ': ' + r.message);
				} else {
					alert(xhr.status + ': ' + xhr.statusText);
				}
			},
			onSuccess: function(r) {
				if (r) {
					Joomla.replaceTokens(r.token)
					if (r.error == false) {
						document.id('jform_ftp_root').set('value', r.data.root);
					} else {
						alert(r.message);
					}
				}
				el.set('disabled', '');
			}
		}).send();
	},

	/**
	 * Method to verify the supplied FTP settings are valid via AJAX request.
	 *
	 * @param el  The page element requesting the event
	 */
	verifyFtpSettings: function(el) {
		// make the ajax call
		el = document.id(el);
		var req = new Request.JSON({
			url      : this.baseUrl + '?task=verifyftpsettings',
			data     : document.id(el.form),
			format   : 'json',
			onRequest: function() {
				el.set('disabled', 'disabled');
			},
			onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token)
					alert(xhr.status + ': ' + r.message);
				} else {
					alert(xhr.status + ': ' + xhr.statusText);
				}
			},
			onSuccess: function(r) {
				if (r) {
					Joomla.replaceTokens(r.token)
					if (r.error == false) {
						alert(Joomla.JText._('INSTL_FTP_SETTINGS_CORRECT', 'Settings correct'));
					} else {
						alert(r.message);
					}
				}
				el.set('disabled', '');
			},
			onError  : function(response) {
				alert('error');
			}
		}).send();
	},

	/**
	 * Method to remove the installation Folder after a successful installation.
	 *
	 * @param el  The page element requesting the event
	 */
	removeFolder: function(el) {
		el = document.id(el);
		if (document.getElementById("languages")) {
			document.id(languages).fade('out');
		}
		var req = new Request.JSON({
			url       : this.baseUrl + '?task=removefolder',
			data      : document.id(el.form),
			format    : 'json',
			onRequest : function() {
				el.set('disabled', 'disabled');
				document.id('theDefaultError').setStyle('display', 'none');
			},
			onComplete: function(r) {
				if (r) {
					Joomla.replaceTokens(r.token);
					if (r.error == false) {
						el.set('value', r.data.text);
						el.set('onclick', '');
						el.set('disabled', 'disabled');
						// Stop keep alive requests
						window.keepAlive = function() {
						};
					} else {
						document.id('theDefaultError').setStyle('display', 'block');
						document.id('theDefaultErrorMessage').set('html', r.message);
						el.set('disabled', '');
					}
				} else {
					document.id('theDefaultError').setStyle('display', 'block');
					document.id('theDefaultErrorMessage').set('html', r);
					el.set('disabled', 'disabled');
				}
			},
			onFailure : function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token);
					document.id('theDefaultError').setStyle('display', 'block');
					document.id('theDefaultErrorMessage').set('html', r.message);
				}
				el.set('disabled', '');
			}
		}).send();
	},

	addToggler: function() {
		new Fx.Accordion($$('h4.moofx-toggler'), $$('div.moofx-slider'), {
			onActive    : function(toggler, i) {
				toggler.addClass('moofx-toggler-down');
			},
			onBackground: function(toggler, i) {
				toggler.removeClass('moofx-toggler-down');
			},
			duration    : 300,
			opacity     : false,
			alwaysHide  : true,
			show        : 1
		});
	},

	toggle: function(id, el, value) {
		var val = document.getElement('input[name=jform[' + el + ']]:checked').value;
		if (val == value) {
			document.id(id).setStyle('display', '');
		} else {
			document.id(id).setStyle('display', 'none');
		}
	}
});
