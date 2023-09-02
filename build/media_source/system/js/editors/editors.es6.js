/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved
import { JoomlaEditorButton, JoomlaEditorDecorator } from 'editor-api';
// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';

if (!window.Joomla) {
  throw new Error('JoomlaEditors API require Joomla to be loaded.');
}

// === The code for keep backward compatibility ===
// Joomla.editors is deprecated use Joomla.Editor instead.
// @TODO: Remove this section in Joomla 6.

// Only define editors if not defined
Joomla.editors = Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
Joomla.editors.instances = new Proxy({}, {
  set(target, p, editor) {
    // eslint-disable-next-line no-use-before-define
    if (!(editor instanceof JoomlaEditorDecorator)) {
      // Add missed method in Legacy editor
      editor.getId = () => p;
      // eslint-disable-next-line no-console
      console.warn('Legacy editors is deprecated. Register the editor instance with JoomlaEditor.register().', p, editor);
    }
    target[p] = editor;
    return true;
  },
  get(target, p) {
    // eslint-disable-next-line no-console
    console.warn('Direct access to Joomla.editors.instances is deprecated. Use JoomlaEditor.getActive() or JoomlaEditor.get(id) to retrieve the editor instance.');
    return target[p];
  },
});
// === End of code for keep backward compatibility ===

// Register couple default actions for Editor Buttons
// Insert static content on cursor
JoomlaEditorButton.registerAction('insert', (editor, options) => {
  const content = options.content || '';
  editor.replaceSelection(content);
});
// Display modal dialog
JoomlaEditorButton.registerAction('modal', (editor, options) => {
  if (options.src && options.src[0] !== '#' && options.src[0] !== '.') {
    // Replace editor parameter to actual editor ID
    const url = options.src.indexOf('http') === 0 ? new URL(options.src) : new URL(options.src, window.location.origin);
    url.searchParams.set('editor', editor.getId());
    if (url.searchParams.has('e_name')) {
      url.searchParams.set('e_name', editor.getId());
    }
    options.src = url.toString();
  }
  const popup = new JoomlaDialog(options);
  popup.addEventListener('joomla-dialog:close', () => {
    Joomla.Modal.setCurrent(null);
    popup.destroy();
  });
  Joomla.Modal.setCurrent(popup);
  popup.show();
});

// Listen to click on Editor button, and run action.
const btnDelegateSelector = '[data-joomla-editor-button-action]';
const btnActionDataAttr = 'joomlaEditorButtonAction';
const btnConfigDataAttr = 'joomlaEditorButtonOptions';

document.addEventListener('click', (event) => {
  const btn = event.target.closest(btnDelegateSelector);
  if (!btn) return;
  const action = btn.dataset[btnActionDataAttr];
  const options = btn.dataset[btnConfigDataAttr] ? JSON.parse(btn.dataset[btnConfigDataAttr]) : {};

  if (action) {
    JoomlaEditorButton.runAction(action, options, btn);
  }
});
