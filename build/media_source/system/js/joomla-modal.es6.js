/* eslint max-classes-per-file: ["error", 2] */
export class JoomlaModalButton extends HTMLElement {
  constructor() {
    super();

    this.onOpen = this.onOpen.bind(this);
    this.createIframeDialog = this.createIframeDialog.bind(this);
  }

  static get observedAttributes() {
    return [
      'type',
      'id',
      'title',
      'url',
      'close-text',
      'close-outside',
    ];
  }

  get type() { return this.getAttribute('type') || 'dialog'; }

  set type(value) { this.setAttribute('type', value); }

  get id() { return this.getAttribute('id') || 'random_id'; }

  set id(value) { this.setAttribute('id', value); }

  get title() { return this.getAttribute('title'); }

  set title(value) { this.setAttribute('title', value); }

  get url() { return this.getAttribute('url'); }

  set url(value) { this.setAttribute('url', value); }

  get closeText() { return this.getAttribute('close-text'); }

  set closeText(value) { this.setAttribute('close-text', value); }

  get clickOutside() { return this.getAttribute('click-outside'); }

  set clickOutside(value) { this.setAttribute('click-outside', value); }

  connectedCallback() {
    // Do we have any HTML nodes
    if (this.children.length) {
      // Check if a modal wrapper exists
      // if (this.children[1] && this.children[1].nodeName && this.children[1].nodeName === 'JOOMLA-MODAL') {}
      // Check if the opener button/link exist
      if (this.firstElementChild && this.firstElementChild.nodeName && ['A', 'BUTTON'].includes(this.firstElementChild.nodeName)) {
        this.opener = this.firstElementChild;
        this.opener.addEventListener('click', this.onOpen);
      }
    }

    // Auto create the modal
    if (this.url) {
      // this.createIframeDialog();
    }
  }

  onOpen() {
    if (this.url) {
      if (this.children[1] && this.children[1].nodeName && this.children[1].nodeName === 'JOOMLA-MODAL') {
        this.modalContainer.open();
        if (Joomla && Joomla.Modal) Joomla.Modal.setCurrent(this.modalContainer);
      } else {
        this.createIframeDialog();
        this.modalContainer.open();
        if (Joomla && Joomla.Modal) Joomla.Modal.setCurrent(this.modalContainer);
      }
    }
  }

  createIframeDialog() {
    this.modalContainer = document.createElement('joomla-modal');
    this.modalContainer.setAttribute('id', `${this.id}-modal`);
    this.modalContainer.setAttribute('title', this.title);
    this.modalContainer.setAttribute('url', this.url);
    this.modalContainer.setAttribute('close-text', this.closeText);
    if (this.clickOutside) this.modalContainer.setAttribute('click-outside', this.clickOutside);

    this.append(this.modalContainer);
  }
}

const templateHeader = `<header>
  <h3></h3>
  <button></button>
</header>`;
const templateArticleIframe = '<article><iframe /></article>';
const templateArticle = '<article></article>';
const templateFooter = '<footer></footer>';

export class JoomlaModal extends HTMLElement {
  constructor() {
    super();

    this.open = this.open.bind(this);
    this.close = this.close.bind(this);
    this.clickOutsideFn = this.clickOutsideFn.bind(this);
    this.onDialogClose = this.onDialogClose.bind(this);

    // this.observerConfig = { attributes: true, childList: true, subtree: true };
    // this.observer = new MutationObserver(this.onObserverChange);
  }

  static get observedAttributes() {
    return [
      'type',
      'id',
      'title',
      'url',
      'close-text',
    ];
  }

  get type() { return this.getAttribute('type') || 'dialog'; }

  set type(value) { this.setAttribute('type', value); }

  get id() { return this.getAttribute('id') || 'random_id'; }

  set id(value) { this.setAttribute('id', value); }

  get title() { return this.getAttribute('title'); }

  set title(value) { this.setAttribute('title', value); }

  get url() { return this.getAttribute('url'); }

  set url(value) { this.setAttribute('url', value); }

  get closeText() { return this.getAttribute('close-text') || 'Close'; }

  set closeText(value) { this.setAttribute('close-text', value); }

  get clickOutside() { return this.getAttribute('click-outside'); }

  set clickOutside(value) { this.setAttribute('click-outside', value); }

  connectedCallback() {
    this.dialog = this.querySelector('dialog');
  }

  open() {
    if (this.firstElementChild && this.firstElementChild.nodeName === 'DIALOG') {
      this.dialog.showModal();
      this.dialog.addEventListener('close', this.onDialogClose);

      if (this.clickOutside) {
        this.dialog.addEventListener('click', this.clickOutsideFn);
      }
    } else {
      this.createIframeDialog();
      this.dialog.showModal();
      this.dialog.addEventListener('close', this.onDialogClose);

      if (this.clickOutside) {
        this.dialog.addEventListener('click', this.clickOutsideFn);
      }
    }
  }

  close() {
    if (this.dialog) {
      this.dialog.close();
    }
  }

  onDialogClose() {
    this.parentNode.removeChild(this);
  }

  clickOutsideFn(event) {
    if (event.target === this.dialog) {
      this.dialog.removeEventListener('click', this.clickOutsideFn);
      this.dialog.close();
    }
  }

  createIframeDialog() {
    const doc = new DOMParser().parseFromString(`${templateHeader}${this.url ? templateArticleIframe : templateArticle}${templateFooter}`, 'text/html');
    this.dialog = document.createElement('dialog');
    this.headerTitleElement = doc.documentElement.querySelector('header > h3');
    this.closeButton = doc.documentElement.querySelector('header > button');
    this.closeButton.setAttribute('aria-label', this.closeText);
    this.closeButton.setAttribute('type', 'button');
    this.closeButton.addEventListener('click', this.close);
    this.headerTitleElement.textContent = this.title;
    this.iframe = doc.documentElement.querySelector('article > iframe');
    this.iframe.src = this.url;

    [...doc.documentElement.children[1].children].forEach((eee) => this.dialog.append(eee));

    this.append(this.dialog);
  }
}

function registerElements() {
  customElements.define('joomla-modal-button', JoomlaModalButton);
  customElements.define('joomla-modal', JoomlaModal);
}

if (window.HTMLDialogElement === undefined) {
  // polyfill required
  import('./polyfill.js').then(registerElements);
} else {
  registerElements();
}
