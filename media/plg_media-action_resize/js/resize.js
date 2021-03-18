/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};
Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function () {
  'use strict'; // Update image

  var resize = function resize(width, height) {
    // The image element
    var image = document.getElementById('image-source'); // The canvas where we will resize the image

    var canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(image, 0, 0, width, height); // The format

    var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension; // The quality

    var quality = document.getElementById('jform_resize_quality').value; // Creating the data from the canvas

    Joomla.MediaManager.Edit.current.contents = canvas.toDataURL("image/".concat(format), quality); // Updating the preview element

    var preview = document.getElementById('image-preview');
    preview.width = width;
    preview.height = height;
    preview.src = Joomla.MediaManager.Edit.current.contents; // Update the width input box

    document.getElementById('jform_resize_width').value = parseInt(width, 10); // Update the height input box

    document.getElementById('jform_resize_height').value = parseInt(height, 10); // Notify the app that a change has been made

    window.dispatchEvent(new Event('mediaManager.history.point'));
  };

  var initResize = function initResize() {
    var funct = function funct() {
      var image = document.getElementById('image-source');
      var resizeWidthInputBox = document.getElementById('jform_resize_width');
      var resizeHeightInputBox = document.getElementById('jform_resize_height'); // Update the input boxes

      resizeWidthInputBox.value = image.width;
      resizeHeightInputBox.value = image.height; // The listeners

      resizeWidthInputBox.addEventListener('change', function (_ref) {
        var target = _ref.target;
        resize(parseInt(target.value, 10), parseInt(target.value, 10) / (image.width / image.height));
      });
      resizeHeightInputBox.addEventListener('change', function (_ref2) {
        var target = _ref2.target;
        resize(parseInt(target.value, 10) * (image.width / image.height), parseInt(target.value, 10));
      }); // Set the values for the range fields

      var resizeWidth = document.getElementById('jform_resize_w');
      var resizeHeight = document.getElementById('jform_resize_h');
      resizeWidth.min = 0;
      resizeWidth.max = image.width;
      resizeWidth.value = image.width;
      resizeHeight.min = 0;
      resizeHeight.max = image.height;
      resizeHeight.value = image.height; // The listeners

      resizeWidth.addEventListener('input', function (_ref3) {
        var target = _ref3.target;
        resize(parseInt(target.value, 10), parseInt(target.value, 10) / (image.width / image.height));
      });
      resizeHeight.addEventListener('input', function (_ref4) {
        var target = _ref4.target;
        resize(parseInt(target.value, 10) * (image.width / image.height), parseInt(target.value, 10));
      });
    };

    setTimeout(funct, 1000);
  }; // Register the Events


  Joomla.MediaManager.Edit.resize = {
    Activate: function Activate(mediaData) {
      // Initialize
      initResize(mediaData);
    },
    Deactivate: function Deactivate() {}
  };
})();