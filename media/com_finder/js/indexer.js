/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(Joomla, document) {
	"use strict";

	Joomla.finderIndexer = function() {

		var totalItems = null;
		var batchSize  = null;
		var offset     = null;
		var progress   = null;
		var optimized  = false;
		var path       = 'index.php?option=com_finder&tmpl=component&format=json';
		var token      = '&' + document.getElementById('finder-indexer-token').getAttribute('name') + '=1';

		var initialize = function() {
			offset   = 0;
			progress = 0;

			getRequest('indexer.start');
		};

		var getRequest = function (task) {
			Joomla.request({
				url:       path + '&task=' + task + token,
				method:    'GET',
				data:      '',
				perform:   true,
				headers:   {'Content-Type': 'application/x-www-form-urlencoded'},
				onSuccess: function(response, xhr) {
					handleResponse(JSON.parse(response));
				},
				onError: function(xhr) {
					handleFailure(xhr);
				}
			});
		};

		var removeElement = function(el) {
			var element = document.getElementById(el);
			if (element) {
				return element.parentNode.removeChild(element);
			}
		};

		var handleResponse = function (json, resp) {
			var progressHeader  = document.getElementById('finder-progress-header');
			var progressMessage = document.getElementById('finder-progress-message');

			try {
				if (json === null) {
					throw resp;
				}
				if (json.error) {
					throw json;
				}
				if (json.start) {
					totalItems = json.totalItems;
				}
				offset += json.batchOffset;
				updateProgress(json.header, json.message);
				if (offset < totalItems) {
					getRequest('indexer.batch');
				} else if (!optimized) {
					optimized = true;
					getRequest('indexer.optimize');
				}
			} catch (error) {
				removeElement('progress');
				try {
					if (json.error) {
						if (progressHeader) {
							progressHeader.innerText = json.header;
							progressHeader.classList.add('finder-error');
						}
						if (progressMessage) {
							progressMessage.innerHTML = json.message;
							progressMessage.classList.add('finder-error');
						}
					}
				} catch (ignore) {
					if (error === '') {
						error = Joomla.JText._('COM_FINDER_NO_ERROR_RETURNED');
					}
					if (progressHeader) {
						progressHeader.innerText = Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED');
						progressHeader.classList.add('finder-error');
					}
					if (progressMessage) {
						progressMessage.innerHTML = error;
						progressMessage.classList.add('finder-error');
					}
				}
			}
			return true;
		};

		var handleFailure = function (xhr) {
			var progressHeader  = document.getElementById('finder-progress-header');
			var progressMessage = document.getElementById('finder-progress-message');

			json = (typeof xhr === 'object' && xhr.responseText) ? xhr.responseText : null;
			json = json ? JSON.parse(json) : null;

			removeElement('progress');

			if (json) {
				json = json.responseText != null ? Json.evaluate(json.responseText, true) : json;
			}
			var header  = json ? json.header : Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED');
			var message = json ? json.message : Joomla.JText._('COM_FINDER_MESSAGE_RETURNED') + '<br>' + json;

			if (progressHeader) {
				progressHeader.innerText = header;
				progressHeader.classList.add('finder-error');
			}
			if (progressMessage) {
				progressMessage.innerHTML = message;
				progressMessage.classList.add('finder-error');
			}
		};

		var updateProgress = function (header, message) {
			progress = (offset / totalItems) * 100;

			var progressBar     = document.getElementById('progress-bar');
			var progressHeader  = document.getElementById('finder-progress-header');
			var progressMessage = document.getElementById('finder-progress-message');

			if (progressHeader) {
				progressHeader.innerText = header;
			}

			if (progressMessage) {
				progressMessage.innerHTML = message;
			}

			if (progressBar) {
				if (progress < 100) {
					progressBar.style.width = progress + '%';
					progressBar.setAttribute('aria-valuenow', progress);
				}
				else {
					progressBar.classList.remove('bar-success');
					progressBar.classList.add('bar-warning');
					progressBar.setAttribute('aria-valuemin', 100);
					progressBar.setAttribute('aria-valuemax', 200);
					progressBar.style.width = progress + '%';
					progressBar.setAttribute('aria-valuenow', progress);
				}
				if (message === msg) {
					removeElement('progress');
					Joomla.Modal.getCurrent().close();
				}
			}
		};

		initialize();
	}
}(Joomla, document));

document.addEventListener('DOMContentLoaded', function() {
	Indexer = Joomla.finderIndexer();
});
