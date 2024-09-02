/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

'use strict';

import punycode from 'punycode';

class JFormValidator {
  constructor() {
    this.customValidators = {};
    this.handlers = [];
    this.handlers = {};
    this.removeMarking = this.removeMarking.bind(this);
    this.inputEmail = () => {
      const input = document.createElement('input');
      input.setAttribute('type', 'email');
      return input.type !== 'text';
    };

    // Default handlers
    this.setHandler('username', (value) => {
      const regex = /[<|>|"|'|%|;|(|)|&]/i;
      return !regex.test(value);
    });
    this.setHandler('password', (value) => {
      const regex = /^\S[\S ]{2,98}\S$/;
      return regex.test(value);
    });
    this.setHandler('numeric', (value) => {
      const regex = /^(\d|-)?(\d|,)*\.?\d*$/;
      return regex.test(value);
    });
    this.setHandler('email', (value) => {
      const newValue = punycode.toASCII(value);
      const regex = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
      return regex.test(newValue);
    });

    // Attach all forms with a class 'form-validate'
    document.querySelectorAll('form').forEach((form) => {
      if (form.classList.contains('form-validate')) {
        this.attachToForm(form);
      }
    });
  }

  get custom() {
    return this.customValidators;
  }

  set custom(value) {
    this.customValidators = value;
  }

  setHandler(name, func, en) {
    const isEnabled = (en === '') ? true : en;
    this.handlers[name] = {
      enabled: isEnabled,
      exec: func,
    };
  }

  // eslint-disable-next-line class-methods-use-this
  markValid(element) {
    // Get a label
    const label = element.form.querySelector(`label[for="${element.id}"]`);
    let message;

    if (element.classList.contains('required') || element.getAttribute('required')) {
      if (label) {
        message = label.querySelector('span.form-control-feedback');
      }
    }

    element.classList.remove('form-control-danger', 'invalid');
    element.classList.add('form-control-success');
    element.parentNode.classList.remove('has-danger');
    element.parentNode.classList.add('has-success');
    element.setAttribute('aria-invalid', 'false');

    // Remove message
    if (message) {
      message.parentNode.removeChild(message);
    }

    // Restore Label
    if (label) {
      label.classList.remove('invalid');
    }
  }

  // eslint-disable-next-line class-methods-use-this
  markInvalid(element, empty) {
    // Get a label
    const label = element.form.querySelector(`label[for="${element.id}"]`);

    element.classList.remove('form-control-success', 'valid');
    element.classList.add('form-control-danger', 'invalid');
    element.parentNode.classList.remove('has-success');
    element.parentNode.classList.add('has-danger');
    element.setAttribute('aria-invalid', 'true');

    // Display custom message
    let mesgCont;
    const message = element.getAttribute('data-validation-text');

    if (label) {
      mesgCont = label.querySelector('span.form-control-feedback');
    }

    if (!mesgCont) {
      const elMsg = document.createElement('span');
      elMsg.classList.add('form-control-feedback');
      if (empty && empty === 'checkbox') {
        elMsg.innerHTML = message !== null ? Joomla.sanitizeHtml(message) : Joomla.sanitizeHtml(Joomla.Text._('JLIB_FORM_FIELD_REQUIRED_CHECK'));
      } else if (empty && empty === 'value') {
        elMsg.innerHTML = message !== null ? Joomla.sanitizeHtml(message) : Joomla.sanitizeHtml(Joomla.Text._('JLIB_FORM_FIELD_REQUIRED_VALUE'));
      } else {
        elMsg.innerHTML = message !== null ? Joomla.sanitizeHtml(message) : Joomla.sanitizeHtml(Joomla.Text._('JLIB_FORM_FIELD_INVALID_VALUE'));
      }

      if (label) {
        label.appendChild(elMsg);
      }
    }

    // Mark the Label as well
    if (label) {
      label.classList.add('invalid');
    }
  }

  // eslint-disable-next-line class-methods-use-this
  removeMarking(element) {
    // Get the associated label
    let message;
    const label = element.form.querySelector(`label[for="${element.id}"]`);

    if (label) {
      message = label.querySelector('span.form-control-feedback');
    }

    element.classList.remove('form-control-danger', 'form-control-success', 'remove');
    element.classList.add('valid');
    element.parentNode.classList.remove('has-danger', 'has-success');

    // Remove message
    if (message && label) {
      label.removeChild(message);
    }

    // Restore Label
    if (label) {
      label.classList.remove('invalid');
    }
  }

  handleResponse(state, element, empty) {
    const tagName = element.tagName.toLowerCase();

    // Set the element and its label (if exists) invalid state
    if ((tagName !== 'button' && element.value !== undefined) || tagName === 'fieldset') {
      if (state === false) {
        this.markInvalid(element, empty);
      } else {
        this.markValid(element);
      }
    }
  }

  validate(element) {
    let tagName;

    // Ignore the element if its currently disabled,
    // because are not submitted for the http-request.
    // For those case return always true.
    if (element.getAttribute('disabled') === 'disabled' || element.getAttribute('display') === 'none') {
      this.handleResponse(true, element);
      return true;
    }
    // If the field is required make sure it has a value
    if (element.getAttribute('required') || element.classList.contains('required')) {
      tagName = element.tagName.toLowerCase();
      if (tagName === 'fieldset' && (element.classList.contains('radio') || element.classList.contains('checkboxes'))) {
        // No options are checked.
        if (element.querySelector('input:checked') === null) {
          this.handleResponse(false, element, 'checkbox');
          return false;
        }
      } else if ((element.getAttribute('type') === 'checkbox' && element.checked !== true) || (tagName === 'select' && !element.value.length)) {
        this.handleResponse(false, element, 'checkbox');
        return false;
      } else if (!element.value || element.classList.contains('placeholder')) {
        // If element has class placeholder that means it is empty.
        this.handleResponse(false, element, 'value');
        return false;
      }
    }

    // Only validate the field if the validate class is set
    const handler = (element.getAttribute('class') && element.getAttribute('class').match(/validate-([a-zA-Z0-9_-]+)/)) ? element.getAttribute('class').match(/validate-([a-zA-Z0-9_-]+)/)[1] : '';

    if (element.getAttribute('pattern') && element.getAttribute('pattern') !== '') {
      if (element.value.length) {
        const isValid = new RegExp(`^${element.getAttribute('pattern')}$`).test(element.value);
        this.handleResponse(isValid, element, 'empty');
        return isValid;
      }

      if (element.hasAttribute('required') || element.classList.contains('required')) {
        this.handleResponse(false, element, 'empty');
        return false;
      }

      this.handleResponse(true, element);
      return true;
    }

    if (handler === '') {
      this.handleResponse(true, element);
      return true;
    }

    // Check the additional validation types
    if ((handler) && (handler !== 'none') && (this.handlers[handler]) && element.value) {
      // Execute the validation handler and return result
      if (this.handlers[handler].exec(element.value, element) !== true) {
        this.handleResponse(false, element, 'invalid_value');
        return false;
      }
    }

    // Return validation state
    this.handleResponse(true, element);
    return true;
  }

  isValid(form) {
    let valid = true;
    let message;
    let error;
    let fields;
    const invalid = [];

    // Validate form fields
    if (form.nodeName === 'FORM') {
      fields = [].slice.call(form.elements);
    } else {
      fields = form.querySelectorAll('input, textarea, select, button, fieldset');
    }
    fields.forEach((field) => {
      if (this.validate(field) === false) {
        valid = false;
        invalid.push(field);
      }
    });

    // Run custom form validators if present
    if (Object.keys(this.customValidators).length) {
      Object.keys(this.customValidators).foreach((key) => {
        if (this.customValidators[key].exec() !== true) {
          valid = false;
        }
      });
    }

    if (!valid && invalid.length > 0) {
      if (form.getAttribute('data-validation-text')) {
        message = form.getAttribute('data-validation-text');
      } else {
        message = Joomla.Text._('JLIB_FORM_CONTAINS_INVALID_FIELDS');
      }

      error = { error: [message] };
      Joomla.renderMessages(error);
    }
    return valid;
  }

  attachToForm(form) {
    const inputFields = [];
    let elements;

    if (form.nodeName === 'FORM') {
      elements = [].slice.call(form.elements);
    } else {
      elements = form.querySelectorAll('input, textarea, select, button, fieldset');
    }

    // Iterate through the form object and attach the validate method to all input fields.
    elements.forEach((element) => {
      const tagName = element.tagName.toLowerCase();

      if (['input', 'textarea', 'select', 'fieldset'].indexOf(tagName) > -1 && element.classList.contains('required')) {
        element.setAttribute('required', '');
      }

      // Attach isValid method to submit button
      if ((tagName === 'input' || tagName === 'button') && (element.getAttribute('type') === 'submit' || element.getAttribute('type') === 'image')) {
        if (element.classList.contains('validate')) {
          element.addEventListener('click', () => this.isValid(form));
        }
      } else if (tagName !== 'button' && !(tagName === 'input' && element.getAttribute('type') === 'button')) {
        // Attach validate method only to fields
        if (tagName !== 'fieldset') {
          element.addEventListener('blur', ({ target }) => this.validate(target));
          element.addEventListener('focus', ({ target }) => this.removeMarking(target));
          if (element.classList.contains('validate-email') && this.inputEmail) {
            element.setAttribute('type', 'email');
          }
        }
        inputFields.push(element);
      }
    });
  }
}

const initialize = () => {
  document.formvalidator = new JFormValidator();

  // Cleanup
  document.removeEventListener('DOMContentLoaded', initialize);
};

document.addEventListener('DOMContentLoaded', initialize);

/**
 * Expose the classes to the global scope
 * These will be removed in Joomla! 6.0
 */
window.JFormValidator = JFormValidator;
window.punycode = punycode;
