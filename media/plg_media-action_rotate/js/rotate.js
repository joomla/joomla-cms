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
	var rotate = function (angle) {
		// The image element
		var image = document.getElementById('image-source');

		// The canvas where we will resize the image
		var canvas = document.createElement("canvas");

		// Pseudo rectangle calculation
		if ((angle >= 0 && angle < 45) || (angle >= 135 && angle < 225) || (angle >= 315 && angle <= 360)) {
			canvas.width = image.width;
			canvas.height = image.height;
		} else {
			// swap
			canvas.width = image.height;
			canvas.height = image.width;
		}
		var ctx = canvas.getContext("2d");
		ctx.translate(canvas.width / 2, canvas.height / 2);
		ctx.rotate(angle * Math.PI / 180);
		ctx.drawImage(image, -image.width / 2, -image.height / 2);

		// The format
		var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : 'jpg';

		// The quality
		var quality = document.getElementById('jform_rotate_quality').value;

		// Creating the data from the canvas
		Joomla.MediaManager.Edit.current.contents = canvas.toDataURL("image/" + format, quality);

		// Updating the image element
		var image = document.getElementById('image-preview');
		image.width = canvas.width;
		image.height = canvas.height;
		image.src = Joomla.MediaManager.Edit.current.contents;

		// Update the width input box
		document.getElementById('jform_rotate_angle').value = angle;

		// Update the height input box
		document.getElementById('jform_rotate_a').value = angle;

		// Notify the app that a change has been made
		window.dispatchEvent(new Event('mediaManager.history.point'));
	};

	var initRotate = function (mediaData) {
		var funct = function () {
			// The listeners
			document.getElementById('jform_rotate_angle').addEventListener('change', function () {
				rotate(parseInt(this.value));
			});
			document.getElementById('jform_rotate_a').addEventListener('input', function () {
				rotate(parseInt(this.value));
			});
		}
		setTimeout(funct, 1000);
	};

	// Register the Events
	Joomla.MediaManager.Edit.rotate = {
		Activate: function (mediaData) {
			// Initialize
			initRotate(mediaData);
		},
		Deactivate: function () {
		}
	};
})();
