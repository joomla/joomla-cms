/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function () {
	"use strict";
	var path;
	// resolveing the path
	path = getQueryVariable('path').split(':');
	path = '/images' + path[1];

	// Setting the focus area in the editor
	var getFocusPoints = function(width){
		Joomla.request({
			url: resolveBaseUrl() +"/administrator/index.php?option=com_media&task=adaptiveimage.cropBoxData&path="+path+"&width="+width,
			method: 'GET',
			onSuccess: (response) => {
				if(response!=''){
					var data = JSON.parse(response);
					Joomla.MediaManager.Edit.smartcrop.cropper.setData({
						"x"      : data["box-left"],
						"y"      : data["box-top"],
						"width"  : data["box-width"],
						"height" : data["box-height"]
					});
				}
			},
		});
	}

	// Saveing the focus points to the storage
	function saveFocusPoints(width) {
		// Data to be saved in the storage
		var data = "&box-left="+Joomla.MediaManager.Edit.smartcrop.cropper.boxLeft+
					"&box-top="+Joomla.MediaManager.Edit.smartcrop.cropper.boxTop+
					"&box-width="+Joomla.MediaManager.Edit.smartcrop.cropper.boxWidth+
					"&box-height="+Joomla.MediaManager.Edit.smartcrop.cropper.boxHeight+
					"&width="+width;

		Joomla.request({
			url: resolveBaseUrl() +"/administrator/index.php?option=com_media&task=adaptiveimage.setfocus&path="+path+data,
			method: 'GET',
		});
	}

	// At Deactivate crop the images and save to cache.
	function cropImages(){
		Joomla.request({
			url: resolveBaseUrl() +"/administrator/index.php?option=com_media&task=adaptiveimage.cropImage&path="+path,
			method: 'GET',
		});
	}

	// Getting the value of any varible in the url
	function getQueryVariable(variable) {
		var query = window.location.search.substring(1);
		var vars = query.split('&');
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split('=');
			if (decodeURIComponent(pair[0]) == variable) {
				return decodeURIComponent(pair[1]);
			}
		}
		return false;
	};

	// Getting the perfect base path
	function resolveBaseUrl() {
		var basePath = window.location.origin;
		var url = window.location.pathname.split('/');
		if (url[1]!='administrator') {
			return basePath+"/"+url[1];
		}
		return basePath;
	}

	// Register the Events
	Joomla.MediaManager.Edit.smartcrop = {
		Activate: function (mediaData) {
			// Initialize
			initSmartCrop(mediaData);
		},
		Deactivate: function () {
			var width = document.getElementById("jform_requestedWidth").value;
			saveFocusPoints(width);
			cropImages();
			if (!Joomla.MediaManager.Edit.smartcrop.cropper) {
				return;
			}
			// Destroy the instance
			Joomla.MediaManager.Edit.smartcrop.cropper.destroy();
		}
	};
	
	var initSmartCrop = function (mediaData) {
		var image = document.getElementById('image-preview');
		
		// Initiate the cropper for gathering the focus point
		Joomla.MediaManager.Edit.smartcrop.cropper = new Cropper(image, { 
			viewMode: 1,
			responsive: false,
			restore: true,
			autoCrop: true,
			movable: true,
			zoomable: false,
			rotatable: false,
			autoCropArea: 0,
			minContainerWidth: image.offsetWidth,
			minContainerHeight: image.offsetHeight,
			crop: function (e) {
				var left, top,canvas_width, canvas_height;

				left = Math.round(e.detail.x);
				top = Math.round(e.detail.y);
				canvas_width = Math.round(e.detail.width);
				canvas_height = Math.round(e.detail.height);
				
				// Saveing cropbox data for focus area
				Joomla.MediaManager.Edit.smartcrop.cropper.boxLeft = left;
				Joomla.MediaManager.Edit.smartcrop.cropper.boxTop = top;
				Joomla.MediaManager.Edit.smartcrop.cropper.boxWidth = canvas_width;
				Joomla.MediaManager.Edit.smartcrop.cropper.boxHeight = canvas_height;

				// Setting the computed focus point into the input fields
				document.getElementById('jform_data_focus_x').value = left;
				document.getElementById('jform_data_focus_y').value = top;
				document.getElementById('jform_data_focus_width').value = canvas_width;
				document.getElementById('jform_data_focus_height').value = canvas_height;

				// Notify the app that a change has been made
				window.dispatchEvent(new Event('mediaManager.history.point'));
			}
		});

		// Wait for the image to load its data
		image.addEventListener('load', function() {

			// Set default aspect ratio after numeric check
			var defaultCropFactor = image.naturalWidth / image.naturalHeight;
			Joomla.MediaManager.Edit.smartcrop.cropper.setAspectRatio(defaultCropFactor);

		});
		var width = document.getElementById("jform_requestedWidth");
		var preWidth = width.value;
		
		getFocusPoints(preWidth);

		width.addEventListener('change', function(){
			saveFocusPoints(preWidth);
			var newWidth = width.value
			getFocusPoints(newWidth);
			preWidth = newWidth;
		});
		
	};
})();

