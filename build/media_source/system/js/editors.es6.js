/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!window.Joomla) {
  throw new Error('JoomlaEditors API require Joomla to be loaded.');
}

// === The code for keep backward compatibility ===
// Joomla.editors is deprecated use Joomla.Editor instead.
// @TODO: Remove this section in Joomla 6.

// Only define editors if not defined
Joomla.editors = Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
Joomla.editors.instances = Joomla.editors.instances || {};
// === End of code for keep backward compatibility ===

/**
 * A decorator for Editor instance.
 */
class JoomlaEditorDecorator {
  /**
   * Internal! The property should not be accessed directly.
   * The editor instance.
   * @type {Object}
   */
  // instance = null;

  /**
   * Internal! The property should not be accessed directly.
   * The editor type/name, eg: tinymce, codemirror, none etc.
   * @type {string}
   */
  // type = '';

  /**
   * Internal! The property should not be accessed directly.
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
   * @returns {Promise<string>}
   */
  // eslint-disable-next-line class-methods-use-this
  getValue() {
    throw new Error('Not implemented');
  }

  /**
   * Replace the complete data of the editor
   * Should be implemented by editor provider.
   *
   * @param {string} value Value to set.
   *
   * @returns {JoomlaEditorDecorator}
   */
  // eslint-disable-next-line class-methods-use-this, no-unused-vars
  setValue(value) {
    throw new Error('Not implemented');
  }

  /**
   * Return the selected text from the editor.
   * Should be implemented by editor provider.
   *
   * @returns {Promise<string>}
   */
  // eslint-disable-next-line class-methods-use-this
  getSelection() {
    throw new Error('Not implemented');
  }

  /**
   * Replace the selected text. If nothing selected, will insert the data at the cursor.
   * Should be implemented by editor provider.
   *
   * @param {string} value
   *
   * @returns {JoomlaEditorDecorator}
   */
  // eslint-disable-next-line class-methods-use-this, no-unused-vars
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
   * @returns {JoomlaEditorDecorator}
   */
  // eslint-disable-next-line class-methods-use-this, no-unused-vars
  disable(enable) {
    throw new Error('Not implemented');
  }
}

/**
 * Editor API.
 */
const JoomlaEditor = {
  /**
   * Internal! The property should not be accessed directly.
   *
   * List of registered instances.
   */
  instances: {},

  /**
   * Internal! The property should not be accessed directly.
   *
   * ID of an active editor instance.
   */
  active: null,

  /**
   * Register editor instance.
   *
   * @param {JoomlaEditorDecorator} editor The editor instance.
   *
   * @returns {JoomlaEditor}
   */
  register(editor) {
    if (!(editor instanceof JoomlaEditorDecorator)) {
      throw new Error('Unexpected editor instance');
    }

    this.instances[editor.getId()] = editor;

    // For backward compatibility
    Joomla.editors.instances[editor.getId()] = editor;

    return this;
  },

  /**
   * Unregister editor instance.
   *
   * @param {JoomlaEditorDecorator|string} editor The editor instance or ID.
   *
   * @returns {JoomlaEditor}
   */
  unregister(editor) {
    let id;
    if (editor instanceof JoomlaEditorDecorator) {
      id = editor.getId();
    } else if (typeof editor === 'string') {
      id = editor;
    } else {
      throw new Error('Unexpected editor instance or identifier');
    }

    delete this.instances[id];

    // For backward compatibility
    delete Joomla.editors.instances[id];

    return this;
  },

  /**
   * Return editor instance by ID.
   *
   * @param {String} id
   *
   * @returns {JoomlaEditorDecorator|boolean}
   */
  get(id) {
    return this.instances[id] || false;
  },

  /**
   * Set currently active editor, the editor that in focus.
   *
   * @param {JoomlaEditorDecorator|string} editor The editor instance or ID.
   *
   * @returns {JoomlaEditor}
   */
  setActive(editor) {
    if (editor instanceof JoomlaEditorDecorator) {
      this.active = editor;
    } else if (this.instances[editor]) {
      this.active = this.instances[editor];
    } else {
      throw new Error('The editor instance not found or it is incorrect');
    }

    return this;
  },

  /**
   * Return active editor, if there exist eny.
   *
   * @returns {JoomlaEditorDecorator}
   */
  getActive() {
    return this.active;
  },
};

/**
 * Editor Buttons API.
 */
const JoomlaEditorButton = {};

Joomla.Editor = JoomlaEditor;
Joomla.EditorButton = JoomlaEditorButton;
window.JoomlaEditorDecorator = JoomlaEditorDecorator;

console.log('Editors', Joomla.editors, Joomla.Editor, Joomla.EditorButton);
