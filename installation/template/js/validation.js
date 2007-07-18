/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL, see LICENSE.php
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
 * @package		Joomla
 * @subpackage	Installation
 * @since		1.5
 */

// JFormValidator prototype
JFormValidator = function() { this.constructor.apply(this, arguments);}
JFormValidator.prototype = {

	constructor: function() 
	{	
		var self = this;
		
		this.valid		= true;
		this.vContinue	= true;
		this.handlers	= Object();
		
		// Default regexes
		this.handlers['date']		= { enabled : true,
									exec : function (value) {
										regex=/(((0[13578]|10|12)([-.\/])(0[1-9]|[12][0-9]|3[01])([-.\/])(\d{4}))|((0[469]|11)([-.\/])([0][1-9]|[12][0-9]|30)([-.\/])(\d{4}))|((2)([-.\/])(0[1-9]|1[0-9]|2[0-8])([-.\/])(\d{4}))|((2)(\.|-|\/)(29)([-.\/])([02468][048]00))|((2)([-.\/])(29)([-.\/])([13579][26]00))|((2)([-.\/])(29)([-.\/])([0-9][0-9][0][48]))|((2)([-.\/])(29)([-.\/])([0-9][0-9][2468][048]))|((2)([-.\/])(29)([-.\/])([0-9][0-9][13579][26])))/;
										return regex.test(value);
									}
								  }
		this.handlers['phone']		= { enabled : true,
									exec : function (value) {
										regex=/^(\d{3}-\d{3}-\d{4})*$/;
										return regex.test(value);
									}
								  }
		this.handlers['zipcode']	= { enabled : true,
									exec : function (value) {
										regex=/(^(?!0{5})(\d{5})(?!-?0{4})(-?\d{4})?$)/;
										return regex.test(value);
									}
								  }
		this.handlers['password']	= { enabled : true,
									exec : function (value) {
										regex=/^[a-zA-Z]\w{3,14}$/;
										return regex.test(value);
									}
								  }
		this.handlers['numeric']	= { enabled : true,
									exec : function (value) {
										regex=/^(\d|-)?(\d|,)*\.?\d*$/;
										return regex.test(value);
									}
								  }
		this.handlers['email']		= { enabled : true,
									exec : function (value) {
										regex=/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
										return regex.test(value);
									}
								  }
	},
	
	registerEvent: function(target,type,args) 
	{
		//use a closure to keep scope
		var self = this;
			
		if (target.addEventListener)   { 
    		target.addEventListener(type,onEvent,true);
		} else if (target.attachEvent) { 
	  		target.attachEvent('on'+type,onEvent);
		} 
		
		function onEvent(e)	{
			e = e||window.event;
			e.element = target;
			return self["on"+type](e, args);
		}
	},
	
	attachToForm: function(form)
	{
		// Iterate through the form object and attach the validate
		// method to all input fields.
		for (var i=0;i < form.elements.length; i++) {
			form.elements[i].onchange = function(){return document.formvalidator.validate(this);}
		}
		// Attach the validate method to the onsubmit event for the given form
		form.onsubmit = function(){return validate(this);}
	},
	
	validate: function(target)
	{
		// Get the value of the target tag.
		switch (target.tagName) {
			case 'INPUT':
			case 'TEXTAREA':
				var value = target.value;
				break;
			case 'SELECT':
				var value = target.options[target.selectedIndex].value;
				break;
		}
		// Check to see if the tag is to be validated
		var pivot = target.className.indexOf('validate');

		// Make sure we are set to go...
		this.vContinue = true; 

		// get all the rules from the input box classname
		if (pivot != -1) {
			var rules = target.className.substring(pivot);
		} else {
			return;
		}
		rules = rules.split(' ');

		/**
		 * Validation rules are as follows
		 * [0] 'validate'	-- to validate the field this should always be 'validate'
		 * [1] 'required'	-- this means the field is required and should be populated
		 * [2] 'type'		-- this represents an additional validation type (ie. email, phone, date)
		 * [3] 'feedbackID'	-- this is the id of the element where feedback is sent to.
		 */
		var validate	= rules[0];
		var required	= rules[1];
		var type		= rules[2];
		var feedbackID	= rules[3];

		// Check for derived feedbackID
		if (feedbackID) {
			if (feedbackID.charAt(0) == '@') {
				feedbackID = target.id + '-' + feedbackID.substring(1);
			}
		}

		// The validation state for the target
		var state;

		//validateRequired() checks if it is required and then sends back feedback
		state = this.validateRequired (required, value, type);
		
		/**
		 * If the field is required and blank the fvContinue field will be false
		 * and we shouldn't bother validating the specific type... it will just 
		 * cause potential errors.
		 */
		if (this.vContinue)
		{
			// Check the additional validation types
			if ((type) && (type != 'none') && (this.handlers[type])) {
				// Execute the validation handler and return result
				if (this.handlers[type].exec(value)) {
			      state = true;
				} else {
			      state = false;
				}
			}
		}

		this.handleResponse(state, target, feedbackID);

		// Return validation state
		return state;
	},
	
	validateRequired: function(required, value, type) 
	{
		//check if required if not, continue validation script
		if (required == "required") {
			//if it is rquired and blank then it is an error and continues to be required
	   		if (value == "") {
				this.vContinue = false;
				return  false;
		 	}
			//if its not blank and has no other validation requirements the field passes
			else if (type == "none") {
				return true;
			}
		}
	},

	isValid: function(form) 
	{
		var valid = true;
		for (var i=0;i < form.elements.length; i++) {
			if (this.validate(form.elements[i]) == false) {
				valid = false;
			}
		}
		return valid;
	},

	handleResponse: function(state, target, feedback)
	{
		// Set the default values for the target and extra objects
		if (target.origBorder != '') {
			target.origBorder = target.style.borderColor;
		}
		// Set color to red if the object doesn't validate
		if (state == false) {
			target.style.borderColor = '#f00';
		} else {
			target.style.borderColor = target.origBorder;
		}

		// Get the extra object
		var	extra = document.getElementById(feedback);
		// Set extra color to red if the object doesn't validate
		if (extra) {
			if (extra.origColor != '') {
				extra.origColor = extra.style.color;
			}
			if (state == false) {
				extra.style.color = '#f00';
			} else {
				extra.style.color = extra.origColor;
			}
		}
	}
}

document.formvalidator = null;
Window.onDomReady(function(){
	document.formvalidator = new JFormValidator();
});