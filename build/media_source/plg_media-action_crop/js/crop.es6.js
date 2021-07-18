/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/* global Cropper */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};
Joomla.MediaManager.Edit = Joomla.MediaManager.Edit || {};

const init = (image) => {
  // Initiate the cropper
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
      const quality = document.getElementById('jform_crop_quality').value;

      // Update the store
      Joomla.MediaManager.Edit.current.contents = this.cropper.getCroppedCanvas().toDataURL(`image/${format}`, quality);

      // Notify the app that a change has been made
      window.dispatchEvent(new Event('mediaManager.history.point'));
    },
  });

  document.getElementById('jform_crop_x').addEventListener('change', ({ currentTarget }) => {
    Joomla.MediaManager.Edit.crop.cropper.setData({ x: parseInt(currentTarget.value, 10) });
  });
  document.getElementById('jform_crop_y').addEventListener('change', ({ currentTarget }) => {
    Joomla.MediaManager.Edit.crop.cropper.setData({ y: parseInt(currentTarget.value, 10) });
  });
  document.getElementById('jform_crop_width').addEventListener('change', ({ currentTarget }) => {
    Joomla.MediaManager.Edit.crop.cropper.setData({ width: parseInt(currentTarget.value, 10) });
  });
  document.getElementById('jform_crop_height').addEventListener('change', ({ currentTarget }) => {
    Joomla.MediaManager.Edit.crop.cropper.setData({ height: parseInt(currentTarget.value, 10) });
  });
  document.getElementById('jform_aspectRatio').addEventListener('change', ({ currentTarget }) => {
    Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(currentTarget.value);
  });
};

// Register the Events
Joomla.MediaManager.Edit.crop = {
  cropper: {
    destroy() {}
  },
  Activate() {
    const image = document.getElementById('image-preview');
    // Get all option elements if future need
    const elements = [].slice.call(document.querySelectorAll('.crop-aspect-ratio-option'));

    // Set default aspect ratio after numeric check, option has a dummy value
    const defaultCropFactor = image.naturalWidth / image.naturalHeight;
    if (!Number.isNaN(defaultCropFactor) && Number.isFinite(defaultCropFactor)) {
      elements[0].value = defaultCropFactor;
    }

    init(image);
    Joomla.MediaManager.Edit.crop.cropper.setAspectRatio(elements[0].value);
  },
  Deactivate() {
    Joomla.MediaManager.Edit.crop.cropper.destroy();
  }
};
