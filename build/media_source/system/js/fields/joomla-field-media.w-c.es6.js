((customElements, Joomla) => {
  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  Joomla.selectedFile = {};

  window.document.addEventListener('onMediaFileSelected', (e) => {
    Joomla.selectedFile = e.detail;
  });

  const execTransform = (resp, editor, fieldClass) => {
    if (resp.success === true) {
      if (resp.data[0].url) {
        if (/local-/.test(resp.data[0].adapter)) {
          const { rootFull } = Joomla.getOptions('system.paths');

          // eslint-disable-next-line prefer-destructuring
          Joomla.selectedFile.url = resp.data[0].url.split(rootFull)[1];
          if (resp.data[0].thumb_path) {
            Joomla.selectedFile.thumb = resp.data[0].thumb_path;
          } else {
            Joomla.selectedFile.thumb = false;
          }
        } else if (resp.data[0].thumb_path) {
          Joomla.selectedFile.thumb = resp.data[0].thumb_path;
        }
      } else {
        Joomla.selectedFile.url = false;
      }

      const isElement = (o) => (
        typeof HTMLElement === 'object' ? o instanceof HTMLElement
          : o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string'
      );

      if (Joomla.selectedFile.url) {
        if (!isElement(editor) && (typeof editor !== 'object')) {
          Joomla.editors.instances[editor].replaceSelection(`<img loading="lazy" src="${Joomla.selectedFile.url}" alt=""/>`);
        } else if (!isElement(editor) && (typeof editor === 'object' && editor.id)) {
          window.parent.Joomla.editors.instances[editor.id].replaceSelection(`<img loading="lazy" src="${Joomla.selectedFile.url}" alt=""/>`);
        } else {
          editor.value = Joomla.selectedFile.url;
          fieldClass.updatePreview();
        }
      }
    }
  };

  /**
   * Create and dispatch onMediaFileSelected Event
   *
   * @param {object}  data  The data for the detail
   *
   * @returns {void}
   */
  Joomla.getImage = (data, editor, fieldClass) => new Promise((resolve, reject) => {
    if (!data || (typeof data === 'object' && (!data.path || data.path === ''))) {
      Joomla.selectedFile = {};
      resolve({
        resp: {
          success: false,
        },
      });
      return;
    }

    const apiBaseUrl = `${Joomla.getOptions('system.paths').baseFull}index.php?option=com_media&format=json`;

    Joomla.request({
      url: `${apiBaseUrl}&task=api.files&url=true&path=${data.path}&${Joomla.getOptions('csrf.token')}=1&format=json`,
      method: 'GET',
      perform: true,
      headers: { 'Content-Type': 'application/json' },
      onSuccess: (response) => {
        const resp = JSON.parse(response);
        resolve(execTransform(resp, editor, fieldClass));
      },
      onError: (err) => {
        reject(err);
      },
    });
  });

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

      if (Joomla.Bootstrap && Joomla.Bootstrap.initModal && typeof Joomla.Bootstrap.initModal === 'function') {
        Joomla.Bootstrap.initModal(this.modalElement);
      }

      this.button.addEventListener('click', this.show);

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

      Joomla.selectedFile = {};

      this.buttonSaveSelectedElement.addEventListener('click', this.onSelected);
    }

    modalClose() {
      Joomla.getImage(Joomla.selectedFile, this.inputElement, this)
        .then(() => {
          Joomla.Modal.getCurrent().close();
          Joomla.selectedFile = {};
        })
        .catch(() => {
          Joomla.Modal.getCurrent().close();
          Joomla.selectedFile = {};
          Joomla.renderMessages({
            error: [Joomla.Text._('JLIB_APPLICATION_ERROR_SERVER')],
          });
        });
    }

    setValue(value) {
      this.inputElement.value = value;
      this.updatePreview();
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

          switch (this.type) {
            case 'image':
              imgPreview.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
              imgPreview.setAttribute('alt', '');
              break;
            default:
              // imgPreview.src = dummy image path;
              break;
          }

          this.previewElement.style.width = this.previewWidth;
          this.previewElement.appendChild(imgPreview);
        }
      }
    }
  }
  customElements.define('joomla-field-media', JoomlaFieldMedia);
})(customElements, Joomla);
