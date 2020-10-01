/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

(() => {
  'use strict';

  // Update image
  const resize = (width, height) => {
    // The image element
    const image = document.getElementById('image-source');

    // The canvas where we will resize the image
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(image, 0, 0, width, height);

    // The format
    const format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;

    // The quality
    const quality = document.getElementById('jform_resize_quality').value;

    // Creating the data from the canvas
    Joomla.MediaManager.Edit.current.contents = canvas.toDataURL(`image/${format}`, quality);

    // Updating the preview element
    const preview = document.getElementById('image-preview');
    preview.width = width;
    preview.height = height;
    preview.src = Joomla.MediaManager.Edit.current.contents;

    // Update the width input box
    document.getElementById('jform_resize_width').value = parseInt(width, 10);

    // Update the height input box
    document.getElementById('jform_resize_height').value = parseInt(height, 10);
  };

  const setHistory = ({ target }) => {
    const image = document.getElementById('image-source');
    const quality = document.getElementById('jform_resize_quality').value;
    const w = parseInt(target.value, 10);
    const h = parseInt(target.value, 10) / (image.width / image.height);
    resize(w, h);

    const resizeData = {
      width: w,
      height: h,
      quality: parseInt(quality, 10),
    };
    window.dispatchEvent(new CustomEvent('mediaManager.history.point', { detail: { resize: resizeData, plugin: 'resize' } }));
  };

  const removeListeners = () => {
    const resizeWidth = document.getElementById('jform_resize_w');
    const resizeHeight = document.getElementById('jform_resize_h');
    const resizeWidthInputBox = document.getElementById('jform_resize_width');
    const resizeHeightInputBox = document.getElementById('jform_resize_height');
    resizeWidthInputBox.removeEventListener('change', setHistory);
    resizeHeightInputBox.removeEventListener('change', setHistory);
    ['mouseup', 'touchend'].forEach((evt) => {
      resizeHeight.addEventListener(evt, setHistory);
      resizeWidth.addEventListener(evt, setHistory);
    });
  };

  const initResize = () => {
    const funct = () => {
      removeListeners();
      const image = document.getElementById('image-source');
      const preview = document.getElementById('image-preview');
      const history = Joomla.MediaManager.Edit.history[Joomla.MediaManager.Edit.history.current];
      if (history && history.file) {
        preview.src = history.file;
      }

      const resizeWidthInputBox = document.getElementById('jform_resize_width');
      const resizeHeightInputBox = document.getElementById('jform_resize_height');

      // Update the input boxes
      resizeWidthInputBox.value = image.width;
      resizeHeightInputBox.value = image.height;

      // The listeners
      resizeWidthInputBox.addEventListener('change', setHistory);
      resizeHeightInputBox.addEventListener('change', setHistory);

      // Set the values for the range fields
      const resizeWidth = document.getElementById('jform_resize_w');
      const resizeHeight = document.getElementById('jform_resize_h');

      resizeWidth.min = 0;
      resizeWidth.max = image.width;
      resizeWidth.value = image.width;

      resizeHeight.min = 0;
      resizeHeight.max = image.height;
      resizeHeight.value = image.height;

      // The listeners
      resizeWidth.addEventListener('input', ({ target }) => {
        resize(
          parseInt(target.value, 10) * (image.width / image.height),
          parseInt(target.value, 10),
        );
      });
      resizeHeight.addEventListener('input', ({ target }) => {
        resize(
          parseInt(target.value, 10) * (image.width / image.height),
          parseInt(target.value, 10),
        );
      });
      ['mouseup', 'touchend'].forEach((evt) => {
        resizeHeight.addEventListener(evt, setHistory);
        resizeWidth.addEventListener(evt, setHistory);
      });
    };
    setTimeout(funct, 1000);
  };

  // Register the Events
  Joomla.MediaManager.Edit.resize = {
    Activate(mediaData) {
      // Initialize
      initResize(mediaData);
    },
    Deactivate() {
      removeListeners();
    },
  };
})();
