/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};

(function() {
	"use strict";

	// Get the options from Joomla.optionStorage
	var options = Joomla.getOptions('com_media', {});

	if (!options) {
		// @TODO Throw an alert
		return;
	}

	// Initiate the registry
	Joomla.MediaManager.Edit.original = {
		filename: options.uploadPath.split('/').pop(),
		extension: options.uploadPath.split('.').pop(),
		contents: 'data:image/' + options.uploadPath.split('.').pop() + ';base64,' +  options.contents
	};
	Joomla.MediaManager.Edit.history= {};
	Joomla.MediaManager.Edit.current= {};

	// Reset the image to the initial state
	Joomla.MediaManager.Edit.Reset = function(current) {
		if (!current || (current && current !== true)) {
			Joomla.MediaManager.Edit.current.contents = Joomla.MediaManager.Edit.original.contents;
		}

		// Clear the DOM
		document.getElementById('media-manager-edit-container').innerHTML = '';

		// Reactivate the current plugin
		var links = [].slice.call(document.querySelectorAll('[data-toggle="tab"]'));

		for (var i = 0, l = links.length; i < l; i++){

			if (links[i].classList.contains('active')) {
				Joomla.MediaManager.Edit[links[i].hash.replace('#attrib-', '').toLowerCase()].Deactivate();
				if (!current || (current && current !== true)) {
					Joomla.MediaManager.Edit[links[i].hash.replace('#attrib-', '').toLowerCase()].Activate(Joomla.MediaManager.Edit.original);
				} else {
					Joomla.MediaManager.Edit[links[i].hash.replace('#attrib-', '').toLowerCase()].Activate(Joomla.MediaManager.Edit.current);
				}
				break;
			}
		}
	};

	// Create history entry
	window.addEventListener('mediaManager.history.point', function() {
		if (Joomla.MediaManager.Edit.original !== Joomla.MediaManager.Edit.current.contents) {
			var key = Object.keys(Joomla.MediaManager.Edit.history).length;
			if (Joomla.MediaManager.Edit.history[key] && Joomla.MediaManager.Edit.history[key - 1] && Joomla.MediaManager.Edit.history[key] === Joomla.MediaManager.Edit.history[key - 1]) {
				return;
			}
			Joomla.MediaManager.Edit.history[key + 1] = Joomla.MediaManager.Edit.current.contents;
		}
	});

	// @TODO History
	Joomla.MediaManager.Edit.Undo = function() {};
	// @TODO History
	Joomla.MediaManager.Edit.Redo = function() {};

	// @TODO Create the progress bar
	Joomla.MediaManager.Edit.createProgressBar = function() {};

	// @TODO Update the progress bar
	Joomla.MediaManager.Edit.updateProgressBar = function(position) {};

	// @TODO Remove the progress bar
	Joomla.MediaManager.Edit.removeProgressBar = function() {};

	// Customize the buttons
	Joomla.submitbutton = function(task) {
		var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension,
		    pathName = window.location.pathname.replace(/&view=file.*/g, ''),
			name = options.uploadPath.split('/').pop(),
			forUpload = {
				'name': name,
				'content': Joomla.MediaManager.Edit.current.contents.replace('data:image/' + format + ';base64,', '')
			},
			uploadPath = options.uploadPath,
			url = options.apiBaseUrl + '&task=api.files&path=' + uploadPath,
			type = 'application/json';

		forUpload[options.csrfToken] = "1";

		var fileDirectory = uploadPath.split('/');
		fileDirectory.pop();
		fileDirectory = fileDirectory.join('/');

		// If we are in root add a backslash
		if (fileDirectory.endsWith(':')) {
			fileDirectory = fileDirectory + '/';
		}

		switch (task) {
			case 'apply':
				Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type);
				Joomla.MediaManager.Edit.Reset(true);
				break;
			case 'save':
				Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type);
				window.location = pathName + '?option=com_media&path=' + fileDirectory;
				break;
			case 'cancel':
				window.location = pathName + '?option=com_media&path=' + fileDirectory;
				break;
			case 'reset':
				Joomla.MediaManager.Edit.Reset('initial');
				break;
			case 'undo':
				// @TODO magic goes here
				break;
			case 'redo':
				// @TODO other magic goes here
				break;
		}
	};

	/**
	 * @TODO Extend Joomla.request and drop this code!!!!
	 */
	// The upload object
	Joomla.UploadFile = {};

	/**
	 * @TODO Extend Joomla.request and drop this code!!!!
	 */
	Joomla.UploadFile.exec = function (name, data, uploadPath, url, type) {

		var xhr = new XMLHttpRequest();

		xhr.upload.onprogress = function(e) {
			Joomla.MediaManager.Edit.updateProgressBar((e.loaded / e.total) * 100);
		};

		xhr.onload = function() {
			try {
				var resp = JSON.parse(xhr.responseText);
			} catch (e) {
				var resp = null;
			}

			if (resp) {
				if (xhr.status == 200) {
					if (resp.success == true) {
						Joomla.MediaManager.Edit.removeProgressBar();
					}

					if (resp.status == '1') {
						Joomla.renderMessages({'success': [resp.message]}, 'true');
						Joomla.MediaManager.Edit.removeProgressBar();
					}
				}
			} else {
				Joomla.MediaManager.Edit.removeProgressBar();
			}
		};

		xhr.onerror = function() {
			Joomla.MediaManager.Edit.removeProgressBar();
		};

		xhr.open("PUT", url, true);
		xhr.setRequestHeader('Content-Type', type);
		Joomla.MediaManager.Edit.createProgressBar();
		xhr.send(data);
	};

	// Once the DOM is ready, initialize everything
	document.addEventListener('DOMContentLoaded', function() {
		// @TODO This needs a good refactoring once we'll get the new UI/C-E
		// Crap to satisfy jQuery's slowlyness!!!
		var func = function() {
			var links = [].slice.call(document.querySelectorAll('[data-toggle="tab"]'));

			if (links.length) {
				// Couple the tabs with the plugin objects
				for (var i = 0, l = links.length; i < l; i++){
					jQuery(links[i]).on('shown.bs.tab', function(event) {
						var data, contents;
						if (event.relatedTarget) {
							Joomla.MediaManager.Edit[event.relatedTarget.hash.replace('#attrib-', '').toLowerCase()].Deactivate();

							// Clear the DOM
							document.getElementById('media-manager-edit-container').innerHTML = '';
						}

						if (!contents in Joomla.MediaManager.Edit.current) {
							data = Joomla.MediaManager.Edit.original;
						} else {
							data = Joomla.MediaManager.Edit.current;
						}

						Joomla.MediaManager.Edit[event.target.hash.replace('#attrib-', '').toLowerCase()].Activate(data);
					});
				}

				// Activate the first plugin
				Joomla.MediaManager.Edit[links[0].hash.replace('#attrib-', '').toLowerCase()].Activate(Joomla.MediaManager.Edit.original);
			}
		};

		setTimeout(func, 100); // jQuery...
	});
})();