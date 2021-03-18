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

(() => {
  'use strict';

  const initCrop = () => {
    const image = document.getElementById('image-preview'); // Initiate the cropper

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

      crop(e) {
        document.getElementById('jform_crop_x').value = Math.round(e.detail.x);
        document.getElementById('jform_crop_y').value = Math.round(e.detail.y);
        document.getElementById('jform_crop_width').value = Math.round(e.detail.width);
        document.getElementById('jform_crop_height').value = Math.round(e.detail.height);
        const format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;
        const quality = document.getElementById('jform_crop_quality').value; // Update the store

        Joomla.MediaManager.Edit.current.contents = this.cropper.getCroppedCanvas().toDataURL(`image/${format}`, quality); // Notify the app that a change has been made

        window.dispatchEvent(new Event('mediaManager.history.point'));
      }

    });
    document.getElementById('jform_crop_x').addEventListener('change', ({
      target
    }) => {
      Joomla.MediaManager.Edit.crop.cropper.setData({
        x: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_crop_y').addEventListener('change', ({
      target
    }) => {
      Joomla.MediaManager.Edit.crop.cropper.setData({
        y: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_crop_width').addEventListener('change', ({
      target
    }) => {
      Joomla.MediaManager.Edit.crop.cropper.setData({
        width: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_crop_height').addEventListener('change', ({
      target
    }) => {
      Joomla.MediaManager.Edit.crop.cropper.setData({
        height: parseInt(target.value, 10)
      });
    });
    document.getElementById('jform_aspectRatio').addEventListener('change', ({
      target
    }) => {
      Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(target.value);
    }); // Wait for the image to load its data

    image.addEventListener('load', () => {
      // Get all option elements if future need
      const elements = [].slice.call(document.querySelectorAll('.crop-aspect-ratio-option')); // Set default aspect ratio after numeric check, option has a dummy value

      const defaultCropFactor = image.naturalWidth / image.naturalHeight;

      if (!Number.isNaN(defaultCropFactor) && Number.isFinite(defaultCropFactor)) {
        elements[0].value = defaultCropFactor;
      }

      Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(elements[0].value);
    });
  }; // Register the Events


  Joomla.MediaManager.Edit.crop = {
    Activate(mediaData) {
      // Initialize
      initCrop(mediaData);
    },

    Deactivate() {
      if (!Joomla.MediaManager.Edit.crop.cropper) {
        return;
      } // Destroy the instance


      Joomla.MediaManager.Edit.crop.cropper.destroy();
    }

  };
})();