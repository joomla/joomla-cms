/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @package     Joomla.Framework
 * @subpackage  Forms
 * @since       1.5
 */
var JFormValidator = function() {
	var $, handlers, inputEmail, custom,

 	setHandler = function(name, fn, en) {
 	 	en = (en === '') ? true : en;
 	 	handlers[name] = {
 	 	 	enabled : en,
 	 	 	exec : fn
 	 	};
 	},

 	findLabel = function(id, form){
 	 	var $label, $form = jQuery(form);
 	 	if (!id) {
 	 	 	return false;
 	 	}
 	 	$label = $form.find('#' + id + '-lbl');
 	 	if ($label.length) {
 	 	 	return $label;
 	 	}
 	 	$label = $form.find('label[for="' + id + '"]');
 	 	if ($label.length) {
 	 	 	return $label;
 	 	}
 	 	return false;
 	},

 	handleResponse = function(state, $el) {
 	 	var $label = $el.data('label');
 	 	// Set the element and its label (if exists) invalid state
 	 	if (state === false) {
 	 	 	$el.addClass('invalid').attr('aria-invalid', 'true');
 	 	 	if($label){
 	 	 	 	$label.addClass('invalid').attr('aria-invalid', 'true');
 	 	 	}
 	 	} else {
 	 	 	$el.removeClass('invalid').attr('aria-invalid', 'false');
 	 	 	if($label){
 	 	 	 	$label.removeClass('invalid').attr('aria-invalid', 'false');
 	 	 	}
 	 	}
 	},

 	validate = function(el) {
 	 	var $el = jQuery(el), tagName, handler;
 	 	// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
 	 	if ($el.attr('disabled')) {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// If the field is required make sure it has a value
 	 	if ($el.attr('required') || $el.hasClass('required')) {
 	 	 	tagName = $el.prop("tagName").toLowerCase();
 	 	 	if (tagName === 'fieldset' && ($el.hasClass('radio') || $el.hasClass('checkboxes'))) {
 	 	 	 	if (!$el.find('input:checked').length){
 	 	 	 	 	handleResponse(false, $el);
 	 	 	 	 	return false;
 	 	 	 	}
 	 	 	//If element has class placeholder that means it is empty.
 	 	 	} else if (!$el.val() || $el.hasClass('placeholder') || ($el.attr('type') === 'checkbox' && !$el.is(':checked'))) {
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Only validate the field if the validate class is set
 	 	handler = ($el.attr('class') && $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
 	 	if (handler === '') {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// Check the additional validation types
 	 	if ((handler) && (handler !== 'none') && (handlers[handler]) && $el.val()) {
 	 	 	// Execute the validation handler and return result
 	 	 	if (handlers[handler].exec($el.val()) !== true) {
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Return validation state
 	 	handleResponse(true, $el);
 	 	return true;
 	},

 	isValid = function(form) {
 	 	var valid = true, i, message, errors, error, label;
 	 	// Validate form fields
 	 	jQuery.each(jQuery(form).find('input, textarea, select, fieldset, button'), function(index, el) {
 	 	 	if (validate(el) === false) {
 	 	 	 	valid = false;
 	 	 	}
 	 	});
 	 	// Run custom form validators if present
 	 	jQuery.each(custom, function(key, validator) {
 	 	 	if (validator.exec() !== true) {
 	 	 	 	valid = false;
 	 	 	}
 	 	});
 	 	if (!valid) {
 	 	 	message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
 	 	 	errors = jQuery("input.invalid, textarea.invalid, select.invalid, fieldset.invalid, button.invalid");
 	 	 	error = {};
 	 	 	error.error = [];
 	 	 	for ( i = 0; i < errors.length; i++) {
 	 	 	 	label = jQuery('label[for=' + errors[i].id + ']').text();
 	 	 	 	if (label !== 'undefined') {
 	 	 	 	 	error.error[i] = message + label.replace("*", "");
 	 	 	 	}
 	 	 	}
 	 	 	Joomla.renderMessages(error);
 	 	}
 	 	return valid;
 	},

 	attachToForm = function(form) {
 	 	var inputFields = [];
 	 	// Iterate through the form object and attach the validate method to all input fields.
 	 	jQuery(form).find('input, textarea, select, fieldset, button').each(function() {
 	 	 	var $el = $(this), id = $el.attr('id'), tagName = $el.prop("tagName").toLowerCase();
 	 	 	if ($el.hasClass('required')) {
 	 	 	 	$el.attr('aria-required', 'true').attr('required', 'required');
 	 	 	}
 	 	 	if ((tagName === 'input' || tagName === 'button') && $el.attr('type') === 'submit') {
 	 	 	 	if ($el.hasClass('validate')) {
 	 	 	 	 	$el.on('click', function() {
 	 	 	 	 	 	return isValid(form);
 	 	 	 	 	});
 	 	 	 	}
 	 	 	} else {
 	 	 	 	if (tagName !== 'fieldset') {
 	 	 	 	 	$el.on('blur', function() {
 	 	 	 	 	 	return validate(this);
 	 	 	 	 	});
 	 	 	 	 	if ($el.hasClass('validate-email') && inputEmail) {
 	 	 	 	 	 	$el.get(0).type = 'email';
 	 	 	 	 	}
 	 	 	 	}
 	 	 	 	$el.data('label', findLabel(id, form));
 	 	 	 	inputFields.push($el);
 	 	 	}
 	 	});
 	 	$(form).data('inputfields', inputFields);
 	},

 	initialize = function() {
 	 	$ = jQuery.noConflict();
 	 	handlers = {};
 	 	custom = custom || {};

 	 	inputEmail = (function() {
 	 	 	var input = document.createElement("input");
 	 	 	input.setAttribute("type", "email");
 	 	 	return input.type !== "text";
 	 	})();
 	 	// Default handlers
 	 	setHandler('username', function(value) {
 	 	 	regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
 	 	 	return !regex.test(value);
 	 	});
 	 	setHandler('password', function(value) {
 	 	 	regex = /^\S[\S ]{2,98}\S$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('numeric', function(value) {
 	 	 	regex = /^(\d|-)?(\d|,)*\.?\d*$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('email', function(value) {
		    value = punycode.toASCII(value);
 	 	 	regex = /^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
 	 	 	return regex.test(value);
 	 	});
 	 	// Attach to forms with class 'form-validate'
 	 	jQuery('form.form-validate').each(function() {
 	 	 	attachToForm(this);
 	 	});
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
jQuery(function() {
	document.formvalidator = new JFormValidator();
});
