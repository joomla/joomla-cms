/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};

Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};
(function() {
	"use strict";

	var initResize = function(imageSrc) {
		// Amend the layout
		var tabContent = document.getElementById('myTabContent'),
			pluginControls = document.getElementById('attrib-Resize');

		tabContent.classList.add('row', 'ml-0', 'mr-0', 'p-0');
		pluginControls.classList.add('col-md-3', 'p-4');

		// Clear previous cropper
		if (Joomla.cropper) Joomla.cropper = {};

		// Initiate the cropper
		Joomla.cropperResize = new Cropper(imageSrc, {
			restore: true,
			responsive:true,
			dragMode: false,
			autoCrop: false,
			autoCropArea: 1,
			guides: false,
			center: false,
			highlight: false,
			cropBoxMovable: false,
			scalable: false,
			zoomable:false,
			cropBoxResizable: false,
			toggleDragModeOnDblclick: false,
			minContainerWidth: imageSrc.offsetWidth,
			minContainerHeight: imageSrc.offsetHeight,
		});
	};

	// Update image
	var updateResizeImage = function(data) {

		var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : 'jpg';

		var quality = document.getElementById('jform_resize_quality').value;

		Joomla.MediaManager.Edit.current.contents = Joomla.cropperResize.getCroppedCanvas(data).toDataURL("image/" + format, quality);

		// Notify the app that a change has been made
		window.dispatchEvent(new Event('mediaManager.history.point'));

		// Make sure that the plugin didn't remove the preview
		document.getElementById('image-preview').src = Joomla.MediaManager.Edit.current.contents;
	};

	// Register the Events
	Joomla.MediaManager.Edit.resize = {
		Activate: function(mediaData) {

			// Create the images for edit and preview
			var baseContainer = document.getElementById('media-manager-edit-container'),
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
			editContainer.style.display = 'none';

			editContainer.appendChild(imageSrc);
			baseContainer.appendChild(editContainer);

			previewContainer.appendChild(imagePreview);
			baseContainer.appendChild(previewContainer);

			// Initialize
			initResize(imageSrc);

			var funct = function() {
				imageSrc = document.getElementById('media-manager-edit-container').querySelector('img');

				// Set the values for the range fields
				var resizeWidth = document.getElementById('jform_resize_w'),
				    resizeHeight = document.getElementById('jform_resize_h');


				resizeWidth.min = 0;
				resizeWidth.max = imageSrc.width;
				resizeWidth.value = imageSrc.width;

				resizeHeight.min = 0;
				resizeHeight.max = imageSrc.height;
				resizeHeight.value = imageSrc.height;

				Joomla.cropperResize.aspectRatio = parseInt(resizeWidth.value) / parseInt(resizeHeight.value);

				resizeWidth.addEventListener('change', function(event) {
					var label = document.getElementById('jform_resize_w-lbl');
					var txt = label.innerText.replace(/:.*/, '');
					label.innerHTML = txt + ' : ' + event.target.value + ' px';

					Joomla.cropperResize.crop({ width: parseInt(document.getElementById('jform_resize_w').value), height: parseInt(document.getElementById('jform_resize_w').value)/ Joomla.cropperResize.aspectRatio });

					updateResizeImage({ width: parseInt(document.getElementById('jform_resize_w').value), height: parseInt(document.getElementById('jform_resize_h').value)/ Joomla.cropperResize.aspectRatio })
				});

				resizeHeight.addEventListener('change', function(event) {
					var label = document.getElementById('jform_resize_h-lbl');
					var txt = label.innerText.replace(/:.*/, '');
					label.innerHTML = txt + ' : ' + event.target.value + ' px';

					Joomla.cropperResize.crop({ width: parseInt(document.getElementById('jform_resize_h').value) * Joomla.cropperResize.aspectRatio, height: parseInt(document.getElementById('jform_resize_h').value) });

					updateResizeImage({ width: parseInt(document.getElementById('jform_resize_h').value) * Joomla.cropperResize.aspectRatio, height: parseInt(document.getElementById('jform_resize_h').value) })
				});
			};

			setTimeout(funct, 1000);
		},
		Deactivate: function() {
			if (!Joomla.cropperResize) {
				return;
			}
			// Destroy the instance
			Joomla.cropperResize.destroy();
		}
	};

})();
