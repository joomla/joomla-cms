/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(function () {
  'use strict';

  // Update image

  var rotate = function rotate(angle) {
    // The image element
    var image = document.getElementById('image-source');

    // The canvas where we will resize the image
    var canvas = document.createElement('canvas');

    // Pseudo rectangle calculation
    if (angle >= 0 && angle < 45 || angle >= 135 && angle < 225 || angle >= 315 && angle <= 360) {
      canvas.width = image.width;
      canvas.height = image.height;
    } else {
      // swap
      canvas.width = image.height;
      canvas.height = image.width;
    }
    var ctx = canvas.getContext('2d');
    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.rotate(angle * Math.PI / 180);
    ctx.drawImage(image, -image.width / 2, -image.height / 2);

    // The format
    var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : 'jpg';

    // The quality
    var quality = document.getElementById('jform_rotate_quality').value;

    // Creating the data from the canvas
    Joomla.MediaManager.Edit.current.contents = canvas.toDataURL('image/' + format, quality);

    // Updating the preview element
    var preview = document.getElementById('image-preview');
    preview.width = canvas.width;
    preview.height = canvas.height;
    preview.src = Joomla.MediaManager.Edit.current.contents;

    // Update the height input box
    document.getElementById('jform_rotate_a').value = angle;

    // Notify the app that a change has been made
    window.dispatchEvent(new Event('mediaManager.history.point'));
  };

  var initRotate = function initRotate() {
    var funct = function funct() {
      // The number input listener
      document.getElementById('jform_rotate_a').addEventListener('input', function (event) {
        rotate(parseInt(event.target.value, 10));

        // Deselect all buttons
        var elements = [].slice.call(document.querySelectorAll('#jform_rotate_distinct label'));
        elements.forEach(function (element) {
          element.classList.remove('active');
          element.classList.remove('focus');
        });
      });

      // The 90 degree rotate buttons listeners
      var elements = [].slice.call(document.querySelectorAll('#jform_rotate_distinct label'));
      elements.forEach(function (element) {
        element.addEventListener('click', function (event) {
          rotate(parseInt(event.target.querySelector('input').value, 10));
        });
      });
    };
    setTimeout(funct, 1000);
  };

  // Register the Events
  Joomla.MediaManager.Edit.rotate = {
    Activate: function Activate(mediaData) {
      // Initialize
      initRotate(mediaData);
    },
    Deactivate: function Deactivate() {}
  };
})();
