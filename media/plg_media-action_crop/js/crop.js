/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* global Cropper */
Joomla = window.Joomla || {};
Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function () {
  'use strict';

  var initCrop = function initCrop() {
    var image = document.getElementById('image-preview'); // Initiate the cropper

    Joomla.MediaManager.Edit.crop.cropper = new Cropper(image, {
      viewMode: 1,
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
        var quality = document.getElementById('jform_crop_quality').value; // Update the store

        Joomla.MediaManager.Edit.current.contents = this.cropper.getCroppedCanvas().toDataURL("image/".concat(format), quality); // Notify the app that a change has been made

        window.dispatchEvent(new Event('mediaManager.history.point'));
      }
    });
    document.getElementById('jform_crop_x').addEventListener('change', function (_ref) {
      var target = _ref.target;
      Joomla.MediaManager.Edit.crop.cropper.setData({
        x: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_crop_y').addEventListener('change', function (_ref2) {
      var target = _ref2.target;
      Joomla.MediaManager.Edit.crop.cropper.setData({
        y: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_crop_width').addEventListener('change', function (_ref3) {
      var target = _ref3.target;
      Joomla.MediaManager.Edit.crop.cropper.setData({
        width: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_crop_height').addEventListener('change', function (_ref4) {
      var target = _ref4.target;
      Joomla.MediaManager.Edit.crop.cropper.setData({
        height: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_aspectRatio').addEventListener('change', function (_ref5) {
      var target = _ref5.target;
      Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(target.value);
    }); // Wait for the image to load its data

    image.addEventListener('load', function () {
      // Get all option elements if future need
      var elements = [].slice.call(document.querySelectorAll('.crop-aspect-ratio-option')); // Set default aspect ratio after numeric check, option has a dummy value

      var defaultCropFactor = image.naturalWidth / image.naturalHeight;

      if (!Number.isNaN(defaultCropFactor) && Number.isFinite(defaultCropFactor)) {
        elements[0].value = defaultCropFactor;
      }

      Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(elements[0].value);
    });
  }; // Register the Events


  Joomla.MediaManager.Edit.crop = {
    Activate: function Activate(mediaData) {
      // Initialize
      initCrop(mediaData);
    },
    Deactivate: function Deactivate() {
      if (!Joomla.MediaManager.Edit.crop.cropper) {
        return;
      } // Destroy the instance


      Joomla.MediaManager.Edit.crop.cropper.destroy();
    }
  };
})();