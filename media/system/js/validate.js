/**
* @version		$Id: validate.js 7401 2007-05-14 04:12:55Z eddieajau $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @author		Louis Landry <louis.landry@joomla.org>
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
				regex=/^[a-zA-Z](([\.\-a-zA-Z0-9@])?[a-zA-Z0-9]*)*$/;
				return regex.test(value);
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
				regex=/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
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
		$A(form.elements).each(function(el){
			el = $(el);
			if ((el.getTag() == 'input' || el.getTag() == 'button') && el.getProperty('type') == 'submit') {
				if (el.hasClass('validate')) {
					el.onclick = function(){return document.formvalidator.isValid(this.form);};
				}
			} else {
				el.addEvent('blur', function(){return document.formvalidator.validate(this);});
			}
		});
	},

	validate: function(el)
	{
		// If the field is required make sure it has a value
		if ($(el).hasClass('required')) {
			if (!($(el).getValue())) {
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
		if ((handler) && (handler != 'none') && (this.handlers[handler]) && $(el).getValue()) {
			// Execute the validation handler and return result
			if (this.handlers[handler].exec($(el).getValue()) != true) {
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
		for (var i=0;i < form.elements.length; i++) {
			if (this.validate(form.elements[i]) == false) {
				valid = false;
			}
		}

		// Run custom form validators if present
		$A(this.custom).each(function(validator){
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
				if (label.getProperty('for') == el.getProperty('id')) {
					el.labelref = label;
				}
			});
		}

		// Set the element and its label (if exists) invalid state
		if (state == false) {
			el.addClass('invalid');
			if (el.labelref) {
				$(el.labelref).addClass('invalid');
			}
		} else {
			el.removeClass('invalid');
			if (el.labelref) {
				$(el.labelref).removeClass('invalid');
			}
		}
	}
});

document.formvalidator = null;
Window.onDomReady(function(){
	document.formvalidator = new JFormValidator();
});