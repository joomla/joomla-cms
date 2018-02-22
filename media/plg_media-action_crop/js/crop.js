/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/
var _this = this;

/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function () {
  'use strict';

  var initCrop = function initCrop() {
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
      crop: function crop(e) {
        document.getElementById('jform_crop_x').value = Math.round(e.detail.x);
        document.getElementById('jform_crop_y').value = Math.round(e.detail.y);
        document.getElementById('jform_crop_width').value = Math.round(e.detail.width);
        document.getElementById('jform_crop_height').value = Math.round(e.detail.height);

        var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;

        var quality = document.getElementById('jform_crop_quality').value;

        // Update the store
        Joomla.MediaManager.Edit.current.contents = this.cropper.getCroppedCanvas().toDataURL('image/' + format, quality);

        // Notify the app that a change has been made
        window.dispatchEvent(new Event('mediaManager.history.point'));
      }
    });

    document.getElementById('jform_crop_x').addEventListener('change', function () {
      Joomla.MediaManager.Edit.crop.cropper.setData({ x: parseInt(_this.value, 10) });
    });
    document.getElementById('jform_crop_y').addEventListener('change', function () {
      Joomla.MediaManager.Edit.crop.cropper.setData({ y: parseInt(_this.value, 10) });
    });
    document.getElementById('jform_crop_width').addEventListener('change', function () {
      Joomla.MediaManager.Edit.crop.cropper.setData({ width: parseInt(_this.value, 10) });
    });
    document.getElementById('jform_crop_height').addEventListener('change', function () {
      Joomla.MediaManager.Edit.crop.cropper.setData({ height: parseInt(_this.value, 10) });
    });

    var elements = document.querySelectorAll('#jform_aspectRatio input');
    var clickFunc = function clickFunc() {
      Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(_this.value);
    };
    for (var i = 0; i < elements.length; i += 1) {
      elements[i].addEventListener('click', clickFunc);
    }
  };

  // Register the Events
  Joomla.MediaManager.Edit.crop = {
    Activate: function Activate(mediaData) {
      // Initialize
      initCrop(mediaData);
    },
    Deactivate: function Deactivate() {
      if (!Joomla.MediaManager.Edit.crop.cropper) {
        return;
      }
      // Destroy the instance
      Joomla.MediaManager.Edit.crop.cropper.destroy();
    }
  };
})();
