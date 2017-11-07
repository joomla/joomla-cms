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
		// Amend the layout
		var tabContent = document.getElementById('myTabContent'),
			pluginControls = document.getElementById('attrib-crop');

		tabContent.classList.add('row', 'ml-0', 'mr-0', 'p-0');
		pluginControls.classList.add('col-md-3', 'p-4');

		// Create the images for edit and preview
		var baseContainer = document.getElementById('media-manager-edit-container'),
			editContainer = document.createElement('div'),
			previewContainer = document.createElement('div'),
			imageSrc = document.createElement('img'),
			imagePreview = document.createElement('img');

		imageSrc.src = mediaData.contents;
		imagePreview.src = mediaData.contents;
		imagePreview.id = 'image-preview';
		imageSrc.style.maxWidth = '100%';
		imagePreview.style.maxWidth = '100%';

		editContainer.appendChild(imageSrc);
		baseContainer.appendChild(editContainer);


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

				var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;

				var quality = document.getElementById('jform_crop_quality').value;

				// Update the store
				Joomla.MediaManager.Edit.current.contents = Joomla.cropperCrop.getCroppedCanvas().toDataURL("image/" + format, quality);

				// Notify the app that a change has been made
				window.dispatchEvent(new Event('mediaManager.history.point'));
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
