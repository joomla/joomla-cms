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

const supportedExtensions = Joomla.getOptions('media-picker', {});
if (!Object.keys(supportedExtensions).length) {
  throw new Error('No supported extensions provided');
}

/**
 * Event Listener that updates the Joomla.selectedMediaFile
 * to the selected file in the media manager
 */
document.addEventListener('onMediaFileSelected', async (e) => {
  Joomla.selectedMediaFile = e.detail;
  const currentModal = Joomla.Modal.getCurrent();
  const container = currentModal.querySelector('.joomla-dialog-body');

  // No extra attributes (lazy, alt) for fields
  if (!container || container.closest('.joomla-dialog-media-field')) {
    return;
  }

  const optionsEl = container.querySelector('joomla-field-mediamore');
  if (optionsEl) {
    optionsEl.parentNode.removeChild(optionsEl);
  }

  const {
    images, audios, videos, documents,
  } = supportedExtensions;

  if (Joomla.selectedMediaFile.path && Joomla.selectedMediaFile.type === 'file') {
    let type;
    if (images.includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'images';
    } else if (audios.includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'audios';
    } else if (videos.includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'videos';
    } else if (documents.includes(Joomla.selectedMediaFile.extension.toLowerCase())) {
      type = 'documents';
    }

    if (type) {
      container.insertAdjacentHTML('afterbegin', `<joomla-field-mediamore
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
  download-check-label="${Joomla.Text._('JFIELD_MEDIA_DOWNLOAD_CHECK_LABEL')}"
  download-check-desc-label="${Joomla.Text._('JFIELD_MEDIA_DOWNLOAD_CHECK_DESC_LABEL')}"
  title-label="${Joomla.Text._('JFIELD_MEDIA_TITLE_LABEL')}"
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
    const { rootFull } = Joomla.getOptions('system.paths');
    const parts = media.url.split(rootFull);
    if (parts.length > 1) {
      // eslint-disable-next-line prefer-destructuring
      Joomla.selectedMediaFile.url = parts[1];
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

    if (!isElement(editor) || editor.replaceSelection) {
      const editorInst = editor.replaceSelection ? editor : Joomla.editors.instances[editor];
      const currentModal = Joomla.Modal.getCurrent();
      attribs = currentModal.querySelector('joomla-field-mediamore');
      if (attribs) {
        if (attribs.getAttribute('alt-check') === 'true') {
          appendAlt = ' alt=""';
        }
        alt = attribs.getAttribute('alt-value') ? ` alt="${attribs.getAttribute('alt-value')}"` : appendAlt;
        classes = attribs.getAttribute('img-classes') ? ` class="${attribs.getAttribute('img-classes')}"` : '';
        figClasses = attribs.getAttribute('fig-classes') ? ` class="image ${attribs.getAttribute('fig-classes')}"` : ' class="image"';
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
        imageElement = `<figure${figClasses}><img src="${Joomla.selectedMediaFile.url}"${classes}${isLazy}${alt} data-path="${Joomla.selectedMediaFile.path}"/><figcaption>${figCaption}</figcaption></figure>`;
      } else {
        imageElement = `<img src="${Joomla.selectedMediaFile.url}"${classes}${isLazy}${alt} data-path="${Joomla.selectedMediaFile.path}"/>`;
      }

      if (attribs) {
        attribs.parentNode.removeChild(attribs);
      }

      editorInst.replaceSelection(imageElement);
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
      fieldClass.markValid();
      fieldClass.setValue(`${Joomla.selectedMediaFile.url}#joomlaImage://${media.path.replace(':', '')}?width=${Joomla.selectedMediaFile.width}&height=${Joomla.selectedMediaFile.height}`);
    }
  }
};

const insertAsOther = (media, editor, fieldClass, type) => {
  if (media.url) {
    const { rootFull } = Joomla.getOptions('system.paths');
    const parts = media.url.split(rootFull);
    if (parts.length > 1) {
      // eslint-disable-next-line prefer-destructuring
      Joomla.selectedMediaFile.url = parts[1];
    } else {
      Joomla.selectedMediaFile.url = media.url;
    }
  } else {
    Joomla.selectedMediaFile.url = false;
  }

  let attribs;
  if (Joomla.selectedMediaFile.url) {
    // Available Only inside an editor
    if (!isElement(editor) || editor.replaceSelection) {
      let outputText;
      const editorInst = editor.replaceSelection ? editor : Joomla.editors.instances[editor];
      const currentModal = Joomla.Modal.getCurrent();
      attribs = currentModal.querySelector('joomla-field-mediamore');
      if (attribs) {
        const embedable = attribs.getAttribute('embed-it');
        if (embedable && embedable === 'true') {
          if (type === 'audios') {
            outputText = `<audio controls src="${Joomla.selectedMediaFile.url}"></audio>`;
          }
          if (type === 'documents') {
            // @todo use ${Joomla.selectedMediaFile.filetype} in type
            const title = attribs.getAttribute('title');
            outputText = `<object type="application/${Joomla.selectedMediaFile.extension}" data="${Joomla.selectedMediaFile.url}" ${title ? `title="${title}"` : ''} width="${attribs.getAttribute('width')}" height="${attribs.getAttribute('height')}">
  ${Joomla.Text._('JFIELD_MEDIA_UNSUPPORTED').replace('{tag}', `<a download href="${Joomla.selectedMediaFile.url}">`).replace(/{extension}/g, Joomla.selectedMediaFile.extension)}
</object>`;
          }
          if (type === 'videos') {
            outputText = `<video controls width="${attribs.getAttribute('width')}" height="${attribs.getAttribute('height')}">
  <source src="${Joomla.selectedMediaFile.url}" type="${Joomla.selectedMediaFile.fileType}">
</video>`;
          }
        } else if (editorInst.getSelection() !== '') {
          outputText = `<a download href="${Joomla.selectedMediaFile.url}">${editorInst.getSelection()}</a>`;
        } else {
          const name = /([\w-]+)\./.exec(Joomla.selectedMediaFile.url);
          outputText = `<a download href="${Joomla.selectedMediaFile.url}">${Joomla.Text._('JFIELD_MEDIA_DOWNLOAD_FILE').replace('{file}', name[1])}</a>`;
        }
      }

      if (attribs) {
        attribs.parentNode.removeChild(attribs);
      }

      editorInst.replaceSelection(outputText);
    } else {
      fieldClass.markValid();
      fieldClass.givenType = type;
      fieldClass.setValue(Joomla.selectedMediaFile.url);
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
    const {
      images, audios, videos, documents,
    } = supportedExtensions;

    if (Joomla.selectedMediaFile.extension && images.includes(media.extension.toLowerCase())) {
      return insertAsImage(media, editor, fieldClass);
    }

    if (Joomla.selectedMediaFile.extension && audios.includes(media.extension.toLowerCase())) {
      return insertAsOther(media, editor, fieldClass, 'audios');
    }

    if (Joomla.selectedMediaFile.extension && documents.includes(media.extension.toLowerCase())) {
      return insertAsOther(media, editor, fieldClass, 'documents');
    }

    if (Joomla.selectedMediaFile.extension && videos.includes(media.extension.toLowerCase())) {
      return insertAsOther(media, editor, fieldClass, 'videos');
    }
  }
  return '';
};

/**
 * Method that resolves the real url for the selected media file
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

  // Compile the url
  const apiUrl = Joomla.getOptions('media-picker-api', {}).apiBaseUrl || 'index.php?option=com_media&format=json';
  const url = new URL(apiUrl, window.location.origin);
  url.searchParams.append('task', 'api.files');
  url.searchParams.append('url', true);
  url.searchParams.append('path', data.path);
  url.searchParams.append('mediatypes', '0,1,2,3');
  url.searchParams.append(Joomla.getOptions('csrf.token'), 1);

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

  get downloadchecktext() { return this.getAttribute('download-check-label'); }

  get downloadcheckdesctext() { return this.getAttribute('download-check-desc-label'); }

  get classestext() { return this.getAttribute('classes-label'); }

  get figclassestext() { return this.getAttribute('figure-classes-label'); }

  get figcaptiontext() { return this.getAttribute('figure-caption-label'); }

  get summarytext() { return this.getAttribute('summary-label'); }

  get widthtext() { return this.getAttribute('width-label'); }

  get heighttext() { return this.getAttribute('height-label'); }

  get titletext() { return this.getAttribute('title-label'); }

  connectedCallback() {
    if (this.type === 'images') {
      this.innerHTML = `<details open>
<summary>${this.summarytext}</summary>
<div class="">
  <div class="form-group">
    <div class="input-group">
      <label class="input-group-text" for="${this.parentId}-alt">${this.alttext}</label>
      <input class="form-control" type="text" id="${this.parentId}-alt" data-is="alt-value" />
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
      <input class="form-control" type="text" id="${this.parentId}-classes" data-is="img-classes"/>
    </div>
  </div>
  <div class="form-group">
    <div class="input-group">
      <label class="input-group-text" for="${this.parentId}-figclasses">${this.figclassestext}</label>
      <input class="form-control" type="text" id="${this.parentId}-figclasses" data-is="fig-classes"/>
    </div>
  </div>
  <div class="form-group">
    <div class="input-group">
      <label class="input-group-text" for="${this.parentId}-figcaption">${this.figcaptiontext}</label>
      <input class="form-control" type="text" id="${this.parentId}-figcaption" data-is="fig-caption"/>
    </div>
  </div>
</div>
</details>`;

      this.lazyInputFn = this.lazyInputFn.bind(this);
      this.altCheckFn = this.altCheckFn.bind(this);
      this.inputFn = this.inputFn.bind(this);

      // Add event listeners
      this.lazyInput = this.querySelector(`#${this.parentId}-lazy`);
      this.lazyInput.addEventListener('change', this.lazyInputFn);
      this.altCheck = this.querySelector(`#${this.parentId}-alt-check`);
      this.altCheck.addEventListener('input', this.altCheckFn);
      [].slice.call(this.querySelectorAll('input[type="text"]'))
        .map((el) => {
          el.addEventListener('input', this.inputFn);
          const { is } = el.dataset;
          if (is) {
            this.setAttribute(is, el.value.replace(/"/g, '&quot;'));
          }
          return el;
        });

      // Set initial values
      this.setAttribute('is-lazy', !!this.lazyInput.checked);
      this.setAttribute('alt-check', false);
    } else if (['audios', 'videos', 'documents'].includes(this.type)) {
      this.innerHTML = `<details open>
<summary>${this.summarytext}</summary>
<div class="">
  <div class="form-group">
    <div class="form-check">
      <input class="form-check-input radio" type="radio" name="flexRadioDefault" id="${this.parentId}-embed-check-2" value="0" checked>
      <label class="form-check-label" for="${this.parentId}-embed-check-2">
        ${this.downloadchecktext}
        <div><small class="form-text">${this.downloadcheckdesctext}</small></div>
      </label>
    </div>
    <div class="form-check">
      <input class="form-check-input radio" type="radio" name="flexRadioDefault" id="${this.parentId}-embed-check-1" value="1">
      <label class="form-check-label" for="${this.parentId}-embed-check-1">
        ${this.embedchecktext}
        <div><small class="form-text">${this.embedcheckdesctext}</small></div>
      </label>
    </div>
  </div>
  <div class="toggable-parts" style="display: none">
    <div style="display: ${this.type === 'audios' ? 'none' : 'block'}">
      <div class="form-group">
        <div class="input-group">
          <label class="input-group-text" for="${this.parentId}-width">${this.widthtext}</label>
          <input class="form-control" type="text" id="${this.parentId}-width" value="800" data-is="width"/>
        </div>
      </div>
      <div class="form-group">
        <div class="input-group">
          <label class="input-group-text" for="${this.parentId}-height">${this.heighttext}</label>
          <input class="form-control" type="text" id="${this.parentId}-height" value="600" data-is="height"/>
        </div>
      </div>
      <div style="display: ${this.type === 'document' ? 'block' : 'none'}">
        <div class="form-group">
          <div class="input-group">
            <label class="input-group-text" for="${this.parentId}-title">${this.titletext}</label>
            <input class="form-control" type="text" id="${this.parentId}-title" value="" data-is="title"/>
          </div>
        </div>
    </div>
  </div>
</div>
</details>`;

      this.embedInputFn = this.embedInputFn.bind(this);
      this.inputFn = this.inputFn.bind(this);

      [].slice.call(this.querySelectorAll('.form-check-input.radio'))
        .map((el) => el.addEventListener('input', this.embedInputFn));
      this.setAttribute('embed-it', false);

      [].slice.call(this.querySelectorAll('input[type="text"]'))
        .map((el) => {
          el.addEventListener('input', this.inputFn);
          const { is } = el.dataset;
          if (is) {
            this.setAttribute(is, el.value.replace(/"/g, '&quot;'));
          }
          return el;
        });
    }
  }

  disconnectedCallback() {
    if (this.type === 'image') {
      this.lazyInput.removeEventListener('input', this.lazyInputFn);
      this.altInput.removeEventListener('input', this.inputFn);
      this.altCheck.removeEventListener('input', this.altCheckFn);
    }

    if (['audio', 'video', 'document'].includes(this.type)) {
      [].slice.call(this.querySelectorAll('.form-check-input.radio'))
        .map((el) => el.removeEventListener('input', this.embedInputFn));
      [].slice.call(this.querySelectorAll('input[type="text"]'))
        .map((el) => el.removeEventListener('input', this.embedInputFn));
    }

    this.innerHTML = '';
  }

  lazyInputFn(e) {
    this.setAttribute('is-lazy', !!e.target.checked);
  }

  altCheckFn(e) {
    this.setAttribute('alt-check', !!e.target.checked);
  }

  inputFn(e) {
    const { is } = e.target.dataset;
    if (is) {
      this.setAttribute(is, e.target.value.replace(/"/g, '&quot;'));
    }
  }

  embedInputFn(e) {
    const { value } = e.target;
    this.setAttribute('embed-it', value !== '0');
    const toggable = this.querySelector('.toggable-parts');

    if (toggable) {
      if (toggable.style.display !== 'block') {
        toggable.style.display = 'block';
      } else {
        toggable.style.display = 'none';
      }
    }
  }
}

customElements.define('joomla-field-mediamore', JoomlaFieldMediaOptions);
