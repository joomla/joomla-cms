/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * PasswordStrength script by Thomas Kjaergaard
 * License: MIT
 * Repo: https://github.com/tkjaergaard/Password-Strength
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Thomas Kjærgaard
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
var PasswordStrength = /*#__PURE__*/function () {
  function PasswordStrength(settings) {
    _classCallCheck(this, PasswordStrength);

    this.lowercase = parseInt(settings.lowercase, 10) || 0;
    this.uppercase = parseInt(settings.uppercase, 10) || 0;
    this.numbers = parseInt(settings.numbers, 10) || 0;
    this.special = parseInt(settings.special, 10) || 0;
    this.length = parseInt(settings.length, 10) || 4;
  }

  _createClass(PasswordStrength, [{
    key: "getScore",
    value: function getScore(value) {
      var _this = this;

      var score = 0;
      var mods = 0;
      var sets = ['lowercase', 'uppercase', 'numbers', 'special', 'length'];
      sets.forEach(function (set) {
        if (_this[set] > 0) {
          mods += 1;
        }
      });
      score += this.constructor.calc(value, /[a-z]/g, this.lowercase, mods);
      score += this.constructor.calc(value, /[A-Z]/g, this.uppercase, mods);
      score += this.constructor.calc(value, /[0-9]/g, this.numbers, mods); // eslint-disable-next-line no-useless-escape

      score += this.constructor.calc(value, /[\$\!\#\?\=\;\:\*\-\_\€\%\&\(\)\`\´]/g, this.special, mods);

      if (mods === 1) {
        score += value.length > this.length ? 100 : 100 / this.length * value.length;
      } else {
        score += value.length > this.length ? 100 / mods : 100 / mods / this.length * value.length;
      }

      return score;
    }
  }], [{
    key: "calc",
    value: function calc(value, pattern, length, mods) {
      var count = value.match(pattern);

      if (count && count.length > length && length !== 0) {
        return 100 / mods;
      }

      if (count && length > 0) {
        return 100 / mods / length * count.length;
      }

      return 0;
    }
  }]);

  return PasswordStrength;
}();
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


(function (Joomla, document) {
  // Method to check the input and set the meter
  var getMeter = function getMeter(element) {
    var meter = document.querySelector('meter');
    var minLength = element.getAttribute('data-min-length');
    var minIntegers = element.getAttribute('data-min-integers');
    var minSymbols = element.getAttribute('data-min-symbols');
    var minUppercase = element.getAttribute('data-min-uppercase');
    var minLowercase = element.getAttribute('data-min-lowercase');
    var strength = new PasswordStrength({
      lowercase: minLowercase || 0,
      uppercase: minUppercase || 0,
      numbers: minIntegers || 0,
      special: minSymbols || 0,
      length: minLength || 4
    });
    var score = strength.getScore(element.value);
    var i = meter.getAttribute('id').replace(/^\D+/g, '');
    var label = element.parentNode.parentNode.querySelector("#password-".concat(i));

    if (score > 79) {
      label.innerText = Joomla.JText._('JFIELD_PASSWORD_INDICATE_COMPLETE');
    }

    if (score > 64 && score < 80) {
      label.innerText = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE');
    }

    if (score > 50 && score < 65) {
      label.innerText = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE');
    }

    if (score > 40 && score < 51) {
      label.innerText = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE');
    }

    if (score < 41) {
      label.innerText = Joomla.JText._('JFIELD_PASSWORD_INDICATE_INCOMPLETE');
    }

    meter.value = score;

    if (!element.value.length) {
      label.innerText = '';
      element.setAttribute('required', '');
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    var fields = [].slice.call(document.querySelectorAll('.js-password-strength')); // Loop  through the fields

    fields.forEach(function (field, index) {
      var initialVal = '';

      if (!field.value.length) {
        initialVal = 0;
      } // Create a progress meter and the label


      var meter = document.createElement('meter');
      meter.setAttribute('id', "progress-".concat(index));
      meter.setAttribute('min', 0);
      meter.setAttribute('max', 100);
      meter.setAttribute('low', 40);
      meter.setAttribute('high', 60);
      meter.setAttribute('optimum', 80);
      meter.value = initialVal;
      var label = document.createElement('div');
      label.setAttribute('class', 'text-center');
      label.setAttribute('id', "password-".concat(index));
      label.setAttribute('aria-live', 'polite');
      field.parentNode.insertAdjacentElement('afterEnd', label);
      field.parentNode.insertAdjacentElement('afterEnd', meter); // Add a data attribute for the required

      if (field.value.length > 0) {
        field.setAttribute('required', true);
      } // Add a listener for input data change


      field.addEventListener('keyup', function (_ref) {
        var target = _ref.target;
        getMeter(target);
      });
    }); // Set a handler for the validation script

    if (fields[0]) {
      document.formvalidator.setHandler('password-strength', function (value) {
        var strengthElements = document.querySelectorAll('.js-password-strength');
        var minLength = strengthElements[0].getAttribute('data-min-length');
        var minIntegers = strengthElements[0].getAttribute('data-min-integers');
        var minSymbols = strengthElements[0].getAttribute('data-min-symbols');
        var minUppercase = strengthElements[0].getAttribute('data-min-uppercase');
        var minLowercase = strengthElements[0].getAttribute('data-min-lowercase');
        var strength = new PasswordStrength({
          lowercase: minLowercase || 0,
          uppercase: minUppercase || 0,
          numbers: minIntegers || 0,
          special: minSymbols || 0,
          length: minLength || 4
        });
        var score = strength.getScore(value);

        if (score === 100) {
          return true;
        }

        return false;
      });
    }
  });
})(Joomla, document);