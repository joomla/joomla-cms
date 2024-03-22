/**
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('Joomla API is not properly initiated');
}

const checker = 'url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3ggRDQENU0dyawAAACZJREFUGNNjPHXqDAMSMDY2ROYyMeAFNJVm/Pv3LzL/7Nnzg8VpAKebCGpIIxHBAAAAAElFTkSuQmCC")';
const template = Object.assign(document.createElement('template'), {
  innerHTML: `
    <style>[part=close] svg { padding-block-start: .2rem; }</style>
    <button type="button" part="opener" aria-expanded="false"></button>
    <div part="panel">
      <slot name="colors"></slot>
      <button type="button" aria-label="Close" part="close">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16" fill="currentColor"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
      </button>
    </div>`,
});

// Expand any short code
function getColorName(value) {
  let newValue = value;
  if (newValue === 'none') return Joomla.Text._('JNONE');
  if (value.startsWith('#') && value.length === 4) {
    const tmpValue = value.split('');
    newValue = tmpValue[0] + tmpValue[1] + tmpValue[1] + tmpValue[2] + tmpValue[2] + tmpValue[3] + tmpValue[3];
  }

  return newValue;
}

class JoomlaFieldSimpleColor extends HTMLElement {
  static formAssociated = true;

  get value() { return this.getAttribute('value'); }

  set value(value) { this.setAttribute('value', value); }

  constructor() {
    super();

    this.attachShadow({ mode: 'open' });
    this.shadowRoot.appendChild(template.content.cloneNode(true));

    this.internals = null;
    this.show = this.show.bind(this);
    this.hide = this.hide.bind(this);
    this.keys = this.keys.bind(this);
    this.colorSelect = this.colorSelect.bind(this);
    this.getActiveElement = this.getActiveElement.bind(this);
    this.onDocumentClick = this.onDocumentClick.bind(this);

    // Create a dummy div for the validation of the colors
    this.div = document.createElement('div');
  }

  connectedCallback() {
    try {
      this.internals = this.attachInternals();
      this.form = this.internals.form;
    } catch (error) {
      throw new Error('Unsupported browser');
    }

    if (this.internals) {
      this.querySelector('input[type=hidden]')?.remove();
    }

    if (this.internals && this.internals.labels.length) {
      this.internals.labels.forEach((label) => label.addEventListener('click', this.show));
    }

    this.button = this.shadowRoot.querySelector('[part=opener]');
    this.panel = this.shadowRoot.querySelector('[part=panel]');
    this.closeButton = this.panel.querySelector('[part=close]');
    this.panel.style.display = 'none';
    this.button.style.background = this.value === 'none' ? checker : this.value;

    this.button.addEventListener('click', this.show);
    this.internals.setFormValue(this.value);
  }

  // Show the panel
  show() {
    let focused;
    this.slotted = this.shadowRoot.querySelector('slot[name=colors]');
    this.addEventListener('keydown', this.keys);
    this.closeButton.addEventListener('click', this.hide);
    this.closeButton.setAttribute('aria-label', Joomla.Text._('JCLOSE'));
    this.slotted.assignedElements().forEach((element) => {
      if (!this.validateColor(element.value)) {
        element.remove();
      }

      element.style.background = element.value === 'none' ? checker : element.value;
      element.setAttribute('aria-label', getColorName(element.value));
      element.addEventListener('click', this.colorSelect);
      if (element.getAttribute('aria-pressed') === 'true') {
        focused = element;
      }
    });
    this.button.style.display = 'none';
    this.panel.style.display = 'flex';
    this.button.setAttribute('aria-expanded', 'true');

    if (focused) {
      focused.focus();
    } else {
      this.closeButton.focus();
    }
    document.addEventListener('click', this.onDocumentClick);
  }

  // Hide the panel
  hide() {
    this.removeEventListener('keydown', this.keys);
    document.removeEventListener('click', this.onDocumentClick);
    this.button.setAttribute('aria-expanded', 'false');
    this.panel.style.display = 'none';
    this.button.style.display = 'block';

    this.slotted.assignedElements().forEach((element) => element.removeEventListener('click', this.colorSelect));
    this.button.focus();
  }

  onDocumentClick(e) {
    if ([...this.internals.labels].includes(e.target)) return;
    if ((e.target.closest('joomla-field-simple-color') !== this) && this.panel.style.display === 'flex') {
      this.hide();
    }
  }

  colorSelect(event) {
    const { currentTarget } = event;
    this.slotted.assignedElements().forEach((element) => element.setAttribute('aria-pressed', element !== currentTarget ? 'false' : 'true'));
    this.button.style.background = currentTarget.value === 'none' ? checker : currentTarget.value;
    this.hide();
    this.internals.setFormValue(currentTarget.value);
    this.value = currentTarget.value;
    this.dispatchEvent(new Event('change'));
  }

  keys(e) {
    if (e.code === 'Escape') {
      this.hide();
    }

    // Trap the focus
    if (e.code === 'Tab') {
      const focusableElements = [...this.slotted.assignedElements(), this.closeButton];
      const focusedIndex = focusableElements.indexOf(this.getActiveElement());

      if (e.shiftKey && (focusedIndex === 0)) {
        focusableElements[focusableElements.length - 1].focus();
        e.preventDefault();
      } else if (!e.shiftKey && focusedIndex === focusableElements.length - 1) {
        focusableElements[0].focus();
        e.preventDefault();
      }
    }
  }

  getActiveElement(root = document) {
    const activeEl = root.activeElement;

    if (!activeEl) {
      return null;
    }

    return activeEl.shadowRoot ? this.getActiveElement(activeEl.shadowRoot) : activeEl;
  }

  validateColor(color) {
    this.div.style.color = color;
    return this.div.style.color !== '';
  }
}

customElements.define('joomla-field-simple-color', JoomlaFieldSimpleColor);
