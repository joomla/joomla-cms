/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @package		Joomla.Framework
 * @subpackage	Forms
 * @since		1.5
 */
var JFormValidator = function($) {
	var handlers, custom, inputEmail;

	var setHandler = function(name, fn, en) {
		en = (en === '') ? true : en;
		handlers[name] = {
			enabled : en,
			exec : fn
		};
	};

	var handleResponse = function(state, $el) {
		// Find the label object for the given field if it exists
		if (!$el.get(0).labelref) {
			$('label').each(function() {
				var $label = $(this);
				if ($label.attr('for') === $el.attr('id')) {
					$el.get(0).labelref = this;
				}
			});
		}
		var labelref = $el.get(0).labelref;
		// Set the element and its label (if exists) invalid state
		if (state === false) {
			$el.addClass('invalid').attr('aria-invalid', 'true');
			if (labelref) {
				$(labelref).addClass('invalid').attr('aria-invalid', 'true');
			}
		} else {
			$el.removeClass('invalid').attr('aria-invalid', 'false');
			if (labelref) {
				$(labelref).removeClass('invalid').attr('aria-invalid', 'false');
			}
		}
	};

	var validate = function(el) {
		var $el = $(el);
		// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
		if ($el.attr('disabled')) {
			handleResponse(true, $el);
			return true;
		}
		// If the field is required make sure it has a value
		if ($el.hasClass('required')) {
			var tagName = $el.prop("tagName").toLowerCase(), i = 0, selector;
			if (tagName === 'fieldset' && ($el.hasClass('radio') || $el.hasClass('checkboxes'))) {
				while (true) {
					selector = "#" + $el.attr('id') + i;
					if ($(selector).get(0)) {
						if ($(selector).is(':checked')) {
							break;
						}
					} else {
						handleResponse(false, $el);
						return false;
					}
					i++;
				}
				//If element has class placeholder that means it is empty.
			} else if (!$el.val() || $el.hasClass('placeholder') || ($el.attr('type') === 'checkbox' && !$el.is(':checked'))) {
				handleResponse(false, $el);
				return false;
			}
		}
		// Only validate the field if the validate class is set
		var handler = ($el.attr('class') && $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
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
	};

	var isValid = function(form) {
		var valid = true, $form = $(form), i;
		// Validate form fields
		$form.find('input, textarea, select, button, fieldset').each(function(index, el){
			if (validate(el) === false) {
				valid = false;
			}
		});
		// Run custom form validators if present
		new Hash(custom).each(function(validator) {
			if (validator.exec() !== true) {
				valid = false;
			}
		});
		if (!valid) {
			var message, errors, error;
			message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
			errors = $("label.invalid");
			error = {};
			error.error = [];
			for ( i = 0; i < errors.length; i++) {
				var label = $(errors[i]).text();
				if (label !== 'undefined') {
					error.error[i] = message + label.replace("*", "");
				}
			}
			Joomla.renderMessages(error);
		}
		return valid;
	};

	var attachToForm = function(form) {
		// Iterate through the form object and attach the validate method to all input fields.
		$(form).find('input,textarea,select,button').each(function() {
			var $el = $(this), tagName = $el.prop("tagName").toLowerCase();
			if ($el.attr('required') === 'required') {
				$el.attr('aria-required', 'true');
			}
			if ((tagName === 'input' && $el.attr('type') === 'submit') || (tagName === 'button' && $el.attr('type') === undefined)) {
				if ($el.hasClass('validate')) {
					$el.on('click', function() {
						return isValid(form);
					});
				}
			} else {
				$el.on('blur', function() {
					return validate(this);
				});
				if ($el.hasClass('validate-email') && inputEmail) {
					$el.get(0).type = 'email';
				}
			}
		});
	};

	var initialize = function() {
		handlers = {};
		custom = {};
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
			regex = /^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
			return regex.test(value);
		});
		// Attach to forms with class 'form-validate'
		$('form.form-validate').each(function() {
			attachToForm(this);
		});
	};

	return {
		initialize : initialize,
		isValid : isValid,
		validate : validate
	};
};

document.formvalidator = null;
window.addEvent('domready', function() {
	document.formvalidator = new JFormValidator(jQuery.noConflict());
	document.formvalidator.initialize();
});
