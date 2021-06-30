((customElements, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  /**
   * An object holding all the information of the selected image in media manager
   * eg:
   * {
   *   extension: "png"
   *   fileType: "image/png"
   *   height: 44
   *   path: "local-images:/powered_by.png"
   *   thumb: undefined
   *   width: 294
   * }
   */
  Joomla.selectedMediaFile = {};

  /**
   * Event Listener that updates the Joomla.selectedMediaFile
   * to the selected file in the media manager
   */
  window.document.addEventListener('onMediaFileSelected', (e) => {
    Joomla.selectedMediaFile = e.detail;

    const currentModal = Joomla.Modal.getCurrent();
    const container = currentModal.querySelector('.modal-body');

    const optionsEl = container.querySelector('joomla-field-mediamore');
    if (optionsEl) {
      optionsEl.parentNode.removeChild(optionsEl);
    }

    // No extra attributes (lazy, alt) for fields
    if (container.closest('joomla-field-media')) {
      return;
    }

    if (Joomla.selectedMediaFile.path) {
      container.insertAdjacentHTML('afterbegin', `
<joomla-field-mediamore
  parent-id="${currentModal.id}"
  summary-label="${Joomla.Text._('JFIELD_MEDIA_SUMMARY_LABEL')}"
  lazy-label="${Joomla.Text._('JFIELD_MEDIA_LAZY_LABEL')}"
  alt-label="${Joomla.Text._('JFIELD_MEDIA_ALT_LABEL')}"
  alt-check-label="${Joomla.Text._('JFIELD_MEDIA_ALT_CHECK_LABEL')}"
  alt-check-desc-label="${Joomla.Text._('JFIELD_MEDIA_ALT_CHECK_DESC_LABEL')}"
  classes-label="${Joomla.Text._('JFIELD_MEDIA_CLASS_LABEL')}"
  figure-classes-label="${Joomla.Text._('JFIELD_MEDIA_FIGURE_CLASS_LABEL')}"
  figure-caption-label="${Joomla.Text._('JFIELD_MEDIA_FIGURE_CAPTION_LABEL')}"
></joomla-field-mediamore>
`);
    }
  });

  /**
   * Method to check if passed param is HTMLElement
   *
   * @param o {string|HTMLElement}  Element to be checked
   *
   * @returns {boolean}
   */
  const isElement = (o) => (
    typeof HTMLElement === 'object' ? o instanceof HTMLElement
      : o && typeof o === 'object' && o.nodeType === 1 && typeof o.nodeName === 'string'
  );

  /**
   * Method to return the image size
   *
   * @param url {string}
   *
   * @returns {bool}
   */
  const getImageSize = (url) => new Promise((resolve, reject) => {
    const img = new Image();
    img.src = url;
    img.onload = () => {
      Joomla.selectedMediaFile.width = img.width;
      Joomla.selectedMediaFile.height = img.height;
      resolve(true);
    };
    img.onerror = () => {
      // eslint-disable-next-line prefer-promise-reject-errors
      reject(false);
    };
  });

  /**
   * Method to append the image in an editor or a field
   *
   * @param {{}} resp
   * @param {string|HTMLElement} editor
   * @param {string} fieldClass
   */
  const execTransform = async (resp, editor, fieldClass) => {
    if (resp.success === true) {
      const media = resp.data[0];
      if (media.url) {
        if (/local-/.test(media.adapter)) {
          const { rootFull } = Joomla.getOptions('system.paths');
          // eslint-disable-next-line prefer-destructuring
          Joomla.selectedMediaFile.url = media.url.split(rootFull)[1];
          if (media.thumb_path) {
            Joomla.selectedMediaFile.thumb = media.thumb_path;
          } else {
            Joomla.selectedMediaFile.thumb = false;
          }
        } else if (media.thumb_path) {
          Joomla.selectedMediaFile.url = media.url;
          Joomla.selectedMediaFile.thumb = media.thumb_path;
        }
      } else {
        Joomla.selectedMediaFile.url = false;
      }

      if (Joomla.selectedMediaFile.url) {
        let attribs;
        let isLazy = '';
        let alt = '';
        let appendAlt = '';
        let classes = '';
        let figClasses = '';
        let figCaption = '';
        let imageElement = '';

        if (!isElement(editor)) {
          const currentModal = fieldClass.closest('.modal-content');
          attribs = currentModal.querySelector('joomla-field-mediamore');
          if (attribs) {
            if (attribs.getAttribute('alt-check') === 'true') {
              appendAlt = ' alt=""';
            }
            alt = attribs.getAttribute('alt-value') ? ` alt="${attribs.getAttribute('alt-value')}"` : appendAlt;
            classes = attribs.getAttribute('img-classes') ? ` class="${attribs.getAttribute('img-classes')}"` : '';
            figClasses = attribs.getAttribute('fig-classes') ? ` class="${attribs.getAttribute('fig-classes')}"` : '';
            figCaption = attribs.getAttribute('fig-caption') ? `${attribs.getAttribute('fig-caption')}` : '';
            if (attribs.getAttribute('is-lazy') === 'true') {
              isLazy = ` loading="lazy" width="${Joomla.selectedMediaFile.width}" height="${Joomla.selectedMediaFile.height}"`;
              if (Joomla.selectedMediaFile.width === 0 || Joomla.selectedMediaFile.height === 0) {
                try {
                  await getImageSize(Joomla.selectedMediaFile.url);
                  isLazy = ` loading="lazy" width="${Joomla.selectedMediaFile.width}" height="${Joomla.selectedMediaFile.height}"`;
                } catch (err) {
                  isLazy = '';
                }
              }
            }
          }

          if (figCaption) {
            imageElement = `<figure${figClasses}><img src="${Joomla.selectedMediaFile.url}"${classes}${isLazy}${alt}/><figcaption>${figCaption}</figcaption></figure>`;
          } else {
            imageElement = `<img src="${Joomla.selectedMediaFile.url}"${classes}${isLazy}${alt}/>`;
          }

          if (attribs) {
            attribs.parentNode.removeChild(attribs);
          }

          Joomla.editors.instances[editor].replaceSelection(imageElement);
        } else {
          if (Joomla.selectedMediaFile.width === 0 || Joomla.selectedMediaFile.height === 0) {
            try {
              await getImageSize(Joomla.selectedMediaFile.url);
              // eslint-disable-next-line no-empty
            } catch (err) {
              Joomla.selectedMediaFile.height = 0;
              Joomla.selectedMediaFile.width = 0;
            }
          }
          editor.value = `${Joomla.selectedMediaFile.url}#joomlaImage://${media.path.replace(':', '')}?width=${Joomla.selectedMediaFile.width}&height=${Joomla.selectedMediaFile.height}`;
          fieldClass.updateState();
        }
      }
    }
  };

  /**
   * Method that resolves the real url for the selected image
   *
   * @param data        {object}         The data for the detail
   * @param editor      {string|object}  The data for the detail
   * @param fieldClass  {HTMLElement}    The fieldClass for the detail
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

    const apiBaseUrl = `${Joomla.getOptions('system.paths').baseFull}index.php?option=com_media`;

    Joomla.request({
      url: `${apiBaseUrl}&task=api.files&url=true&path=${data.path}&${Joomla.getOptions('csrf.token')}=1&format=json`,
      method: 'GET',
      perform: true,
      headers: { 'Content-Type': 'application/json' },
      onSuccess: async (response) => {
        const resp = JSON.parse(response);
        resolve(await execTransform(resp, editor, fieldClass));
      },
      onError: (err) => {
        reject(err);
      },
    });
  });

  /**
   * A simple Custom Element for adding alt text and controlling
   * the lazy loading on a selected image
   *
   * Will be rendered only for editor content images
   * Attributes:
   * - parent-id: the id of the parent media field {string}
   * - lazy-label: The text for the checkbox label {string}
   * - alt-label: The text for the alt label {string}
   * - is-lazy: The value for the lazyloading (calculated, defaults to 'true') {string}
   * - alt-value: The value for the alt text (calculated, defaults to '') {string}
   */
  class JoomlaFieldMediaOptions extends HTMLElement {
    constructor() {
      super();

      this.lazyInputFn = this.lazyInputFn.bind(this);
      this.altInputFn = this.altInputFn.bind(this);
      this.altCheckFn = this.altCheckFn.bind(this);
      this.imgClassesFn = this.imgClassesFn.bind(this);
      this.figclassesFn = this.figclassesFn.bind(this);
      this.figcaptionFn = this.figcaptionFn.bind(this);
    }

    get parentId() { return this.getAttribute('parent-id'); }

    get lazytext() { return this.getAttribute('lazy-label'); }

    get alttext() { return this.getAttribute('alt-label'); }

    get altchecktext() { return this.getAttribute('alt-check-label'); }

    get altcheckdesctext() { return this.getAttribute('alt-check-desc-label'); }

    get classestext() { return this.getAttribute('classes-label'); }

    get figclassestext() { return this.getAttribute('figure-classes-label'); }

    get figcaptiontext() { return this.getAttribute('figure-caption-label'); }

    get summarytext() { return this.getAttribute('summary-label'); }

    connectedCallback() {
      this.innerHTML = `
<details open>
  <summary>${this.summarytext}</summary>
  <div class="">
    <div class="form-group">
      <div class="input-group">
        <label class="input-group-text" for="${this.parentId}-alt">${this.alttext}</label>
        <input class="form-control" type="text" id="${this.parentId}-alt" />
      </div>
    </div>
    <div class="form-group">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="${this.parentId}-alt-check">
        <label class="form-check-label" for="${this.parentId}-alt-check">${this.altchecktext}</label>
        <div><small class="form-text">${this.altcheckdesctext}</small></div>
      </div>
    </div>
    <div class="form-group">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="${this.parentId}-lazy" checked>
        <label class="form-check-label" for="${this.parentId}-lazy">${this.lazytext}</label>
      </div>
    </div>
    <div class="form-group">
      <div class="input-group">
        <label class="input-group-text" for="${this.parentId}-classes">${this.classestext}</label>
        <input class="form-control" type="text" id="${this.parentId}-classes" />
      </div>
    </div>
    <div class="form-group">
      <div class="input-group">
        <label class="input-group-text" for="${this.parentId}-figclasses">${this.figclassestext}</label>
        <input class="form-control" type="text" id="${this.parentId}-figclasses" />
      </div>
    </div>
    <div class="form-group">
      <div class="input-group">
        <label class="input-group-text" for="${this.parentId}-figcaption">${this.figcaptiontext}</label>
        <input class="form-control" type="text" id="${this.parentId}-figcaption" />
      </div>
    </div>
  </div>
</details>`;

      // Add event listeners
      this.lazyInput = this.querySelector(`#${this.parentId}-lazy`);
      this.lazyInput.addEventListener('change', this.lazyInputFn);
      this.altInput = this.querySelector(`#${this.parentId}-alt`);
      this.altInput.addEventListener('input', this.altInputFn);
      this.altCheck = this.querySelector(`#${this.parentId}-alt-check`);
      this.altCheck.addEventListener('input', this.altCheckFn);
      this.imgClasses = this.querySelector(`#${this.parentId}-classes`);
      this.imgClasses.addEventListener('input', this.imgClassesFn);
      this.figClasses = this.querySelector(`#${this.parentId}-figclasses`);
      this.figClasses.addEventListener('input', this.figclassesFn);
      this.figCaption = this.querySelector(`#${this.parentId}-figcaption`);
      this.figCaption.addEventListener('input', this.figcaptionFn);

      // Set initial values
      this.setAttribute('is-lazy', !!this.lazyInput.checked);
      this.setAttribute('alt-value', '');
      this.setAttribute('alt-check', false);
      this.setAttribute('img-classes', '');
      this.setAttribute('fig-classes', '');
      this.setAttribute('fig-caption', '');
    }

    disconnectedCallback() {
      this.lazyInput.removeEventListener('input', this.lazyInputFn);
      this.altInput.removeEventListener('input', this.altInputFn);
      this.altCheck.removeEventListener('input', this.altCheckFn);
      this.imgClasses.removeEventListener('input', this.imgClassesFn);
      this.figClasses.removeEventListener('input', this.figclassesFn);
      this.figCaption.removeEventListener('input', this.figcaptionFn);

      this.innerHTML = '';
    }

    lazyInputFn(e) {
      this.setAttribute('is-lazy', !!e.target.checked);
    }

    altInputFn(e) {
      this.setAttribute('alt-value', e.target.value.replace(/"/g, '&quot;'));
    }

    altCheckFn(e) {
      this.setAttribute('alt-check', !!e.target.checked);
    }

    imgClassesFn(e) {
      this.setAttribute('img-classes', e.target.value);
    }

    figclassesFn(e) {
      this.setAttribute('fig-classes', e.target.value);
    }

    figcaptionFn(e) {
      this.setAttribute('fig-caption', e.target.value);
    }
  }

  customElements.define('joomla-field-mediamore', JoomlaFieldMediaOptions);
})(customElements, Joomla);
