/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved
import { JoomlaEditorButton } from 'editor-api';
// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';

// Register modal-media action
JoomlaEditorButton.registerAction('modal-media', (editor, options) => {
  // Create a dialog popup
  let dialog;
  options.popupButtons = [
    {
      label: Joomla.Text._('PLG_IMAGE_BUTTON_INSERT'),
      className: 'button button-success btn btn-success',
      location: 'header',
      onClick: () => {
        if (Joomla.selectedMediaFile && Joomla.selectedMediaFile.path) {
          Joomla.getMedia(Joomla.selectedMediaFile, editor).then(() => {
            dialog.close();
            Joomla.selectedMediaFile = {};
          });
        }
      },
    },
    {
      label: '',
      ariaLabel: Joomla.Text._('JCLOSE'),
      className: 'button-close btn-close',
      data: { buttonClose: '', dialogClose: '' },
      location: 'header',
    },
  ];

  dialog = new JoomlaDialog(options);
  dialog.addEventListener('joomla-dialog:close', () => {
    Joomla.Modal.setCurrent(null);
    dialog.destroy();
  });
  Joomla.Modal.setCurrent(dialog);
  dialog.show();
});
