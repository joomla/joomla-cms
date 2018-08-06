/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

!(function (Joomla, document) {
	'use strict';

	var inProgress = false;

	Joomla.sampledataAjax = function(type, steps, step) {
		// Get variables
		var baseUrl = 'index.php?option=com_ajax&format=json&group=sampledata';
		var options = Joomla.getOptions('sample-data');

		// Create list
		var list = document.createElement('li');
		list.classList.add('sampledata-steps-' + type + '-' + step);

		// Create paragraph
		var para = document.createElement('p');
		para.classList.add('loader-image');
		para.classList.add('text-center');

		// Create image
		var img = document.createElement('img');
		img.setAttribute('src', options.icon);
		img.setAttribute('width', 30);
		img.setAttribute('height', 30);

		// Append everything
		para.appendChild(img);
		list.appendChild(para);
		document.querySelector('.sampledata-progress-' + type + ' ul').appendChild(list);

		Joomla.request({
			url: baseUrl + '&type=' + type + '&plugin=SampledataApplyStep' + step + '&step=' + step,
			method: 'GET',
			perform: true,
			onSuccess: function(response, xhr) {
				var response = JSON.parse(response);
				// Remove loader image
				var loader = list.querySelector('.loader-image');
				loader.parentNode.removeChild(loader);

				if (response.success && response.data && response.data.length > 0) {
					var success, value, progressClass;
					var progress = document.querySelector('.sampledata-progress-' + type + ' .progress-bar');

					// Display all messages that we got
					for (var i = 0, l = response.data.length; i < l; i++) {
						value   = response.data[i];

						if (value === null) {
							continue;
						}

						success = value.success;
						progressClass = success ? 'bg-success' : 'bg-danger';

						// Display success alert
						if (success) {
							Joomla.renderMessages({success: [value.message]}, '.sampledata-steps-' + type + '-' + step);
						} else {
							Joomla.renderMessages({error: [value.message]}, '.sampledata-steps-' + type + '-' + step);
						}
					}

					// Update progress
					progress.innerText = step + '/' + steps;
					progress.style.width = (step/steps) * 100 + '%';
					progress.classList.add(progressClass);

					// Move on next step
					if (success && (step <= steps)) {
						step++;
						if (step <= steps) {
							Joomla.sampledataAjax(type, steps, step);
						}
					}

				} else {
					// Display error alert
					Joomla.renderMessages({'error': [Joomla.JText._('MOD_SAMPLEDATA_INVALID_RESPONSE')]}, '.sampledata-steps-' + type + '-' + step);

					inProgress = false;
				}
			},
			onError: function(xhr) {
				alert('Something went wrong! Please close and reopen the browser and try again!');
			}
		});
	};

	Joomla.sampledataApply = function(el) {
		var type  = el.getAttribute('data-type');
		var steps = el.getAttribute('data-steps');

		// Check whether the work in progress or we already processed with current item
		if (inProgress) {
			return;
		}

		if (el.getAttribute('data-processed')) {
			alert(Joomla.JText._('MOD_SAMPLEDATA_ITEM_ALREADY_PROCESSED'));
			return;
		}

		// Make sure that use run this not by random clicking on the page links
		if (!confirm(Joomla.JText._('MOD_SAMPLEDATA_CONFIRM_START'))) {
			return false;
		}

		// Turn on the progress container
		var progress = document.querySelectorAll('.sampledata-progress-' + type);
		for (var i = 0, l = progress.length; i < l; i++) {
			progress[i].classList.remove('d-none');
		}

		el.getAttribute('data-processed', true);

		inProgress = true;
		Joomla.sampledataAjax(type, steps, 1);
		return false;
	};

	document.addEventListener('DOMContentLoaded', function() {
		var sampleDataWrapper = document.getElementById('sample-data-wrapper');
		if (sampleDataWrapper) {
			var links = sampleDataWrapper.querySelectorAll('.apply-sample-data');
			for (var i = 0, l = links.length; i < l; i++) {
				links[i].addEventListener('click', function(e) {
					Joomla.sampledataApply(e.target);
				});
			}
		}
	});

})(Joomla, document);
