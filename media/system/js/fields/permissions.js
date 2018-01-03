/**
 * @copyright       Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
	'use strict';

	/**
	 * Function to send Permissions via Ajax to Com-Config Application Controller
	 */
	Joomla.sendPermissions = function(event) {
		// Set the icon while storing the values
		var icon = document.getElementById('icon_' + this.id);
		icon.removeAttribute('class');
		icon.setAttribute('class', 'fa fa-spinner fa-spin');

		// Get values and prepare GET-Parameter
		var asset     = 'not',
			component = Joomla.getUrlParam('component'),
			extension = Joomla.getUrlParam('extension'),
			option    = Joomla.getUrlParam('option'),
			view      = Joomla.getUrlParam('view'),
			title     = component,
			value     = this.value,
			context   = '';

		if (document.getElementById('jform_context')) {
			context = document.getElementById('jform_context').value;
			context = context.split('.')[0];
		}

		if (option == 'com_config' && component == false && extension == false) {
			asset = 'root.1';
		}
		else if (extension == false && view == 'component') {
			asset = component;
		}
		else if (context) {
			if (view == 'group') {
				asset = context + '.fieldgroup.' + Joomla.getUrlParam('id');
			}
			else {
				asset = context + '.field.' + Joomla.getUrlParam('id');
			}
			title = document.getElementById('jform_title').value;
		}
		else if (extension != false && view != false) {
			asset = extension + '.' + view + '.' + Joomla.getUrlParam('id');
			title = document.getElementById('jform_title').value;
		}
		else if (extension == false && view != false) {
			asset = option + '.' + view + '.' + Joomla.getUrlParam('id');
			title = document.getElementById('jform_title').value;
		}

		var id                  = this.id.replace('jform_rules_', ''),
			lastUnderscoreIndex = id.lastIndexOf('_');

		var permissionData = {
			comp   : asset,
			action : id.substring(0, lastUnderscoreIndex),
			rule   : id.substring(lastUnderscoreIndex + 1),
			value  : value,
			title  : title
		};

		// Remove JS messages, if they exist.
		Joomla.removeMessages();

		// Ajax request
		// TO-DO: Move to Joomla.request
		jQuery.ajax({
			method: 'POST',
			url: document.getElementById('permissions-sliders').getAttribute('data-ajaxuri'),
			data: permissionData,
			datatype: 'json'
		})
		.fail(function (jqXHR, textStatus, error) {
			// Remove the spinning icon.
			icon.removeAttribute('style');

			Joomla.renderMessages(Joomla.ajaxErrorsMessages(jqXHR, textStatus, error));

			icon.setAttribute('class', 'fa fa-times');
		})
		.done(function (response) {
			// Remove the spinning icon.
			icon.removeAttribute('style');

			if (response.data) {
				// Check if everything is OK
				if (response.data.result == true) {
					icon.setAttribute('class', 'fa fa-check');

					var badgeSpan = event.target.parentNode.parentNode.nextElementSibling.querySelector('span');
					badgeSpan.removeAttribute('class');
					badgeSpan.setAttribute('class', response.data['class']);
					badgeSpan.innerHTML = response.data.text;
				}
			}

			// Render messages, if any. There are only message in case of errors.
			if (typeof response.messages == 'object' && response.messages !== null) {
				Joomla.renderMessages(response.messages);

				if (response.data && response.data.result == true)
				{
					icon.setAttribute('class', 'fa fa-check');
				} else {
					icon.setAttribute('class', 'fa fa-times');
				}
			}
		});
	}

	/**
	 * Function to get parameters out of the URL
	 */
	Joomla.getUrlParam = function(variable) {
		var query = window.location.search.substring(1);
		var vars = query.split('&');
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split('=');
			if (pair[0] == variable) {
				return pair[1];
			}
		}
		return false;
	}

})(Joomla, document);
