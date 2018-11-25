/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
class JoomlaEditorNone extends HTMLElement {
  constructor() {
    super();

    // Properties
    this.editor = '';

    // Bindings
    this.unregisterEditor = this.unregisterEditor.bind(this);
    this.registerEditor = this.registerEditor.bind(this);
    this.childrenChange = this.childrenChange.bind(this);

    // Watch for children changes.
    // eslint-disable-next-line no-return-assign
    new MutationObserver(() => this.childrenChange())
      .observe(this, { childList: true });
  }

  /**
   * Lifecycle
   */
  connectedCallback() {
    // Note the mutation observer won't fire for initial contents,
    // so childrenChange is also called here.
    this.childrenChange();
  }

  /**
   * Lifecycle
   */
  disconnectedCallback() {
    this.unregisterEditor();
  }

  /**
   * Register the editor
   */
  registerEditor() {
    if (!window.Joomla
        || !window.Joomla.editors
        || typeof window.Joomla.editors !== 'object'
        || window.Joomla.editors === null) {
      throw new Error('The Joomla API is not correctly registered.');
    }

    this.editor = this.editor || this.querySelector('textarea');

    Joomla.editors.instances[this.editor.id] = {
      id: this.editor.id,
      element: this.editor,
      // eslint-disable-next-line no-return-assign
      getValue: () => this.editor.value,
      // eslint-disable-next-line no-return-assign
      setValue: text => this.editor.value = text,
      // eslint-disable-next-line no-return-assign
      replaceSelection: (text) => {
        if (this.editor.selectionStart || this.editor.selectionStart === 0) {
          const startPos = this.editor.selectionStart;
          const endPos = this.editor.selectionEnd;
          this.editor.value = this.editor.value.substring(0, startPos)
            + text
            + this.editor.value.substring(endPos, this.editor.value.length);
        } else {
          this.editor.value += text;
        }
      },
      onSave: () => {},
    };
  }

  /**
   * Remove the editor from the Joomla API
   */
  unregisterEditor() {
    if (this.editor) {
      delete Joomla.editors.instances[this.editor.id];
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
      this.unregisterEditor();
      this.registerEditor();
    }
  }
}
customElements.define('joomla-editor-none', JoomlaEditorNone);
