/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  'use strict';

  var JFormValidator = /*#__PURE__*/function () {
    function JFormValidator() {
      var _this = this;

      _classCallCheck(this, JFormValidator);

      this.customValidators = {};
      this.handlers = [];
      this.handlers = {};
      this.removeMarking = this.removeMarking.bind(this);

      this.inputEmail = function () {
        var input = document.createElement('input');
        input.setAttribute('type', 'email');
        return input.type !== 'text';
      }; // Default handlers


      this.setHandler('username', function (value) {
        var regex = new RegExp('[<|>|"|\'|%|;|(|)|&]', 'i');
        return !regex.test(value);
      });
      this.setHandler('password', function (value) {
        var regex = /^\S[\S ]{2,98}\S$/;
        return regex.test(value);
      });
      this.setHandler('numeric', function (value) {
        var regex = /^(\d|-)?(\d|,)*\.?\d*$/;
        return regex.test(value);
      });
      this.setHandler('email', function (value) {
        var newValue = window.punycode.toASCII(value);
        var regex = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        return regex.test(newValue);
      }); // Attach all forms with a class 'form-validate'

      var forms = [].slice.call(document.querySelectorAll('form'));
      forms.forEach(function (form) {
        if (form.classList.contains('form-validate')) {
          _this.attachToForm(form);
        }
      });
    }

    _createClass(JFormValidator, [{
      key: "setHandler",
      value: function setHandler(name, func, en) {
        var isEnabled = en === '' ? true : en;
        this.handlers[name] = {
          enabled: isEnabled,
          exec: func
        };
      } // eslint-disable-next-line class-methods-use-this

    }, {
      key: "markValid",
      value: function markValid(element) {
        // Get a label
        var label = element.form.querySelector("label[for=\"".concat(element.id, "\"]"));
        var message;

        if (element.classList.contains('required') || element.getAttribute('required')) {
          if (label) {
            message = label.querySelector('span.form-control-feedback');
          }
        }

        element.classList.remove('form-control-danger');
        element.classList.remove('invalid');
        element.classList.add('form-control-success');
        element.parentNode.classList.remove('has-danger');
        element.parentNode.classList.add('has-success');
        element.setAttribute('aria-invalid', 'false'); // Remove message

        if (message) {
          message.parentNode.removeChild(message);
        } // Restore Label


        if (label) {
          label.classList.remove('invalid');
        }
      } // eslint-disable-next-line class-methods-use-this

    }, {
      key: "markInvalid",
      value: function markInvalid(element, empty) {
        // Get a label
        var label = element.form.querySelector("label[for=\"".concat(element.id, "\"]"));
        element.classList.remove('form-control-success');
        element.classList.remove('valid');
        element.classList.add('form-control-danger');
        element.classList.add('invalid');
        element.parentNode.classList.remove('has-success');
        element.parentNode.classList.add('has-danger');
        element.setAttribute('aria-invalid', 'true'); // Display custom message

        var mesgCont;
        var message = element.getAttribute('data-validation-text');

        if (label) {
          mesgCont = label.querySelector('span.form-control-feedback');
        }

        if (!mesgCont) {
          var elMsg = document.createElement('span');
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
        } // Mark the Label as well


        if (label) {
          label.classList.add('invalid');
        }
      } // eslint-disable-next-line class-methods-use-this

    }, {
      key: "removeMarking",
      value: function removeMarking(element) {
        // Get the associated label
        var message;
        var label = element.form.querySelector("label[for=\"".concat(element.id, "\"]"));

        if (label) {
          message = label.querySelector('span.form-control-feedback');
        }

        element.classList.remove('form-control-danger');
        element.classList.remove('form-control-success');
        element.classList.remove('invalid');
        element.classList.add('valid');
        element.parentNode.classList.remove('has-danger');
        element.parentNode.classList.remove('has-success'); // Remove message

        if (message) {
          if (label) {
            label.removeChild(message);
          }
        } // Restore Label


        if (label) {
          label.classList.remove('invalid');
        }
      }
    }, {
      key: "handleResponse",
      value: function handleResponse(state, element, empty) {
        // Set the element and its label (if exists) invalid state
        if (element.tagName.toLowerCase() !== 'button' && element.value !== undefined) {
          if (state === false) {
            this.markInvalid(element, empty);
          } else {
            this.markValid(element);
          }
        }
      }
    }, {
      key: "validate",
      value: function validate(element) {
        var tagName; // Ignore the element if its currently disabled,
        // because are not submitted for the http-request.
        // For those case return always true.

        if (element.getAttribute('disabled') === 'disabled' || element.getAttribute('display') === 'none') {
          this.handleResponse(true, element);
          return true;
        } // If the field is required make sure it has a value


        if (element.getAttribute('required') || element.classList.contains('required')) {
          tagName = element.tagName.toLowerCase();

          if (tagName === 'fieldset' && (element.classList.contains('radio') || element.classList.contains('checkboxes'))) {
            if (!element.querySelector('input:checked').length) {
              this.handleResponse(false, element, 'checkbox');
              return false;
            }
          } else if (element.getAttribute('type') === 'checkbox' && element.checked !== true || tagName === 'select' && !element.value.length) {
            this.handleResponse(false, element, 'checkbox');
            return false;
          } else if (!element.value || element.classList.contains('placeholder')) {
            // If element has class placeholder that means it is empty.
            this.handleResponse(false, element, 'value');
            return false;
          }
        } // Only validate the field if the validate class is set


        var handler = element.getAttribute('class') && element.getAttribute('class').match(/validate-([a-zA-Z0-9_-]+)/) ? element.getAttribute('class').match(/validate-([a-zA-Z0-9_-]+)/)[1] : '';

        if (element.getAttribute('pattern') && element.getAttribute('pattern') !== '') {
          if (element.value.length) {
            var isValid = new RegExp("^".concat(element.getAttribute('pattern'), "$")).test(element.value);
            this.handleResponse(isValid, element, 'empty');
            return isValid;
          }

          if (element.hasAttribute('required') || element.classList.contains('required')) {
            this.handleResponse(false, element, 'empty');
            return false;
          }

          this.handleResponse(true, element);
          return false;
        }

        if (handler === '') {
          this.handleResponse(true, element);
          return true;
        } // Check the additional validation types


        if (handler && handler !== 'none' && this.handlers[handler] && element.value) {
          // Execute the validation handler and return result
          if (this.handlers[handler].exec(element.value, element) !== true) {
            this.handleResponse(false, element, 'invalid_value');
            return false;
          }
        } // Return validation state


        this.handleResponse(true, element);
        return true;
      }
    }, {
      key: "isValid",
      value: function isValid(form) {
        var _this2 = this;

        var valid = true;
        var message;
        var error;
        var invalid = []; // Validate form fields

        var fields = [].slice.call(form.querySelectorAll('input, textarea, select, button'));
        fields.forEach(function (field) {
          if (_this2.validate(field) === false) {
            valid = false;
            invalid.push(field);
          }
        }); // Run custom form validators if present

        if (Object.keys(this.customValidators).length) {
          Object.keys(this.customValidators).foreach(function (key) {
            if (_this2.customValidators[key].exec() !== true) {
              valid = false;
            }
          });
        }

        if (!valid && invalid.length > 0) {
          if (form.getAttribute('data-validation-text')) {
            message = form.getAttribute('data-validation-text');
          } else {
            message = Joomla.JText._('JLIB_FORM_CONTAINS_INVALID_FIELDS');
          }

          error = {
            error: [message]
          };
          Joomla.renderMessages(error);
        }

        return valid;
      }
    }, {
      key: "attachToForm",
      value: function attachToForm(form) {
        var _this3 = this;

        var inputFields = [];
        var elements = [].slice.call(form.querySelectorAll('input, textarea, select, button, fieldset')); // Iterate through the form object and attach the validate method to all input fields.

        elements.forEach(function (element) {
          var tagName = element.tagName.toLowerCase();

          if (['input', 'textarea', 'select', 'fieldset'].indexOf(tagName) > -1 && element.classList.contains('required')) {
            element.setAttribute('required', '');
          } // Attach isValid method to submit button


          if ((tagName === 'input' || tagName === 'button') && (element.getAttribute('type') === 'submit' || element.getAttribute('type') === 'image')) {
            if (element.classList.contains('validate')) {
              element.addEventListener('click', function () {
                return _this3.isValid(form);
              });
            }
          } else if (tagName !== 'button' && !(tagName === 'input' && element.getAttribute('type') === 'button')) {
            // Attach validate method only to fields
            if (tagName !== 'fieldset') {
              element.addEventListener('blur', function (_ref) {
                var target = _ref.target;
                return _this3.validate(target);
              });
              element.addEventListener('focus', function (_ref2) {
                var target = _ref2.target;
                return _this3.removeMarking(target);
              });

              if (element.classList.contains('validate-email') && _this3.inputEmail) {
                element.setAttribute('type', 'email');
              }
            }

            inputFields.push(element);
          }
        });
      }
    }, {
      key: "custom",
      get: function get() {
        return this.customValidators;
      },
      set: function set(value) {
        this.customValidators = value;
      }
    }]);

    return JFormValidator;
  }();

  var initialize = function initialize() {
    document.formvalidator = new JFormValidator(); // Cleanup

    document.removeEventListener('DOMContentLoaded', initialize);
  };

  document.addEventListener('DOMContentLoaded', initialize);
})(document);