/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

Object.append(Browser.Features, {
	inputemail: (function() {
		var i = document.createElement("input");
		i.setAttribute("type", "email");
		return i.type !== "text";
	})()
});

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @package		Joomla.Framework
 * @subpackage	Forms
 * @since		1.5
 */
var JFormValidator = new Class({
	initialize: function()
	{
		// Initialize variables
		this.handlers	= Object();
		this.custom		= Object();

		// Default handlers
		this.setHandler('username',
			function (value) {
				regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
				return !regex.test(value);
			}
		);

		this.setHandler('password',
			function (value) {
				regex=/^\S[\S ]{2,98}\S$/;
				return regex.test(value);
			}
		);

		this.setHandler('numeric',
			function (value) {
				regex=/^(\d|-)?(\d|,)*\.?\d*$/;
				return regex.test(value);
			}
		);

		this.setHandler('email',
			function (value) {
				regex=/^[a-zA-Z0-9._-]+(\+[a-zA-Z0-9._-]+)*@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
				return regex.test(value);
			}
		);

		// Attach to forms with class 'form-validate'
		var forms = $$('form.form-validate');
		forms.each(function(form){ this.attachToForm(form); }, this);
	},

	setHandler: function(name, fn, en)
	{
		en = (en == '') ? true : en;
		this.handlers[name] = { enabled: en, exec: fn };
	},

	attachToForm: function(form)
	{
		// Iterate through the form object and attach the validate method to all input fields.
		form.getElements('input,textarea,select,button').each(function(el){
			if (el.hasClass('required')) {
				el.set('aria-required', 'true');
				el.set('required', 'required');
			}
			if ((document.id(el).get('tag') == 'input' || document.id(el).get('tag') == 'button') && document.id(el).get('type') == 'submit') {
				if (el.hasClass('validate')) {
					el.onclick = function(){return document.formvalidator.isValid(this.form);};
				}
			} else {
				el.addEvent('blur', function(){return document.formvalidator.validate(this);});
				if (el.hasClass('validate-email') && Browser.Features.inputemail) {
					el.type = 'email';
				}
			}
		});
	},

	validate: function(el)
	{
		el = document.id(el);

		// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
		if(el.get('disabled')) {
			this.handleResponse(true, el);
			return true;
		}

		// If the field is required make sure it has a value
		if (el.hasClass('required')) {
			if (el.get('tag')=='fieldset' && (el.hasClass('radio') || el.hasClass('checkboxes'))) {
				for(var i=0;;i++) {
					if (document.id(el.get('id')+i)) {
						if (document.id(el.get('id')+i).checked) {
							break;
						}
					}
					else {
						this.handleResponse(false, el);
						return false;
					}
				}
			}
			else if (!(el.get('value'))) {
				this.handleResponse(false, el);
				return false;
			}
		}

		// Only validate the field if the validate class is set
		var handler = (el.className && el.className.search(/validate-([a-zA-Z0-9\_\-]+)/) != -1) ? el.className.match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
		if (handler == '') {
			this.handleResponse(true, el);
			return true;
		}

		// Check the additional validation types
		if ((handler) && (handler != 'none') && (this.handlers[handler]) && el.get('value')) {
			// Execute the validation handler and return result
			if (this.handlers[handler].exec(el.get('value')) != true) {
				this.handleResponse(false, el);
				return false;
			}
		}

		// Return validation state
		this.handleResponse(true, el);
		return true;
	},

	isValid: function(form)
	{
		var valid = true;

		// Validate form fields
		var elements = form.getElements('fieldset').concat(Array.from(form.elements));
		for (var i=0;i < elements.length; i++) {
			if (this.validate(elements[i]) == false) {
				valid = false;
			}
		}

		// Run custom form validators if present
		new Hash(this.custom).each(function(validator){
			if (validator.exec() != true) {
				valid = false;
			}
		});

		return valid;
	},

	handleResponse: function(state, el)
	{
		// Find the label object for the given field if it exists
		if (!(el.labelref)) {
			var labels = $$('label');
			labels.each(function(label){
				if (label.get('for') == el.get('id')) {
					el.labelref = label;
				}
			});
		}

		// Set the element and its label (if exists) invalid state
		if (state == false) {
			el.addClass('invalid');
			el.set('aria-invalid', 'true');
			if (el.labelref) {
				document.id(el.labelref).addClass('invalid');
				document.id(el.labelref).set('aria-invalid', 'true');
			}
		} else {
			el.removeClass('invalid');
			el.set('aria-invalid', 'false');
			if (el.labelref) {
				document.id(el.labelref).removeClass('invalid');
				document.id(el.labelref).set('aria-invalid', 'false');
			}
		}
	}
});

document.formvalidator = null;
window.addEvent('domready', function(){
	document.formvalidator = new JFormValidator();
});
