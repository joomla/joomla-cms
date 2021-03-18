/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/* eslint class-methods-use-this: ["error", { "exceptMethods": ["rgbToHex", "hslToRgb"] }] */
(function (document) {
  'use strict';
  /**
   * Regex for hex values e.g. #FF3929
   * @type {RegExp}
   */

  var hexRegex = new RegExp(/^#([a-z0-9]{1,2})([a-z0-9]{1,2})([a-z0-9]{1,2})$/i);
  /**
   * Regex for rgb values e.g. rgba(255, 0, 24, 0.5);
   * @type {RegExp}
   */

  var rgbRegex = new RegExp(/^rgba?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)(?:[\D]+([0-9](?:.\d+)?))?\)$/i);
  /**
   * Regex for hsl values e.g. hsl(255,0,24);
   * @type {RegExp}
   */

  var hslRegex = new RegExp(/^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$/i);
  /**
   * Regex for saturation and lightness of hsl - only accepts 1 or 0 or 0.4 or 40
   * @type {RegExp}
   */

  var hslNumberRegex = new RegExp(/^(([0-1])|(0\\.[0-9]+)|([0-9]{1,2})|(100))$/);
  /**
   * Regex for hue values - one to three numbers
   * @type {RegExp}
   */

  var hueRegex = new RegExp(/^[0-9]{1,3}$/);
  /**
   * Creates a slider for the color values hue, saturation and light.
   *
   * @since 4.0
   */

  var JoomlaFieldColorSlider = /*#__PURE__*/function () {
    /**
     * @param {HTMLElement} element
     */
    function JoomlaFieldColorSlider(element) {
      var _this = this;

      _classCallCheck(this, JoomlaFieldColorSlider);

      // Elements
      this.messageSpan = element.querySelector('.form-control-feedback');
      this.mainInput = element.querySelector('.color-input');
      this.input = element.querySelector('#slider-input');
      this.sliders = element.querySelectorAll('.color-slider');
      this.hueSlider = element.querySelector('#hue-slider');
      this.saturationSlider = element.querySelector('#saturation-slider');
      this.lightSlider = element.querySelector('#light-slider');
      this.alphaSlider = element.querySelector('#alpha-slider'); // Attributes

      this.color = element.dataset.color || '';
      this.default = element.dataset.default || '';
      this.format = this.input.dataset.format || 'hex';
      this.saveFormat = this.mainInput.dataset.format || 'hex';
      this.preview = element.dataset.preview === 'true';
      this.setAlpha = this.format === 'hsla' || this.format === 'rgba';
      this.hue = 360;
      this.saturation = 1;
      this.light = 1;
      this.alpha = 1;
      this.defaultHsl = [this.hue, this.saturation, this.light, this.alpha];
      this.setInitValue();
      this.setBackground(); // Hide preview field, when selected value should not be visible

      if (!this.preview) {
        this.input.classList.add('hidden');
      } else {
        this.setInputPattern();
      } // Always hide main input field (value saved in database)


      this.mainInput.classList.add('hidden');
      Array.prototype.forEach.call(this.sliders, function (slider) {
        slider.addEventListener('change', function () {
          return _this.updateValue(slider);
        });
      });
      this.input.addEventListener('change', function () {
        return _this.changeInput(_this.input);
      });
    }
    /**
     * Set selected value into input field and set it as its background-color.
     */


    _createClass(JoomlaFieldColorSlider, [{
      key: "updateValue",
      value: function updateValue(slider) {
        this.showError('');
        var hsl = this.getSliderValueAsHsl(slider.value, slider.dataset.type);
        var rgb = this.hslToRgb(hsl);

        var _hsl = _slicedToArray(hsl, 4);

        this.hue = _hsl[0];
        this.saturation = _hsl[1];
        this.light = _hsl[2];
        this.alpha = _hsl[3];
        this.input.style.border = "2px solid ".concat(this.getRgbString(rgb));
        this.setSliderValues(hsl, slider.dataset.type);
        this.setInputValue(hsl);
        this.setBackground(slider);
      }
      /**
       * React on user changing input value
       *
       * @param {HTMLElement} inputField
       */

    }, {
      key: "changeInput",
      value: function changeInput(inputField) {
        var hsl = [this.hue, this.saturation, this.light, this.alpha];

        if (!inputField.value) {
          this.mainInput.value = '';
          this.showError('');
          return;
        }

        if (!this.checkValue(inputField.value)) {
          this.showError('JFIELD_COLOR_ERROR_WRONG_FORMAT');
          this.setInputValue(this.defaultHsl);
        } else {
          this.showError('');

          switch (this.format) {
            case 'hue':
              hsl[0] = inputField.value;
              this.hue = inputField.value;
              break;

            case 'saturation':
              hsl[1] = inputField.value;
              this.saturation = inputField.value;
              break;

            case 'light':
              hsl[2] = inputField.value;
              this.light = inputField.value;
              break;

            case 'alpha':
              hsl[3] = inputField.value;
              this.alpha = inputField.value;
              break;

            default:
              hsl = this.getHsl(inputField.value);
          }

          this.setSliderValues(hsl);
          this.setInputValue(hsl, true);
        }
      }
      /**
       * Check validity of value
       *
       * @param {number|string} value to check
       * @param {string=false} format for which the value gets tested
       * @returns {boolean}
       */

    }, {
      key: "checkValue",
      value: function checkValue(value, format) {
        var test = format || this.format;

        switch (test) {
          case 'hue':
            return value <= 360 && hueRegex.test(value);

          case 'saturation':
          case 'light':
          case 'alpha':
            return hslNumberRegex.test(value);

          case 'hsl':
          case 'hsla':
            return hslRegex.test(value);

          case 'hex':
            return hexRegex.test(value);

          case 'rgb':
          case 'rgba':
            return rgbRegex.test(value);

          default:
            return false;
        }
      }
      /**
       * Set validation pattern on input field
       */

    }, {
      key: "setInputPattern",
      value: function setInputPattern() {
        var pattern; // RegExp has '/' at start and end

        switch (this.format) {
          case 'hue':
            pattern = hueRegex.source.slice(1, -1);
            break;

          case 'saturation':
          case 'light':
          case 'alpha':
            pattern = hslNumberRegex.source.slice(1, -1);
            break;

          case 'hsl':
          case 'hsla':
            pattern = hslRegex.source.slice(1, -1);
            break;

          case 'rgb':
            pattern = rgbRegex.source.slice(1, -1);
            break;

          case 'hex':
          default:
            pattern = hexRegex.source.slice(1, -1);
        }

        this.input.setAttribute('pattern', pattern);
      }
      /**
       * Set linear gradient for slider background
       * @param {HTMLInputElement} [exceptSlider]
       */

    }, {
      key: "setBackground",
      value: function setBackground(exceptSlider) {
        var _this2 = this;

        Array.prototype.forEach.call(this.sliders, function (slider) {
          // Jump over changed slider
          if (exceptSlider === slider) {
            return;
          }

          var colors = [];
          var endValue = 100; // Longer start color so slider selection matches displayed colors

          colors.push(_this2.getSliderValueAsRgb(0, slider.dataset.type));

          if (slider.dataset.type === 'hue') {
            var steps = Math.floor(360 / 20);
            endValue = 360;

            for (var i = 0; i <= 360; i += steps) {
              colors.push(_this2.getSliderValueAsRgb(i, slider.dataset.type));
            }
          } else {
            for (var _i2 = 0; _i2 <= 100; _i2 += 10) {
              colors.push(_this2.getSliderValueAsRgb(_i2, slider.dataset.type));
            }
          } // Longer end color so slider selection matches displayed colors


          colors.push(_this2.getSliderValueAsRgb(endValue, slider.dataset.type));
          colors = colors.map(function (value) {
            return _this2.getRgbString(value);
          });
          slider.style.background = "linear-gradient(90deg, ".concat(colors.join(','), ")");
          slider.style.webkitAppearance = 'none';
        });
      }
      /**
       * Convert given color into hue, saturation and light
       */

    }, {
      key: "setInitValue",
      value: function setInitValue() {
        // The initial value can be also a color defined in css
        var cssValue = window.getComputedStyle(this.input).getPropertyValue(this.default);
        this.default = cssValue || this.default;

        if (this.color === '' || typeof this.color === 'undefined') {
          // Unable to get hsl with empty value
          this.input.value = '';
          this.mainInput.value = '';
          return;
        }

        var value = this.checkValue(this.color, this.saveFormat) ? this.color : this.default;

        if (!value) {
          this.showError('JFIELD_COLOR_ERROR_NO_COLOUR');
          return;
        }

        var hsl = []; // When given value is a number, use it as defined format and get rest from default value

        if (/^[0-9]+$/.test(value)) {
          hsl = this.default && this.getHsl(this.default);

          if (this.format === 'hue') {
            hsl[0] = value;
          }

          if (this.format === 'saturation') {
            hsl[1] = value > 1 ? value / 100 : value;
          }

          if (this.format === 'light') {
            hsl[2] = value > 1 ? value / 100 : value;
          }

          if (this.format === 'alpha') {
            hsl[3] = value > 1 ? value / 100 : value;
          }
        } else {
          hsl = this.getHsl(value);
        }

        var _hsl2 = hsl;

        var _hsl3 = _slicedToArray(_hsl2, 3);

        this.hue = _hsl3[0];
        this.saturation = _hsl3[1];
        this.light = _hsl3[2];
        this.alpha = hsl[4] || this.alpha;
        this.defaultHsl = this.default ? this.getHsl(this.default) : hsl;
        this.setSliderValues(hsl);
        this.setInputValue(hsl);
        this.input.style.border = "2px solid ".concat(this.getRgbString(this.hslToRgb(hsl)));
      }
      /**
       * Insert message into error message span
       * Message gets handled with Joomla.Text or as empty string
       *
       * @param {string} msg
       */

    }, {
      key: "showError",
      value: function showError(msg) {
        this.messageSpan.innerText = msg ? Joomla.Text._(msg) : '';
      }
      /**
       * Convert value into HSLa e.g. #003E7C => [210, 100, 24]
       * @param {array|number|string} value
       * @returns {array}
       */

    }, {
      key: "getHsl",
      value: function getHsl(value) {
        var hsl = [];

        if (Array.isArray(value)) {
          hsl = value;
        } else if (hexRegex.test(value)) {
          hsl = this.hexToHsl(value);
        } else if (rgbRegex.test(value)) {
          hsl = this.rgbToHsl(value);
        } else if (hslRegex.test(value)) {
          var matches = value.match(hslRegex);
          hsl = [matches[1], matches[2], matches[3], matches[4]];
        } else {
          this.showError('JFIELD_COLOR_ERROR_CONVERT_HSL');
          return this.defaultHsl;
        } // Convert saturation etc. values from e.g. 40 to 0.4


        var i;

        for (i = 1; i < hsl.length; i += 1) {
          hsl[i] = hsl[i] > 1 ? hsl[i] / 100 : hsl[i];
        }

        return hsl;
      }
      /**
       * Returns HSL value from color slider value
       * @params {int} value convert this value
       * @params {string} type type of value: hue, saturation, light or alpha
       * @returns array
       */

    }, {
      key: "getSliderValueAsHsl",
      value: function getSliderValueAsHsl(value, type) {
        var h = this.hue;
        var s = this.saturation;
        var l = this.light;
        var a = this.alpha;

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
        } // Percentage light and saturation


        if (l > 1) {
          l /= 100;
        }

        if (s > 1) {
          s /= 100;
        }

        if (a > 1) {
          a /= 100;
        }

        return [h, s, l, a];
      }
      /**
       * Calculates RGB value from color slider value
       * @params {int} value convert this value
       * @params {string} type type of value: hue, saturation, light or alpha
       * @returns array
       */

    }, {
      key: "getSliderValueAsRgb",
      value: function getSliderValueAsRgb(value, type) {
        return this.hslToRgb(this.getSliderValueAsHsl(value, type));
      }
      /**
       * Set value in all sliders
       * @param {array} [hsla]
       * @param {string} [except]
       */

    }, {
      key: "setSliderValues",
      value: function setSliderValues(_ref, except) {
        var _ref2 = _slicedToArray(_ref, 4),
            h = _ref2[0],
            s = _ref2[1],
            l = _ref2[2],
            a = _ref2[3];

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
       * Set value in text input fields depending on their format
       * @param {array} hsl
       * @param {boolean=false} onlyMain indicates to change mainInput only
       */

    }, {
      key: "setInputValue",
      value: function setInputValue(hsl, onlyMain) {
        var _this3 = this;

        var inputs = [this.mainInput];

        if (!onlyMain) {
          inputs.push(this.input);
        }

        inputs.forEach(function (input) {
          var value;

          switch (input.dataset.format) {
            case 'hsl':
              value = _this3.getHslString(hsl);
              break;

            case 'hsla':
              value = _this3.getHslString(hsl, true);
              break;

            case 'rgb':
              value = _this3.getRgbString(_this3.hslToRgb(hsl));
              break;

            case 'rgba':
              value = _this3.getRgbString(_this3.hslToRgb(hsl), true);
              break;

            case 'hex':
              value = _this3.rgbToHex(_this3.hslToRgb(hsl));
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

          input.value = value;
        });
      }
      /**
       * Put RGB values into a string like 'rgb(<R>, <G>, <B>)'
       * @params {array} rgba
       * @params {boolean=false} withAlpha
       * @return {string}
       */

    }, {
      key: "getRgbString",
      value: function getRgbString(_ref3, withAlpha) {
        var _ref4 = _slicedToArray(_ref3, 4),
            r = _ref4[0],
            g = _ref4[1],
            b = _ref4[2],
            a = _ref4[3];

        if (withAlpha || this.setAlpha) {
          var alpha = typeof a === 'undefined' ? this.alpha : a;
          return "rgba(".concat(r, ", ").concat(g, ", ").concat(b, ", ").concat(alpha, ")");
        }

        return "rgb(".concat(r, ", ").concat(g, ", ").concat(b, ")");
      }
      /**
       * Put HSL values into a string like 'hsl(<H>, <S>%, <L>%, <a>)'
       * @params {array} values
       * @params {boolean=false} withAlpha
       * @return {string}
       */

    }, {
      key: "getHslString",
      value: function getHslString(values, withAlpha) {
        var _values = _slicedToArray(values, 4),
            h = _values[0],
            s = _values[1],
            l = _values[2],
            a = _values[3];

        s *= 100;
        l *= 100;

        var _map = [h, s, l].map(function (value) {
          return Math.round(value);
        });

        var _map2 = _slicedToArray(_map, 3);

        h = _map2[0];
        s = _map2[1];
        l = _map2[2];

        if (withAlpha || this.setAlpha) {
          a = a || this.alpha;
          return "hsla(".concat(h, ", ").concat(s, "%, ").concat(l, "%, ").concat(a, ")");
        }

        return "hsl(".concat(h, ", ").concat(s, "%, ").concat(l, "%)");
      }
      /**
       * Returns hsl values out of hex
       * @param {array} rgb
       * @return {string}
       */

    }, {
      key: "rgbToHex",
      value: function rgbToHex(rgb) {
        var r = rgb[0].toString(16).toUpperCase();
        var g = rgb[1].toString(16).toUpperCase();
        var b = rgb[2].toString(16).toUpperCase(); // Double value for hex with '#' and 6 chars

        r = r.length === 1 ? "".concat(r).concat(r) : r;
        g = g.length === 1 ? "".concat(g).concat(g) : g;
        b = b.length === 1 ? "".concat(b).concat(b) : b;
        return "#".concat(r).concat(g).concat(b);
      }
      /**
       * Returns hsl values out of rgb
       * @param {string|array} values
       * @return {array}
       */

    }, {
      key: "rgbToHsl",
      value: function rgbToHsl(values) {
        var rgb = values;

        if (typeof values === 'string') {
          var parts = values.match(rgbRegex);
          rgb = [parts[1], parts[2], parts[3], parts[4]];
        }

        var _rgb$map = rgb.map(function (value) {
          return value > 1 ? value / 255 : value;
        }),
            _rgb$map2 = _slicedToArray(_rgb$map, 3),
            r = _rgb$map2[0],
            g = _rgb$map2[1],
            b = _rgb$map2[2];

        var max = Math.max(r, g, b);
        var min = Math.min(r, g, b);
        var l = (max + min) / 2;
        var d = max - min;
        var h = 0;
        var s = 0;
        var a = rgb[3] || values[3] || this.alpha;

        if (max !== min) {
          if (max === 0) {
            s = max;
          } else if (min === 1) {
            s = min;
          } else {
            s = (max - l) / Math.min(l, 1 - l);
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

    }, {
      key: "hexToHsl",
      value: function hexToHsl(hex) {
        var parts = hex.match(hexRegex);
        var r = parts[1];
        var g = parts[2];
        var b = parts[3];
        var rgb = [parseInt(r, 16), parseInt(g, 16), parseInt(b, 16)];
        return this.rgbToHsl(rgb);
      }
      /**
       * Convert HSLa values into RGBa
       * @param {array} hsla
       * @returns {number[]}
       */

    }, {
      key: "hslToRgb",
      value: function hslToRgb(_ref5) {
        var _ref6 = _slicedToArray(_ref5, 4),
            h = _ref6[0],
            sat = _ref6[1],
            light = _ref6[2],
            alpha = _ref6[3];

        var r = 1;
        var g = 1;
        var b = 1; // Saturation and light were calculated as 0.24 instead of 24%

        var s = sat > 1 ? sat / 100 : sat;
        var l = light > 1 ? light / 100 : light;
        var a = alpha > 1 ? alpha / 100 : alpha;

        if (h < 0 || h > 360 || s < 0 || s > 1 || l < 0 || l > 1) {
          this.showError('JFIELD_COLOR_ERROR_CONVERT_HSL');
          return this.hslToRgb(this.defaultHsl);
        }

        var c = (1 - Math.abs(2 * l - 1)) * s;
        var hi = h / 60;
        var x = c * (1 - Math.abs(hi % 2 - 1));
        var m = l - c / 2;

        if (h >= 0 && h < 60) {
          r = c;
          g = x;
          b = 0;
        } else if (h >= 60 && h < 120) {
          r = x;
          g = c;
          b = 0;
        } else if (h >= 120 && h < 180) {
          r = 0;
          g = c;
          b = x;
        } else if (h >= 180 && h < 240) {
          r = 0;
          g = x;
          b = c;
        } else if (h >= 240 && h < 300) {
          r = x;
          g = 0;
          b = c;
        } else if (h >= 300 && h <= 360) {
          r = c;
          g = 0;
          b = x;
        } else {
          this.showError('JFIELD_COLOR_ERROR_CONVERT_HUE');
          return this.hslToRgb(this.defaultHsl);
        }

        var rgb = [r, g, b].map(function (value) {
          return Math.round((value + m) * 255);
        });
        rgb.push(a);
        return rgb;
      }
    }]);

    return JoomlaFieldColorSlider;
  }();

  document.addEventListener('DOMContentLoaded', function () {
    var fields = document.querySelectorAll('.color-slider-wrapper');

    if (fields) {
      Array.prototype.forEach.call(fields, function (slider) {
        // eslint-disable-next-line no-new
        new JoomlaFieldColorSlider(slider);
      });
    }
  });
})(document);