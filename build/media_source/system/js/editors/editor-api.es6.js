/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved
import JoomlaEditorDecorator from 'editor-decorator';

/**
 * Editor API.
 */
const JoomlaEditor = {
  /**
   * Internal! The property should not be accessed directly.
   *
   * List of registered editors.
   */
  instances: {},

  /**
   * Internal! The property should not be accessed directly.
   *
   * An active editor instance.
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

    if (this.active && this.active === this.instances[id]) {
      this.active = null;
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
const JoomlaEditorButton = {
  /**
   * Internal! The property should not be accessed directly.
   *
   * A collection of button actions.
   */
  actions: {},

  /**
   * Register new button action, or override existing.
   *
   * @param {String} name Action name
   * @param {Function} handler Callback that will be executed.
   *
   * @returns {JoomlaEditorButton}
   */
  registerAction(name, handler) {
    if (!name || !handler) {
      throw new Error('Missed values for Action registration');
    }
    if (!(handler instanceof Function)) {
      throw new Error(`Unexpected handler for action "${name}", expecting Function`);
    }

    this.actions[name] = handler;
    return this;
  },

  /**
   * Get registered handler by action name.
   *
   * @param {String} name Action name
   *
   * @returns {Function|false}
   */
  getActionHandler(name) {
    return this.actions[name] || false;
  },

  /**
   * Execute action.
   *
   * @param {String} name Action name
   * @param {Object} options An options object
   * @param {HTMLElement} button An optional element, that triggers the action
   *
   * @returns {*}
   */
  runAction(name, options, button) {
    const handler = this.getActionHandler(name);
    let editor = JoomlaEditor.getActive();
    if (!handler) {
      throw new Error(`Handler for "${name}" action not found`);
    }
    // Try to find a legacy editor
    // @TODO: Remove this section in Joomla 6
    if (!editor && button) {
      const parent = button.closest('fieldset, div:not(.editor-xtd-buttons)');
      const textarea = parent ? parent.querySelector('textarea[id]') : false;
      editor = textarea && Joomla.editors.instances[textarea.id] ? Joomla.editors.instances[textarea.id] : false;
      if (editor) {
        // eslint-disable-next-line no-console
        console.warn('Legacy editors is deprecated. Set active editor instance with JoomlaEditor.setActive().');
      }
    }
    if (!editor) {
      throw new Error('An active editor are not available');
    }

    return handler(editor, options);
  },
};

export { JoomlaEditor, JoomlaEditorButton, JoomlaEditorDecorator };
