/**
<<<<<<< HEAD
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
=======
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
>>>>>>> staging
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
	'use strict';

	var data = {
		'option' : 'com_ajax',
		'group'  : 'system',
		'plugin' : 'renderStatsMessage',
		'format' : 'raw'
	};

	Joomla.initStatsEvents = function() {
		var messageContainer = document.getElementById('system-message-container');
		var joomlaAlert      = messageContainer.querySelector('.js-pstats-alert');
		var detailsContainer = messageContainer.querySelector('.js-pstats-data-details');
		var details = messageContainer.querySelector('.js-pstats-btn-details');
		var always  = messageContainer.querySelector('.js-pstats-btn-allow-always');
		var once    = messageContainer.querySelector('.js-pstats-btn-allow-once');
		var never   = messageContainer.querySelector('.js-pstats-btn-allow-never');

		// Show details about the information being sent
		document.addEventListener('click', function(event) {
			if (event.target.classList.contains('js-pstats-btn-details')) {
				event.preventDefault();
				detailsContainer.classList.toggle('d-none');
			}
		});

		// Always allow
		document.addEventListener('click', function(event) {
			if (event.target.classList.contains('js-pstats-btn-allow-always')) {
				event.preventDefault();

				// Remove message
				joomlaAlert.close();

				// Set data
				data.plugin = 'sendAlways';

				Joomla.getJson(data);
			}
		});

		// Allow once
		document.addEventListener('click', function(event) {
			if (event.target.classList.contains('js-pstats-btn-allow-once')) {
				event.preventDefault();

				// Remove message
				joomlaAlert.close();

				// Set data
				data.plugin = 'sendOnce';

				Joomla.getJson(data);
			}
		});

		// Never allow
		document.addEventListener('click', function(event) {
			if (event.target.classList.contains('js-pstats-btn-allow-never')) {
				event.preventDefault();

				// Remove message
				joomlaAlert.close();

				// Set data
				data.plugin = 'sendNever';

				Joomla.getJson(data);
			}
		});
	}

	Joomla.getJson = function(data) {
		var messageContainer = document.getElementById('system-message-container');
		Joomla.request({
			url: 'index.php?option=' + data.option + '&group=' + data.group + '&plugin=' + data.plugin +  '&format=' + data.format,
			method: 'GET',
			perform: true,
			headers: {'Content-Type': 'application/json'},
			onSuccess: function(response, xhr) {
				try {
					response = JSON.parse(response);
				} catch(e) {
					throw new Error(e);
				}

				if (response && response.html) {
					messageContainer.innerHTML = response.html;
					messageContainer.querySelector('.js-pstats-alert').style.display = 'block';

					Joomla.initStatsEvents();
				}
			},
			onError: function(xhr) {
				Joomla.renderMessages({error: [xhr.response]});
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function() {
		data.plugin = 'sendStats';
		Joomla.getJson(data);
	});

})(Joomla, document);
