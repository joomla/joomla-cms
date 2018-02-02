/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function () {
	"use strict";

	var initCrop = function (mediaData) {
		var image = document.getElementById('image-preview');

		// Initiate the cropper
		Joomla.MediaManager.Edit.crop.cropper = new Cropper(image, {
			// viewMode: 1,
			responsive: true,
			restore: true,
			autoCrop: true,
			movable: false,
			zoomable: false,
			rotatable: false,
			autoCropArea: 1,
			// scalable: false,
			minContainerWidth: image.offsetWidth,
			minContainerHeight: image.offsetHeight,
			crop: function (e) {
				document.getElementById('jform_crop_x').value = Math.round(e.detail.x);
				document.getElementById('jform_crop_y').value = Math.round(e.detail.y);
				document.getElementById('jform_crop_width').value = Math.round(e.detail.width);
				document.getElementById('jform_crop_height').value = Math.round(e.detail.height);

				var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;

				var quality = document.getElementById('jform_crop_quality').value;

				// Update the store
				Joomla.MediaManager.Edit.current.contents = this.cropper.getCroppedCanvas().toDataURL("image/" + format, quality);

				// Notify the app that a change has been made
				window.dispatchEvent(new Event('mediaManager.history.point'));
			}
		});

		document.getElementById('jform_crop_x').addEventListener('change', function (e) {
			Joomla.MediaManager.Edit.crop.cropper.setData({x: parseInt(this.value)});
		});
		document.getElementById('jform_crop_y').addEventListener('change', function (e) {
			Joomla.MediaManager.Edit.crop.cropper.setData({y: parseInt(this.value)});
		});
		document.getElementById('jform_crop_width').addEventListener('change', function (e) {
			Joomla.MediaManager.Edit.crop.cropper.setData({width: parseInt(this.value)});
		});
		document.getElementById('jform_crop_height').addEventListener('change', function (e) {
			Joomla.MediaManager.Edit.crop.cropper.setData({height: parseInt(this.value)});
		});

		var elements = document.querySelectorAll("#jform_aspectRatio input");
		for (var i = 0; i < elements.length; i++) {
			elements[i].addEventListener('click', function (e) {
				Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(this.value);
			});
		}
	};

	// Register the Events
	Joomla.MediaManager.Edit.crop = {
		Activate: function (mediaData) {
			// Initialize
			initCrop(mediaData);
		},
		Deactivate: function () {
			if (!Joomla.MediaManager.Edit.crop.cropper) {
				return;
			}
			// Destroy the instance
			Joomla.MediaManager.Edit.crop.cropper.destroy();
		}
	};
})();
