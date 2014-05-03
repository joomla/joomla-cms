/**
 * @package		Joomla.Installation
 * @subpackage	JavaScript
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
var wnd = null;

function winclose () {
    wnd.close();
}

function regInstallUser () {
            var jparams = 'name=' + document.getElementById('nameReg').value + '&email=' + document.getElementById('emailReg').value + '&site=' + document.getElementById('siteReg').value;
            wnd = window.open('http://www.jokte.org/administrator/components/com_regusjokte/views/reguser/tmpl/ajaxreg.php?' + jparams, 'Comments', 'width=490,height=350,location=no,toolbars=no,status=no,scrollbars=no,titlebar=no,menubar=no');
            setTimeout("winclose()",3000);
        };

var Installation = new Class({
    initialize: function(container, base) {
        this.sampleDataLoaded = false;
        this.busy = false;
        this.container = container;
        this.spinner = new Spinner(this.container);
        this.baseUrl = base;
        this.view = '';

        this.pageInit();
    },

    pageInit: function() {
    	this.addToggler();
		// Attach the validator
		$$('form.form-validate').each(function(form){ this.attachToForm(form); }, document.formvalidator);

		if (this.view == 'site' && this.sampleDataLoaded) {
			var select = document.id('jform_db_old');
			var button = document.id('theDefault').children[0];
			button.setAttribute('disabled', 'disabled');
			select.setAttribute('disabled', 'disabled');
			button.setAttribute('value', Joomla.JText._('INSTL_SITE_SAMPLE_LOADED', 'Datos de ejemplo cargados correctamente.'));
		}
    },

    submitform: function() {
		var form = document.id('adminForm');

		if (this.busy) {
			alert(Joomla.JText._('INSTL_PROCESS_BUSY', 'Proceso en progreso. Por favor espere...'));
			return false;
		}

		var req = new Request.JSON({
			method: 'post',
			url: this.baseUrl,
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
					window.location = this.baseUrl+'?view='+r.data.view;
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
		});
		req.post(form.toQueryString()+'&task='+form.task.value+'&format=json');

		return false;
	},

	goToPage: function(page, fromSubmit) {
		var url = this.baseUrl+'?tmpl=body&view='+page;
		var req = new Request.HTML({
			method: 'get',
			url: url,
			onRequest: function() {
				if (!fromSubmit) {
					Joomla.removeMessages();
					this.spinner.show(true);
				}
			}.bind(this),
			onSuccess: function (r) {
				this.view = page;
				document.id(this.container).empty().adopt(r);

				// Attach JS behaviors to the newly loaded HTML
				this.pageInit();

				this.spinner.hide(true);
				this.busy = false;

				//Take care of the sidebar
				var active = $$('.active');
				active.removeClass('active');
				var nextStep = document.id(page);
				nextStep.addClass('active');
			}.bind(this)
		}).send();

		return false;
	},

	/**
 	 * Method to install sample data via AJAX request.
	 */
	sampleData: function(el, filename) {
		this.busy = true;
		sample_data_spinner = new Spinner('sample-data-region');
		sample_data_spinner.show(true);
		el = document.id(el);
		filename = document.id(filename);
		var req = new Request.JSON({
			method: 'get',
			url: 'index.php?'+document.id(el.form).toQueryString(),
			data: {'task':'setup.loadSampleData', 'format':'json'},
			onRequest: function() {
				el.set('disabled', 'disabled');
				filename.set('disabled', 'disabled');
				document.id('theDefaultError').setStyle('display','none');
			},
			onSuccess: function(r) {
				if (r) {
					Joomla.replaceTokens(r.token);
					this.sampleDataLoaded = r.data.sampleDataLoaded;
					if (r.error == false) {
						el.set('value', Joomla.JText._('INSTL_SITE_SAMPLE_LOADED', 'Datos de ejemplo cargados correctamente.'));
						el.set('onclick','');
						el.set('disabled', 'disabled');
						filename.set('disabled', 'disabled');
						document.id('jform_sample_installed').set('value','1');
					} else {
						document.id('theDefaultError').setStyle('display','block');
						document.id('theDefaultErrorMessage').set('html', r.message);
						el.set('disabled', '');
						filename.set('disabled', '');
					}
				} else {
					document.id('theDefaultError').setStyle('display','block');
					document.id('theDefaultErrorMessage').set('html', response );
					el.set('disabled', 'disabled');
					filename.set('disabled', 'disabled');
				}
				this.busy = false;
				sample_data_spinner.hide(true);
			}.bind(this),
			onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token);
					document.id('theDefaultError').setStyle('display','block');
					document.id('theDefaultErrorMessage').set('html', r.message);
				}
				el.set('disabled', '');
				filename.set('disabled', '');
				this.busy = false;
				sample_data_spinner.hide(true);
			}
		}).send();
	},

	/**
 	 * Method to detect the FTP root via AJAX request.
 	 */
	detectFtpRoot: function(el) {
		el = document.id(el);
		var req = new Request.JSON({
			method: 'get',
			url: 'index.php?'+document.id(el.form).toQueryString(),
			data: {'task':'setup.detectFtpRoot', 'format':'json'},
			onRequest: function() {
				el.set('disabled', 'disabled');
			},
			onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token)
					alert(xhr.status+': '+r.message);
				} else {
					alert(xhr.status+': '+xhr.statusText);
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

	verifyFtpSettings: function(el) {
		// make the ajax call
		el = document.id(el);
		var req = new Request.JSON({
			method: 'get',
			url: 'index.php?'+document.id(el.form).toQueryString(),
			data: {'task':'setup.verifyFtpSettings', 'format':'json'},
			onRequest: function() {
				el.set('disabled', 'disabled'); },
				onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token)
					alert(xhr.status+': '+r.message);
				} else {
					alert(xhr.status+': '+xhr.statusText);
				}
			},
			onSuccess: function(r) {
				if (r) {
					Joomla.replaceTokens(r.token)
					if (r.error == false) {
						alert(Joomla.JText._('INSTL_FTP_SETTINGS_CORRECT', 'Opciones correctas'));
					} else {
						alert(r.message);
					}
				}
				el.set('disabled', '');
			},
			onError: function(response) {
				alert('error');
			}
		}).send();
	},

	/**
	 * Method to remove the installation Folder after a successful installation.
 	 */
	removeFolder: function(el) {
		el = document.id(el);
		var req = new Request.JSON({
			method: 'get',
			url: 'index.php?'+document.id(el.form).toQueryString(),
			data: {'task':'setup.removeFolder', 'format':'json'},
			onRequest: function() {
				el.set('disabled', 'disabled');
				document.id('theDefaultError').setStyle('display','none');
			},
			onComplete: function(r) {
				if (r) {
					Joomla.replaceTokens(r.token);
					if (r.error == false) {
						el.set('value', r.data.text);
						el.set('onclick','');
						el.set('disabled', 'disabled');
					} else {
						document.id('theDefaultError').setStyle('display','block');
						document.id('theDefaultErrorMessage').set('html', r.message);
						el.set('disabled', '');
					}
				} else {
					document.id('theDefaultError').setStyle('display','block');
					document.id('theDefaultErrorMessage').set('html', response );
					el.set('disabled', 'disabled');
				}
			},
			onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					Joomla.replaceTokens(r.token);
					document.id('theDefaultError').setStyle('display','block');
					document.id('theDefaultErrorMessage').set('html', r.message);
				}
				el.set('disabled', '');
			}
		}).send();
	},

	addToggler: function() {
		new Fx.Accordion($$('h4.moofx-toggler'), $$('div.moofx-slider'), {
			onActive: function(toggler, i) {
				toggler.addClass('moofx-toggler-down');
			},
			onBackground: function(toggler, i) {
				toggler.removeClass('moofx-toggler-down');
			},
			duration: 300,
			opacity: false,
			alwaysHide:true,
			show: 1
		});
    }
});
