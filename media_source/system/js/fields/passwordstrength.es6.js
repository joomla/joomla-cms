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
  const getMeter = (element, meter) => {
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

    let message = '';
    let instruction = '';

    if (!element.value.length) {
      meter.value = 0;
      element.setAttribute('required', '');

      return { message, instruction };
    }

    if (element.value !== element.value.trim()) {
      message = Joomla.Text._('JFIELD_PASSWORD_SPACES_IN_PASSWORD');
      meter.value = 0;
    } else {
      message = score === 100
        ? Joomla.Text._('JFIELD_PASSWORD_INDICATE_COMPLETE')
        : Joomla.Text._('JFIELD_PASSWORD_INDICATE_INCOMPLETE');

      meter.value = score;
    }

    if (
      element.id === 'jform_admin_password'
      && element.value.length < strength.length
    ) {
      const description = document.getElementById(`${element.id}-desc`);
      const requirement = description ? description.textContent.trim() : '';

      if (requirement) {
        const characterCount = Joomla.Text._(
          'INSTL_ADMIN_PASSWORD_CHARACTERS_ENTERED',
        )
          .replace(/%1\$s/g, () => String(element.value.length))
          .replace(/%2\$s/g, () => String(strength.length));

        instruction = `${requirement} ${characterCount}`;
      }
    }

    return { message, instruction };
  };

  const initialize = () => {
    const fields = document.querySelectorAll('.js-password-strength');

    // Loop  through the fields
    fields.forEach((field, index) => {
      if (field.dataset.passwordStrengthInitialized === 'true') {
        return;
      }

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

      const label = document.createElement('output');
      label.setAttribute('id', `password-${index}`);
      label.setAttribute('for', field.id);
      label.setAttribute('role', 'status');
      label.setAttribute('aria-live', 'polite');
      label.setAttribute('aria-atomic', 'true');

      const visibleStatus = document.createElement('span');
      visibleStatus.setAttribute('aria-hidden', 'true');

      const spokenInstruction = document.createElement('span');
      spokenInstruction.classList.add('visually-hidden');

      label.appendChild(visibleStatus);
      label.appendChild(spokenInstruction);
      field.parentNode.insertAdjacentElement('afterEnd', label);
      field.parentNode.insertAdjacentElement('afterEnd', meter);
      field.dataset.passwordStrengthInitialized = 'true';

      // Add a data attribute for the required
      if (field.value.length > 0) {
        field.setAttribute('required', true);
      }

      // Add a listener for input data change (status feedback)
      let feedbackTimer = null;
      let lastFeedbackTime = 0;
      let pendingFeedback = null;
      let previousStatus = '';

      const renderFeedback = () => {
        feedbackTimer = null;

        if (!pendingFeedback) {
          return;
        }

        const { message, instruction } = pendingFeedback;
        pendingFeedback = null;

        const statusChanged = message !== previousStatus;
        let spokenMessage = '';

        if (message) {
          if (instruction) {
            spokenMessage = statusChanged
              ? `${message} ${instruction}`
              : instruction;
          } else {
            spokenMessage = message;
          }
        }

        if (
          visibleStatus.textContent !== message
          || spokenInstruction.textContent !== spokenMessage
        ) {
          label.setAttribute('aria-busy', 'true');
          visibleStatus.textContent = message;
          spokenInstruction.textContent = spokenMessage;
          label.setAttribute('aria-busy', 'false');
        }

        previousStatus = message;
        lastFeedbackTime = Date.now();
      };

      field.addEventListener('input', () => {
        pendingFeedback = getMeter(field, meter);

        if (!field.value.length || !pendingFeedback.instruction) {
          window.clearTimeout(feedbackTimer);
          feedbackTimer = null;
          renderFeedback();
          return;
        }

        if (feedbackTimer !== null) {
          return;
        }

        const delay = Math.max(
          0,
          500 - (Date.now() - lastFeedbackTime),
        );

        feedbackTimer = window.setTimeout(renderFeedback, delay);
      });

      field.addEventListener('blur', () => {
        window.clearTimeout(feedbackTimer);
        feedbackTimer = null;

        if (pendingFeedback) {
          renderFeedback();
        }
      });
    });

    // Set a handler for the validation script
    if (fields[0] && document.formvalidator) {
      document.formvalidator.setHandler('password-strength', (value) => {
        if (value !== value.trim()) {
          return false;
        }

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

        return strength.getScore(value) === 100;
      });
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
  } else {
    initialize();
  }
})(Joomla, document);
