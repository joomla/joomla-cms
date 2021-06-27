/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
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
document.addEventListener('onMediaFileSelected', async (e) => {
  Joomla.selectedMediaFile = e.detail;
  const currentModal = Joomla.Modal.getCurrent();
  const container = currentModal.querySelector('.modal-body');

  if (!container) {
    return;
  }

  const optionsEl = container.querySelector('joomla-field-mediamore');
  if (optionsEl) {
    optionsEl.parentNode.removeChild(optionsEl);
  }

  // No extra attributes (lazy, alt) for fields
  if (container.closest('joomla-field-media')) {
    return;
  }

  if (Joomla.selectedMediaFile.path) {
    let type;
    if (['png', 'jpg', 'jpeg', 'bpm', 'gif', 'webp'].includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'image';
    } else if (['mp3'].includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'audio';
    } else if (['mp4'].includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'video';
    } else if (['doc', 'docx', 'pdf'].includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'document';
    }

    if (type) {
      container.insertAdjacentHTML('afterbegin', `
<joomla-field-mediamore
  parent-id="${currentModal.id}"
  type="${type}"
  summary-label="${Joomla.Text._('JFIELD_MEDIA_SUMMARY_LABEL')}"
  lazy-label="${Joomla.Text._('JFIELD_MEDIA_LAZY_LABEL')}"
  alt-label="${Joomla.Text._('JFIELD_MEDIA_ALT_LABEL')}"
  alt-check-label="${Joomla.Text._('JFIELD_MEDIA_ALT_CHECK_LABEL')}"
  alt-check-desc-label="${Joomla.Text._('JFIELD_MEDIA_ALT_CHECK_DESC_LABEL')}"
  classes-label="${Joomla.Text._('JFIELD_MEDIA_CLASS_LABEL')}"
  figure-classes-label="${Joomla.Text._('JFIELD_MEDIA_FIGURE_CLASS_LABEL')}"
  figure-caption-label="${Joomla.Text._('JFIELD_MEDIA_FIGURE_CAPTION_LABEL')}"
  embed-check-label="${Joomla.Text._('JFIELD_MEDIA_EMBED_CHECK_LABEL')}"
  embed-check-desc-label="${Joomla.Text._('JFIELD_MEDIA_EMBED_CHECK_DESC_LABEL')}"
  controls-label="${Joomla.Text._('JFIELD_MEDIA_CONTROLS_LABEL')}"
  controls-desc-label="${Joomla.Text._('JFIELD_MEDIA_CONTROLS_DESC_LABEL')}"
  width-label="${Joomla.Text._('JFIELD_MEDIA_WIDTH_LABEL')}"
  height-label="${Joomla.Text._('JFIELD_MEDIA_HEIGHT_LABEL')}"
></joomla-field-mediamore>
`);
    }
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

const insertAsImage = async (media, editor, fieldClass) => {
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
      fieldClass.updatePreview();
    }
  }
};

const insertAsOther = (media, editor, fieldClass, type) => {
  if (media.url) {
    if (/local-/.test(media.adapter)) {
      const { rootFull } = Joomla.getOptions('system.paths');
      // eslint-disable-next-line prefer-destructuring
      Joomla.selectedMediaFile.url = `${media.url.split(rootFull)[1]}`;
    } else {
      Joomla.selectedMediaFile.url = media.url;
    }
  } else {
    Joomla.selectedMediaFile.url = false;
  }

  let attribs;
  if (Joomla.selectedMediaFile.url) {
    // Available Only inside an editor
    if (!isElement(editor)) {
      let outputText;
      const currentModal = fieldClass.closest('.modal-content');
      attribs = currentModal.querySelector('joomla-field-mediamore');
      if (attribs) {
        const embedable = attribs.getAttribute('embed-it');
        if (embedable && embedable === 'true') {
          if (type === 'audio') {
            const controls = attribs.getAttribute('with-controls') ? 'controls="true"' : '';
            outputText = `<audio ${controls} src="${Joomla.selectedMediaFile.url}"></audio>`;
          }
          if (type === 'document') {
            // @todo use ${Joomla.selectedMediaFile.filetype} in type
            outputText = `<object type="application/${Joomla.selectedMediaFile.extension}" data="${Joomla.selectedMediaFile.url}" width="${attribs.getAttribute('width')}" height="${attribs.getAttribute('height')}">
  ${Joomla.Text._('JFIELD_MEDIA_UNSUPPORTED').replace('{tag}', `<a download href="${Joomla.selectedMediaFile.url}">`).replace(/{extension}/g, Joomla.selectedMediaFile.extension)}
  </object>`;
          }
          if (type === 'video') {
            const controls = attribs.getAttribute('with-controls') ? 'controls="true"' : '';
            outputText = `<video ${controls} width="${attribs.getAttribute('width')}" height="${attribs.getAttribute('height')}">
  <source src="${Joomla.selectedMediaFile.url}" type="${Joomla.selectedMediaFile.fileType}">
  </video>`;
          }
        } else if (Joomla.editors.instances[editor].getSelection() !== '') {
          outputText = `<a download href="${Joomla.selectedMediaFile.url}">${Joomla.editors.instances[editor].getSelection()}</a>`;
        } else {
          const name = /([\w-]+)\./.exec(Joomla.selectedMediaFile.url);
          outputText = `<a download href="${Joomla.selectedMediaFile.url}">${Joomla.Text._('JFIELD_MEDIA_DOWNLOAD_FILE').replace('{file}', name[1])}</a>`;
        }
      }

      if (attribs) {
        attribs.parentNode.removeChild(attribs);
      }

      Joomla.editors.instances[editor].replaceSelection(outputText);
    }
  }
};
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
    if (Joomla.selectedMediaFile.extension && ['png', 'jpg', 'jpeg', 'bpm', 'gif', 'webp'].includes(media.extension.toLowerCase())) {
      return insertAsImage(media, editor, fieldClass);
    }

    if (['mp3'].includes(media.extension.toLowerCase())) {
      return insertAsOther(media, editor, fieldClass, 'audio');
    }

    if (['doc', 'docx', 'pdf'].includes(media.extension.toLowerCase())) {
      return insertAsOther(media, editor, fieldClass, 'document');
    }

    if (['mp4'].includes(media.extension.toLowerCase())) {
      return insertAsOther(media, editor, fieldClass, 'video');
    }
    return '';
  }
  return '';
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
Joomla.getMedia = (data, editor, fieldClass) => new Promise((resolve, reject) => {
  if (!data || (typeof data === 'object' && (!data.path || data.path === ''))) {
    Joomla.selectedMediaFile = {};
    resolve({
      resp: {
        success: false,
      },
    });
    return;
  }

  const url = `${Joomla.getOptions('system.paths').baseFull}index.php?option=com_media&task=api.files&url=true&path=${data.path}&${Joomla.getOptions('csrf.token')}=1&format=json`;
  fetch(
    url,
    {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    },
  )
    .then((response) => response.json())
    .then(async (response) => resolve(await execTransform(response, editor, fieldClass)))
    .catch((error) => reject(error));
});

// For B/C purposes
Joomla.getImage = Joomla.getMedia;

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
  get type() { return this.getAttribute('type'); }

  get parentId() { return this.getAttribute('parent-id'); }

  get lazytext() { return this.getAttribute('lazy-label'); }

  get alttext() { return this.getAttribute('alt-label'); }

  get altchecktext() { return this.getAttribute('alt-check-label'); }

  get altcheckdesctext() { return this.getAttribute('alt-check-desc-label'); }

  get embedchecktext() { return this.getAttribute('embed-check-label'); }

  get embedcheckdesctext() { return this.getAttribute('embed-check-desc-label'); }

  get classestext() { return this.getAttribute('classes-label'); }

  get figclassestext() { return this.getAttribute('figure-classes-label'); }

  get figcaptiontext() { return this.getAttribute('figure-caption-label'); }

  get summarytext() { return this.getAttribute('summary-label'); }

  get controlstext() { return this.getAttribute('controls-label'); }

  get controlsdesctext() { return this.getAttribute('controls-desc-label'); }

  get widthtext() { return this.getAttribute('width-label'); }

  get heighttext() { return this.getAttribute('height-label'); }

  connectedCallback() {
    if (this.type === 'image') {
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

      this.lazyInputFn = this.lazyInputFn.bind(this);
      this.altInputFn = this.altInputFn.bind(this);
      this.altCheckFn = this.altCheckFn.bind(this);
      this.imgClassesFn = this.imgClassesFn.bind(this);
      this.figclassesFn = this.figclassesFn.bind(this);
      this.figcaptionFn = this.figcaptionFn.bind(this);

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
    } else if (['audio', 'video', 'document'].includes(this.type)) {
      this.innerHTML = `
<details open>
<summary>${this.summarytext}</summary>
<div class="">
  <div class="form-group">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" id="${this.parentId}-embed-check">
      <label class="form-check-label" for="${this.parentId}-embed-check">${this.embedchecktext}</label>
      <div><small class="form-text">${this.embedcheckdesctext}</small></div>
    </div>
  </div>
  <div class="form-group">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" id="${this.parentId}-controls-check">
      <label class="form-check-label" for="${this.parentId}-controls-check">${this.controlstext}</label>
      <div><small class="form-text">${this.controlsdesctext}</small></div>
    </div>
  </div>
  <div style="display: ${this.type === 'audio' ? 'none' : 'block'}">
    <div class="form-group">
      <div class="input-group">
        <label class="input-group-text" for="${this.parentId}-width">${this.widthtext}</label>
        <input class="form-control" type="text" id="${this.parentId}-width" value="800"/>
      </div>
    </div>
    <div class="form-group">
      <div class="input-group">
        <label class="input-group-text" for="${this.parentId}-height">${this.heighttext}</label>
        <input class="form-control" type="text" id="${this.parentId}-height" value="600"/>
      </div>
    </div>
  </div>
</div>
</details>`;

      this.embedInputFn = this.embedInputFn.bind(this);
      this.embedCheck = this.querySelector(`#${this.parentId}-embed-check`);
      this.embedCheck.addEventListener('input', this.embedInputFn);
      this.setAttribute('embed-it', !!this.embedCheck.checked);
      this.controlsInputFn = this.controlsInputFn.bind(this);
      this.controlsCheck = this.querySelector(`#${this.parentId}-controls-check`);
      this.controlsCheck.addEventListener('input', this.controlsInputFn);
      this.setAttribute('with-controls', !!this.controlsCheck.checked);
      this.widthInputFn = this.widthInputFn.bind(this);
      this.width = this.querySelector(`#${this.parentId}-width`);
      this.width.addEventListener('input', this.widthInputFn);
      this.setAttribute('width', this.width.value);
      this.heightInputFn = this.heightInputFn.bind(this);
      this.height = this.querySelector(`#${this.parentId}-height`);
      this.height.addEventListener('input', this.heightInputFn);
      this.setAttribute('height', this.height.value);
    }
  }

  disconnectedCallback() {
    if (this.type === 'image') {
      this.lazyInput.removeEventListener('input', this.lazyInputFn);
      this.altInput.removeEventListener('input', this.altInputFn);
      this.altCheck.removeEventListener('input', this.altCheckFn);
      this.imgClasses.removeEventListener('input', this.imgClassesFn);
      this.figClasses.removeEventListener('input', this.figclassesFn);
      this.figCaption.removeEventListener('input', this.figcaptionFn);
    }

    if (['audio', 'video', 'document'].includes(this.type)) {
      this.embedCheck.removeEventListener('input', this.embedInputFn);
    }

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

  embedInputFn(e) {
    this.setAttribute('embed-it', !!e.target.checked);
  }

  controlsInputFn(e) {
    this.setAttribute('with-controls', !!e.target.checked);
  }

  widthInputFn(e) {
    this.setAttribute('width', e.target.value);
  }

  heightInputFn(e) {
    this.setAttribute('height', e.target.value);
  }
}

customElements.define('joomla-field-mediamore', JoomlaFieldMediaOptions);
