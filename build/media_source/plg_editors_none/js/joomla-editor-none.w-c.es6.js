/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
window.customElements.define('joomla-editor-none', class extends HTMLElement {
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
   * Get the selected text
   */
  getSelection() {
    if (document.selection) {
      // IE support
      this.editor.focus();
      return document.selection.createRange();
    } if (this.editor.selectionStart || this.editor.selectionStart === 0) {
      // MOZILLA/NETSCAPE support
      return this.editor.value.substring(this.editor.selectionStart, this.editor.selectionEnd);
    }
    return this.editor.value;
  }

  /**
   * Register the editor
   */
  registerEditor() {
    if (!window.Joomla
        || !window.Joomla.editors
        || typeof window.Joomla.editors !== 'object') {
      throw new Error('The Joomla API is not correctly registered.');
    }

    window.Joomla.editors.instances[this.editor.id] = {
      id: () => this.editor.id,
      element: () => this.editor,
      // eslint-disable-next-line no-return-assign
      getValue: () => this.editor.value,
      // eslint-disable-next-line no-return-assign
      setValue: (text) => this.editor.value = text,
      // eslint-disable-next-line no-return-assign
      getSelection: () => this.getSelection(),
      // eslint-disable-next-line no-return-assign
      disable: (disabled) => {
        this.editor.disabled = disabled;
        this.editor.readOnly = disabled;
      },
      // eslint-disable-next-line no-return-assign
      replaceSelection: (text) => {
        if (this.editor.selectionStart || this.editor.selectionStart === 0) {
          this.editor.value = this.editor.value.substring(0, this.editor.selectionStart)
            + text
            + this.editor.value.substring(this.editor.selectionEnd, this.editor.value.length);
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
      delete window.Joomla.editors.instances[this.editor.id];
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
});
