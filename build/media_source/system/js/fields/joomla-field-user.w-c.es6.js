/**
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';

class JoomlaFieldUser extends HTMLElement {
  constructor() {
    super();

    this.onUserSelect = '';
    this.onchangeStr = '';

    // Bind context
    this.modalClose = this.modalClose.bind(this);
    this.setValue = this.setValue.bind(this);
    this.modalOpen = this.modalOpen.bind(this);
  }

  static get observedAttributes() {
    return ['url', 'modal', 'modal-width', 'modal-height', 'modal-title', 'input', 'input-name', 'button-select'];
  }

  get url() {
    return this.getAttribute('url');
  }

  set url(value) {
    this.setAttribute('url', value);
  }

  get modalWidth() {
    return this.getAttribute('modal-width');
  }

  set modalWidth(value) {
    this.setAttribute('modal-width', value);
  }

  get modalTitle() {
    return this.getAttribute('modal-title');
  }

  set modalTitle(value) {
    this.setAttribute('modal-title', value);
  }

  get modalHeight() {
    return this.getAttribute('modal-height');
  }

  set modalHeight(value) {
    this.setAttribute('modal-height', value);
  }

  get inputId() {
    return this.getAttribute('input');
  }

  set inputId(value) {
    this.setAttribute('input', value);
  }

  get inputNameClass() {
    return this.getAttribute('input-name');
  }

  set inputNameClass(value) {
    this.setAttribute('input-name', value);
  }

  get buttonSelectClass() {
    return this.getAttribute('button-select');
  }

  set buttonSelectClass(value) {
    this.setAttribute('button-select', value);
  }

  connectedCallback() {
    // Set up elements
    this.input = this.querySelector(this.inputId);
    this.inputName = this.querySelector(this.inputNameClass);
    this.buttonSelect = this.querySelector(this.buttonSelectClass);

    if (this.buttonSelect) {
      this.buttonSelect.addEventListener('click', this.modalOpen.bind(this));
      // this.modal.addEventListener('hide', this.removeIframe.bind(this));

      // Check for onchange callback,
      this.onchangeStr = this.input.getAttribute('data-onchange');
      if (this.onchangeStr) {
        // eslint-disable-next-line no-new-func
        this.onUserSelect = new Function(this.onchangeStr);
        this.input.addEventListener('change', this.onUserSelect);
      }
    }
  }

  disconnectedCallback() {
    if (this.onUserSelect && this.input) {
      this.input.removeEventListener('change', this.onUserSelect);
    }

    if (this.buttonSelect) {
      this.buttonSelect.removeEventListener('click', this.modalOpen);
    }

    if (this.modal) {
      this.modal.removeEventListener('hide', this);
    }
  }

  // Opens the modal
  modalOpen() {
    // Create and show the dialog
    const dialog = new JoomlaDialog({
      popupType: 'iframe',
      src: this.url,
      textHeader: this.modalTitle,
      width: this.modalWidth,
      height: this.modalHeight,
    });
    dialog.classList.add('joomla-dialog-user-field');
    dialog.show();

    // Wait for message
    const msgListener = (event) => {
      // Avoid cross origins
      if (event.origin !== window.location.origin) return;
      // Check message type
      if (event.data.messageType === 'joomla:content-select') {
        this.setValue(event.data.id, event.data.name);
        dialog.close();
      } else if (event.data.messageType === 'joomla:cancel') {
        dialog.close();
      }
    };
    window.addEventListener('message', msgListener);

    dialog.addEventListener('joomla-dialog:close', () => {
      window.removeEventListener('message', msgListener);
      dialog.destroy();
      this.dialog = null;
      // Focus on the input field to re-trigger the validation
      this.inputName.focus();
      this.buttonSelect.focus();
    });

    this.dialog = dialog;
  }

  // Closes the modal
  modalClose() {
    if (this.dialog) {
      this.dialog.close();
    }
  }

  // Sets the value
  setValue(value, name) {
    this.input.setAttribute('value', value);
    this.inputName.setAttribute('value', name || value);
    // trigger change event both on the input and on the custom element
    this.input.dispatchEvent(new CustomEvent('change'));
    this.dispatchEvent(new CustomEvent('change', {
      detail: { value, name },
      bubbles: true,
    }));
  }
}

customElements.define('joomla-field-user', JoomlaFieldUser);
