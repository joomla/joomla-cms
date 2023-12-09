/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved, max-classes-per-file
import { JoomlaEditor, JoomlaEditorDecorator } from 'editor-api';

/**
 * EditorNone Decorator for Joomla.Editor
 */
// eslint-disable-next-line max-classes-per-file
class EditorNoneDecorator extends JoomlaEditorDecorator {
  /**
   * @returns {string}
   */
  getValue() {
    return this.instance.getValue();
  }

  /**
   * @param {string} value
   * @returns {EditorNoneDecorator}
   */
  setValue(value) {
    this.instance.setValue(value);
    return this;
  }

  /**
   * @returns {string}
   */
  getSelection() {
    return this.instance.getSelection();
  }

  replaceSelection(value) {
    this.instance.replaceSelection(value);
    return this;
  }

  disable(enable) {
    if (this.instance.editor) {
      this.instance.editor.disabled = !enable;
      this.instance.editor.readOnly = !enable;
    }
    return this;
  }
}

class JoomlaEditorNone extends HTMLElement {
  constructor() {
    super();

    // Properties
    this.editor = '';

    // Bindings
    this.unregisterEditor = this.unregisterEditor.bind(this);
    this.registerEditor = this.registerEditor.bind(this);
    this.childrenChange = this.childrenChange.bind(this);
    this.getSelection = this.getSelection.bind(this);

    // Watch for children changes.
    // eslint-disable-next-line no-return-assign
    new MutationObserver(() => this.childrenChange())
      .observe(this, { childList: true });

    // Find out when editor is interacted
    this.interactionCallback = () => {
      if (this.editor) {
        JoomlaEditor.setActive(this.editor.id);
      }
    };
  }

  /**
   * Lifecycle
   */
  connectedCallback() {
    // Note the mutation observer won't fire for initial contents,
    // so childrenChange is also called here.
    this.childrenChange();
    this.addEventListener('click', this.interactionCallback);
  }

  /**
   * Lifecycle
   */
  disconnectedCallback() {
    this.unregisterEditor();
    this.removeEventListener('click', this.interactionCallback);
  }

  /**
   * Get editor value
   */
  getValue() {
    return this.editor.value;
  }

  /**
   * Set editor value
   * @param {string} text
   */
  setValue(text) {
    this.editor.value = text;
  }

  /**
   * Get the selected text
   */
  getSelection() {
    if (this.editor.selectionStart || this.editor.selectionStart === 0) {
      return this.editor.value.substring(this.editor.selectionStart, this.editor.selectionEnd);
    }
    return this.editor.value;
  }

  /**
   * Replace selected text
   * @param {string} text
   */
  replaceSelection(text) {
    const ed = this.editor;
    if (ed.selectionStart || ed.selectionStart === 0) {
      ed.value = ed.value.substring(0, ed.selectionStart)
        + text
        + ed.value.substring(ed.selectionEnd, ed.value.length);
    } else {
      ed.value += text;
    }
  }

  /**
   * Register the editor
   */
  registerEditor() {
    const jEditor = new EditorNoneDecorator(this, 'none', this.editor.id);
    JoomlaEditor.register(jEditor);
  }

  /**
   * Remove the editor from the Joomla API
   */
  unregisterEditor() {
    if (this.editor) {
      JoomlaEditor.unregister(this.editor.id);
    }
  }

  /**
   * Called when element's child list changes
   */
  childrenChange() {
    // Ensure the first child is an input with a textarea type.
    if (this.firstElementChild
            && this.firstElementChild.tagName
            && this.firstElementChild.tagName.toLowerCase() === 'textarea'
            && this.firstElementChild.getAttribute('id')) {
      this.editor = this.firstElementChild;
      this.unregisterEditor();
      this.registerEditor();
    }
  }
}

customElements.define('joomla-editor-none', JoomlaEditorNone);
