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
   * Regex for rgb values e.g. rgba(255, 0, 24, 0.5);
   * @type {RegExp}
   */
  const rgbRegex = new RegExp(
    /^rgba?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)(?:[\D]+([0-9](?:.\d+)?))?\)$/i,
  );

  /**
   * Regex for hsl values e.g. hsl(255,0,24);
   * @type {RegExp}
   */
  const hslRegex = new RegExp(
    /^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$/i,
  );

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
      this.sliders = element.querySelectorAll('.color-slider');
      this.hueSlider = element.querySelector('.hue-slider');
      this.saturationSlider = element.querySelector('.saturation-slider');
      this.lightSlider = element.querySelector('.light-slider');
      this.alphaSlider = element.querySelector('.alpha-slider');

      // Attributes
      this.color = element.dataset.color || '';
      this.default = element.dataset.default || '';
      this.display = element.dataset.display.split(',') || ['full'];
      this.format = element.dataset.format || 'hex';
      this.preview = element.dataset.preview === 'true';
      this.setAlpha = this.format === 'hsla' || this.format === 'rgba';

      this.hue = 360;
      this.saturation = 1;
      this.light = 1;
      this.alpha = 1;

      this.setInitValue();
      this.setBackground();

      // Hide input field, when selected value should not be visible
      if (!this.preview) {
        this.input.style.display = 'none';
      }

      Array.prototype.forEach.call(this.sliders, (slider) => {
        slider.addEventListener('change', () => this.updateValue(slider));
      });
    }

    /**
     * Set selected value into input field and set it as its background-color.
     */
    updateValue(slider) {
      const rgb = this.getValueAsRgb(slider.value, slider.dataset.type);
      const hsl = this.rgbToHsl(rgb);
      [this.hue, this.saturation, this.light, this.alpha] = hsl;

      this.input.style.border = `2px solid ${this.getRgbString(rgb)}`;
      this.setSliderValues(hsl, slider.dataset.type);
      this.setInputValue(hsl);
      this.setBackground(slider);
    }

    /**
     * Set linear gradient for slider background
     * @param {HTMLInputElement} [exceptSlider]
     */
    setBackground(exceptSlider) {
      Array.prototype.forEach.call(this.sliders, (slider) => {
        // Jump over changed slider
        if (exceptSlider === slider) {
          return;
        }

        let colors = [];
        let endValue = 100;
        slider.style.webkitAppearance = 'none';

        // Longer start color so slider selection matches displayed colors
        colors.push(this.getValueAsRgb(0, slider.dataset.type));

        if (slider.dataset.type === 'hue') {
          const steps = Math.floor(360 / 20);
          endValue = 360;

          for (let i = 0; i <= 360; i += steps) {
            colors.push(this.getValueAsRgb(i, slider.dataset.type));
          }
        } else {
          for (let i = 0; i <= 100; i += 10) {
            colors.push(this.getValueAsRgb(i, slider.dataset.type));
          }
        }

        // Longer end color so slider selection matches displayed colors
        colors.push(this.getValueAsRgb(endValue, slider.dataset.type));

        colors = colors.map(value => this.getRgbString(value));
        slider.style.background = `linear-gradient(90deg, ${colors.join(',')})`;
      });
    }

    /**
     * Convert given color into hue, saturation and light
     */
    setInitValue() {
      // The initial value can be also a color defined in css
      const cssValue = window.getComputedStyle(this.input).getPropertyValue(this.default);
      const value = cssValue || this.color || this.default || '';
      let hsl = [this.hue, this.saturation, this.light, this.alpha];

      if (!value) {
        return;
      }

      if (/^[0-9]+$/.test(value)) {
        if (this.display.indexOf('hue') !== -1) {
          hsl[0] = value;
        }
        if (this.display.indexOf('saturation') !== -1) {
          hsl[1] = value;
        }
        if (this.display.indexOf('light') !== -1) {
          hsl[2] = value;
        }
        if (this.display.indexOf('alpha') !== -1) {
          hsl[3] = value;
        }
      } else if (hexRegex.test(value)) {
        hsl = this.hexToHsl(value);
      } else if (rgbRegex.test(value)) {
        hsl = this.rgbToHsl(value);
      } else if (hslRegex.test(value)) {
        const matches = value.match(hslRegex);
        hsl = [matches[1], matches[2], matches[3], matches[4]];
      } else {
        throw new Error(`Incorrect input value ${value}.`);
      }

      hsl[1] = hsl[1] > 1 ? hsl[1] / 100 : hsl[1];
      hsl[2] = hsl[2] > 1 ? hsl[2] / 100 : hsl[2];

      [this.hue, this.saturation, this.light] = hsl;
      this.alpha = hsl[4] || this.alpha;

      this.setSliderValues(hsl);
      this.setInputValue(hsl);

      if (/^[0-9]+$/.test(value) === false) {
        this.input.style.border = `2px solid ${this.getRgbString(this.hslToRgb(hsl))}`;
      }
    }

    /**
     * Set value in all sliders
     * @param {array} [hsla]
     * @param {string} [except]
     */
    setSliderValues([h, s, l, a], except) {
      if (this.hueSlider && except !== 'hue') {
        this.hueSlider.value = Math.round(h);
      }
      if (this.saturationSlider && except !== 'saturation') {
        this.saturationSlider.value = Math.round(s * 100);
      }
      if (this.lightSlider && except !== 'light') {
        this.lightSlider.value = Math.round(l * 100);
      }
      if (a && this.alphaSlider && except !== 'alpha') {
        this.alphaSlider.value = Math.round(a * 100);
      }
    }

    /**
     * Set value in input field depending on format
     * @param {array} hsl
     */
    setInputValue(hsl) {
      let value;

      switch (this.format) {
        case 'hsl':
        case 'hsla':
          value = this.getHslString(hsl);
          break;
        case 'rgb':
        case 'rgba':
          value = this.getRgbString(this.hslToRgb(hsl));
          break;
        case 'hex':
          value = this.rgbToHex(this.hslToRgb(hsl));
          break;
        case 'alpha':
          value = Math.round(hsl[3] * 100);
          break;
        case 'saturation':
          value = Math.round(hsl[1] * 100);
          break;
        case 'light':
          value = Math.round(hsl[2] * 100);
          break;
        case 'hue':
        default:
          value = Math.round(hsl[0]);
          break;
      }

      this.input.setAttribute('value', value);
    }

    /**
     * Calculates RGB value from color slider value
     * @params {int} value convert this value
     * @params {string} type type of value: hue, saturation or light
     * @returns string|array
     */
    getValueAsRgb(value, type) {
      let h = this.hue;
      let s = this.saturation;
      let l = this.light;
      let a = this.alpha;

      switch (type) {
        case 'alpha':
          a = value;
          break;
        case 'saturation':
          s = value;
          break;
        case 'light':
          l = value;
          break;
        case 'hue':
        default:
          h = value;
      }

      // Percentage light and saturation
      if (l > 1) {
        l /= 100;
      }
      if (s > 1) {
        s /= 100;
      }
      if (a > 1) {
        a /= 100;
      }

      return this.hslToRgb([h, s, l, a]);
    }

    /**
     * Put RGB values into a string like 'rgb(<R>, <G>, <B>)'
     * @params {array} rgba
     */
    getRgbString([r, g, b, a]) {
      if (this.setAlpha) {
        const alpha = a || this.alpha;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
      }
      return `rgb(${r}, ${g}, ${b})`;
    }

    /**
     * Put HSL values into a string like 'hsl(<H>, <S>%, <L>%, <a>)'
     * @params {array} values
     */
    getHslString(values) {
      let [h, s, l, a] = values;

      s *= 100;
      l *= 100;
      [h, s, l] = [h, s, l].map(value => Math.round(value));

      if (this.setAlpha) {
        a = a || this.alpha;
        return `hsla(${h}, ${s}%, ${l}%, ${a})`;
      }

      return `hsl(${h}, ${s}%, ${l}%)`;
    }

    /**
     * Returns hsl values out of hex
     * @param {array} rgb
     * @return {string}
     */
    rgbToHex(rgb) {
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
    rgbToHsl(values) {
      let rgb = values;

      if (typeof values === 'string') {
        const parts = values.match(rgbRegex);
        rgb = [parts[1], parts[2], parts[3], parts[4]];
      }

      const [r, g, b] = rgb.map(value => (value > 1 ? value / 255 : value));
      const max = Math.max(r, g, b);
      const min = Math.min(r, g, b);
      const l = (max + min) / 2;
      const d = max - min;
      let h = 0;
      let s = 0;
      let a = rgb[3] || values[3] || this.alpha;

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
      a = a > 1 ? a / 100 : a;

      return [h, s, l, a];
    }

    /**
     * Returns hsl values out of hex
     * @param {string} hex
     * @return {array}
     */
    hexToHsl(hex) {
      const parts = hex.match(hexRegex);
      const r = parts[1];
      const g = parts[2];
      const b = parts[3];

      const rgb = [parseInt(r, 16), parseInt(g, 16), parseInt(b, 16)];

      return this.rgbToHsl(rgb);
    }

    /**
     * Convert HSLa values into RGBa
     * @param {array} hsla
     * @returns {number[]}
     */
    hslToRgb([h, sat, light, alpha]) {
      let r = 1;
      let g = 1;
      let b = 1;

      // Saturation and light were calculated as 0.24 instead of 24%
      const s = sat > 1 ? sat / 100 : sat;
      const l = light > 1 ? light / 100 : light;
      const a = alpha > 1 ? alpha / 100 : alpha;

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
      } else {
        throw new Error(`Unable to convert hue ${h} into RGB.`);
      }

      const rgb = [r, g, b].map(value => Math.round((value + m) * 255));
      rgb.push(a);

      return rgb;
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const fields = document.querySelectorAll('.color-slider-wrapper');

    if (fields) {
      Array.prototype.forEach.call(fields, (slider) => {
        // eslint-disable-next-line no-new
        new JoomlaFieldColorSlider(slider);
      });
    }
  });
})(document);
