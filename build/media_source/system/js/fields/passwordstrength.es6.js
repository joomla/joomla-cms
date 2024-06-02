/**
 * PasswordStrength script by Thomas Kjaergaard
 * License: MIT
 * Repo: https://github.com/tkjaergaard/Password-Strength
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Thomas Kjærgaard
 *
 * ADAPTED BY: Joomla for use in the Joomla! CMS
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
class PasswordStrength {
  constructor(settings) {
    this.lowercase = parseInt(settings.lowercase, 10) || 0;
    this.uppercase = parseInt(settings.uppercase, 10) || 0;
    this.numbers = parseInt(settings.numbers, 10) || 0;
    this.special = parseInt(settings.special, 10) || 0;
    this.length = parseInt(settings.length, 10) || 12;
  }

  getScore(value) {
    let score = 0;
    let mods = 0;
    const sets = ['lowercase', 'uppercase', 'numbers', 'special', 'length'];
    sets.forEach((set) => {
      if (this[set] > 0) {
        mods += 1;
      }
    });

    score += this.constructor.calc(value, /[a-z]/g, this.lowercase, mods);
    score += this.constructor.calc(value, /[A-Z]/g, this.uppercase, mods);
    score += this.constructor.calc(value, /[0-9]/g, this.numbers, mods);
    // eslint-disable-next-line no-useless-escape
    score += this.constructor.calc(
      value,
      /[@$!#?=;:*\-_€%&()`´+[\]{}'"\\|,.<>/~^]/g,
      this.special,
      mods,
    );

    if (mods === 1) {
      score += value.length > this.length
        ? 100
        : (100 / this.length) * value.length;
    } else {
      score += value.length > this.length
        ? (100 / mods)
        : ((100 / mods) / this.length) * value.length;
    }

    return score;
  }

  static calc(value, pattern, length, mods) {
    const count = value.match(pattern);
    if (count && count.length > length && length !== 0) {
      return 100 / mods;
    }
    if (count && length > 0) {
      return ((100 / mods) / length) * count.length;
    }

    return 0;
  }
}

/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla, document) => {
  // Method to check the input and set the meter
  const getMeter = (element) => {
    const meter = document.querySelector('meter');
    const minLength = element.getAttribute('data-min-length');
    const minIntegers = element.getAttribute('data-min-integers');
    const minSymbols = element.getAttribute('data-min-symbols');
    const minUppercase = element.getAttribute('data-min-uppercase');
    const minLowercase = element.getAttribute('data-min-lowercase');

    const strength = new PasswordStrength({
      lowercase: minLowercase || 0,
      uppercase: minUppercase || 0,
      numbers: minIntegers || 0,
      special: minSymbols || 0,
      length: minLength || 12,
    });

    const score = strength.getScore(element.value);
    const i = meter.getAttribute('id').replace(/^\D+/g, '');
    const label = element.parentNode.parentNode.querySelector(`#password-${i}`);

    if (score === 100) {
      label.innerText = Joomla.Text._('JFIELD_PASSWORD_INDICATE_COMPLETE');
    } else {
      label.innerText = Joomla.Text._('JFIELD_PASSWORD_INDICATE_INCOMPLETE');
    }
    meter.value = score;

    if (!element.value.length) {
      label.innerText = '';
      element.setAttribute('required', '');
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    const fields = document.querySelectorAll('.js-password-strength');

    // Loop  through the fields
    fields.forEach((field, index) => {
      let initialVal = '';

      if (!field.value.length) {
        initialVal = 0;
      }

      // Create a progress meter and the label
      const meter = document.createElement('meter');
      meter.setAttribute('id', `progress-${index}`);
      meter.setAttribute('min', 0);
      meter.setAttribute('max', 100);
      meter.setAttribute('low', 40);
      meter.setAttribute('high', 99);
      meter.setAttribute('optimum', 100);
      meter.value = initialVal;

      const label = document.createElement('div');
      label.setAttribute('class', 'text-center');
      label.setAttribute('id', `password-${index}`);
      label.setAttribute('aria-live', 'polite');

      field.parentNode.insertAdjacentElement('afterEnd', label);
      field.parentNode.insertAdjacentElement('afterEnd', meter);

      // Add a data attribute for the required
      if (field.value.length > 0) {
        field.setAttribute('required', true);
      }

      // Add a listener for input data change
      field.addEventListener('keyup', ({ target }) => getMeter(target));
    });

    // Set a handler for the validation script
    if (fields[0]) {
      document.formvalidator.setHandler('password-strength', (value) => {
        const strengthElements = document.querySelectorAll('.js-password-strength');
        const minLength = strengthElements[0].getAttribute('data-min-length');
        const minIntegers = strengthElements[0].getAttribute('data-min-integers');
        const minSymbols = strengthElements[0].getAttribute('data-min-symbols');
        const minUppercase = strengthElements[0].getAttribute('data-min-uppercase');
        const minLowercase = strengthElements[0].getAttribute('data-min-lowercase');

        const strength = new PasswordStrength({
          lowercase: minLowercase || 0,
          uppercase: minUppercase || 0,
          numbers: minIntegers || 0,
          special: minSymbols || 0,
          length: minLength || 12,
        });

        const score = strength.getScore(value);
        if (score === 100) {
          return true;
        }

        return false;
      });
    }
  });
})(Joomla, document);
