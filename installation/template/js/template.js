/**
 * @package     Joomla.Installation
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	// Make sure that we have the Joomla object
	Joomla = window.Joomla || {};
	Joomla.installation = Joomla.installation || {};

	Joomla.serialiseForm = function( form ) {
		var i, l, obj = [], elements = form.querySelectorAll( "input, select, textarea" );
		for(i = 0, l = elements.length; i < l; i++) {
			var name = elements[i].name;
			var value = elements[i].value;
			if(name) {
				if ((elements[i].type === 'checkbox' && elements[i].checked === true) || (elements[i].type !== 'checkbox')) {
					obj.push(name.replace('[', '%5B').replace(']', '%5D') + '=' + value);
				}
			}
		}
		return obj.join("&");
	};


	/**
	 * Method to request a different page via AJAX
	 *
	 * @param  page        The name of the view to request
	 * @param  fromSubmit  Unknown use
	 *
	 * @return {Boolean}
	 */
	Joomla.goToPage = function(page, fromSubmit) {
		if (!fromSubmit) {
			Joomla.removeMessages();
			Joomla.loadingLayer("show");
		}

		if (page) {
			window.location = Joomla.baseUrl + '?view=' + page + '&layout=default';
		}

		return false;
	};

	/**
	 * Method to submit a form from the installer via AJAX
	 *
	 * @return {Boolean}
	 */
	Joomla.submitform = function(form) {
		var data = Joomla.serialiseForm(form);

		Joomla.loadingLayer("show");
		Joomla.removeMessages();

		Joomla.request({
			type     : "POST",
			url      : Jooomla.baseUrl,
			data     : data,
			dataType : 'json',
			onSuccess: function (response, xhr) {
				response = JSON.parse(response);

				if (response.messages) {
					Joomla.renderMessages(response.messages);
				}

				if (response.error) {
					Joomla.renderMessages({'error': [response.message]});
					Joomla.loadingLayer("hide");
				} else {
					Joomla.loadingLayer("hide");
					if (response.data && response.data.view) {
						Install.goToPage(response.data.view, true);
					}
				}
			},
			onError  : function (xhr) {
				Joomla.loadingLayer("hide");
				busy = false;
				try {
					var r = JSON.parse(xhr.responseText);
					Joomla.replaceTokens(r.token);
					alert(r.message);
				} catch (e) {
				}
			}
		});

		return false;
	};

	Joomla.scrollTo = function (elem, pos)
	{
		var y = elem.scrollTop;
		y += (pos - y) * 0.3;
		if (Math.abs(y-pos) < 2)
		{
			elem.scrollTop = pos;
			return;
		}
		elem.scrollTop = y;
		setTimeout(Joomla.scrollTo, 40, elem, pos);
	};

	Joomla.checkFormField = function(fields) {
		var state = [];
		fields.forEach(function(field) {
			state.push(document.formvalidator.validate(document.querySelector(field)));
		});

		if (state.indexOf(false) > -1) {
			return false;
		}
		return true;
	};

	// Init on dom content loaded event
	Joomla.makeRandomDbPrefix = function() {
		var numbers = '0123456789', letters = 'abcdefghijklmnopqrstuvwxyz', symbols = numbers + letters;
		var prefix = letters[Math.floor(Math.random() * 24)];

		for (var i = 0; i < 4; i++ ) {
			prefix += symbols[Math.floor(Math.random() * 34)];
		}

		document.getElementById('jform_db_prefix').value = prefix + '_';

		return prefix + '_';
	};

	/**
	 * Initializes JavaScript events on each request, required for AJAX
	 */
	Joomla.pageInit = function() {
		// Attach the validator
		[].slice.call(document.querySelectorAll('form.form-validate')).forEach(function(form) {
			document.formvalidator.attachToForm(form);
		});

		// Create and append the loading layer.
		Joomla.loadingLayer("load");

		// Check for FTP credentials
		Joomla.installation = Joomla.installation || {};

		// @todo FTP persistent data ?
		// Initialize the FTP installation data
		// if (sessionStorage && sessionStorage.getItem('installation-data')) {
		// 	var data = sessionStorage.getItem('installData').split(',');
		// 	Joomla.installation.data = {
		// 		ftpUsername: data[0],
		// 		ftpPassword: data[1],
		// 		ftpHost: data[2],
		// 		ftpPort: data[3],
		// 		ftpRoot: data[4]
		// 	};
		// }
		return 'Loaded...'
	};


	/**
	 * Executes the required tasks to complete site installation
	 *
	 * @param tasks       An array of install tasks to execute
	 */
	Joomla.install = function(tasks, form) {
		if (!form) {
			throw new Error('No form provided')
		}
		if (!tasks.length) {
			Joomla.goToPage('remove');
			return;
		}

		var task = tasks.shift();
		var data = Joomla.serialiseForm(form);
		Joomla.loadingLayer("show");

		Joomla.request({
			method: "POST",
			url : Joomla.baseUrl + '?task=installation.' + task,
			data: data,
			perform: true,
			onSuccess: function(response, xhr){
				response = JSON.parse(response);
				Joomla.replaceTokens(response.token);

				if (response.messages) {
					Joomla.renderMessages(response.messages);
					Joomla.goToPage(response.data.view, true);
				} else {
					Joomla.loadingLayer('hide');
					Joomla.install(tasks, form);
				}
			},
			onError:   function(xhr){
				Joomla.renderMessages([['', Joomla.JText._('JLIB_DATABASE_ERROR_DATABASE_CONNECT', 'A Database error occurred.')]]);
				Joomla.goToPage('remove');

				try {
					var r = JSON.parse(xhr.responseText);
					Joomla.replaceTokens(r.token);
					alert(r.message);
				} catch (e) {
				}
			}
		});
	};

	/* Load scripts async */
	document.addEventListener('DOMContentLoaded', function() {
		var page = document.getElementById('installer-view');

		// Set the base URL
		Joomla.baseUrl = Joomla.getOptions('system.installation').url ? Joomla.getOptions('system.installation').url.replace(/&amp;/g, '&') : 'index.php';

		// Show the container
		var container = document.getElementById('container-installation');
		if (container) {
			Joomla.installationBaseUrl = container.getAttribute('data-base-url');
			Joomla.installationBaseUrl += "installation/index.php"
		} else {
			throw new Error('Javascript required to be enabled!')
		}

		if (page && page.getAttribute('data-page-name')) {
			var script = document.querySelector('script[src*="template.js"]');
			el = document.createElement('script');
			el.src = script.src.replace("template.js", page.getAttribute('data-page-name') + '.js');
			document.head.appendChild(el);
		}

		if (container) {
			container.classList.remove('no-js');
			container.style.display = "block";
		}
	});
})();

