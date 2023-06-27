/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable-next-line import/no-unresolved
import { createFromTextarea } from 'codemirror';
import { EditorState } from '@codemirror/state';

class CodemirrorEditor extends HTMLElement {
  // constructor() {
  //   super();
  //
  //   this.instance = null;
  //   this.host = window.location.origin;
  //   this.element = this.querySelector('textarea');
  //
  //   // Observer instance to refresh the Editor when it become visible, eg after Tab switching
  //   this.intersectionObserver = new IntersectionObserver((entries) => {
  //     if (entries[0].isIntersecting && this.instance) {
  //       this.instance.refresh();
  //     }
  //   }, { threshold: 0 });
  // }

  // static get observedAttributes() {
  //   return ['options'];
  // }

  get options() { return JSON.parse(this.getAttribute('options')); }

  // set options(value) { this.setAttribute('options', value); }
  //
  // attributeChangedCallback(attr, oldValue, newValue) {
  //   switch (attr) {
  //     case 'options':
  //       if (oldValue && newValue !== oldValue) {
  //         this.refresh(this.element);
  //       }
  //       break;
  //     default:
  //     // Do nothing
  //   }
  // }

  async connectedCallback() {
    // Register Editor
    // this.instance = window.CodeMirror.fromTextArea(this.element, this.options);
    // this.instance.disable = (disabled) => this.setOption('readOnly', disabled ? 'nocursor' : false);

    this.element = this.querySelector('textarea');
    const editor = await createFromTextarea(this.element, this.options);
    this.instance = editor;

console.log(editor.state);

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

    // Watch when the element in viewport, and refresh the editor
    // this.intersectionObserver.observe(this);
  }

  disconnectedCallback() {
    // Remove from the Joomla API
    delete Joomla.editors.instances[this.element.id];

    // Remove from observer
    // this.intersectionObserver.unobserve(this);
  }

  // refresh = (element) => {
  //   this.instance.fromTextArea(element, this.options);
  // }
}

customElements.define('joomla-editor-codemirror', CodemirrorEditor);
