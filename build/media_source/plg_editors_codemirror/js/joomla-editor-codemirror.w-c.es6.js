/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable-next-line import/no-unresolved
import { createFromTextarea } from 'codemirror';

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

  connectedCallback() {

    // Register Editor
    // this.instance = window.CodeMirror.fromTextArea(this.element, this.options);
    // this.instance.disable = (disabled) => this.setOption('readOnly', disabled ? 'nocursor' : false);

    const options = this.options;
    this.element = this.querySelector('textarea');
    this.instance = createFromTextarea(this.element, this.options);

    Joomla.editors.instances[this.element.id] = this.instance;

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
