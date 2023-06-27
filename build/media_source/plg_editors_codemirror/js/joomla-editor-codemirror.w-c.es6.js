/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable-next-line import/no-unresolved
import { createFromTextarea } from 'codemirror';
import { EditorState } from '@codemirror/state';
import { keymap } from "@codemirror/view";

class CodemirrorEditor extends HTMLElement {

  get options() { return JSON.parse(this.getAttribute('options')); }

  get fsCombo() { return this.getAttribute('fs-combo'); }

  async connectedCallback() {
    const { options } = this;

    // Configure full screen feature
    if (this.fsCombo) {
      options.customExtensions = options.customExtensions || [];
      options.customExtensions.push(() => {
        return keymap.of([
          {key: this.fsCombo, run: this.toggleFullScreen},
          {key: 'Escape', run: this.closeFullScreen},
        ]);
      });
    }

    // Create an editor instance
    this.element = this.querySelector('textarea');
    const editor = await createFromTextarea(this.element, options);
    this.instance = editor;

    // Register Editor for Joomla api
    Joomla.editors.instances[this.element.id] = {
      id: () => this.element.id,
      element: () => this.element,
      getValue: () => editor.state.doc.toString(),
      setValue: (text) => {
        editor.dispatch({
          changes: { from: 0, to: editor.state.doc.length, insert: text },
        });
      },
      getSelection: () => editor.state.sliceDoc(
        editor.state.selection.main.from,
        editor.state.selection.main.to,
      ),
      replaceSelection: (text) => {
        const v = editor.state.replaceSelection(text);
        editor.dispatch(v);
      },
      disable: (disabled) => {
        editor.state.config.compartments.forEach((facet, compartment) => {
          // eslint-disable-next-line no-underscore-dangle
          if (compartment._j_name === 'readOnly') {
            editor.dispatch({
              effects: compartment.reconfigure(EditorState.readOnly.of(disabled)),
            });
          }
        });
      },
      onSave: () => {},
    };
  }

  disconnectedCallback() {
    if (this.instance) {
      this.element.style.display = '';
      this.instance.destroy();
    }
    // Remove from the Joomla API
    delete Joomla.editors.instances[this.element.id];
  }

  toggleFullScreen = () => {
    this.classList.toggle('fullscreen');
  }

  closeFullScreen = () => {
    this.classList.remove('fullscreen');
  }
}

customElements.define('joomla-editor-codemirror', CodemirrorEditor);
