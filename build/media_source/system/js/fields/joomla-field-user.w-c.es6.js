((customElements, Joomla) => {
  class JoomlaFieldUser extends HTMLElement {
    constructor() {
      super();

      this.onUserSelect = '';
      this.onchangeStr = '';

      // Bind events
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
      // Set up elements
      this.modal = this.querySelector(this.modalClass);
      this.modalBody = this.querySelector('.modal-body');
      this.input = this.querySelector(this.inputId);
      this.inputName = this.querySelector(this.inputNameClass);
      this.buttonSelect = this.querySelector(this.buttonSelectClass);

      // Bootstrap modal init
      if (this.modal
        && window.bootstrap
        && window.bootstrap.Modal
        && !window.bootstrap.Modal.getInstance(this.modal)) {
        Joomla.initialiseModal(this.modal, { isJoomla: true });
      }

      if (this.buttonSelect) {
        this.buttonSelect.addEventListener('click', this.modalOpen.bind(this));
        this.modal.addEventListener('hide', this.removeIframe.bind(this));

        // Check for onchange callback,
        this.onchangeStr = this.input.getAttribute('data-onchange');
        if (this.onchangeStr) {
          /* eslint-disable */
          this.onUserSelect = new Function(this.onchangeStr);
          this.input.addEventListener('change', this.onUserSelect);
          /* eslint-enable */
        }
      }
    }

    disconnectedCallback() {
      if (this.onchangeStr && this.input) {
        this.input.removeEventListener('change', this.onUserSelect);
      }

      if (this.buttonSelect) {
        this.buttonSelect.removeEventListener('click', this);
      }

      if (this.modal) {
        this.modal.removeEventListener('hide', this);
      }
    }

    buttonClick({ target }) {
      this.setValue(target.getAttribute('data-user-value'), target.getAttribute('data-user-name'));
      this.modalClose();
    }

    iframeLoad() {
      const iframeDoc = this.iframeEl.contentWindow.document;
      const buttons = [].slice.call(iframeDoc.querySelectorAll('.button-select'));

      buttons.forEach((button) => {
        button.addEventListener('click', this.buttonClick);
      });
    }

    // Opens the modal
    modalOpen() {
      // Reconstruct the iframe
      this.removeIframe();
      const iframe = document.createElement('iframe');
      iframe.setAttribute('name', 'field-user-modal');
      iframe.src = this.url.replace('{field-user-id}', this.input.getAttribute('id'));
      iframe.setAttribute('width', this.modalWidth);
      iframe.setAttribute('height', this.modalHeight);

      this.modalBody.appendChild(iframe);

      this.modal.open();

      this.iframeEl = this.modalBody.querySelector('iframe');

      // handle the selection on the iframe
      this.iframeEl.addEventListener('load', this.iframeLoad);
    }

    // Closes the modal
    modalClose() {
      Joomla.Modal.getCurrent().close();
      this.modalBody.innerHTML = '';
    }

    // Remove the iframe
    removeIframe() {
      this.modalBody.innerHTML = '';
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

  customElements.define('joomla-field-user', JoomlaFieldUser);
})(customElements, Joomla);
