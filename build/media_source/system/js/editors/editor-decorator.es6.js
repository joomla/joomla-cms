/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A decorator for Editor instance.
 */
export default class JoomlaEditorDecorator {
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
   * @returns {string}
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
   * @returns {string}
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
