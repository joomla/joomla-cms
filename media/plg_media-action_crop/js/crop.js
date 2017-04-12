/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};

Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function() {
	"use strict";

	var initCrop = function(mediaData) {

		// Create the images for edit and preview
		var baseContainer = document.getElementById('media-manager-edit-container'),
			editH3 = document.createElement('h3'),
			previewH3 = document.createElement('h3'),
			editContainer = document.createElement('div'),
			previewContainer = document.createElement('div'),
			imageSrc = document.createElement('img'),
			imagePreview = document.createElement('img');

		imageSrc.src = mediaData.contents;
		imagePreview.src = mediaData.contents;
		imagePreview.id = 'image-preview';
		imageSrc.style.maxWidth = '100%';
		imagePreview.style.maxWidth = '100%';
		editH3.innerText = 'Edit area:';
		previewH3.innerText = 'Actual preview:';

		editContainer.appendChild(editH3);
		editContainer.appendChild(imageSrc);
		baseContainer.appendChild(editContainer);

		previewContainer.appendChild(previewH3);
		previewContainer.appendChild(imagePreview);
		baseContainer.appendChild(previewContainer);

		// Clear previous cropper
		if (Joomla.cropper) Joomla.cropper = {};

		// Initiate the cropper
		Joomla.cropperCrop = new Cropper(imageSrc, {
			// viewMode: 1,
			responsive:true,
			restore:true,
			autoCrop:true,
			movable: false,
			zoomable: false,
			rotatable: false,
			autoCropArea: 1,
			// scalable: false,
			minContainerWidth: imageSrc.offsetWidth,
			minContainerHeight: imageSrc.offsetHeight,
			crop: function(e) {
				document.getElementById('jform_crop_x').value = e.detail.x;
				document.getElementById('jform_crop_y').value = e.detail.y;
				document.getElementById('jform_crop_width').value = e.detail.width;
				document.getElementById('jform_crop_height').value = e.detail.height;

				var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : 'jpg';

				// Update the store
				Joomla.MediaManager.Edit.current.contents = Joomla.cropperCrop.getCroppedCanvas().toDataURL("image/" + format, 1.0);

				// Notify the app that a change has been made
				window.dispatchEvent(new Event('mediaManager.history.point'));

				// Make sure that the plugin didn't remove the preview
				document.getElementById('image-preview').src = Joomla.MediaManager.Edit.current.contents;
			}
		});

		document.getElementById('jform_crop_x').value = 0;
		document.getElementById('jform_crop_y').value = 0;
		document.getElementById('jform_crop_width').value = imageSrc.offsetWidth;
		document.getElementById('jform_crop_height').value = imageSrc.offsetHeight;
	};

	// Register the Events
	Joomla.MediaManager.Edit.crop = {
		Activate: function(mediaData) {
			// Initialize
			initCrop(mediaData);
		},
		Deactivate: function() {
			if (!Joomla.cropperCrop) {
				return;
			}
			// Destroy the instance
			Joomla.cropperCrop.destroy();
		}
	};
})();
