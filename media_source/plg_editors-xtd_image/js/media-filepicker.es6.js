/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

import JoomlaDialog from 'joomla.dialog';

if (!window.Joomla) {
  throw new Error('Joomla API is not properly initiated');
}

const { apiBaseUrl } = Joomla.getOptions('media-picker-api', {});
if (!apiBaseUrl) {
  throw new Error('The "media-picker-api" script option is required');
}

/**
 * Resolve the real, root-relative URL for the currently selected media file.
 *
 * @param {object} item  The selected media file (Joomla.selectedMediaFile)
 *
 * @returns {Promise<{url: string, width: number, height: number}|null>}
 */
const resolveUrl = (item) => {
  const url = new URL(apiBaseUrl, window.location.origin);
  url.searchParams.append('task', 'api.files');
  url.searchParams.append('url', true);
  url.searchParams.append('path', item.path);
  url.searchParams.append('mediatypes', '0,1,2,3');
  url.searchParams.append(Joomla.getOptions('csrf.token'), 1);

  return fetch(url, {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' },
  })
    .then((response) => response.json())
    .then((response) => {
      if (!response.success || !response.data || !response.data[0] || !response.data[0].url) {
        return null;
      }

      const media = response.data[0];
      const { rootFull } = Joomla.getOptions('system.paths', {});
      const parts = rootFull ? media.url.split(rootFull) : [media.url];
      const relativeUrl = parts.length > 1 ? parts[1] : media.url;

      return {
        url: relativeUrl,
        width: item.width,
        height: item.height,
      };
    });
};

/**
 * Open the Joomla Media Manager and resolve with the selected file's URL.
 *
 * Designed to be consumed by TinyMCE's file_picker_callback, but usable by
 * any editor that needs a plain media URL.
 *
 * @param {{filetype?: string}} meta  The TinyMCE file picker meta object
 *
 * @returns {Promise<{url: string, alt?: string, width: number, height: number}|null>}
 */
Joomla.editorMediaPicker = (meta = {}) => new Promise((resolve) => {
  // Restrict the media manager to the relevant types (images=0, audios=1, videos=2, documents=3)
  const mediatypes = meta.filetype === 'image' ? '0' : '1,2';

  Joomla.selectedMediaFile = {};

  // Ensure the promise resolves exactly once, whether the user selects or dismisses
  let settled = false;
  const settle = (value) => {
    if (settled) {
      return;
    }
    settled = true;
    resolve(value);
  };

  const dialog = new JoomlaDialog({
    popupType: 'iframe',
    src: `index.php?option=com_media&view=media&tmpl=component&mediatypes=${mediatypes}`,
    textHeader: Joomla.Text._('PLG_IMAGE_BUTTON_IMAGE'),
    popupButtons: [
      {
        label: Joomla.Text._('JSELECT'),
        className: 'button button-success btn btn-success',
        location: 'header',
        onClick: async () => {
          const item = Joomla.selectedMediaFile;
          let result = null;

          if (item && item.path && item.type === 'file') {
            try {
              result = await resolveUrl(item);
            } catch (err) {
              result = null;
            }
          }

          settle(result);
          Joomla.selectedMediaFile = {};
          dialog.close();
        },
      },
      {
        label: '',
        ariaLabel: Joomla.Text._('JCLOSE'),
        className: 'button-close btn-close',
        data: { buttonClose: '', dialogClose: '' },
        location: 'header',
      },
    ],
  });

  // Skip the extra alt/lazy attribute UI injected by joomla-media-select; TinyMCE has its own fields
  dialog.classList.add('joomla-dialog-media-field');

  dialog.addEventListener('joomla-dialog:close', () => {
    Joomla.Modal.setCurrent(null);
    dialog.destroy();
    Joomla.selectedMediaFile = {};

    // Resolve null when the dialog is dismissed without a selection
    settle(null);
  });

  Joomla.Modal.setCurrent(dialog);
  dialog.show();
});

Joomla.editorFilePickers = Joomla.editorFilePickers || {};
Joomla.editorFilePickers.image = Joomla.editorMediaPicker;
Joomla.editorFilePickers.media = Joomla.editorMediaPicker;
