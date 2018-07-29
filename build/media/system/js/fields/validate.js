/**
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @since  1.5
 */
const JFormValidator = function () {
  'use strict';

  let handlers; let inputEmail; let custom;


  const forEach = function (arr, f) {
    for (let i = 0, e = arr.length; i < e; ++i) f(arr[i], i);
  };


  const setHandler = function (name, fn, en) {
    en = (en === '') ? true : en;
    handlers[name] = {
      enabled: en,
      exec: fn,
    };
  };


  const handleResponse = function (state, el, empty) {
    // Set the element and its label (if exists) invalid state
    if (el.tagName.toLowerCase() !== 'button' && el.value !== 'undefined') {
      if (state === false) {
        markInvalid(el, empty);
      } else {
        markValid(el);
      }
    }
  };


  var markValid = function (el) {
    // Get a label
    const label = el.form.querySelector(`label[for="${el.id}"]`);

    if (el.classList.contains('required') || el.getAttribute('required')) {
      var message;
      if (label) {
        message = label.querySelector('span.form-control-feedback');
      }
    }

    el.classList.remove('form-control-danger');
    el.classList.add('form-control-success');
    el.parentNode.classList.remove('has-danger');
    el.parentNode.classList.add('has-success');
    el.setAttribute('aria-invalid', 'false');

    // Remove message
    if (message) {
      message.parentNode.removeChild(message);
    }

    // Restore Label
    if (label) {
      label.classList.remove('invalid');
    }
  };


  var markInvalid = function (el, empty) {
    // Get a label
    const label = el.form.querySelector(`label[for="${el.id}"]`);

    el.classList.remove('form-control-success');
    el.classList.add('form-control-danger');
    el.classList.add('invalid');
    el.parentNode.classList.remove('has-success');
    el.parentNode.classList.add('has-danger');
    el.setAttribute('aria-invalid', 'true');

    // Display custom message
    let mesgCont; const
      message = el.getAttribute('data-validation-text');

    if (label) {
      mesgCont = label.querySelector('span.form-control-feedback');
    }

    if (!mesgCont) {
      const elMsg = document.createElement('span');
      elMsg.classList.add('form-control-feedback');
      if (empty && empty === 'checkbox') {
        elMsg.innerHTML = message || Joomla.JText._('JLIB_FORM_FIELD_REQUIRED_CHECK');
      } else if (empty && empty === 'value') {
        elMsg.innerHTML = message || Joomla.JText._('JLIB_FORM_FIELD_REQUIRED_VALUE');
      } else {
        elMsg.innerHTML = message || Joomla.JText._('JLIB_FORM_FIELD_INVALID_VALUE');
      }

      if (label) {
        label.appendChild(elMsg);
      }
    }

    // Mark the Label as well
    if (label) {
      label.classList.add('invalid');
    }
  };


  const removeMarking = function (el) {
    // Get a label
    let message; const
      label = el.form.querySelector(`label[for="${el.id}"]`);

    if (label) {
      message = label.querySelector('span.form-control-feedback');
    }

    el.classList.remove('form-control-danger');
    el.classList.remove('form-control-success');
    el.classList.remove('invalid');
    el.classList.add('valid');
    el.parentNode.classList.remove('has-danger');
    el.parentNode.classList.remove('has-success');

    // Remove message
    if (message) {
      if (label) {
        label.removeChild(message);
      }
    }

    // Restore Label
    if (label) {
      label.classList.remove('invalid');
    }
  };


  const validate = function (el) {
    let tagName; let
      handler;
    // Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
    if (el.getAttribute('disabled') === 'disabled' || el.getAttribute('display') === 'none') {
      handleResponse(true, el);
      return true;
    }
    // If the field is required make sure it has a value
    if (el.getAttribute('required') || el.classList.contains('required')) {
      tagName = el.tagName.toLowerCase();
      if (tagName === 'fieldset' && (el.classList.contains('radio') || el.classList.contains('checkboxes'))) {
        if (!el.querySelector('input:checked').length) {
          handleResponse(false, el, 'checkbox');
          return false;
        }
      } else if ((el.getAttribute('type') === 'checkbox' && !el.checked.length !== 0) || (tagName === 'select' && !el.value.length)) {
        handleResponse(false, el, 'checkbox');
        return false;
        // If element has class placeholder that means it is empty.
      } else if (!el.value || el.classList.contains('placeholder')) {
        handleResponse(false, el, 'value');
        return false;
      }
    }

    // Only validate the field if the validate class is set
    handler = (el.getAttribute('class') && el.getAttribute('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? el.getAttribute('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : '';

    if (el.getAttribute('pattern') && el.getAttribute('pattern') != '') {
      if (el.value.length) {
        isValid = new RegExp(`^${el.getAttribute('pattern')}$`).test(el.value);
        handleResponse(isValid, el, 'empty');
        return isValid;
      }
      handleResponse(false, el);
      return false;
    }
    if (handler === '') {
      handleResponse(true, el);
      return true;
    }

    // Check the additional validation types
    if ((handler) && (handler !== 'none') && (handlers[handler]) && el.value) {
      // Execute the validation handler and return result
      if (handlers[handler].exec(el.value, el) !== true) {
        handleResponse(false, el, 'invalid_value');
        return false;
      }
    }

    // Return validation state
    handleResponse(true, el);
    return true;
  };


  var isValid = function (form) {
    let fields; let valid = true; let message; let error; let label; const invalid = []; let i; let
      l;
    // Validate form fields
    fields = form.querySelectorAll('input, textarea, select, button');
    for (i = 0, l = fields.length; i < l; i++) {
      if (validate(fields[i]) === false) {
        valid = false;
        invalid.push(fields[i]);
      }
    }

    // Run custom form validators if present
    for (const prop in custom) {
      if (custom.hasOwnProperty(prop)) {
        if (custom[validator].exec() !== true) {
          valid = false;
        }
      }
    }

    forEach(custom, (key, validator) => {
      if (validator.exec() !== true) {
        valid = false;
      }
    });

    if (!valid && invalid.length > 0) {
      if (form.getAttribute('data-validation-text')) {
        message = form.getAttribute('data-validation-text');
      } else {
        message = Joomla.JText._('JLIB_FORM_CONTAINS_INVALID_FIELDS');
      }

      error = { error: [message] };
      Joomla.renderMessages(error);
    }
    return valid;
  };


  const attachToForm = function (form) {
    const inputFields = []; let
      elements;
    // Iterate through the form object and attach the validate method to all input fields.
    elements = form.querySelectorAll('input, textarea, select, button, fieldset');
    for (let i = 0, l = elements.length; i < l; i++) {
      const el = elements[i]; const
        tagName = el.tagName.toLowerCase();

      if (['input', 'textarea', 'select', 'fieldset'].indexOf(tagName) > -1 && el.classList.contains('required')) {
        el.setAttribute('required', '');
      }

      // Attach isValid method to submit button
      if ((tagName === 'input' || tagName === 'button') && (el.getAttribute('type') === 'submit' || el.getAttribute('type') === 'image')) {
        if (el.classList.contains('validate')) {
          el.addEventListener('click', () => isValid(form));
        }
      }

      // Attach validate method only to fields
      else if (tagName !== 'button' && !(tagName === 'input' && el.getAttribute('type') === 'button')) {
        if (tagName !== 'fieldset') {
          el.addEventListener('blur', function () {
            return validate(this);
          });
          el.addEventListener('focus', function () {
            return removeMarking(this);
          });
          if (el.classList.contains('validate-email') && inputEmail) {
            el.setAttribute('type', 'email');
          }
        }
        inputFields.push(el);
      }
    }
  };


  const initialize = function () {
    handlers = {};
    custom = custom || {};

    inputEmail = (function () {
      const input = document.createElement('input');
      input.setAttribute('type', 'email');
      return input.type !== 'text';
    }());

    // Default handlers
    setHandler('username', (value, element) => {
      const regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", 'i');
      return !regex.test(value);
    });
    setHandler('password', (value, element) => {
      const regex = /^\S[\S ]{2,98}\S$/;
      return regex.test(value);
    });
    setHandler('numeric', (value, element) => {
      const regex = /^(\d|-)?(\d|,)*\.?\d*$/;
      return regex.test(value);
    });
    setHandler('email', (value, element) => {
      value = punycode.toASCII(value);
      const regex = /^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
      return regex.test(value);
    });

    // Attach to forms with class 'form-validate'
    const forms = document.querySelectorAll('form');
    for (let i = 0, l = forms.length; i < l; i++) {
      if (forms[i].classList.contains('form-validate')) {
        attachToForm(forms[i]);
      }
    }
  };

  // Initialize handlers and attach validation to form
  initialize();

  return {
    isValid,
    validate,
    setHandler,
    attachToForm,
    custom,
  };
};

document.formvalidator = null;

document.addEventListener('DOMContentLoaded', () => {
  document.formvalidator = new JFormValidator();
});
