/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

import JoomlaDialog from 'joomla.dialog';

/**
 * Generic web component for a modal-select field.
 *
 * Wraps field elements like joomla-field-fancy-select, select or input and a browse button.
 * On button click: opens a JoomlaDialog iframe, listens for the joomla:content-select postMessage and
 * sets the inner field's value.
 *
 * Attributes (all optional):
 *   modal-url    – iframe src for the picker modal
 *   modal-title  – dialog header text
 *
 * Dispatched events:
 *   joomla-field-modal-select:open  – before dialog opens; cancelable
 *   change                          – on the component after selection; detail: { value, title }
 */
class JoomlaFieldModalSelect extends HTMLElement {
  constructor() {
    super();
    this.onSelectClick = this.onSelectClick.bind(this);
  }

  connectedCallback() {
    this.buttonSelect = this.querySelector('[data-button-action="select"]');
    // Support fancy-select, plain <select>, or plain <input>
    this.fieldEl = this.querySelector('joomla-field-fancy-select')
      || this.querySelector('select')
      || this.querySelector('input');

    if (this.buttonSelect) {
      this.buttonSelect.addEventListener('click', this.onSelectClick);
    }
  }

  disconnectedCallback() {
    if (this.buttonSelect) {
      this.buttonSelect.removeEventListener('click', this.onSelectClick);
    }
    if (this.dialog) {
      this.dialog.close();
      this.dialog = null;
    }
  }

  get modalUrl() { return this.getAttribute('modal-url') || ''; }
  get modalTitle() { return this.getAttribute('modal-title') || ''; }

  onSelectClick() {
    if (this.dialog) return;
    if (!this.modalUrl) return;

    // Allow consumers to cancel before the dialog opens
    const openEvent = new CustomEvent('joomla-field-modal-select:open', {
      bubbles: true,
      cancelable: true,
    });
    if (!this.dispatchEvent(openEvent)) return;

    const dialog = new JoomlaDialog({
      popupType: 'iframe',
      src: this.modalUrl,
      textHeader: this.modalTitle,
    });
    dialog.show();

    const msgListener = (event) => {
      if (event.origin !== window.location.origin) return;
      const { messageType, id, title } = event.data;

      if (messageType === 'joomla:content-select') {
        const value = String(id || '');
        if (this.fieldEl) {
          this.fieldEl.value = value;
          // fancy-select (Choices.js) does not fire native change on programmatic set
          // for plain elements, dispatchEvent on the element itself is correct too.
          const eventTarget = this.fieldEl.tagName === 'JOOMLA-FIELD-FANCY-SELECT'
            ? (this.fieldEl.querySelector('select') ?? this.fieldEl)
            : this.fieldEl;
          eventTarget.dispatchEvent(new CustomEvent('change', { bubbles: true, cancelable: true }));
        }
        // Also fire change on the component itself
        this.dispatchEvent(new CustomEvent('change', {
          detail: { value, title: title || '' },
          bubbles: true,
        }));
        dialog.close();
      } else if (messageType === 'joomla:cancel') {
        dialog.close();
      }
    };

    window.addEventListener('message', msgListener);
    dialog.addEventListener('joomla-dialog:close', () => {
      window.removeEventListener('message', msgListener);
      dialog.destroy();
      this.dialog = null;
      if (this.buttonSelect) this.buttonSelect.focus();
    });

    this.dialog = dialog;
  }
}

customElements.define('joomla-field-modal-select', JoomlaFieldModalSelect);
