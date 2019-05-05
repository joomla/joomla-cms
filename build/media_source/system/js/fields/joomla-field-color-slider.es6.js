/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document) => {
  'use strict';

  /**
   * Regex for hex values e.g. #FF3929
   * @type {RegExp}
   */
  const hexRegex = new RegExp(/^#([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})$/i);

  /**
   * Regex for rgb values e.g. rgb(255,0,24);
   * @type {RegExp}
   */
  const rgbRegex = new RegExp(/^rgb\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)\)$/i);

  /**
   * Regex for hsl values e.g. hsl(255,0,24);
   * @type {RegExp}
   */
  const hslRegex = new RegExp(/^hsl\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+\)$/i);

  /**
   * Creates a slider for the color values hue, saturation and light.
   *
   * @since 4.0
   */
  class JoomlaFieldColorSlider {
    /**
     * @param {HTMLElement} element
     */
    constructor(element) {
      // Elements
      this.input = element.querySelector('.color-input');
      this.slider = element.querySelector('.color-slider');

      // Attributes
      this.color = element.dataset.color || '';
      this.default = element.dataset.default || '';
      this.display = element.dataset.display || 'hue';
      this.format = element.dataset.format || 'hex';
      this.preview = element.dataset.preview === 'true';

      this.hue = 360;
      this.saturation = 1;
      this.light = 1;

      this.setInitValue();
      this.setBackground();

      // Hide input field, when selected value should not be visible
      if (!this.preview) {
        this.input.style.display = 'none';
      }

      this.slider.addEventListener('change', () => this.updateValue());
    }

    /**
     * Set selected value into input field and set it as its background-color.
     */
    updateValue() {
      const rgb = this.getValueAsRgb();
      this.input.style.background = this.getRgbString(rgb);
      this.setInputValue(this.convertRgbToHsl(rgb));
    }

    /**
     * Set linear gradient for slider background
     */
    setBackground() {
      let colors = [];
      let endValue = 100;

      this.slider.style.webkitAppearance = 'none';

      // Longer start color so slider selection matches displayed colors
      colors.push(this.getValueAsRgb(0));

      if (this.display === 'hue') {
        const steps = Math.floor(360 / 20);
        endValue = 360;

        for (let i = 0; i <= 360; i += steps) {
          colors.push(this.getValueAsRgb(i));
        }
      } else {
        for (let i = 0; i <= 100; i += 10) {
          colors.push(this.getValueAsRgb(i));
        }
      }

      // Longer end color so slider selection matches displayed colors
      colors.push(this.getValueAsRgb(endValue));

      // IE uses hex values
      colors = colors.map(value => this.convertRgbToHex(value));

      this.slider.style.background = `linear-gradient(to right, ${colors.join(',')})`;
    }

    /**
     * Convert given color into hue, saturation and light
     */
    setInitValue() {
      const value = this.color || this.default || '';
      let hsl = [];

      if (!value) {
        return;
      }

      if (typeof value === 'number') {
        if (this.display === 'hue') {
          hsl[0] = value;
        }
        if (this.display === 'saturation') {
          hsl[1] = value;
        }
        if (this.display === 'light') {
          hsl[2] = value;
        }
      } else if (hexRegex.test(value)) {
        hsl = this.convertHexToHsl(value);
      } else if (rgbRegex.test(value)) {
        hsl = this.convertRgbToHsl(value);
      } else if (hslRegex.test(value)) {
        const matches = value.match(hslRegex);
        hsl = [matches[1], matches[2], matches[3]];
      } else {
        throw new Error(`Incorrect input value ${value}.`);
      }

      hsl[1] = hsl[1] > 1 ? hsl[1] / 100 : hsl[1];
      hsl[2] = hsl[2] > 1 ? hsl[2] / 100 : hsl[2];

      [this.hue, this.saturation, this.light] = hsl;

      switch (this.display) {
        case 'saturation':
          this.slider.value = Math.round(this.saturation * 100);
          break;
        case 'light':
          this.slider.value = Math.round(this.light * 100);
          break;
        case 'hue':
        default:
          this.slider.value = Math.round(this.hue);
          break;
      }

      this.setInputValue(hsl);

      if (typeof value !== 'number') {
        this.input.style.background = this.getRgbString(this.convertHslToRgb(hsl));
      }
    }

    /**
     * Set value in input field depending on format
     * @param {array} hsl
     */
    setInputValue(hsl) {
      switch (this.format) {
        case 'hsl':
          this.input.value = this.getHslString(hsl);
          break;
        case 'rgb':
          this.input.value = this.getRgbString(this.convertHslToRgb(hsl));
          break;
        case 'hex':
          this.input.value = this.convertRgbToHex(this.convertHslToRgb(hsl));
          break;
        case 'saturation':
          this.input.value = Math.round(hsl[1] * 100);
          break;
        case 'light':
          this.input.value = Math.round(hsl[2] * 100);
          break;
        case 'hue':
        default:
          this.input.value = Math.round(hsl[0]);
          break;
      }
    }

    /**
     * Calculates RGB value from color slider value
     * @params {int} [value]
     * @returns string|array
     */
    getValueAsRgb(value) {
      const input = value === undefined ? this.slider.value : value;
      let h = this.hue;
      let s = this.saturation;
      let l = this.light;

      if (this.display === 'hue') {
        h = input;
      }
      if (this.display === 'saturation') {
        s = input;
      }
      if (this.display === 'light') {
        l = input;
      }

      // Percentage light and saturation
      if (l > 1) {
        l /= 100;
      }
      if (s > 1) {
        s /= 100;
      }

      return this.convertHslToRgb([h, s, l]);
    }

    /**
     * Put RGB values into a string like 'rgb(<R>, <G>, <B>)'
     * @params {array} rgb
     */
    getRgbString(rgb) {
      return `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
    }

    /**
     * Put HSL values into a string like 'hsl(<H>, <S>%, <L>%)'
     * @params {array} values
     */
    getHslString(values) {
      let hsl = values;

      hsl[1] *= 100;
      hsl[2] *= 100;
      hsl = hsl.map(value => Math.round(value));

      return `hsl(${hsl[0]}, ${hsl[1]}%, ${hsl[2]}%)`;
    }

    /**
     * Returns hsl values out of hex
     * @param {array} rgb
     * @return {string}
     */
    convertRgbToHex(rgb) {
      let r = rgb[0].toString(16).toUpperCase();
      let g = rgb[1].toString(16).toUpperCase();
      let b = rgb[2].toString(16).toUpperCase();

      // Add zero for '#' + 6 chars
      r = r.length === 1 ? `0${r}` : r;
      g = g.length === 1 ? `0${g}` : g;
      b = b.length === 1 ? `0${b}` : b;

      return `#${r}${g}${b}`;
    }

    /**
     * Returns hsl values out of rgb
     * @param {string|array} values
     * @return {array}
     */
    convertRgbToHsl(values) {
      let rgb = values;

      if (typeof values === 'string') {
        const parts = values.match(rgbRegex);
        rgb = [parts[1], parts[2], parts[3]];
      }

      const [r, g, b] = rgb.map(value => (value > 1 ? value / 255 : value));
      const max = Math.max(r, g, b);
      const min = Math.min(r, g, b);
      const l = (max + min) / 2;
      const d = max - min;
      let h = 0;
      let s = 0;

      if (max !== min) {
        if (max === 0) {
          s = max;
        } else if (min === 1) {
          s = min;
        } else {
          s = (max - l) / (Math.min(l, 1 - l));
        }

        switch (max) {
          case r:
            h = 60 * (g - b) / d;
            break;
          case g:
            h = 60 * (2 + (b - r) / d);
            break;
          case b:
          default:
            h = 60 * (4 + (r - g) / d);
            break;
        }
      }

      h = h < 0 ? h + 360 : h;

      return [h, s, l];
    }

    /**
     * Returns hsl values out of hex
     * @param {string} hex
     * @return {array}
     */
    convertHexToHsl(hex) {
      const parts = hex.match(hexRegex);
      const r = parts[1];
      const g = parts[2];
      const b = parts[3];

      const rgb = [parseInt(r, 16), parseInt(g, 16), parseInt(b, 16)];

      return this.convertRgbToHsl(rgb);
    }

    /**
     * Convert HSL values into RGB
     * @param {array} hsl
     * @returns {number[]}
     */
    convertHslToRgb([h, sat, light]) {
      let r = 1;
      let g = 1;
      let b = 1;

      // Saturation and light were calculated as 0.24 instead of 24%
      const s = sat > 1 ? sat / 100 : sat;
      const l = light > 1 ? light / 100 : light;

      if (h < 0 || h > 360 || s < 0 || s > 1 || l < 0 || l > 1) {
        throw new Error(`Unable to convert hsl(${h}, ${s}, ${l}) into RGB.`);
      }

      const c = (1 - Math.abs(2 * l - 1)) * s;
      const hi = h / 60;
      const x = c * (1 - Math.abs((hi % 2) - 1));
      const m = l - c / 2;

      if (h >= 0 && h < 60) {
        [r, g, b] = [c, x, 0];
      } else if (h >= 60 && h < 120) {
        [r, g, b] = [x, c, 0];
      } else if (h >= 120 && h < 180) {
        [r, g, b] = [0, c, x];
      } else if (h >= 180 && h < 240) {
        [r, g, b] = [0, x, c];
      } else if (h >= 240 && h < 300) {
        [r, g, b] = [x, 0, c];
      } else if (h >= 300 && h <= 360) {
        [r, g, b] = [c, 0, x];
      }

      return [r, g, b].map(value => Math.round((value + m) * 255));
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const sliders = document.querySelectorAll('.color-slider-wrapper');

    if (sliders) {
      Array.prototype.forEach.call(sliders, (slider) => {
        // eslint-disable-next-line no-new
        new JoomlaFieldColorSlider(slider);
      });
    }
  });
})(document);
