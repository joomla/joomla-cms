((customElements, Joomla) => {
  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  /**
   * An object holding all the information of the selected image on media manager
   * eg:
   * {
   *   extension: "png"
   *   fileType: "image/png"
   *   height: 44
   *   path: "local-0:/powered_by.png"
   *   thumb: undefined
   *   width: 294
   * }
   */
  Joomla.selectedMediaFile = {};

  /**
   * Event Listener that updates the Joomla.selectedMediaFile
   * to the selected file on the media manager
   */
  window.document.addEventListener('onMediaFileSelected', (e) => {
    Joomla.selectedMediaFile = e.detail;

    const currentModal = Joomla.Modal.getCurrent();
    const container = currentModal.querySelector('.modal-body');

    // No extra attributes (lazy, alt) for fields
    if (container.closest('joomla-field-media')) {
      return;
    }

    const optionsEl = container.querySelector('joomla-field-mediamore');
    if (optionsEl) {
      optionsEl.parentElement.removeChild(optionsEl);
    }

    if (Joomla.selectedMediaFile.path) {
      container.insertAdjacentHTML('afterbegin', `<joomla-field-mediamore with-alt parent-id="${currentModal.id}" lazy-label="Image will be lazyloaded" alt-label="Alternative text" confirm-text="Confirm"></joomla-field-mediamore>`);
    }
  });

  /**
   * Method to append the image in an editor or a field
   *
   * @param resp
   * @param editor
   * @param fieldClass
   */
  const execTransform = (resp, editor, fieldClass) => {
    if (resp.success === true) {
      if (resp.data[0].url) {
        if (/local-/.test(resp.data[0].adapter)) {
          const { rootFull } = Joomla.getOptions('system.paths');

          // eslint-disable-next-line prefer-destructuring
          Joomla.selectedMediaFile.url = resp.data[0].url.split(rootFull)[1];
          if (resp.data[0].thumb_path) {
            Joomla.selectedMediaFile.thumb = resp.data[0].thumb_path;
          } else {
            Joomla.selectedMediaFile.thumb = false;
          }
        } else if (resp.data[0].thumb_path) {
          Joomla.selectedMediaFile.thumb = resp.data[0].thumb_path;
        }
      } else {
        Joomla.selectedMediaFile.url = false;
      }

      const isElement = (o) => (
        typeof HTMLElement === 'object' ? o instanceof HTMLElement
          : o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string'
      );

      const appendParam = (url, key, value) => {
        const newKey = encodeURIComponent(key);
        const newValue = encodeURIComponent(value);
        const r = new RegExp(`(&|\\?)${key}=[^\&]*`);
        let s = url;
        const param = `${newKey}=${newValue}`;

        s = s.replace(r, `$1${param}`);

        if (!RegExp.$1 && s.includes('?')) {
          return `${s}&${param}`;
        }

        if (!RegExp.$1 && !s.includes('?')) {
          return `${s}?${param}`;
        }

        return s;
      };

      if (Joomla.selectedMediaFile.url) {
        let isLasy;
        let alt;

        if (!isElement(editor) && (typeof editor !== 'object')) {
          const currentModal = fieldClass.closest('.modal-content');
          const attribs = currentModal.querySelector('joomla-field-mediamore');
          if (attribs) {
            isLasy = attribs.getAttribute('is-lazy') === 'true' ? 'loading="lazy"' : '';
            alt = attribs.getAttribute('alt-value') ? `alt="${attribs.getAttribute('alt-value')}"` : 'alt=""';
          }

          alt = alt || 'alt=""';
          Joomla.editors.instances[editor].replaceSelection(`<img ${isLasy} src="${Joomla.selectedMediaFile.url}" width="${Joomla.selectedMediaFile.width}" height="${Joomla.selectedMediaFile.height}" ${alt} />`);
          attribs.parentNode.removeChild(attribs);
        } else if (!isElement(editor) && (typeof editor === 'object' && editor.id)) {
          const currentModal = fieldClass.closest('.modal-content');
          const attribs = currentModal.querySelector('joomla-field-mediamore');
          if (attribs) {
            isLasy = attribs.getAttribute('is-lazy') === 'true' ? 'loading="lazy"' : '';
            alt = attribs.getAttribute('alt-value') ? `alt="${attribs.getAttribute('alt-value')}"` : 'alt=""';
          }

          alt = alt || 'alt=""';
          window.parent.Joomla.editors.instances[editor.id].replaceSelection(`<img ${isLasy} src="${Joomla.selectedMediaFile.url}" width="${Joomla.selectedMediaFile.width}" height="${Joomla.selectedMediaFile.height}" ${alt} />`);
          attribs.parentNode.removeChild(attribs);
        } else {
          const val = appendParam(Joomla.selectedMediaFile.url, 'joomla_image_width', Joomla.selectedMediaFile.width);
          editor.value = appendParam(val, 'joomla_image_height', Joomla.selectedMediaFile.height);
          fieldClass.updatePreview();
        }
      }
    }
  };

  /**
   * Method that resolves the real url for the image
   *
   * @param data        {object}       The data for the detail
   * @param editor      string|object  The data for the detail
   * @param fieldClass  HTMLElement    The data for the detail
   *
   * @returns {void}
   */
  Joomla.getImage = (data, editor, fieldClass) => new Promise((resolve, reject) => {
    if (!data || (typeof data === 'object' && (!data.path || data.path === ''))) {
      Joomla.selectedMediaFile = {};
      resolve({
        resp: {
          success: false,
        },
      });
      return;
    }

    const apiBaseUrl = `${Joomla.getOptions('system.paths').rootFull}administrator/index.php?option=com_media&format=json`;

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

  class JoomlaFieldMediaOptions extends HTMLElement {
    constructor() {
      super();
      this.lazyInputFn = this.lazyInputFn.bind(this);
      this.altInputFn = this.altInputFn.bind(this);
      this.adjustHeight = this.adjustHeight.bind(this);
    }

    get parentId() {return this.getAttribute('parent-id'); }
    get lazytext() {return this.getAttribute('lazy-label'); }
    get alttext() {return this.getAttribute('alt-label'); }
    get confirmtext() {return this.getAttribute('confirm-text'); }
    get enableAltField() { return this.hasAttribute('with-alt'); }

    connectedCallback() {
      const altField = `
<div class="col-auto">
  <div class="input-group">
    <div class="input-group-prepend">
      <label class="input-group-text" for="${this.parentId}-alt">${this.alttext}</label>
    </div>
    <input class="form-control" type="text" id="${this.parentId}-alt" />
  </div>
</div>`;

      this.innerHTML = `
<div class="form-row align-items-center">
  ${this.enableAltField ? altField : ''}
  <div class="col-auto">
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" id="${this.parentId}-lazy" checked>
      <label class="form-check-label" for="${this.parentId}-lazy">${this.lazytext}</label>
    </div>
  </div>
</div>`;

      this.lazyInput = this.querySelector(`#${this.parentId}-lazy`);
      this.lazyInput.addEventListener('change', this.lazyInputFn);
      this.setAttribute('is-lazy', !!this.lazyInput.checked);

      if (this.enableAltField) {
        this.altInput = this.querySelector(`#${this.parentId}-alt`);
        this.altInput.addEventListener('input', this.altInputFn);
      }

      requestAnimationFrame(this.adjustHeight);
    }

    disconnectedCallback() {
      this.lazyInput.removeEventListener('click', this.lazyInputFn);
      if (this.enableAltField) {
        this.altInput.removeEventListener('click', this.altInputFn);
      }

      this.innerHTML = '';
    }

    lazyInputFn(e) {
      this.setAttribute('is-lazy', !!e.target.checked);
    }

    altInputFn(e) {
      this.setAttribute('alt-value', e.target.value);
    }

    adjustHeight() {
      const that = this;
      const nextEl = this.nextElementSibling;
      requestAnimationFrame(() => {
        const height = `${nextEl.getBoundingClientRect().height - that.getBoundingClientRect().height}`
        nextEl.style.height = `${height}px`;
      });
    }
  }

  customElements.define('joomla-field-mediamore', JoomlaFieldMediaOptions);
})(customElements, Joomla);
