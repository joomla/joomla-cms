document.addEventListener('DOMContentLoaded', function() {
	EventBus.addEventListener('onActivate', function(e, context, imageElement){
		if (context != 'crop') {
			return;
		}

		var cropper = new Cropper(imageElement, {
			minContainerHeight: imageElement.offsetHeight,
			crop: function(e) {
				document.getElementById('jform_crop_x').value = e.detail.x;
				document.getElementById('jform_crop_y').value = e.detail.y;
				document.getElementById('jform_crop_width').value = e.detail.width;
				document.getElementById('jform_crop_height').value = e.detail.height;
			}
		});

		document.getElementById('jform_crop_x').value = 0;
		document.getElementById('jform_crop_y').value = 0;
		document.getElementById('jform_crop_width').value = imageElement.offsetWidth;
		document.getElementById('jform_crop_height').value = imageElement.offsetHeight;

		imageElement.cropper = cropper;
	});

	EventBus.addEventListener('onDeactivate', function(e, context, imageElement){
		if (context != 'crop' || !imageElement.cropper) {
			return;
		}

		imageElement.cropper.destroy();
	});
}, false);