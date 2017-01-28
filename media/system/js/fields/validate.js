/**
 * @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @since  1.5
 */
var JFormValidator = function() {
	"use strict";
	var handlers, inputEmail, custom,

	    forEach = function(arr, f) {
		    for (var i = 0, e = arr.length; i < e; ++i) f(arr[i], i);
	    },

	    setHandler = function(name, fn, en) {
		    en = (en === '') ? true : en;
		    handlers[name] = {
			    enabled : en,
			    exec : fn
		    };
	    },

	    handleResponse = function(state, el, empty) {
		    // Set the element and its label (if exists) invalid state
		    if (state === false) {
			    markInvalid(el, empty);

		    } else {
			    markValid(el);
		    }
	    },

	    markValid = function(el) {
		    // Get a label
		    var label = el.form.querySelector('label[for="' + el.id + '"]');

		    if (el.classList.contains('required') || el.getAttribute('required')) {
			    var message;
			    if (el.parentNode.tagName.toLowerCase() === 'span' && el.parentNode.classList.contains('input-group')) {
				    message = el.parentNode.parentNode.querySelector('span.invalid')
			    } else {
				    message = el.parentNode.querySelector('span.invalid')
			    }

			    el.classList.remove('invalid');
			    el.classList.add('valid');
			    el.setAttribute('aria-invalid', 'false');

			    // Remove message
			    if (message) {
				    el.parentNode.removeChild(message);
			    }

			    // Restore Label
			    if (label) {
				    label.classList.remove('invalid');
			    }
		    }
	    },

	    markInvalid = function(el, empty) {
		    // Get a label
		    var label = el.form.querySelector('label[for="' + el.id + '"]');

		    el.classList.remove('valid');
		    el.classList.add('invalid');
		    el.setAttribute('aria-invalid', 'true');

		    // Display custom message
		    var mesgCont, message = el.getAttribute('data-validation-text');

		    if (el.parentNode.tagName.toLowerCase() === 'span' && el.parentNode.classList.contains('input-group')) {
			    mesgCont = el.parentNode.parentNode.querySelector('span.invalid')
		    } else {
			    mesgCont = el.parentNode.querySelector('span.invalid')
		    }

		    if (!mesgCont) {
			    var elMsg = document.createElement('span');
			    elMsg.classList.add('invalid');
			    if (empty && empty === 'checkbox') {
				    elMsg.innerHTML = message ? message : Joomla.JText._('JLIB_FORM_FIELD_REQUIRED_CHECK');
			    } else if (empty && empty === 'value') {
				    elMsg.innerHTML = message ? message : Joomla.JText._('JLIB_FORM_FIELD_REQUIRED_VALUE');
			    } else {
				    elMsg.innerHTML = message ? message : Joomla.JText._('JLIB_FORM_FIELD_INVALID_VALUE');
			    }

			    if (el.parentNode.tagName.toLowerCase() === 'span' && el.parentNode.classList.contains('input-group')) {
				    el.parentNode.parentNode.appendChild(elMsg)
			    } else {
				    el.parentNode.appendChild(elMsg)
			    }
		    }

		    // Mark the Label as well
		    if (label) {
			    label.classList.add('invalid');
		    }
	    },

	    removeMarking = function(el) {
		    // Get a label
		    var message, label = el.form.querySelector('label[for="' + el.id + '"]');

		    if (el.parentNode.tagName.toLowerCase() === 'span' && el.parentNode.classList.contains('input-group')) {
			    message = el.parentNode.parentNode.querySelector('span.invalid')
		    } else {
			    message = el.parentNode.querySelector('span.invalid')
		    }

		    el.classList.remove('invalid');
		    el.classList.remove('valid');

		    // Remove message
		    if (message) {
			    if (el.parentNode.tagName.toLowerCase() === 'span' && el.parentNode.classList.contains('input-group')) {
				    console.log(el.parentNode.parentNode)
				    el.parentNode.parentNode.removeChild(message);
			    } else {
				    el.parentNode.removeChild(message);
			    }
		    }

		    // Restore Label
		    if (label) {
			    label.classList.remove('invalid');
			    label.classList.remove('valid');
		    }
	    },

	    validate = function(el) {
		    debugger;
		    var tagName, handler;
		    // Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
		    if (el.getAttribute('disabled') == 'disabled' || el.getAttribute('display') == 'none') {
			    handleResponse(true, el);
			    return true;
		    }
		    // If the field is required make sure it has a value
		    if (el.getAttribute('required') || el.classList.contains('required')) {
			    tagName = el.tagName.toLowerCase();
			    if (tagName === 'fieldset' && (el.classList.contains('radio') || el.classList.contains('checkboxes'))) {
				    if (!el.querySelector('input:checked').length){
					    handleResponse(false, el, 'checkbox');
					    return false;
				    }
			    } else if ((el.getAttribute('type') === 'checkbox' && !el.checked.length !== 0) || (tagName === 'select' && !el.value.length)) {
				    handleResponse(false, el, 'checkbox');
				    return false;
				    //If element has class placeholder that means it is empty.
			    } else if (!el.value || el.classList.contains('placeholder')) {
				    handleResponse(false, el, 'value');
				    return false;
			    }
		    }
		    // Only validate the field if the validate class is set
		    handler = (el.getAttribute('class') && el.getAttribute('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? el.getAttribute('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";

		    if (el.getAttribute('pattern') && el.getAttribute('pattern') != '') {
			    if (el.value.length) {
				    isValid = new RegExp('^' + el.getAttribute('pattern') + '$').test(el.value);
				    handleResponse(isValid, el, 'empty');
				    return isValid;
			    } else {
				    handleResponse(false, el);
				    return false;
			    }
		    } else {
			    if (handler === '') {
				    handleResponse(true, el);
				    return true;
			    }
			    // Check the additional validation types
			    if ((handler) && (handler !== 'none') && (handlers[handler]) && el.value) {
				    // Execute the validation handler and return result
				    if (handlers[handler].exec(el.value, el) !== true) {
					    handleResponse(false, el, 'value');
					    return false;
				    }
			    }
			    // Return validation state
			    handleResponse(true, el);
			    return true;
		    }
	    },

	    isValid = function(form) {
		    var fields, valid = true, message, error, label, invalid = [], i, l;
		    // Validate form fields
		    fields = form.querySelectorAll('input, textarea, select, button');
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
			if (form.getAttribute('data-validation-text')) {
				    message = form.getAttribute('data-validation-text');
			    } else {
				    message = Joomla.JText._('JLIB_FORM_CONTAINS_INVALID_FIELDS');
			    }

			    error = {"error": [message]};
			    Joomla.renderMessages(error);
		    }
		    return valid;
	    },

	    attachToForm = function(form) {
		    var inputFields = [], elements;
		    // Iterate through the form object and attach the validate method to all input fields.
		    elements = form.querySelectorAll('input, textarea, select, button, fieldset');
		    for (var i = 0, l = elements.length; i < l; i++) {
			    var el = elements[i], tagName = el.tagName.toLowerCase();

			    if (['input', 'textarea', 'select', 'fieldset'].indexOf(tagName) > -1 && el.classList.contains('required')) {
				    el.setAttribute('required', '');
				    el.setAttribute('aria-required', 'true');
			    }

			    // Attach isValid method to submit button
			    if ((tagName === 'input' || tagName === 'button') && (el.getAttribute('type') === 'submit' || el.getAttribute('type') === 'image')) {

				    if (el.classList.contains('validate')) {
					    el.addEventListener('click', function() {
						    return isValid(form);
					    });
				    }
			    }
			    // Attach validate method only to fields
			    else if (tagName !== 'button' && !(tagName === 'input' && el.getAttribute('type') === 'button')) {
				    if (el.classList.contains('required')) {
					    el.setAttribute('aria-required', true);
					    //el.getAttribute('required') = 'required';
				    }
				    if (tagName !== 'fieldset') {
					    el.addEventListener('blur', function() {
						    return validate(this);
					    });
					    el.addEventListener('focus', function() {
						    return removeMarking(this);
					    });
					    if (el.classList.contains('validate-email') && inputEmail) {
						    el.setAttribute('type', 'email');
					    }
				    }
				    inputFields.push(el);
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
