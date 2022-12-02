
class JoomlaFieldUser extends HTMLElement {
  constructor() {
    super();

    // Bind events
    this.modalOpen = this.modalOpen.bind(this);
    this.buttonClick = this.buttonClick.bind(this);
    this.iframeLoad = this.iframeLoad.bind(this);
    this.modalClose = this.modalClose.bind(this);
    this.setValue = this.setValue.bind(this);
  }

  static get observedAttributes() {
    return ['url', 'modal', 'modal-width', 'modal-height', 'input', 'input-name', 'button-select'];
  }

  get url() { return this.getAttribute('url'); }

  set url(value) { this.setAttribute('url', value); }

  get modalClass() { return this.getAttribute('modal'); }

  set modalClass(value) { this.setAttribute('modal', value); }

  get modalWidth() { return this.getAttribute('modal-width'); }

  set modalWidth(value) { this.setAttribute('modal-width', value); }

  get modalHeight() { return this.getAttribute('modal-height'); }

  set modalHeight(value) { this.setAttribute('modal-height', value); }

  get inputId() { return this.getAttribute('input'); }

  set inputId(value) { this.setAttribute('input', value); }

  get inputNameClass() { return this.getAttribute('input-name'); }

  set inputNameClass(value) { this.setAttribute('input-name', value); }

  get buttonSelectClass() { return this.getAttribute('button-select'); }

  set buttonSelectClass(value) { this.setAttribute('button-select', value); }

  connectedCallback() {
    this.input = this.querySelector(this.inputId);
    this.inputName = this.querySelector(this.inputNameClass);
    this.buttonSelect = this.querySelector(this.buttonSelectClass);


    if (this.buttonSelect) {
      this.buttonSelect.addEventListener('click', this.modalOpen.bind(this));
    }
  }

  disconnectedCallback() {
    if (this.buttonSelect) {
      this.buttonSelect.removeEventListener('click', this);
    }
  }

  buttonClick({ target }) {
    this.setValue(target.getAttribute('data-user-value'), target.getAttribute('data-user-name'));
    this.modalClose();
  }

  iframeLoad() {
    const iframeDoc = this.iframeEl.contentWindow.document;

    iframeDoc.querySelectorAll('.button-select').forEach((button) => {
      button.addEventListener('click', this.buttonClick);
    });
  }

  // Opens the modal
  modalOpen() {
    this.modalContainer = document.createElement('joomla-modal');
    this.modalContainer.setAttribute('id', `user-select-modal`);
    this.modalContainer.setAttribute('title', 'Select User');
    // this.modalContainer.setAttribute('url', this.url);
    this.modalContainer.setAttribute('url', this.url.replace('{field-user-id}', this.input.getAttribute('id')));
    this.modalContainer.setAttribute('close-text', 'Clooooooose');
    this.modalContainer.setAttribute('click-outside', false);

    this.append(this.modalContainer);

    this.modalContainer.open();

    this.modal = this.querySelector('dialog');
    this.iframeEl = this.querySelector('iframe');

    // handle the selection on the iframe
    this.iframeEl.addEventListener('load', this.iframeLoad);
  }

  // Closes the modal
  modalClose() {
    // Joomla.Modal.getCurrent().close();
    if (this.modal) {
      this.modal.close();
      this.removeChild(this.modalContainer);
    }
  }

  // Sets the value
  setValue(value, name) {
    this.input.setAttribute('value', value);
    this.inputName.setAttribute('value', name || value);
    // trigger change event both on the input and on the custom element
    this.input.dispatchEvent(new Event('change'));
    this.dispatchEvent(new CustomEvent('change', {
      detail: { value, name },
      bubbles: true,
    }));
  }
}

customElements.whenDefined('joomla-modal').then(() => customElements.define('joomla-field-user', JoomlaFieldUser));
