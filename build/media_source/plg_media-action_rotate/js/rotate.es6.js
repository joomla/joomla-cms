/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
let activated = false;

// Update image
const rotate = (angle, image) => {
  // The canvas where we will rotate the image
  let canvas = document.createElement('canvas');

  // Pseudo rectangle calculation
  if ((angle >= 0 && angle < 45)
    || (angle >= 135 && angle < 225)
    || (angle >= 315 && angle <= 360)) {
    canvas.width = image.naturalWidth;
    canvas.height = image.naturalHeight;
  } else {
    // swap
    canvas.width = image.naturalHeight;
    canvas.height = image.naturalWidth;
  }

  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.translate(canvas.width / 2, canvas.height / 2);
  ctx.rotate((angle * Math.PI) / 180);
  ctx.drawImage(image, -image.naturalWidth / 2, -image.naturalHeight / 2);

  // The format
  const format = Joomla.MediaManager.Edit.original.extension.toLowerCase() === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension.toLowerCase();

  // The quality
  const quality = document.getElementById('jform_rotate_quality').value;

  // Creating the data from the canvas
  Joomla.MediaManager.Edit.current.contents = canvas.toDataURL(`image/${format}`, quality);

  // Updating the preview element
  image.width = canvas.width;
  image.height = canvas.height;
  image.src = '';
  requestAnimationFrame(
    () => requestAnimationFrame(() => {
      image.src = Joomla.MediaManager.Edit.current.contents;
    }),
  );

  // Update the angle input box
  document.getElementById('jform_rotate_a').value = angle;

  // Notify the app that a change has been made
  window.dispatchEvent(new Event('mediaManager.history.point'));
  canvas = null;
};

const initRotate = (image) => {
  if (!activated) {
    // The number input listener
    document.getElementById('jform_rotate_a').addEventListener('change', ({ target }) => {
      rotate(parseInt(target.value, 10), image);

      target.value = 0;
      // Deselect all buttons
      document.querySelectorAll('#jform_rotate_distinct label').forEach((element) => element.classList.remove('active', 'focus'));
    });

    // The 90 degree rotate buttons listeners
    document.querySelectorAll('#jform_rotate_distinct [type=radio]').forEach((element) => {
      element.addEventListener('click', ({ target }) => {
        rotate(parseInt(target.value, 10), image);

        // Deselect all buttons
        document.querySelectorAll('#jform_rotate_distinct label').forEach((el) => el.classList.remove('active', 'focus'));
      });
    });

    activated = true;
  }
};

window.addEventListener('media-manager-edit-init', () => {
  // Register the Events
  Joomla.MediaManager.Edit.plugins.rotate = {
    Activate(image) {
      return new Promise((resolve) => {
        // Initialize
        initRotate(image);
        resolve();
      });
    },
    Deactivate(/* image */) {
      return new Promise((resolve) => {
        resolve();
      });
    },
  };
}, { once: true });
