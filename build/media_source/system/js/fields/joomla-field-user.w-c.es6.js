/**
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';

function getText(translateableText, fallbackText) {
  const translatedText = typeof Joomla?.Text?._ === 'function' ? Joomla.Text._(translateableText) : '';

  return translatedText !== translateableText ? translatedText : fallbackText;
}

const texts = {
  selectUser: ['JLIB_FORM_CHANGE_USER', 'Select User'],
}

const template = Object.assign(document.createElement('template'), {
  innerHTML: `
      <style>svg { width: 1em; height: 1rem; vertical-align: text-bottom; }</style>
      <input part="name" readonly>
      <button type="button" part="opener" aria-label="${getText(texts.selectUser[0], texts.selectUser[1])}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <path fill="currentColor" d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/>
        </svg>
      </button>`,
});

class JoomlaFieldUser extends HTMLElement {
  static formAssociated = true;

  constructor() {
    super();

    this.attachShadow({ mode: 'open' });
    this.shadowRoot.appendChild(template.content.cloneNode(true));

    // Bind context
    this.modalClose = this.modalClose.bind(this);
    this.setValue = this.setValue.bind(this);
    this.modalOpen = this.modalOpen.bind(this);

    this.input = this.shadowRoot.querySelector('[part=name]');
    this.button = this.shadowRoot.querySelector('[part=opener]');
  }

  static get observedAttributes() {
    return ['url', 'name'];
  }

  get value() { return this.getAttribute('value'); }

  set value(value) { this.setAttribute('value', value); }

  get username() { return this.getAttribute('username'); }

  set username(value) { this.setAttribute('username', value); }

  get url() { return this.getAttribute('url'); }

  set url(value) { this.setAttribute('url', value); }

  connectedCallback() {
    try {
      this.internals = this.attachInternals();
      this.form = this.internals.form;
    } catch (error) {
      throw new Error('Unsupported browser');
    }

    if (this.internals && this.internals.labels.length && !(this.hasAttribute('readonly') || this.hasAttribute('disabled'))) {
      this.internals.labels.forEach((label) => label.addEventListener('click', this.modalOpen));
    }

    if (this.internals) {
      this.querySelector('input[type=hidden]')?.remove();
    }

    if (!(this.hasAttribute('readonly') || this.hasAttribute('disabled'))) {
      this.button.addEventListener('click', this.modalOpen);
      this.input.addEventListener('click', this.modalOpen);
    }
    if (this.hasAttribute('readonly')) {
      this.button.remove();
    }

    this.form = this.internals.form;
    this.internals.setFormValue(this.value);
    this.input.value = this.value ? this.username : '';
    this.input.placeholder = this.value ? '' : getText(texts.selectUser[0], texts.selectUser[1]);
  }

  disconnectedCallback() {
    if (this.internals && this.internals.labels.length) {
      this.internals.labels.forEach((label) => label.removeEventListener('click', this.modalOpen));
    }
  }

  // Opens the modal
  modalOpen() {
    // Create and show the dialog
    const dialog = new JoomlaDialog({
      popupType: 'iframe',
      src: this.url,
      textHeader: getText(texts.selectUser[0], texts.selectUser[1]),
    });
    dialog.classList.add('joomla-dialog-user-field');
    dialog.show();

    // Wait for message
    const msgListener = (event) => {
      // Avoid cross origins
      if (event.origin !== window.location.origin) return;
      // Check message type
      if (event.data.messageType === 'joomla:content-select') {
        this.setValue(event.data.id, event.data.name);
        dialog.close();
      } else if (event.data.messageType === 'joomla:cancel') {
        dialog.close();
      }
    };
    window.addEventListener('message', msgListener);

    dialog.addEventListener('joomla-dialog:close', () => {
      window.removeEventListener('message', msgListener);
      dialog.destroy();
      this.dialog = null;
    });

    this.dialog = dialog;
  }

  // Closes the modal
  modalClose() {
    if (this.dialog) {
      this.dialog.close();
    }
  }

  // Sets the value
  setValue(value, name) {
    this.internals.setFormValue(parseInt(value, 10));
    this.value = value;
    this.username = name;
    this.input.value = this.value ? this.username : '';
    this.input.placeholder = this.value ? '' : getText(texts.selectUser[0], texts.selectUser[1]);

    if (this.hasAttribute('required')){
      // if (this.value === '') {
      //   this.internals.setValidity({ valueMissing: true }, 'User needs to be selected');
      // } else {
      //   this.internals.setValidity({});
      // }
    }

    const event = new Event('change', { bubbles: true });
    event.name = name;
    event.value = value;

    this.dispatchEvent(event);
  }
}

customElements.define('joomla-field-user', JoomlaFieldUser);
