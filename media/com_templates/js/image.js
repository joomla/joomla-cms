/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		// Configuration for image cropping
		var image  = document.getElementById('image-crop'),
		    width  = document.getElementById('imageWidth').value,
		    height = document.getElementById('imageHeight').value;

		var cropper = new Cropper(image, {
			viewMode: 0,
			scalable: true,
			zoomable: true,
			minCanvasWidth: width,
			minCanvasHeight: height,
		});

		image.addEventListener('crop', function (e) {
			document.getElementById('x').value = e.detail.x;
			document.getElementById('y').value = e.detail.y;
			document.getElementById('w').value = e.detail.width;
			document.getElementById('h').value = e.detail.height;
		});

	});

})();
