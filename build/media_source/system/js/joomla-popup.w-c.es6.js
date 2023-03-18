/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Default template for the popup
const popupTemplate = `<div class="joomla-popup-container">
  <header class="joomla-popup-header"></header>
  <section class="joomla-popup-body"></section>
  <footer class="joomla-popup-footer"></footer>
</div>`;

/**
 * JoomlaPopup class for Joomla Dialog implementation.
 * With use of <joomla-popup> custom element as dialog holder.
 */
class JoomlaPopup extends HTMLElement {
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
   * An optional list of buttons, to be rendered in footer, or bottom of the popup body.
   * Example:
   *   [{label: 'Yes', onClick: () => popup.close()},
   *   {label: 'No', onClick: () => popup.close(), className: 'btn btn-danger'}]
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
   * A template for the popup
   * @type {string|HTMLTemplateElement}
   */
  // popupTemplate = popupTemplate;

  /**
   * Class constructor
   * @param {Object} config
   */
  constructor(config) {
    super();

    // Define default params (doing it here because browser support of public props)
    this.popupType = 'inline';
    this.textHeader = '';
    this.iconHeader = '';
    this.textClose = 'Close';
    this.popupContent = '';
    this.src = '';
    this.popupButtons = [];
    this.cancelable = true;
    this.width = '';
    this.height = '';
    this.popupTemplate = popupTemplate;

    if (!config) return;

    // Check configurable properties
    ['popupType', 'textHeader', 'textClose', 'popupContent', 'src',
      'popupButtons', 'cancelable', 'width', 'height', 'popupTemplate', 'iconHeader', 'id'].forEach((key) => {
      if (config[key] !== undefined) {
        this[key] = config[key];
      }
    });

    if (config.className) {
      this.classList.add(...config.className.split(' '));
    }
  }

  connectedCallback() {
    this.renderLayout();
  }

  /**
   * Render a main layout, based on given template.
   * @returns {JoomlaPopup}
   */
  renderLayout() {
    if (this.dialog) return this;

    // On close callback
    const onClose = () => {
      this.dispatchEvent(new CustomEvent('joomla-popup:close'));
    };
    const onCancel = (event) => {
      if (!this.cancelable) {
        event.preventDefault();
      }
    };

    // Check for existing layout
    if (this.firstElementChild && this.firstElementChild.nodeName === 'DIALOG') {
      this.dialog = this.firstElementChild;
      this.dialog.addEventListener('cancel', onCancel);
      this.dialog.addEventListener('close', onClose);
      this.popupTmplB = this.querySelector('.joomla-popup-body') || this.dialog;
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
    this.popupTmplH = this.dialog.querySelector('.joomla-popup-header');
    this.popupTmplB = this.dialog.querySelector('.joomla-popup-body');
    this.popupTmplF = this.dialog.querySelector('.joomla-popup-footer');
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
      btn.ariaLabel = btnData.ariaLabel || '';

      if (btnData.className) {
        btn.classList.add(...btnData.className.split(' '));
      } else {
        btn.classList.add('button', 'button-primary', 'btn', 'btn-primary');
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
   * Render the body content, based on popupType.
   * @returns {JoomlaPopup}
   */
  renderBodyContent() {
    if (!this.popupTmplB || this.popupContentElement) return this;

    // Callback for loaded content event listener
    const onLoad = () => {
      this.classList.add('loaded');
      this.classList.remove('loading');
      this.popupContentElement.removeEventListener('load', onLoad);
      this.dispatchEvent(new CustomEvent('joomla-popup:load'));
    };

    this.classList.add('loading');

    switch (this.popupType) {
      // Create an Inline content
      case 'inline': {
        this.popupTmplB.insertAdjacentHTML('afterbegin', this.popupContent);
        this.popupContentElement = this.popupTmplB;
        onLoad();
        break;
      }

      // Create an IFrame content
      case 'iframe': {
        const frame = document.createElement('iframe');
        frame.addEventListener('load', onLoad);
        frame.src = this.src;
        frame.style.width = '100%';
        frame.style.height = '100%';
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
            this.popupTmplB.insertAdjacentHTML('afterbegin', text);
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

    return this.popupTmplB || false;
  }

  /**
   * Open the popup as modal window.
   * Will append the element to Document body if not appended before.
   *
   * @returns {JoomlaPopup}
   */
  show() {
    if (!this.parentElement) {
      document.body.appendChild(this);
    }

    this.dialog.showModal();
    this.dispatchEvent(new CustomEvent('joomla-popup:open'));
    return this;
  }

  /**
   * Alias for show() method.
   * @returns {JoomlaPopup}
   */
  open() {
    return this.show();
  }

  /**
   * Closes the popup
   *
   * @returns {JoomlaPopup}
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
   * @returns {JoomlaPopup}
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
    this.dialog = null;
    this.popupTmplH = null;
    this.popupTmplB = null;
    this.popupTmplF = null;
    this.popupContentElement = null;
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
        onClick: () => popup.destroy(),
      }];
      popup.cancelable = false;
      popup.classList.add('joomla-popup-alert');
      popup.addEventListener('joomla-popup:close', () => resolve());
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
          onClick: () => {
            result = true;
            popup.destroy();
          },
        },
        {
          label: Joomla.Text._('JNO', 'No'),
          onClick: () => {
            result = false;
            popup.destroy();
          },
          className: 'button btn btn-outline-secondary',
        },
      ];
      popup.cancelable = false;
      popup.classList.add('joomla-popup-confirm');
      popup.addEventListener('joomla-popup:close', () => resolve(result));
      popup.show();
    });
  }
}

window.JoomlaPopup = JoomlaPopup;
customElements.define('joomla-popup', JoomlaPopup);

/**
 * Auto create a popup dynamically on click, eg:
 *
 * <button type="button" data-joomla-popup='{"popupType": "iframe", "src": "content/url.html"}'>Click</button>
 * <button type="button" data-joomla-popup='{"popupType": "inline", "popupContent": "#id-of-content-element"}'>Click</button>
 * <a href="content/url.html" data-joomla-popup>Click</a>
 */
const delegateSelector = '[data-joomla-popup]';
const configDataAttr = 'joomlaPopup';
const configCacheFlag = 'joomlaPopupCache';

document.addEventListener('click', (event) => {
  const triggerEl = event.target.closest(delegateSelector);
  if (!triggerEl) return;
  event.preventDefault();

  // Check for cached instance
  const cacheable = !!triggerEl.dataset[configCacheFlag];
  if (cacheable && triggerEl.JoomlaPopupInstance) {
    Joomla.Modal.setCurrent(triggerEl.JoomlaPopupInstance);
    triggerEl.JoomlaPopupInstance.show();
    return;
  }
  // Parse config
  const config = triggerEl.dataset[configDataAttr] ? JSON.parse(triggerEl.dataset[configDataAttr]) : {};

  // Check click on anchor
  if (triggerEl.nodeName === 'A') {
    if (!config.popupType) {
      config.popupType = triggerEl.hash ? 'inline' : 'iframe';
    }
    if (!config.src && config.popupType === 'iframe') {
      config.src = triggerEl.href;
    } else if (!config.popupContent && config.popupType === 'inline') {
      config.popupContent = triggerEl.hash;
    }
  }

  // Check for content selector
  if (config.popupContent && (config.popupContent[0] === '.' || config.popupContent[0] === '#')) {
    const content = document.querySelector(config.popupContent);
    config.popupContent = content ? content.innerHTML.trim() : config.popupContent;
  }

  if (config.popupContent) {
    config.popupContent = Joomla.sanitizeHtml(config.popupContent);
  }

  // Check for template selector
  if (config.popupTemplate && (config.popupTemplate[0] === '.' || config.popupTemplate[0] === '#')) {
    const template = document.querySelector(config.popupTemplate);
    if (template && template.nodeName === 'TEMPLATE') {
      config.popupTemplate = template;
    }
  } else if (config.popupTemplate) {
    config.popupTemplate = Joomla.sanitizeHtml(config.popupTemplate);
  }

  const popup = new JoomlaPopup(config);
  if (cacheable) {
    triggerEl.JoomlaPopupInstance = popup;
  }

  popup.addEventListener('joomla-popup:close', () => {
    Joomla.Modal.setCurrent(null);
    if (!cacheable) {
      popup.destroy();
    }
  });

  Joomla.Modal.setCurrent(popup);
  popup.show();
});

export default JoomlaPopup;
