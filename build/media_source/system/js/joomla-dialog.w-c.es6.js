/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Default template for the popup
const popupTemplate = `<div class="joomla-dialog-container">
  <header class="joomla-dialog-header"></header>
  <section class="joomla-dialog-body"></section>
  <footer class="joomla-dialog-footer"></footer>
</div>`;

/**
 * JoomlaDialog class for Joomla Dialog implementation.
 * With use of <joomla-dialog> custom element as dialog holder.
 */
class JoomlaDialog extends HTMLElement {
  /**
   * The popup type, supported: inline, iframe, image, ajax.
   * @type {string}
   */
  // popupType = 'inline';

  /**
   * An optional text for header.
   * @type {string}
   */
  // textHeader = '';

  /**
   * An optional text for close button. Applied when no Buttons provided.
   * @type {string}
   */
  // textClose = 'Close';

  /**
   * Content string (html) for inline type popup.
   * @type {string}
   */
  // popupContent = '';

  /**
   * Source path for iframe, image, ajax.
   * @type {string}
   */
  // src = '';

  /**
   * An optional list of buttons, to be rendered in footer or header, or bottom or top of the popup body.
   * Example:
   *   [{label: 'Yes', onClick: () => popup.close()},
   *   {label: 'No', onClick: () => popup.close(), className: 'btn btn-danger'},
   *   {label: 'Click me', onClick: () => popup.close(), location: 'header'}]
   * @type {[]}
   */
  // popupButtons = [];

  /**
   * Whether popup can be closed by Esc button.
   *
   * @type {boolean}
   */
  // cancelable = true;

  /**
   * An optional limit for the popup width, any valid CSS value.
   * @type {string}
   */
  // width = '';

  /**
   * An optional height for the popup, any valid CSS value.
   * @type {string}
   */
  // height = '';

  /**
   * An optional Class names for header icon.
   *
   * @type {string}
   */
  // iconHeader = '';

  /**
   * A template for the popup.
   * @type {string|HTMLTemplateElement}
   */
  // popupTemplate = popupTemplate;

  /**
   * The element where to attach the dialog, for cases when no parentElement exist, see show().
   * This allows to keep the dialog in the same branch of DOM as the popupContent.
   * @type {string|HTMLElement}
   */
  // preferredParent = null;

  /**
   * Class constructor
   * @param {Object} config
   */
  constructor(config) {
    super();

    // Define default params (doing it here because browser support of public props)
    this.popupType = this.getAttribute('type') || 'inline';
    this.textHeader = this.getAttribute('text-header') || '';
    this.iconHeader = '';
    this.textClose = Joomla.Text._('JCLOSE', 'Close');
    this.popupContent = '';
    this.src = this.getAttribute('src') || '';
    this.popupButtons = [];
    this.cancelable = !this.hasAttribute('not-cancelable');
    this.width = this.getAttribute('width') || '';
    this.height = this.getAttribute('height') || '';
    this.popupTemplate = popupTemplate;
    this.preferredParent = null;
    // @internal. Parent of the popupContent for cases when it is HTMLElement. Need for recovery on destroy().
    this.popupContentSrcLocation = null;

    if (!config) return;

    // Check configurable properties
    ['popupType', 'textHeader', 'textClose', 'popupContent', 'src', 'popupButtons', 'cancelable',
      'width', 'height', 'popupTemplate', 'iconHeader', 'id', 'preferredParent'].forEach((key) => {
      if (config[key] !== undefined) {
        this[key] = config[key];
      }
    });

    // Check class name
    if (config.className) {
      this.classList.add(...config.className.split(' '));
    }

    // Check dataset properties
    if (config.data) {
      Object.entries(config.data).forEach(([k, v]) => {
        this.dataset[k] = v;
      });
    }
  }

  /**
   * Internal. Connected Callback.
   */
  connectedCallback() {
    this.renderLayout();
  }

  /**
   * Internal. Render a main layout, based on given template.
   * @returns {JoomlaDialog}
   */
  renderLayout() {
    if (this.dialog) return this;

    // On close callback
    const onClose = () => {
      this.dispatchEvent(new CustomEvent('joomla-dialog:close', { bubbles: true }));
    };
    const onCancel = (event) => {
      if (!this.cancelable) {
        // Prevent closing by Esc
        event.preventDefault();
      }
    };

    // Check for existing layout
    if (this.firstElementChild && this.firstElementChild.nodeName === 'DIALOG') {
      this.dialog = this.firstElementChild;
      this.dialog.addEventListener('cancel', onCancel);
      this.dialog.addEventListener('close', onClose);
      this.popupTmplB = this.querySelector('.joomla-dialog-body') || this.dialog;
      this.popupContentElement = this.popupTmplB;
      return this;
    }

    // Render a template
    let templateContent;
    if (this.popupTemplate.tagName && this.popupTemplate.tagName === 'TEMPLATE') {
      templateContent = this.popupTemplate.content.cloneNode(true);
    } else {
      const template = document.createElement('template');
      template.innerHTML = this.popupTemplate;
      templateContent = template.content;
    }

    this.dialog = document.createElement('dialog');
    this.dialog.appendChild(templateContent);
    this.dialog.addEventListener('cancel', onCancel);
    this.dialog.addEventListener('close', onClose);
    this.appendChild(this.dialog);

    // Get template parts
    this.popupTmplH = this.dialog.querySelector('.joomla-dialog-header');
    this.popupTmplB = this.dialog.querySelector('.joomla-dialog-body');
    this.popupTmplF = this.dialog.querySelector('.joomla-dialog-footer');
    this.popupContentElement = null;

    if (!this.popupTmplB) {
      throw new Error('The popup body not found in the template.');
    }

    // Set the header
    if (this.popupTmplH && this.textHeader) {
      const h = document.createElement('h3');
      h.insertAdjacentHTML('afterbegin', this.textHeader);
      this.popupTmplH.insertAdjacentElement('afterbegin', h);

      if (this.iconHeader) {
        const i = document.createElement('span');
        i.ariaHidden = true;
        i.classList.add('header-icon');
        i.classList.add(...this.iconHeader.split(' '));
        this.popupTmplH.insertAdjacentElement('afterbegin', i);
      }
    }

    // Set the body
    this.renderBodyContent();
    this.setAttribute('type', this.popupType);

    // Create buttons if any
    const buttons = this.popupButtons || [];

    // Add at least one button to close the popup
    if (!buttons.length) {
      buttons.push({
        label: '',
        ariaLabel: this.textClose,
        className: 'button-close btn-close',
        data: { buttonClose: '' },
        onClick: () => this.close(),
        location: 'header',
      });
    }

    // Buttons holders
    const btnHHolder = document.createElement('div');
    const btnFHolder = document.createElement('div');
    btnHHolder.classList.add('buttons-holder');
    btnFHolder.classList.add('buttons-holder');

    this.popupButtons.forEach((btnData) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = btnData.label || '';
      btn.ariaLabel = btnData.ariaLabel || null;

      if (btnData.className) {
        btn.classList.add(...btnData.className.split(' '));
      } else {
        btn.classList.add('button', 'button-primary', 'btn', 'btn-primary');
      }

      if (btnData.data) {
        Object.entries(btnData.data).forEach(([k, v]) => {
          btn.dataset[k] = v;
        });
        if (btnData.data.dialogClose !== undefined) {
          btnData.onClick = () => this.close();
        }
        if (btnData.data.dialogDestroy !== undefined) {
          btnData.onClick = () => this.destroy();
        }
      }

      if (btnData.onClick) {
        btn.addEventListener('click', btnData.onClick);
      }

      if (btnData.location === 'header') {
        btnHHolder.appendChild(btn);
      } else {
        btnFHolder.appendChild(btn);
      }
    });

    if (btnHHolder.children.length) {
      if (this.popupType === 'image' && !this.textHeader) {
        this.popupTmplB.insertAdjacentElement('afterbegin', btnHHolder);
      } else if (this.popupTmplH) {
        this.popupTmplH.insertAdjacentElement('beforeend', btnHHolder);
      } else {
        this.popupTmplB.insertAdjacentElement('afterbegin', btnHHolder);
      }
    }

    if (btnFHolder.children.length) {
      (this.popupTmplF || this.popupTmplB).insertAdjacentElement('beforeend', btnFHolder);
    }

    // Adjust the sizes if requested
    if (this.width) {
      this.dialog.style.width = '100%';
      this.dialog.style.maxWidth = this.width;
    }

    if (this.height) {
      this.dialog.style.height = this.height;
    }

    // Mark an empty template elements
    if (this.popupTmplH && !this.popupTmplH.children.length) {
      this.popupTmplH.classList.add('empty');
    }

    if (this.popupTmplF && !this.popupTmplF.children.length) {
      this.popupTmplF.classList.add('empty');
    }

    return this;
  }

  /**
   * Internal. Render the body content, based on popupType.
   * @returns {JoomlaDialog}
   */
  renderBodyContent() {
    if (!this.popupTmplB || this.popupContentElement) return this;

    // Callback for loaded content event listener
    const onLoad = () => {
      this.classList.add('loaded');
      this.classList.remove('loading');
      this.popupContentElement.removeEventListener('load', onLoad);
      this.dispatchEvent(new CustomEvent('joomla-dialog:load'));

      if (this.popupType === 'inline' || this.popupType === 'ajax') {
        // Dispatch joomla:updated for inline content
        this.popupContentElement.dispatchEvent(new CustomEvent('joomla:updated', {
          bubbles: true,
          cancelable: true,
        }));
      }
    };

    this.classList.add('loading');

    switch (this.popupType) {
      // Create an Inline content
      case 'inline': {
        let inlineContent = this.popupContent;

        // Check for content selector: src: '#content-selector' or src: '.content-selector'
        if (!inlineContent && this.src && (this.src[0] === '.' || this.src[0] === '#')) {
          inlineContent = document.querySelector(this.src);
          this.popupContent = inlineContent;
        }

        if (inlineContent instanceof HTMLElement) {
          // Render content provided as HTMLElement
          if (inlineContent.nodeName === 'TEMPLATE') {
            this.popupTmplB.appendChild(inlineContent.content.cloneNode(true));
          } else {
            // Store parent reference to be able to recover after the popup is destroyed
            this.popupContentSrcLocation = {
              parent: inlineContent.parentElement,
              nextSibling: inlineContent.nextSibling,
            };
            this.popupTmplB.appendChild(inlineContent);
          }
        } else {
          // Render content string
          this.popupTmplB.insertAdjacentHTML('afterbegin', Joomla.sanitizeHtml(inlineContent));
        }
        this.popupContentElement = this.popupTmplB;
        onLoad();
        break;
      }

      // Create an IFrame content
      case 'iframe': {
        const frame = document.createElement('iframe');
        frame.addEventListener('load', onLoad);
        frame.src = this.src;
        // Enlarge default size of iframe, make sure it is usable without extra styling
        frame.width = '100%';
        frame.height = '720';
        if (!this.width) {
          frame.style.maxWidth = '100%';
          frame.width = '1024';
        }
        frame.classList.add('iframe-content');
        this.popupContentElement = frame;
        this.popupTmplB.appendChild(frame);
        break;
      }

      // Create an Image content
      case 'image': {
        const img = document.createElement('img');
        img.addEventListener('load', onLoad);
        img.src = this.src;
        this.popupContentElement = img;
        this.popupTmplB.appendChild(img);
        break;
      }

      // Create an AJAX content
      case 'ajax': {
        fetch(this.src)
          .then((response) => {
            if (response.status !== 200) {
              throw new Error(response.statusText);
            }
            return response.text();
          }).then((text) => {
            this.popupTmplB.insertAdjacentHTML('afterbegin', Joomla.sanitizeHtml(text));
            this.popupContentElement = this.popupTmplB;
            onLoad();
          }).catch((error) => {
            throw error;
          });
        break;
      }

      default: {
        throw new Error('Unknown popup type requested');
      }
    }

    return this;
  }

  /**
   * Internal. Find an Element to be used as parent element,
   * for cases when Dialog does not have one already. See show().
   *
   * @returns {HTMLElement|boolean}
   */
  findPreferredParent() {
    let parent;
    if (this.preferredParent instanceof HTMLElement) {
      // We have configured one already
      parent = this.preferredParent;
    } else if (this.preferredParent) {
      // Query Document
      parent = document.querySelector(this.preferredParent);
    } else if (this.popupType === 'inline') {
      // Pick the parent element of the Content
      let inlineContent = this.popupContent;
      // Check for content selector: src: '#content-selector' or src: '.content-selector'
      if (!inlineContent && this.src && (this.src[0] === '.' || this.src[0] === '#')) {
        inlineContent = document.querySelector(this.src);
        parent = inlineContent ? inlineContent.parentElement : false;
      }
    }

    return parent || false;
  }

  /**
   * Return the popup header element.
   * @returns {HTMLElement|boolean}
   */
  getHeader() {
    this.renderLayout();

    return this.popupTmplH || false;
  }

  /**
   * Return the popup body element.
   * @returns {HTMLElement}
   */
  getBody() {
    this.renderLayout();

    return this.popupTmplB;
  }

  /**
   * Return the popup content element, or body for inline and ajax types.
   * @returns {HTMLElement}
   */
  getBodyContent() {
    this.renderLayout();

    return this.popupContentElement || this.popupTmplB;
  }

  /**
   * Return the popup footer element.
   * @returns {HTMLElement|boolean}
   */
  getFooter() {
    this.renderLayout();

    return this.popupTmplF || false;
  }

  /**
   * Open the popup as modal window.
   * Will append the element to Document body if not appended before.
   *
   * @returns {JoomlaDialog}
   */
  show() {
    // Check whether the element already attached to DOM
    if (!this.parentElement) {
      // Check for preferred parent to attach to DOM
      const parent = this.findPreferredParent();
      (parent || document.body).appendChild(this);
    }

    this.dialog.showModal();
    this.dispatchEvent(new CustomEvent('joomla-dialog:open', { bubbles: true }));
    return this;
  }

  /**
   * Alias for show() method.
   * @returns {JoomlaDialog}
   */
  open() {
    return this.show();
  }

  /**
   * Closes the popup
   *
   * @returns {JoomlaDialog}
   */
  close() {
    if (!this.dialog) {
      throw new Error('Calling close for non opened dialog is discouraged.');
    }

    this.dialog.close();
    return this;
  }

  /**
   * Alias for close() method.
   * @returns {JoomlaDialog}
   */
  hide() {
    return this.close();
  }

  /**
   * Destroys the popup.
   */
  destroy() {
    if (!this.dialog) {
      return;
    }

    this.dialog.close();
    this.removeChild(this.dialog);
    this.parentElement.removeChild(this);

    // Restore original location of the popup content element
    if (this.popupContentSrcLocation && this.popupContent) {
      const { parent, nextSibling } = this.popupContentSrcLocation;
      parent.insertBefore(this.popupContent, nextSibling);
    }

    this.dialog = null;
    this.popupTmplH = null;
    this.popupTmplB = null;
    this.popupTmplF = null;
    this.popupContentElement = null;
    this.popupContentSrcLocation = null;
  }

  /**
   * Helper method to show an Alert.
   *
   * @param {String} message
   * @param {String} title
   * @returns {Promise}
   */
  static alert(message, title) {
    return new Promise((resolve) => {
      const popup = new this();
      popup.popupType = 'inline';
      popup.popupContent = message;
      popup.textHeader = title || Joomla.Text._('INFO', 'Info');
      popup.popupButtons = [{
        label: Joomla.Text._('JOK', 'Okay'),
        data: { buttonOk: '' },
        onClick: () => popup.close(),
      }];
      popup.classList.add('joomla-dialog-alert');
      popup.addEventListener('joomla-dialog:close', () => {
        popup.destroy();
        resolve();
      });
      popup.show();
    });
  }

  /**
   * Helper method to show a Confirmation popup.
   *
   * @param {String} message
   * @param {String} title
   * @returns {Promise}
   */
  static confirm(message, title) {
    return new Promise((resolve) => {
      let result = false;
      const popup = new this();
      popup.popupType = 'inline';
      popup.popupContent = message;
      popup.textHeader = title || Joomla.Text._('INFO', 'Info');
      popup.popupButtons = [
        {
          label: Joomla.Text._('JYES', 'Yes'),
          data: { buttonOk: '' },
          onClick: () => {
            result = true;
            popup.destroy();
          },
        },
        {
          label: Joomla.Text._('JNO', 'No'),
          data: { buttonCancel: '' },
          onClick: () => {
            result = false;
            popup.destroy();
          },
          className: 'button button-secondary btn btn-outline-secondary',
        },
      ];
      popup.cancelable = false;
      popup.classList.add('joomla-dialog-confirm');
      popup.addEventListener('joomla-dialog:close', () => resolve(result));
      popup.show();
    });
  }
}

customElements.define('joomla-dialog', JoomlaDialog);

export default JoomlaDialog;
