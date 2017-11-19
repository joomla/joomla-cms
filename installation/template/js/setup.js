/**
 * @package     Joomla.Installation
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Method to set the language for the installation UI via AJAX
 *
 * @return {Boolean}
 */
Joomla.setlanguage = function(form) {
	var data = Joomla.serialiseForm(form);

	Joomla.loadingLayer("show");
	Joomla.removeMessages();

	Joomla.request({
		url: Joomla.baseUrl,
		method: 'POST',
		data: data,
		perform: true,
		onSuccess: function(response, xhr){
			response = JSON.parse(response);
			Joomla.replaceTokens(response.token);

			if (response.messages) {
				Joomla.renderMessages(response.messages);
			}

			if (response.error) {
				Joomla.renderMessages({'error': [response.message]});
				Joomla.loadingLayer("hide");
			} else {
				Joomla.loadingLayer("hide");
				Joomla.goToPage(response.data.view, true);
			}
		},
		onError:   function(xhr){
			Joomla.loadingLayer("hide");
			try {
				var r = JSON.parse(xhr.responseText);
				Joomla.replaceTokens(r.token);
				alert(r.message);
			} catch (e) {}
		}
	});

	return false;
};

Joomla.checkInputs = function() {
	document.getElementById('jform_admin_password2').value = document.getElementById('jform_admin_password').value;

	var inputs = [].slice.call(document.querySelectorAll('input[type="password"], input[type="text"], input[type="email"], select')),
		state = true;
	inputs.forEach(function(item) {
		if (!item.valid) state = false;
	});

	// Reveal everything
	document.getElementById('installStep1').classList.add('active');
	document.getElementById('installStep2').classList.add('active');
	document.getElementById('installStep3').classList.add('active');


	if (Joomla.checkFormField(['#jform_site_name', '#jform_admin_user', '#jform_admin_email', '#jform_admin_password', '#jform_db_type', '#jform_db_host', '#jform_db_user', '#jform_db_name'])) {
		Joomla.checkDbCredentials();
	}
};


Joomla.checkDbCredentials = function() {
	Joomla.loadingLayer("show");

	var form = document.getElementById('adminForm'),
		data = Joomla.serialiseForm(form);

	Joomla.request({
		method: "POST",
		url : Joomla.installationBaseUrl + '?task=installation.dbcheck',
		data: data,
		perform: true,
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
		onSuccess: function(response, xhr){
			response = JSON.parse(response);
			Joomla.loadingLayer('hide');
			Joomla.replaceTokens(response.token);
			if (response.messages) {
				Joomla.loadingLayer('hide');
				Joomla.renderMessages(response.messages);
				// You shall not pass, DB credentials error!!!!
			} else {
				Joomla.loadingLayer('hide');

				// Run the installer - we let this handle the redirect for now
				// TODO: Convert to promises
				Joomla.install(['config'], form);
			}
		},
		onError:   function(xhr){
			Joomla.renderMessages([['', Joomla.JText._('JLIB_DATABASE_ERROR_DATABASE_CONNECT', 'A Database error occurred.')]]);
			//Install.goToPage('summary');
			Joomla.loadingLayer('hide');
			try {
				var r = JSON.parse(xhr.responseText);
				Joomla.replaceTokens(r.token);
				alert(r.message);
			} catch (e) {
			}
		}
	});
};


(function() {
	// Merge options from the session storage
	if (sessionStorage && sessionStorage.getItem('installation-data')) {
		Joomla.extend(this.options, sessionStorage.getItem('installation-data'));
	}

	Joomla.pageInit();
	var el = document.querySelector('.nav-steps.hidden');
	if (el) {
		el.classList.remove('hidden');
	}

	// Focus to the next field
	if (document.getElementById('jform_site_name')) {
		document.getElementById('jform_site_name').focus();
	}

	// Select language
	var languageEl = document.getElementById('jform_language');

	if (languageEl) {
		languageEl.addEventListener('change', function(e) {
			var form = document.getElementById('languageForm');
			Joomla.setlanguage(form)
		})
	}

	if (document.getElementById('step1')) {
		document.getElementById('step1').addEventListener('click', function(e) {
			e.preventDefault();
			if (Joomla.checkFormField(['#jform_site_name'])) {
				if (document.getElementById('languageForm')) {
					document.getElementById('languageForm').style.display = 'none';
				}
				if (document.getElementById('installStep2')) {
					document.getElementById('installStep2').classList.add('active');
					document.getElementById('installStep1').classList.remove('active');

					// Focus to the next field
					if (document.getElementById('jform_admin_user')) {
						document.getElementById('jform_admin_user').focus();
					}
				}
			}
		})
	}

	if (document.getElementById('step2')) {
		document.getElementById('step2').addEventListener('click', function(e) {
			e.preventDefault();
			if (Joomla.checkFormField(['#jform_admin_user', '#jform_admin_email', '#jform_admin_password'])) {
				if (document.getElementById('installStep3')) {
					document.getElementById('installStep3').classList.add('active');
					document.getElementById('installStep2').classList.remove('active');
					document.getElementById('setupButton').style.display = 'block';

					Joomla.makeRandomDbPrefix();

					// Focus to the next field
					if (document.getElementById('jform_db_type')) {
						document.getElementById('jform_db_type').focus();
					}
				}
			}
		});

		document.getElementById('setupButton').addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			Joomla.checkInputs();
		})
	}

})();
