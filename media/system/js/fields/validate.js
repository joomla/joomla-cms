/**
 * @copyright	Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @since  1.5
 */
function forEach(arr, f) {
	for (var i = 0, e = arr.length; i < e; ++i) f(arr[i], i);
}

var JFormValidator = function() {
	"use strict";
	var handlers, inputEmail, custom,

		setHandler = function(name, fn, en) {
			en = (en === '') ? true : en;
			handlers[name] = {
				enabled : en,
				exec : fn
			};
		},

		findLabel = function(id, form){
			var label;
			if (!id) {
				return false;
			}
			// This is old syntax for labels, do we need to support it?
			label = form.querySelector('#' + id + '-lbl');
			if (label.length) {
			 	return label;
			}
			label = form.querySelector('label[for="' + id + '"]');
			if (label.length) {
				return label;
			}
			return false;
		},

		handleResponse = function(state, $el) {
			// Get a label
			var label = $el.getAttribute('data-label');
			if (label === undefined) {
				label = findLabel($el.id, $el[0].form);
				$el.setAttribute('data-label', label);
			}

			// Set the element and its label (if exists) invalid state
			if (state === false) {
				$el.classList.add('invalid');
			$el.setAttribute('aria-invalid', 'true');
				if (label) {
					label.classList.add('invalid');
				}
			} else {
				$el.classList.remove('invalid');
				$el.setAttribute('aria-invalid', 'false');
				if (label) {
					label.classList.remove('invalid');
				}
			}
		},

		validate = function(el) {
			var $el = el, tagName, handler;
			// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
			if ($el.getAttribute('disabled') == 'disabled' || $el.getAttribute('display') == 'none') {
				handleResponse(true, $el);
				return true;
			}
			// If the field is required make sure it has a value
			if ($el.getAttribute('required') || $el.classList.contains('required')) {
				tagName = $el.tagName.toLowerCase();
				if (tagName === 'fieldset' && ($el.classList.contains('radio') || $el.classList.contains('checkboxes'))) {
					if (!$el.querySelector('input:checked').length){
						handleResponse(false, $el);
						return false;
					}
					//If element has class placeholder that means it is empty.
				} else if (!$el.value || $el.classList.contains('placeholder') || ($el.getAttribute('type') === 'checkbox' && !$el.checked.length !== 0)) {
					handleResponse(false, $el);
					return false;
				}
			}
			// Only validate the field if the validate class is set
			handler = ($el.getAttribute('class') && $el.getAttribute('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? $el.getAttribute('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";

			if ($el.getAttribute('pattern') && $el.getAttribute('pattern') != '') {
				if ($el.value.length) {
					isValid = new RegExp('^' + $el.getAttribute('pattern') + '$').test($el.value);
					handleResponse(isValid, $el);
					return isValid;
				} else {
					handleResponse(false, $el);
					return false;
				}
			} else {
				if (handler === '') {
					handleResponse(true, $el);
					return true;
				}
				// Check the additional validation types
				if ((handler) && (handler !== 'none') && (handlers[handler]) && $el.value) {
					// Execute the validation handler and return result
					if (handlers[handler].exec($el.value, $el) !== true) {
						handleResponse(false, $el);
						return false;
					}
				}
				// Return validation state
				handleResponse(true, $el);
				return true;
			}
		},

		isValid = function(form) {
			var fields, valid = true, message, error, label, invalid = [], i, l;
			// Validate form fields
			fields = document.querySelectorAll('input, textarea, select, button');
			for (i = 0, l = fields.length; i < l; i++) {
				if (validate(fields[i]) === false) {
					valid = false;
					invalid.push(fields[i]);
				}
			}
			// Run custom form validators if present
			for (var prop in custom) {
				if (custom.hasOwnProperty(prop)) {
					if (custom[validator].exec() !== true) {
						valid = false;
					}
				}
			}

			forEach(custom, function(key, validator) {
				if (validator.exec() !== true) {
					valid = false;
				}
			});

			if (!valid && invalid.length > 0) {
				message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
				error = {"error": []};
				for (i = invalid.length - 1; i >= 0; i--) {
					label = (invalid[i].getAttribute("name")) ? invalid[i].getAttribute("name") : invalid[i].getAttribute("id");
					label = label.replace("jform[", "").replace("]", "").replace("_", " ");
					label = label.toLowerCase().replace( /\b./g, function(a){ return a.toUpperCase(); } );

					if (label) {
						error.error.push(message + label);
					}
				}
				Joomla.renderMessages(error);
			}
			return valid;
		},

		attachToForm = function(form) {
			var inputFields = [], elements;
			// Iterate through the form object and attach the validate method to all input fields.
			elements = form.querySelectorAll('input, textarea, select, button');
			for (var i = 0, l = elements.length; i < l; i++) {
				var $el = elements[i], tagName = $el.tagName.toLowerCase();
				// Attach isValid method to submit button
				if ((tagName === 'input' || tagName === 'button') && ($el.getAttribute('type') === 'submit' || $el.getAttribute('type') === 'image')) {
					if ($el.classList.contains('validate')) {
						$el.addEventListener('click', function() {
							return isValid(form);
						});
					}
				}
				// Attach validate method only to fields
				else if (tagName !== 'button' && !(tagName === 'input' && $el.getAttribute('type') === 'button')) {
					if ($el.classList.contains('required')) {
						$el.setAttribute('aria-required', true);
						//$el.getAttribute('required') = 'required';
					}
					if (tagName !== 'fieldset') {
						$el.addEventListener('blur', function() {
							return validate(this);
						});
						if ($el.classList.contains('validate-email') && inputEmail) {
							$el.setAttribute('type', 'email');
						}
					}
					inputFields.push($el);
				}
			}
		},

		initialize = function() {
			handlers = {};
			custom = custom || {};

			inputEmail = (function() {
				var input = document.createElement("input");
				input.setAttribute("type", "email");
				return input.type !== "text";
			})();
			// Default handlers
			setHandler('username', function(value, element) {
				var regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
				return !regex.test(value);
			});
			setHandler('password', function(value, element) {
				var regex = /^\S[\S ]{2,98}\S$/;
				return regex.test(value);
			});
			setHandler('numeric', function(value, element) {
				var regex = /^(\d|-)?(\d|,)*\.?\d*$/;
				return regex.test(value);
			});
			setHandler('email', function(value, element) {
				value = punycode.toASCII(value);
				var regex = /^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
				return regex.test(value);
			});
			// Attach to forms with class 'form-validate'
			var forms = document.querySelectorAll('form');
			for (var i = 0, l = forms.length; i < l; i++) {
				if (forms[i].classList.contains('form-validate'))
				{
					attachToForm(forms[i]);
				}
			}
		};

	// Initialize handlers and attach validation to form
	initialize();

	return {
		isValid : isValid,
		validate : validate,
		setHandler : setHandler,
		attachToForm : attachToForm,
		custom: custom
	};
};

document.formvalidator = null;

document.addEventListener("DOMContentLoaded", function() {
	document.formvalidator = new JFormValidator();
});
