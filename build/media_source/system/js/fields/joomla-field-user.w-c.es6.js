((customElements, Joomla) => {
  class JoomlaFieldUser extends HTMLElement {
    constructor() {
      super();

      this.onUserSelect = '';
      this.onchangeStr = '';

      // Bind events
      this.modalOpen = this.modalOpen.bind(this);
      this.modalClose = this.modalClose.bind(this);
      this.buttonClick = this.buttonClick.bind(this);
      this.iframeLoad = this.iframeLoad.bind(this);
      this.setValue = this.setValue.bind(this);
    }

    static get observedAttributes() {
      return ['url', 'modal-width', 'modal-height', 'input', 'input-name', 'button-select'];
    }

    get url() { return this.getAttribute('url'); }

    set url(value) { this.setAttribute('url', value); }

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
      requestAnimationFrame(() => {
        // Set up elements
        this.input = this.querySelector(this.inputId);
        this.inputName = this.querySelector(this.inputNameClass);
        this.buttonSelect = this.querySelector(this.buttonSelectClass);

        if (this.buttonSelect) {
          this.buttonSelect.addEventListener('click', this.modalOpen.bind(this));
        }
      });
    }

    disconnectedCallback() {
      if (this.buttonSelect) {
        this.buttonSelect.removeEventListener('click', this.modalOpen);
      }
    }

    buttonClick({ target }) {
      this.setValue(target.getAttribute('data-user-value'), target.getAttribute('data-user-name'));
      Joomla.Modal.getCurrent().close();
    }

    iframeLoad() {
      const iframeDoc = this.dialog.querySelector('iframe').contentWindow.document;

      iframeDoc.querySelectorAll('.button-select').forEach((button) => button.addEventListener('click', this.buttonClick));
    }

    // Opens the modal
    modalOpen() {
      // eslint-disable-next-line
      this.dialog = new JoomlaDialog({
        popupType: 'iframe',
        textHeader: Joomla.Text._('JLIB_FORM_CHANGE_IMAGE'),
        src: this.url.replace('{field-user-id}', this.input.getAttribute('id')),
        popupButtons: [
          { label: Joomla.Text._('JCANCEL'), onClick: () => this.modalClose(), className: 'btn btn-outline-danger ms-2' },
        ],
      });

      Joomla.selectedMediaFile = {};

      this.dialog.addEventListener('joomla-dialog:load', this.iframeLoad);

      this.dialog.show();
      Joomla.Modal.setCurrent(this.dialog);
    }

    modalClose() {
      this.dialog.destroy();
      Joomla.Modal.setCurrent(null);
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
