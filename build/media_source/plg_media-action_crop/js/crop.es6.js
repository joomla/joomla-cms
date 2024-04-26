/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/* global Cropper */
let formElements;
let activated = false;
let instance;

const addListeners = () => {
  formElements.cropX.addEventListener('change', ({ currentTarget }) => {
    instance.setData({ x: parseInt(currentTarget.value, 10) });
  });
  formElements.cropY.addEventListener('change', ({ currentTarget }) => {
    instance.setData({ y: parseInt(currentTarget.value, 10) });
  });
  formElements.cropWidth.addEventListener('change', ({ currentTarget }) => {
    instance.setData({ width: parseInt(currentTarget.value, 10) });
  });
  formElements.cropHeight.addEventListener('change', ({ currentTarget }) => {
    instance.setData({ height: parseInt(currentTarget.value, 10) });
  });
  formElements.aspectRatio.addEventListener('change', ({ currentTarget }) => {
    instance.setAspectRatio(currentTarget.value);
  });
  activated = true;
};

const init = (image) => {
  // Set default aspect ratio after numeric check, option has a dummy value
  const defaultCropFactor = image.naturalWidth / image.naturalHeight;
  if (!Number.isNaN(defaultCropFactor) && Number.isFinite(defaultCropFactor)) {
    formElements.cropAspectRatioOption.value = defaultCropFactor;
  }

  // Initiate the cropper
  instance = new Cropper(image, {
    viewMode: 1,
    responsive: true,
    restore: true,
    autoCrop: true,
    movable: false,
    zoomable: false,
    rotatable: false,
    autoCropArea: 1,
    // scalable: false,
    crop(e) {
      formElements.cropX.value = Math.round(e.detail.x);
      formElements.cropY.value = Math.round(e.detail.y);
      formElements.cropWidth.value = Math.round(e.detail.width);
      formElements.cropHeight.value = Math.round(e.detail.height);
      const format = Joomla.MediaManager.Edit.original.extension.toLowerCase() === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension.toLowerCase();
      const quality = formElements.cropQuality.value;

      // Update the store
      Joomla.MediaManager.Edit.current.contents = this.cropper.getCroppedCanvas().toDataURL(`image/${format}`, quality);

      // Notify the app that a change has been made
      window.dispatchEvent(new Event('mediaManager.history.point'));
    },
  });

  // Add listeners
  if (!activated) {
    addListeners();
  }

  instance.setAspectRatio(formElements.cropAspectRatioOption.value);
};

// Register the Events
window.addEventListener('media-manager-edit-init', () => {
  formElements = {
    aspectRatio: document.getElementById('jform_aspectRatio'),
    cropHeight: document.getElementById('jform_crop_height'),
    cropWidth: document.getElementById('jform_crop_width'),
    cropY: document.getElementById('jform_crop_y'),
    cropX: document.getElementById('jform_crop_x'),
    cropQuality: document.getElementById('jform_crop_quality'),
    cropAspectRatioOption: document.querySelector('.crop-aspect-ratio-option'),
  };
  Joomla.MediaManager.Edit.plugins.crop = {
    Activate(image) {
      return new Promise((resolve /* , reject */) => {
        init(image);

        resolve();
      });
    },
    Deactivate(image) {
      return new Promise((resolve /* , reject */) => {
        if (image.cropper) {
          image.cropper.destroy();
          instance = null;
        }
        resolve();
      });
    },
  };
}, { once: true });
