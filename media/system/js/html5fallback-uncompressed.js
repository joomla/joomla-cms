/**
 * @copyright	Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

 /**
 * Unobtrusive Form Validation and HTML5 Form polyfill library
 *
 * Inspired by: Ryan Seddon <http://thecssninja.com/javascript/H5F>
 *
 * @package		Joomla.Framework
 * @subpackage	Forms
 */

(function($,document,undefined){
	// Utility function
	if(typeof Object.create !== 'function'){
		Object.create = function(obj){
			function F(){};
			F.prototype = obj;
			return new F();
		};
	}

	var H5Form = {
		init: function(options, elem){
			var self = this;
			self.elem = elem;
			self.$elem = $(elem);
			elem.H5Form = self;
			self.options = $.extend({}, $.fn.h5f.options, options);
			self.field = document.createElement("input");
			self.checkSupport(self);
			//check whether the element is form or not
			if(elem.nodeName.toLowerCase() === "form"){
				self.bindWithForm(self.elem, self.$elem);
			}
		},

		bindWithForm : function(form, $form){
			var self = this,
				novalidate = !!$form.attr('novalidate'),
				f = form.elements,
				flen = f.length;
			if(self.options.formValidationEvent === "onSubmit"){
				$form.on('submit',function(e){
					var formnovalidate = this.H5Form.donotValidate != undefined ? this.H5Form.donotValidate : false;
					if(!formnovalidate && !novalidate && !self.validateForm(self)){
						//prevent form from submit
						e.preventDefault();
						this.donotValidate = false;
					}
					else{
						$form.find(':input').each(function(){
							self.placeholder(self,this,'submit');
						});
					}
				});
			}
			$form.on('focusout focusin', function(event){
				self.placeholder(self, event.target, event.type);
			});

			$form.on('focusout change', self.validateField);

			$form.find('fieldset').on('change',function(){
				self.validateField(this);
			});

			if(!self.browser.isFormnovalidateNative){
				$form.find(':submit[formnovalidate]').on('click',function(){
					self.donotValidate = true;
				});
			}
			while(flen--) {
				//assign graphical polyfills
				var elem = f[flen];
				self.polyfill(elem);
				self.autofocus(self, elem);
			}
		},

		polyfill : function(elem){
			if(elem.nodeName.toLowerCase() === 'form')return true;
			var	self = elem.form.H5Form;
			self.placeholder(self, elem);
			self.numberType(self, elem);
		},

		checkSupport : function(self){
			self.browser = {};
			self.browser.isRequiredNative = !!("required" in self.field);
			self.browser.isPatternNative = !!("pattern" in self.field);
			self.browser.isPlaceholderNative = !!("placeholder" in self.field);
			self.browser.isAutofocusNative = !!("autofocus" in self.field);
			self.browser.isFormnovalidateNative = !!("formnovalidate" in self.field);

			self.field.setAttribute('type', 'email');
			self.browser.isEmailNative = (self.field.type == 'email');

			self.field.setAttribute('type', 'url');
			self.browser.isUrlNative = (self.field.type == 'url');

			self.field.setAttribute('type', 'number');
			self.browser.isNumberNative = (self.field.type == 'number');

			self.field.setAttribute('type', 'range');
			self.browser.isRangeNative = (self.field.type == 'range');
		},

		validateForm : function(){
			var self = this,
				form = self.elem,
				f = form.elements,
				flen = f.length,
				isFieldValid = true;
			form.isValid = true;

			for(var i=0; i<flen; i++) {
				var elem = f[i];
				elem.isRequired = !!elem.required;
				elem.isDisabled = !!elem.disabled;

				//Do Validation
				if(!elem.isDisabled) {
					isFieldValid = self.validateField(elem);
					// Set focus to first invalid field
					if(form.isValid && !isFieldValid){
						self.setFocusOn(elem);
					}
					form.isValid = isFieldValid && form.isValid;
				}
			}
			if(self.options.doRenderMessage){
				self.renderErrorMessages(self, form);
			}
			return form.isValid;
		},

		validateField : function(e) {
			var elem = e.target || e;
			if(elem.form === undefined){
				return null;
			}
			var	self = elem.form.H5Form,
				$elem = $(elem),
				isMissing = false,
				isRequired = !!($(elem).attr("required")),
				isDisabled = !!($elem.attr("disabled"));
			if(!elem.isDisabled){
				isMissing = !self.browser.isRequiredNative && isRequired && self.isValueMissing(self, elem);
				isPatternMismatched = !self.browser.isPatternNative && self.matchPattern(self, elem);
			}
			elem.validityState = {
				valueMissing: isMissing,
				patterMismatch : isPatternMismatched,
				valid: (elem.isDisabled || !(isMissing || isPatternMismatched))
			};

			if(!self.browser.isRequiredNative){
				if(elem.validityState.valueMissing){
					$elem.addClass(self.options.requiredClass);
				}
				else{
					$elem.removeClass(self.options.requiredClass);
				}
			}

			if(!self.browser.isPatternNative){
				if(elem.validityState.patterMismatch){
					$elem.addClass(self.options.patternClass);
				}
				else{
					$elem.removeClass(self.options.patternClass);
				}
			}

			if(!elem.validityState.valid){
				$elem.addClass(self.options.invalidClass);
				var $labelref = self.findLabel($elem);
				$labelref.addClass(self.options.invalidClass);
			}
			else{
				$elem.removeClass(self.options.invalidClass);
				var $labelref = self.findLabel($elem);
				$labelref.removeClass(self.options.invalidClass)
			}
			return elem.validityState.valid;
		},

		isValueMissing : function(self, elem){
			var $elem = $(elem),
				node = /^(input|textarea|select)$/i,
	            ignoredType = /^submit$/i,
				val = $elem.val(),
				type = elem.type !== undefined ? elem.type : elem.tagName.toLowerCase(),
				specialTypes = /^(checkbox|radio|fieldset)$/i;
			if(!specialTypes.test(type) && !ignoredType.test(type)){
				if(val === ""){
					return true;
				}
				else if(!self.browser.isPlaceholderNative && $elem.hasClass(self.options.placeholderClass)){
					return true;
				}
			}
			else if(specialTypes.test(type)){

				if(type === "checkbox"){
					return !$elem.is(':checked');
				}
				else {
					var elements;
					if(type === "fieldset"){
						elements = $elem.find('input');
					}
					else{
						elements = document.getElementsByName(elem.name);
					}
			        for(var i=0; i<elements.length; i++){
						if($(elements[i]).is(':checked')){
							return false;
						}
			        }
			        // Since no checkbox or radio box is checked value is missing.
			        return true;
				}
			}
			return false;
		},

		matchPattern : function(self, elem){
			var $elem = $(elem),
				val = !self.browser.isPlaceholderNative &&
						$elem.attr('placeholder') &&
						$elem.hasClass(self.options.placeholderClass) ?
							'' : $elem.attr('value'),
				pattern = $elem.attr('pattern'),
				type = $elem.attr('type');
			if(val !== ""){
				if(type === "email") {
					var emailMatched = true;
					if($elem.attr('multiple') !== undefined){
						val = val.split(self.options.mutipleDelimiter);
						for (var i = 0; i < val.length; i++) {
							emailMatched = self.options.emailPatt.test(val[i].replace(/[ ]*/g,''));
							if(!emailMatched)return true;
						}
					}
					else {
						return !self.options.emailPatt.test(val);
					}
				} else if(type === "url") {
					return !self.options.urlPatt.test(val);
				} else if(type === 'text') {
					if(pattern !== undefined){
						usrPatt = new RegExp('^(?:' + pattern + ')$');
						return !usrPatt.test(val);
					}
				}
			}
			return false;
		},

		placeholder : function(self, elem, event) {
	        var $elem = $(elem),
	        	attrs = { placeholder: $elem.attr("placeholder") },
	            focus = /^(focusin|submit)$/i, //events on which field should be blank
	            node = /^(input|textarea)$/i,
	            ignoredType = /^password$/i,
	            isNative = self.browser.isPlaceholderNative;
	        if(!isNative && node.test(elem.nodeName) && !ignoredType.test(elem.type) && attrs.placeholder !== undefined) {
	            if(elem.value === "" && !focus.test(event)) {
	                elem.value = attrs.placeholder;
	                $elem.addClass(self.options.placeholderClass);

	            } else if(elem.value === attrs.placeholder && focus.test(event)) {
	                elem.value = "";
	                $elem.removeClass(self.options.placeholderClass);
	            }
	        }
	    },

	    numberType : function(self, elem) {
	    	var $elem = $(elem);
	    		node = /^input$/i,
	    		type = $elem.attr('type');

			if(node.test(elem.nodeName) && ((type == "number" && !self.browser.isNumberNative) || (type == "range" && !self.browser.isRangeNative)))
			{

				var min = parseInt($elem.attr('min')),
					max = parseInt($elem.attr('max')),
					step = parseInt($elem.attr('step')),
					value = parseInt($elem.attr('value')),
					attributes = $elem.prop("attributes"),
					$select = $('<select>'),
					$option;

				min = isNaN(min) ? -100 : min;

				for (var i=min; i <= max ; i+=step) {
					$option = $("<option>").attr('value',i).text(i);

					if(value == i || (value > i && value < i + step)){
						$option.attr('selected','');
					}

					$select.append($option);
				}

				$.each(attributes, function() {
					$select.attr(this.name, this.value);
				});

				$elem.replaceWith($select);
			}
	    },

	    autofocus : function(self, elem){
	    	var $elem = $(elem),
				doAutofocus = !!$elem.attr("autofocus"),
	            node = /^(input|textarea|select|fieldset)$/i,
	            ignoredType = /^submit$/i,
	            isNative = self.browser.isAutofocusNative;
	        if(!isNative && node.test(elem.nodeName) && !ignoredType.test(elem.type) && doAutofocus){
				$(document).ready(function(){
					self.setFocusOn(elem);
				});
			}
	    },
	    //Extras
	    findLabel : function($elem){
	    	var $label = $('label[for="'+$elem.attr('id')+'"]');

			if($label.length <= 0) {
			    var $parentElem = $elem.parent(),
			        parentTagName = $parentElem.get(0).tagName.toLowerCase();

			    if(parentTagName == "label") {
			        $label = $parentElem;
			    }
			}
			return $label;
	    },

	    setFocusOn : function(elem){
			if(elem.tagName.toLowerCase() === "fieldset"){
				$(elem).find(":first").focus();
			}
			else{
				$(elem).focus();
			}
		},

	    renderErrorMessages : function(self, form){
	    	var f = form.elements,
				flen = f.length,
				error = {};
				error.errors = new Array();
			while(flen--) {
				var $elem = $(f[flen]),
					$label = self.findLabel($elem);
				if($elem.hasClass(self.options.requiredClass)) {
						error.errors[flen] = $label.text().replace("*", "") + self.options.requiredMessage;
				}
				if($elem.hasClass(self.options.patternClass)) {
						error.errors[flen] = $label.text().replace("*", "") + self.options.patternMessage;
				}
			}
			if(error.errors.length > 0){
				Joomla.renderMessages(error);
			}
	    }
	};
	$.fn.h5f = function(options){
		return this.each(function(){
			var h5form = Object.create(H5Form);
			h5form.init(options, this);
		});
	};
	$.fn.h5f.options = {
	    invalidClass : "invalid",
	    requiredClass : "required",
	    requiredMessage : " is required.",
	    placeholderClass : "placeholder",
	    patternClass : "pattern",
	    patternMessage : " doesn't match pattern.",
	    doRenderMessage : false,
	    formValidationEvent : 'onSubmit',
	    emailPatt : /^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,
	    urlPatt : /[a-z][\-\.+a-z]*:\/\//i
	};
	$(function(){
		$('form').h5f({doRenderMessage : true, requiredClass : "musthavevalue"});
	});
})(jQuery,document);
