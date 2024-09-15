/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved, max-classes-per-file
import { JoomlaEditor, JoomlaEditorDecorator } from 'editor-api';
// eslint-disable-next-line import/no-unresolved
import { createFromTextarea, EditorState, keymap } from 'codemirror';

/**
 * Codemirror Decorator for JoomlaEditor
 */
// eslint-disable-next-line max-classes-per-file
class CodemirrorDecorator extends JoomlaEditorDecorator {
  /**
   * @returns {string}
   */
  getValue() {
    return this.instance.state.doc.toString();
  }

  /**
   * @param {String} value
   * @returns {CodemirrorDecorator}
   */
  setValue(value) {
    const editor = this.instance;
    editor.dispatch({
      changes: { from: 0, to: editor.state.doc.length, insert: value },
    });
    return this;
  }

  /**
   * @returns {string}
   */
  getSelection() {
    const { state } = this.instance;
    return state.sliceDoc(
      state.selection.main.from,
      state.selection.main.to,
    );
  }

  replaceSelection(value) {
    const v = this.instance.state.replaceSelection(value);
    this.instance.dispatch(v);
    return this;
  }

  disable(enable) {
    const editor = this.instance;
    editor.state.config.compartments.forEach((facet, compartment) => {
      if (compartment.$j_name === 'readOnly') {
        editor.dispatch({
          effects: compartment.reconfigure(EditorState.readOnly.of(!enable)),
        });
      }
    });
    return this;
  }
}

class CodemirrorEditor extends HTMLElement {
  constructor() {
    super();

    this.toggleFullScreen = () => {
      if (!this.classList.contains('fullscreen')) {
        this.classList.add('fullscreen');
        document.documentElement.scrollTop = 0;
        document.documentElement.style.overflow = 'hidden';
      } else {
        this.closeFullScreen();
      }
    };

    this.closeFullScreen = () => {
      this.classList.remove('fullscreen');
      document.documentElement.style.overflow = '';
    };

    this.interactionCallback = () => {
      JoomlaEditor.setActive(this.element.id);
    };
  }

  get options() { return JSON.parse(this.getAttribute('options')); }

  get fsCombo() { return this.getAttribute('fs-combo'); }

  async connectedCallback() {
    const { options } = this;

    // Configure full screen feature
    if (this.fsCombo) {
      options.customExtensions = options.customExtensions || [];
      options.customExtensions.push(() => keymap.of([
        { key: this.fsCombo, run: this.toggleFullScreen },
        { key: 'Escape', run: this.closeFullScreen },
      ]));

      // Relocate BS modals, to resolve z-index issue in full screen
      this.bsModals = this.querySelectorAll('.joomla-modal.modal');
      this.bsModals.forEach((modal) => document.body.appendChild(modal));
    }

    // Create and register the Editor
    this.element = this.querySelector('textarea');
    this.instance = await createFromTextarea(this.element, options);
    this.jEditor = new CodemirrorDecorator(this.instance, 'codemirror', this.element.id);
    JoomlaEditor.register(this.jEditor);

    // Find out when editor is interacted
    this.addEventListener('click', this.interactionCallback);
  }

  disconnectedCallback() {
    if (this.instance) {
      this.element.style.display = '';
      this.instance.destroy();
    }
    // Remove from the Joomla API
    JoomlaEditor.unregister(this.element.id);
    this.removeEventListener('click', this.interactionCallback);

    // Restore modals
    if (this.bsModals && this.bsModals.length) {
      this.bsModals.forEach((modal) => this.appendChild(modal));
    }
  }
}

customElements.define('joomla-editor-codemirror', CodemirrorEditor);
