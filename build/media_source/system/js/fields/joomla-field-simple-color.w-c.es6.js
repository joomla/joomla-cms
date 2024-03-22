/**
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('Joomla API is not properly initiated');
}

const checker = 'url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3ggRDQENU0dyawAAACZJREFUGNNjPHXqDAMSMDY2ROYyMeAFNJVm/Pv3LzL/7Nnzg8VpAKebCGpIIxHBAAAAAElFTkSuQmCC")';
const colorNames = {
  aliceblue: '#f0f8ff',
  antiquewhite: '#faebd7',
  aqua: '#00ffff',
  aquamarine: '#7fffd4',
  azure: '#f0ffff',
  beige: '#f5f5dc',
  bisque: '#ffe4c4',
  black: '#000000',
  blanchedalmond: '#ffebcd',
  blue: '#0000ff',
  blueviolet: '#8a2be2',
  brown: '#a52a2a',
  burlywood: '#deb887',
  cadetblue: '#5f9ea0',
  chartreuse: '#7fff00',
  chocolate: '#d2691e',
  coral: '#ff7f50',
  cornflowerblue: '#6495ed',
  cornsilk: '#fff8dc',
  crimson: '#dc143c',
  cyan: '#00ffff',
  darkblue: '#00008b',
  darkcyan: '#008b8b',
  darkgoldenrod: '#b8860b',
  darkgray: '#a9a9a9',
  darkgreen: '#006400',
  darkgrey: '#a9a9a9',
  darkkhaki: '#bdb76b',
  darkmagenta: '#8b008b',
  darkolivegreen: '#556b2f',
  darkorange: '#ff8c00',
  darkorchid: '#9932cc',
  darkred: '#8b0000',
  darksalmon: '#e9967a',
  darkseagreen: '#8fbc8f',
  darkslateblue: '#483d8b',
  darkslategray: '#2f4f4f',
  darkslategrey: '#2f4f4f',
  darkturquoise: '#00ced1',
  darkviolet: '#9400d3',
  deeppink: '#ff1493',
  deepskyblue: '#00bfff',
  dimgray: '#696969',
  dimgrey: '#696969',
  dodgerblue: '#1e90ff',
  firebrick: '#b22222',
  floralwhite: '#fffaf0',
  forestgreen: '#228b22',
  fuchsia: '#ff00ff',
  gainsboro: '#dcdcdc',
  ghostwhite: '#f8f8ff',
  gold: '#ffd700',
  goldenrod: '#daa520',
  gray: '#808080',
  green: '#008000',
  greenyellow: '#adff2f',
  grey: '#808080',
  honeydew: '#f0fff0',
  hotpink: '#ff69b4',
  indianred: '#cd5c5c',
  indigo: '#4b0082',
  ivory: '#fffff0',
  khaki: '#f0e68c',
  lavender: '#e6e6fa',
  lavenderblush: '#fff0f5',
  lawngreen: '#7cfc00',
  lemonchiffon: '#fffacd',
  lightblue: '#add8e6',
  lightcoral: '#f08080',
  lightcyan: '#e0ffff',
  lightgoldenrodyellow: '#fafad2',
  lightgray: '#d3d3d3',
  lightgreen: '#90ee90',
  lightgrey: '#d3d3d3',
  lightpink: '#ffb6c1',
  lightsalmon: '#ffa07a',
  lightseagreen: '#20b2aa',
  lightskyblue: '#87cefa',
  lightslategray: '#778899',
  lightslategrey: '#778899',
  lightsteelblue: '#b0c4de',
  lightyellow: '#ffffe0',
  lime: '#00ff00',
  limegreen: '#32cd32',
  linen: '#faf0e6',
  magenta: '#ff00ff',
  maroon: '#800000',
  mediumaquamarine: '#66cdaa',
  mediumblue: '#0000cd',
  mediumorchid: '#ba55d3',
  mediumpurple: '#9370db',
  mediumseagreen: '#3cb371',
  mediumslateblue: '#7b68ee',
  mediumspringgreen: '#00fa9a',
  mediumturquoise: '#48d1cc',
  mediumvioletred: '#c71585',
  midnightblue: '#191970',
  mintcream: '#f5fffa',
  mistyrose: '#ffe4e1',
  moccasin: '#ffe4b5',
  navajowhite: '#ffdead',
  navy: '#000080',
  oldlace: '#fdf5e6',
  olive: '#808000',
  olivedrab: '#6b8e23',
  orange: '#ffa500',
  orangered: '#ff4500',
  orchid: '#da70d6',
  palegoldenrod: '#eee8aa',
  palegreen: '#98fb98',
  paleturquoise: '#afeeee',
  palevioletred: '#db7093',
  papayawhip: '#ffefd5',
  peachpuff: '#ffdab9',
  peru: '#cd853f',
  pink: '#ffc0cb',
  plum: '#dda0dd',
  powderblue: '#b0e0e6',
  purple: '#800080',
  red: '#ff0000',
  rosybrown: '#bc8f8f',
  royalblue: '#4169e1',
  saddlebrown: '#8b4513',
  salmon: '#fa8072',
  sandybrown: '#f4a460',
  seagreen: '#2e8b57',
  seashell: '#fff5ee',
  sienna: '#a0522d',
  silver: '#c0c0c0',
  skyblue: '#87ceeb',
  slateblue: '#6a5acd',
  slategray: '#708090',
  slategrey: '#708090',
  snow: '#fffafa',
  springgreen: '#00ff7f',
  steelblue: '#4682b4',
  tan: '#d2b48c',
  teal: '#008080',
  thistle: '#d8bfd8',
  tomato: '#ff6347',
  turquoise: '#40e0d0',
  violet: '#ee82ee',
  wheat: '#f5deb3',
  white: '#ffffff',
  whitesmoke: '#f5f5f5',
  yellow: '#ffff00',
  yellowgreen: '#9acd32',
};
const template = document.createElement('template');
template.innerHTML = `
<style>
[part=close] svg {
  padding-block-start: .2rem;
}
</style>
<button type="button" part="opener" aria-expanded="false"></button>
<div part="panel">
  <slot name="colors"></slot>
  <button type="button" aria-label="Close" part="close">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16" fill="currentColor"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
  </button>
</div>`;

// Expand any short code
function getColorName(value) {
  let newValue = value;
  if (newValue === 'none') return Joomla.Text._('JNONE');
  if (value.length === 4) {
    const tmpValue = value.split('');
    newValue = tmpValue[0] + tmpValue[1] + tmpValue[1] + tmpValue[2] + tmpValue[2] + tmpValue[3] + tmpValue[3];
  }

  // eslint-disable-next-line no-restricted-syntax
  for (const color in colorNames) {
    if (Object.prototype.hasOwnProperty.call(colorNames, color) && newValue.toLowerCase() === colorNames[color]) {
      return color;
    }
  }

  return value;
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
      element.setAttribute('label', getColorName(element.value));
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
