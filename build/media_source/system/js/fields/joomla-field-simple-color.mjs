/**
 * Based on:
 * Very simple jQuery Color Picker
 * Copyright (C) 2012 Tanguy Krotoff
 * Licensed under the MIT license
 *
 * ADAPTED BY: Dimitris Grammatikogiannis
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
((customElements) => {
  const KEYCODE = {
    TAB: 9,
    ESC: 27,
  };

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

  class JoomlaFieldSimpleColor extends HTMLElement {
    constructor() {
      super();

      // Define some variables
      this.select = '';
      this.options = [];
      this.icon = '';
      this.panel = '';
      this.buttons = [];
      this.focusableElements = null;
      this.focusableSelectors = [
        'a[href]',
        'area[href]',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        'button:not([disabled])',
        'iframe',
        'object',
        'embed',
        '[contenteditable]',
        '[tabindex]:not([tabindex^="-"])'];
    }

    connectedCallback() {
      this.select = this.querySelector('select');

      if (!this.select) {
        throw new Error('Simple color field requires a select element');
      }

      this.options = [].slice.call(this.select.querySelectorAll('option'));

      this.select.classList.add('hidden');

      // Build the pop up
      this.options.forEach((option) => {
        let color = option.value;
        let clss = 'swatch';

        if (color === 'none') {
          clss += ' nocolor';
          color = 'transparent';
        }
        if (option.selected) {
          clss += ' active';
        }

        const el = document.createElement('button');
        el.setAttribute('class', clss);
        el.style.backgroundColor = color;
        el.setAttribute('type', 'button');
        const a11yColor = color === 'transparent' ? this.textTransp : this.getColorName(color);
        el.innerHTML = Joomla.sanitizeHtml(`<span class="visually-hidden">${a11yColor}</span>`);

        this.buttons.push(el);
      });

      // Add a close button
      const close = document.createElement('button');
      close.setAttribute('class', 'btn-close');
      close.setAttribute('type', 'button');
      close.setAttribute('aria-label', this.textClose);

      this.buttons.push(close);

      let color = this.select.value;
      let clss = '';

      if (color === 'none') {
        clss += ' nocolor';
        color = 'transparent';
      }

      this.icon = document.createElement('button');

      if (clss) {
        this.icon.setAttribute('class', clss);
      }

      const uniqueId = `simple-color-${Math.random().toString(36).substr(2, 10)}`;
      this.icon.setAttribute('type', 'button');
      this.icon.setAttribute('tabindex', '0');
      this.icon.style.backgroundColor = color;
      this.icon.innerHTML = Joomla.sanitizeHtml(`<span class="visually-hidden">${this.textSelect}</span>`);
      this.icon.id = uniqueId;
      this.select.insertAdjacentElement('beforebegin', this.icon);
      this.icon.addEventListener('click', this.show.bind(this));

      this.panel = document.createElement('div');
      this.panel.classList.add('simplecolors-panel');
      this.panel.setAttribute('aria-labelledby', uniqueId);
      this.hide = this.hide.bind(this);
      this.colorSelect = this.colorSelect.bind(this);

      this.buttons.forEach((el) => {
        if (el.classList.contains('btn-close')) {
          el.addEventListener('click', this.hide);
        } else {
          el.addEventListener('click', this.colorSelect);
        }

        this.panel.insertAdjacentElement('beforeend', el);
      });

      this.appendChild(this.panel);

      this.focusableElements = [].slice
        .call(this.panel.querySelectorAll(this.focusableSelectors.join()));

      this.keys = this.keys.bind(this);
      this.hide = this.hide.bind(this);
      this.mousedown = this.mousedown.bind(this);
    }

    static get observedAttributes() {
      return ['text-select', 'text-color', 'text-close', 'text-transparent'];
    }

    get textSelect() { return this.getAttribute('text-select'); }

    get textColor() { return this.getAttribute('text-color'); }

    get textClose() { return this.getAttribute('text-close'); }

    get textTransp() { return this.getAttribute('text-transparent'); }

    // Show the panel
    show() {
      document.addEventListener('mousedown', this.hide);
      this.addEventListener('keydown', this.keys);
      this.panel.addEventListener('mousedown', this.mousedown);
      this.panel.setAttribute('data-open', '');

      const focused = this.panel.querySelector('button');

      if (focused) {
        focused.focus();
      }
    }

    // Hide panel
    hide() {
      document.removeEventListener('mousedown', this.hide, false);
      this.removeEventListener('keydown', this.keys);

      if (this.panel.hasAttribute('data-open')) {
        this.panel.removeAttribute('data-open');
      }

      this.icon.focus();
    }

    colorSelect(e) {
      let color = '';
      let bgcolor = '';
      let clss = '';

      if (e.target.classList.contains('nocolor')) {
        color = 'none';
        bgcolor = 'transparent';
        clss = 'nocolor';
      } else {
        color = this.rgb2hex(e.target.style.backgroundColor);
        bgcolor = color;
      }

      // Reset the active class
      this.buttons.forEach((el) => {
        if (el.classList.contains('active')) {
          el.classList.remove('active');
        }
      });

      // Add the active class to the selected button
      e.target.classList.add('active');

      this.icon.classList.remove('nocolor');
      this.icon.setAttribute('class', clss);
      this.icon.style.backgroundColor = bgcolor;

      // trigger change event both on the select and on the custom element
      this.select.dispatchEvent(new Event('change'));
      this.dispatchEvent(new CustomEvent('change', {
        detail: { value: color },
        bubbles: true,
      }));

      // Hide the panel
      this.hide();

      // Change select value
      this.options.forEach((el) => {
        if (el.selected) {
          el.removeAttribute('selected');
        }

        if (el.value === bgcolor) {
          el.setAttribute('selected', '');
        }
      });
    }

    keys(e) {
      if (e.keyCode === KEYCODE.ESC) {
        this.hide();
      }

      if (e.keyCode === KEYCODE.TAB) {
        // Get the index of the current active element
        const focusedIndex = this.focusableElements.indexOf(document.activeElement);

        // If first element is focused and shiftkey is in use, focus last item within modal
        if (e.shiftKey && (focusedIndex === 0 || focusedIndex === -1)) {
          this.focusableElements[this.focusableElements.length - 1].focus();
          e.preventDefault();
        }
        // If last element is focused and shiftkey is not in use, focus first item within modal
        if (!e.shiftKey && focusedIndex === this.focusableElements.length - 1) {
          this.focusableElements[0].focus();
          e.preventDefault();
        }
      }
    }

    // Prevents the mousedown event from "eating" the click event.
    // eslint-disable-next-line class-methods-use-this
    mousedown(e) {
      e.stopPropagation();
      e.preventDefault();
    }

    getColorName(value) {
      // Expand any short code
      let newValue = value;
      if (value.length === 4) {
        const tmpValue = value.split('');
        newValue = tmpValue[0] + tmpValue[1] + tmpValue[1] + tmpValue[2]
          + tmpValue[2] + tmpValue[3] + tmpValue[3];
      }

      // eslint-disable-next-line no-restricted-syntax
      for (const color in colorNames) {
        // eslint-disable-next-line no-prototype-builtins
        if (colorNames.hasOwnProperty(color) && newValue.toLowerCase() === colorNames[color]) {
          return color;
        }
      }

      return `${this.textColor} ${value.replace('#', '').split('').join(', ')}`;
    }

    /**
     * Converts a RGB color to its hexadecimal value.
     * See http://stackoverflow.com/questions/1740700/get-hex-value-rather-than-rgb-value-using-$
     */
    // eslint-disable-next-line class-methods-use-this
    rgb2hex(rgb) {
      const hex = (x) => (`0${parseInt(x, 10).toString(16)}`).slice(-2);
      const matches = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

      return `#${hex(matches[1])}${hex(matches[2])}${hex(matches[3])}`;
    }
  }

  customElements.define('joomla-field-simple-color', JoomlaFieldSimpleColor);
})(customElements);
