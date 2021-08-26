((customElements, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  class JoomlaFieldMedia extends HTMLElement {
    constructor() {
      super();

      this.onSelected = this.onSelected.bind(this);
      this.show = this.show.bind(this);
      this.clearValue = this.clearValue.bind(this);
      this.modalClose = this.modalClose.bind(this);
      this.setValue = this.setValue.bind(this);
      this.updatePreview = this.updatePreview.bind(this);
    }

    static get observedAttributes() {
      return ['type', 'base-path', 'root-folder', 'url', 'modal-container', 'modal-width', 'modal-height', 'input', 'button-select', 'button-clear', 'button-save-selected', 'preview', 'preview-width', 'preview-height'];
    }

    get type() { return this.getAttribute('type'); }

    set type(value) { this.setAttribute('type', value); }

    get basePath() { return this.getAttribute('base-path'); }

    set basePath(value) { this.setAttribute('base-path', value); }

    get rootFolder() { return this.getAttribute('root-folder'); }

    set rootFolder(value) { this.setAttribute('root-folder', value); }

    get url() { return this.getAttribute('url'); }

    set url(value) { this.setAttribute('url', value); }

    get modalContainer() { return this.getAttribute('modal-container'); }

    set modalContainer(value) { this.setAttribute('modal-container', value); }

    get input() { return this.getAttribute('input'); }

    set input(value) { this.setAttribute('input', value); }

    get buttonSelect() { return this.getAttribute('button-select'); }

    set buttonSelect(value) { this.setAttribute('button-select', value); }

    get buttonClear() { return this.getAttribute('button-clear'); }

    set buttonClear(value) { this.setAttribute('button-clear', value); }

    get buttonSaveSelected() { return this.getAttribute('button-save-selected'); }

    set buttonSaveSelected(value) { this.setAttribute('button-save-selected', value); }

    get modalWidth() { return this.getAttribute(parseInt('modal-width', 10)); }

    set modalWidth(value) { this.setAttribute('modal-width', value); }

    get modalHeight() { return this.getAttribute(parseInt('modal-height', 10)); }

    set modalHeight(value) { this.setAttribute('modal-height', value); }

    get previewWidth() { return this.getAttribute(parseInt('preview-width', 10)); }

    set previewWidth(value) { this.setAttribute('preview-width', value); }

    get previewHeight() { return this.getAttribute(parseInt('preview-height', 10)); }

    set previewHeight(value) { this.setAttribute('preview-height', value); }

    get preview() { return this.getAttribute('preview'); }

    set preview(value) { this.setAttribute('preview', value); }

    get previewContainer() { return this.getAttribute('preview-container'); }

    // attributeChangedCallback(attr, oldValue, newValue) {}

    connectedCallback() {
      this.button = this.querySelector(this.buttonSelect);
      this.inputElement = this.querySelector(this.input);
      this.buttonClearEl = this.querySelector(this.buttonClear);
      this.modalElement = this.querySelector('.joomla-modal');
      this.buttonSaveSelectedElement = this.querySelector(this.buttonSaveSelected);
      this.previewElement = this.querySelector('.field-media-preview');

      if (!this.button || !this.inputElement || !this.buttonClearEl || !this.modalElement
        || !this.buttonSaveSelectedElement) {
        throw new Error('Misconfiguaration...');
      }

      this.button.addEventListener('click', this.show);

      // Bootstrap modal init
      if (this.modalElement
        && window.bootstrap
        && window.bootstrap.Modal
        && window.bootstrap.Modal.getInstance(this.modalElement) === undefined) {
        Joomla.initialiseModal(this.modalElement, { isJoomla: true });
      }

      if (this.buttonClearEl) {
        this.buttonClearEl.addEventListener('click', this.clearValue);
      }

      this.updatePreview();
    }

    disconnectedCallback() {
      if (this.button) {
        this.button.removeEventListener('click', this.show);
      }
      if (this.buttonClearEl) {
        this.buttonClearEl.removeEventListener('click', this.clearValue);
      }
    }

    onSelected(event) {
      event.preventDefault();
      event.stopPropagation();

      this.modalClose();
      return false;
    }

    show() {
      this.modalElement.open();

      Joomla.selectedMediaFile = {};

      this.buttonSaveSelectedElement.addEventListener('click', this.onSelected);
    }

    async modalClose() {
      try {
        await Joomla.getImage(Joomla.selectedMediaFile, this.inputElement, this);
      } catch (err) {
        Joomla.renderMessages({
          error: [Joomla.Text._('JLIB_APPLICATION_ERROR_SERVER')],
        });
      }

      Joomla.selectedMediaFile = {};
      Joomla.Modal.getCurrent().close();
    }

    setValue(value) {
      this.inputElement.value = value;
      this.updatePreview();

      // trigger change event both on the input and on the custom element
      this.inputElement.dispatchEvent(new Event('change'));
      this.dispatchEvent(new CustomEvent('change', {
        detail: { value },
        bubbles: true,
      }));
    }

    clearValue() {
      this.setValue('');
    }

    updatePreview() {
      if (['true', 'static'].indexOf(this.preview) === -1 || this.preview === 'false' || !this.previewElement) {
        return;
      }

      // Reset preview
      if (this.preview) {
        const { value } = this.inputElement;

        if (!value) {
          this.previewElement.innerHTML = '<span class="field-media-preview-icon"></span>';
        } else {
          this.previewElement.innerHTML = '';
          const imgPreview = new Image();

          const mediaType = {
            image() {
              imgPreview.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
              imgPreview.setAttribute('alt', '');
            },
          };

          mediaType[this.type]();

          this.previewElement.style.width = this.previewWidth;
          this.previewElement.appendChild(imgPreview);
        }
      }
    }
  }
  customElements.define('joomla-field-media', JoomlaFieldMedia);
})(customElements, Joomla);
