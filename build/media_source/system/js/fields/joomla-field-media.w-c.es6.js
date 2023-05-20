/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('Joomla API is not properly initiated');
}

/**
 * Extract the extensions
 *
 * @param {*} path
 * @returns {string}
 */
const getExtension = (path) => {
  const parts = path.split(/[#]/);
  if (parts.length > 1) {
    return parts[1].split(/[?]/)[0].split('.').pop().trim();
  }
  return path.split(/[#?]/)[0].split('.').pop().trim();
};

class JoomlaFieldMedia extends HTMLElement {
  constructor() {
    super();

    this.onSelected = this.onSelected.bind(this);
    this.show = this.show.bind(this);
    this.clearValue = this.clearValue.bind(this);
    this.modalClose = this.modalClose.bind(this);
    this.setValue = this.setValue.bind(this);
    this.updatePreview = this.updatePreview.bind(this);
    this.validateValue = this.validateValue.bind(this);
    this.markValid = this.markValid.bind(this);
    this.markInvalid = this.markInvalid.bind(this);
    this.mimeType = '';
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

  get modalWidth() { return parseInt(this.getAttribute('modal-width'), 10); }

  set modalWidth(value) { this.setAttribute('modal-width', value); }

  get modalHeight() { return parseInt(this.getAttribute('modal-height'), 10); }

  set modalHeight(value) { this.setAttribute('modal-height', value); }

  get previewWidth() { return parseInt(this.getAttribute('preview-width'), 10); }

  set previewWidth(value) { this.setAttribute('preview-width', value); }

  get previewHeight() { return parseInt(this.getAttribute('preview-height'), 10); }

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
      && !window.bootstrap.Modal.getInstance(this.modalElement)) {
      Joomla.initialiseModal(this.modalElement, { isJoomla: true });
    }

    if (this.buttonClearEl) {
      this.buttonClearEl.addEventListener('click', this.clearValue);
    }

    this.supportedExtensions = Joomla.getOptions('media-picker', {});

    if (!Object.keys(this.supportedExtensions).length) {
      throw new Error('Joomla API is not properly initiated');
    }

    this.inputElement.removeAttribute('readonly');
    this.inputElement.addEventListener('change', this.validateValue);
    this.updatePreview();
  }

  disconnectedCallback() {
    if (this.button) {
      this.button.removeEventListener('click', this.show);
    }
    if (this.buttonClearEl) {
      this.buttonClearEl.removeEventListener('click', this.clearValue);
    }
    if (this.inputElement) {
      this.inputElement.removeEventListener('change', this.validateValue);
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
      await Joomla.getMedia(Joomla.selectedMediaFile, this.inputElement, this);
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
    this.validatedUrl = value;
    this.mimeType = Joomla.selectedMediaFile.fileType;
    this.updatePreview();

    // trigger change event both on the input and on the custom element
    this.inputElement.dispatchEvent(new Event('change'));
    this.dispatchEvent(new CustomEvent('change', {
      detail: { value },
      bubbles: true,
    }));
  }

  async validateValue(event) {
    let { value } = event.target;
    if (this.validatedUrl === value || value === '') return;

    if (/^(http(s)?:\/\/).+$/.test(value)) {
      try {
        fetch(value).then((response) => {
          if (response.status === 200) {
            this.validatedUrl = value;
            this.markValid();
          } else {
            this.validatedUrl = value;
            this.markInvalid();
          }
        });
      } catch (err) {
        this.validatedUrl = value;
        this.markInvalid();
      }
    } else {
      if (/^\//.test(value)) {
        value = value.substring(1);
      }

      const hashedUrl = value.split('#');
      const urlParts = hashedUrl[0].split('/');
      const rest = urlParts.slice(1);
      fetch(`${Joomla.getOptions('system.paths').rootFull}/${value}`)
        .then((response) => response.blob())
        .then((blob) => {
          if (blob.type.includes('image')) {
            const img = new Image();
            img.src = URL.createObjectURL(blob);

            img.onload = () => {
              this.inputElement.value = `${urlParts[0]}/${rest.join('/')}#joomlaImage://local-${urlParts[0]}/${rest.join('/')}?width=${img.width}&height=${img.height}`;
              this.validatedUrl = `${urlParts[0]}/${rest.join('/')}#joomlaImage://local-${urlParts[0]}/${rest.join('/')}?width=${img.width}&height=${img.height}`;
              this.markValid();
            };
          } else if (blob.type.includes('audio')) {
            this.mimeType = blob.type;
            this.inputElement.value = value;
            this.validatedUrl = value;
            this.markValid();
          } else if (blob.type.includes('video')) {
            this.mimeType = blob.type;
            this.inputElement.value = value;
            this.validatedUrl = value;
            this.markValid();
          } else if (blob.type.includes('application/pdf')) {
            this.mimeType = blob.type;
            this.inputElement.value = value;
            this.validatedUrl = value;
            this.markValid();
          } else {
            this.validatedUrl = value;
            this.markInvalid();
          }
        })
        .catch(() => {
          this.setValue(value);
          this.validatedUrl = value;
          this.markInvalid();
        });
    }
  }

  markValid() {
    this.inputElement.removeAttribute('required');
    this.inputElement.removeAttribute('pattern');
    if (document.formvalidator) {
      document.formvalidator.validate(this.inputElement);
    }
  }

  markInvalid() {
    this.inputElement.setAttribute('required', '');
    this.inputElement.setAttribute('pattern', '/^(http://INVALID/).+$/');
    if (document.formvalidator) {
      document.formvalidator.validate(this.inputElement);
    }
  }

  clearValue() {
    this.setValue('');
    this.validatedUrl = '';
    this.inputElement.removeAttribute('required');
    this.inputElement.removeAttribute('pattern');
    if (document.formvalidator) {
      document.formvalidator.validate(this.inputElement);
    }
  }

  updatePreview() {
    if (['true', 'static'].indexOf(this.preview) === -1 || this.preview === 'false' || !this.previewElement) {
      return;
    }

    // Reset preview
    if (this.preview) {
      const { value } = this.inputElement;
      const { supportedExtensions } = this;
      if (!value) {
        this.buttonClearEl.style.display = 'none';
        this.previewElement.innerHTML = Joomla.sanitizeHtml('<span class="field-media-preview-icon"></span>');
      } else {
        let type;
        this.buttonClearEl.style.display = '';
        this.previewElement.innerHTML = '';
        const ext = getExtension(value);

        if (supportedExtensions.images.includes(ext)) type = 'images';
        if (supportedExtensions.audios.includes(ext)) type = 'audios';
        if (supportedExtensions.videos.includes(ext)) type = 'videos';
        if (supportedExtensions.documents.includes(ext)) type = 'documents';
        let previewElement;

        const mediaType = {
          images: () => {
            if (supportedExtensions.images.includes(ext)) {
              previewElement = new Image();
              previewElement.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
              previewElement.setAttribute('alt', '');
            }
          },
          audios: () => {
            if (supportedExtensions.audios.includes(ext)) {
              previewElement = document.createElement('audio');
              previewElement.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
              previewElement.setAttribute('controls', '');
            }
          },
          videos: () => {
            if (supportedExtensions.videos.includes(ext)) {
              previewElement = document.createElement('video');
              const previewElementSource = document.createElement('source');
              previewElementSource.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
              previewElementSource.type = this.mimeType;
              previewElement.setAttribute('controls', '');
              previewElement.setAttribute('width', this.previewWidth);
              previewElement.setAttribute('height', this.previewHeight);
              previewElement.appendChild(previewElementSource);
            }
          },
          documents: () => {
            if (supportedExtensions.documents.includes(ext)) {
              previewElement = document.createElement('object');
              previewElement.data = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
              previewElement.type = this.mimeType;
              previewElement.setAttribute('width', this.previewWidth);
              previewElement.setAttribute('height', this.previewHeight);
            }
          },
        };

        // @todo more checks
        if (this.givenType && ['images', 'audios', 'videos', 'documents'].includes(this.givenType)) {
          mediaType[this.givenType]();
        } else if (type && ['images', 'audios', 'videos', 'documents'].includes(type)) {
          mediaType[type]();
        } else {
          return;
        }

        this.previewElement.style.width = this.previewWidth;
        this.previewElement.appendChild(previewElement);
      }
    }
  }
}
customElements.define('joomla-field-media', JoomlaFieldMedia);
