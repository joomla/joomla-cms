/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!window.Joomla) {
  throw new Error('JoomlaEditors API require Joomla to be loaded.')
}

// === The code for keep backward compatibility ===
// Only define editors if not defined
Joomla.editors = Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
Joomla.editors.instances = Joomla.editors.instances || {
  /**
   * *****************************************************************
   * All Editors MUST register, per instance, the following callbacks:
   * *****************************************************************
   *
   * getValue         Type  Function  Should return the complete data from the editor
   *                                  Example: () => { return this.element.value; }
   * setValue         Type  Function  Should replace the complete data of the editor
   *                                  Example: (text) => { return this.element.value = text; }
   * getSelection     Type  Function  Should return the selected text from the editor
   *                                  Example: function () { return this.selectedText; }
   * disable          Type  Function  Toggles the editor into disabled mode. When the editor is
   *                                  active then everything should be usable. When inactive the
   *                                  editor should be unusable AND disabled for form validation
   *                                  Example: (bool) => { return this.disable = value; }
   * replaceSelection Type  Function  Should replace the selected text of the editor
   *                                  If nothing selected, will insert the data at the cursor
   *                                  Example:
   *                                  (text) => {
   *                                    return insertAtCursor(this.element, text);
   *                                    }
   *
   * USAGE (assuming that jform_articletext is the textarea id)
   * {
   * To get the current editor value:
   *  Joomla.editors.instances['jform_articletext'].getValue();
   * To set the current editor value:
   *  Joomla.editors.instances['jform_articletext'].setValue('Joomla! rocks');
   * To replace(selection) or insert a value at  the current editor cursor (replaces the J3
   * jInsertEditorText API):
   *  replaceSelection:
   *  Joomla.editors.instances['jform_articletext'].replaceSelection('Joomla! rocks')
   * }
   *
   * *********************************************************
   * ANY INTERACTION WITH THE EDITORS SHOULD USE THE ABOVE API
   * *********************************************************
   */
};
// === End of code for keep backward compatibility ===

/**
 * A decorator for Editor instance.
 */
class JoomlaEditorDecorator {
  /**
   * The editor instance.
   * @type {Object}
   */
  // instance = null;

  /**
   * The editor type/name, eg: tinymce, codemirror, none etc.
   * @type {string}
   */
  // type = '';

  /**
   * HTML ID of the editor.
   * @type {string}
   */
  // id = '';

  /**
   * Class constructor.
   *
   * @param {Object} instance The editor instance
   * @param {string} type The editor type/name
   * @param {string} id The editor ID
   */
  constructor(instance, type, id) {
    if (!instance || !type || !id) {
      throw new Error('Missed values for class constructor');
    }

    this.instance = instance;
    this.type = type;
    this.id = id;
  }

  /**
   * Returns the editor instance object.
   *
   * @returns {Object}
   */
  getRawInstance() {
    return this.instance;
  }

  /**
   * Returns the editor type/name.
   *
   * @returns {string}
   */
  getType() {
    return this.type;
  }

  /**
   * Returns the editor id.
   *
   * @returns {string}
   */
  getId() {
    return this.id;
  }

  /**
   * Return the complete data from the editor.
   * Should be implemented by editor provider.
   *
   * @returns {Promise}
   */
  getValue() {
    throw new Error('Not implemented');
  }

  /**
   * Replace the complete data of the editor
   * Should be implemented by editor provider.
   *
   * @param {string} value Value to set.
   *
   * @returns {this}
   */
  setValue(value) {
    throw new Error('Not implemented');
  }

  /**
   * Return the selected text from the editor.
   * Should be implemented by editor provider.
   *
   * @returns {Promise}
   */
  getSelection() {
    throw new Error('Not implemented');
  }

  /**
   * Replace the selected text. If nothing selected, will insert the data at the cursor.
   * Should be implemented by editor provider.
   *
   * @param {string} value
   *
   * @returns {this}
   */
  replaceSelection(value) {
    throw new Error('Not implemented');
  }

  /**
   * Toggles the editor disabled mode. When the editor is active then everything should be usable.
   * When inactive the editor should be unusable AND disabled for form validation.
   * Should be implemented by editor provider.
   *
   * @param {boolean} enable True to enable, false or undefined to disable.
   *
   * @returns {this}
   */
  disable(enable) {
    throw new Error('Not implemented');
  }
}

/**
 * Editor API.
 */
const JoomlaEditor = {};

/**
 * Editor Buttons API.
 */
const JoomlaEditorButton = {};


Joomla.Editor = JoomlaEditor;
Joomla.EditorButton = JoomlaEditorButton;
window.JoomlaEditorDecorator = JoomlaEditorDecorator;

console.log('Editors', Joomla.editors, Joomla.Editor, Joomla.EditorButton)
