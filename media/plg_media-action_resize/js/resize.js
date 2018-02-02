/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function () {
	"use strict";

	// Update image
	var resize = function (width, height) {
		// The image element
		var image = document.getElementById('image-source');

		// The canvas where we will resize the image
		var canvas = document.createElement("canvas");
		canvas.width = width;
		canvas.height = height;
		canvas.getContext("2d").drawImage(image, 0, 0, width, height);

		// The format
		var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : 'jpg';

		// The quality
		var quality = document.getElementById('jform_resize_quality').value;

		// Creating the data from the canvas
		Joomla.MediaManager.Edit.current.contents = canvas.toDataURL("image/" + format, quality);

		// Updating the image element
		var image = document.getElementById('image-preview');
		image.width = width;
		image.height = height;
		image.src = Joomla.MediaManager.Edit.current.contents;

		// Update the width input box
		document.getElementById('jform_resize_width').value = parseInt(width);

		// Update the height input box
		document.getElementById('jform_resize_height').value = parseInt(height);

		// Notify the app that a change has been made
		window.dispatchEvent(new Event('mediaManager.history.point'));
	};

	var initResize = function (mediaData) {
		var funct = function () {
			var image = document.getElementById('image-source');

			var resizeWidth = document.getElementById('jform_resize_width'),
				resizeHeight = document.getElementById('jform_resize_height');

			// Update the input boxes
			resizeWidth.value = image.width;
			resizeHeight.value = image.height;

			// The listeners
			resizeWidth.addEventListener('change', function () {
				resize(parseInt(this.value), parseInt(this.value) / (image.width / image.height));
			});
			resizeHeight.addEventListener('change', function () {
				resize(parseInt(this.value) * (image.width / image.height), parseInt(this.value));
			});

			// Set the values for the range fields
			var resizeWidth = document.getElementById('jform_resize_w'),
				resizeHeight = document.getElementById('jform_resize_h');

			resizeWidth.min = 0;
			resizeWidth.max = image.width;
			resizeWidth.value = image.width;

			resizeHeight.min = 0;
			resizeHeight.max = image.height;
			resizeHeight.value = image.height;

			// The listeners
			resizeWidth.addEventListener('input', function () {
				resize(parseInt(this.value), parseInt(this.value) / (image.width / image.height));
			});
			resizeHeight.addEventListener('input', function () {
				resize(parseInt(this.value) * (image.width / image.height), parseInt(this.value));
			});
		}
		setTimeout(funct, 1000);
	};

	// Register the Events
	Joomla.MediaManager.Edit.resize = {
		Activate: function (mediaData) {
			// Initialize
			initResize(mediaData);
		},
		Deactivate: function () {
		}
	};
})();
