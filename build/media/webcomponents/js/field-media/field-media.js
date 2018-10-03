((customElements, Joomla) => {
  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  Joomla.selectedFile = {};

  window.document.addEventListener('onMediaFileSelected', (e) => {
    Joomla.selectedFile = e.detail;
  });

  Joomla.doIt = (resp, editor, fieldClass) => {
    if (resp.success === true) {
      if (resp.data[0].url) {
        if (/local-/.test(resp.data[0].adapter)) {
          const server = Joomla.getOptions('system.paths').rootFull;

          Joomla.selectedFile.url = resp.data[0].url.split(server)[1];
          if (resp.data[0].thumb_path) {
            Joomla.selectedFile.thumb = resp.data[0].thumb_path;
          } else {
            Joomla.selectedFile.thumb = false;
          }
        } else {
          if (resp.data[0].thumb_path) {
            Joomla.selectedFile.thumb = resp.data[0].thumb_path;
          }
        }
      } else {
        Joomla.selectedFile.url = false;
      }

      const isElement = (o) => {
        return (
          typeof HTMLElement === 'object' ? o instanceof HTMLElement :
            o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string'
        );
      };

      if (Joomla.selectedFile.url) {
        if (!isElement(editor) && (typeof editor !== 'object')) {
          Joomla.editors.instances[editor].replaceSelection(`<img src="${Joomla.selectedFile.url}" alt=""/>`);
        } else if (!isElement(editor) && (typeof editor === 'object' && editor.id)) {
          window.parent.Joomla.editors.instances[editor.id].replaceSelection(`<img src="${Joomla.selectedFile.url}" alt=""/>`);
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
  Joomla.getImage = (data, editor, fieldClass) => {
    return new Promise((resolve, reject) => {
      const apiBaseUrl = `${Joomla.getOptions('system.paths').rootFull}administrator/index.php?option=com_media&format=json`;

      Joomla.request({
        url: `${apiBaseUrl}&task=api.files&url=true&path=${data.path}&${Joomla.getOptions('csrf.token')}=1&format=json`,
        method: 'GET',
        perform: true,
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          const resp = JSON.parse(response);
          resolve(Joomla.doIt(resp, editor, fieldClass));
        },
        onError: () => {
          reject();
        },
      });
    });
  };

  class JoomlaFieldMedia extends HTMLElement {
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
      const button = this.querySelector(this.buttonSelect);
      const buttonClear = this.querySelector(this.buttonClear);
      this.show = this.show.bind(this);
      this.modalClose = this.modalClose.bind(this);
      this.clearValue = this.clearValue.bind(this);
      this.setValue = this.setValue.bind(this);
      this.updatePreview = this.updatePreview.bind(this);

      button.addEventListener('click', this.show);

      if (buttonClear) {
        buttonClear.addEventListener('click', this.clearValue);
      }

      this.updatePreview();
    }

    disconnectedCallback() {
      const button = this.querySelector(this.buttonClear);
      button.removeEventListener('click', this);
    }

    show() {
      const self = this;

      this.querySelector('[role="dialog"]').open();

      this.querySelector(this.buttonSaveSelected).addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (this.selectedPath) {
          self.setValue(this.selectedPath);
        }

        self.modalClose();
        return false;
      });
    }

    modalClose() {
      const input = this.querySelector(this.input);
      Joomla.getImage(Joomla.selectedFile, input, this);

      Joomla.Modal.getCurrent().close();
    }

    setValue(value) {
      this.querySelector(this.input).value = value;
      this.updatePreview();
    }

    clearValue() {
      this.setValue('');
    }

    updatePreview() {
      if (['true', 'static'].indexOf(this.preview) === -1 || this.preview === 'false') {
        return;
      }

      // Reset preview
      if (this.preview) {
        const input = this.querySelector(this.input);
        const value = input.value;
        const div = this.querySelector('.field-media-preview');

        if (!value) {
          div.innerHTML = '<span class="field-media-preview-icon fa fa-picture-o"></span>';
        } else {
          div.innerHTML = '';
          const imgPreview = new Image();

          switch (this.type) {
            case 'image':
              imgPreview.src = imgPreview.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
              break;
            default:
              // imgPreview.src = dummy image path;
              break;
          }

          div.style.width = this.previewWidth;
          div.appendChild(imgPreview);
        }
      }
    }
  }
  customElements.define('joomla-field-media', JoomlaFieldMedia);
})(customElements, Joomla);
