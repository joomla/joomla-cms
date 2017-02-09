/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field user
 */
;(function($){
	'use strict';

	$.fieldUser = function(container, options){
		// Merge options with defaults
		this.options = $.extend({}, $.fieldUser.defaults, options);

		// Set up elements
		this.$container = $(container);
		this.$modal = this.$container.find(this.options.modal);
		this.$modalBody = this.$modal.children('.modal-body');
		this.$input = this.$container.find(this.options.input);
		this.$inputName = this.$container.find(this.options.inputName);
		this.$buttonSelect = this.$container.find(this.options.buttonSelect);

		// Bind events
		this.$buttonSelect.on('click', this.modalOpen.bind(this));
		this.$modal.on('hide', this.removeIframe.bind(this));

		// Check for onchange callback,
		var onchangeStr =  this.$input.attr('data-onchange'), onchangeCallback;
		if(onchangeStr) {
			onchangeCallback = new Function(onchangeStr);
			this.$input.on('change', onchangeCallback.bind(this.$input));
		}

	};

	// display modal for select the file
	$.fieldUser.prototype.modalOpen = function() {
		var $iframe = $('<iframe>', {
			name: 'field-user-modal',
			src: this.options.url.replace('{field-user-id}', this.$input.attr('id')),
			width: this.options.modalWidth,
			height: this.options.modalHeight
		});
		this.$modalBody.append($iframe);
		this.$modal.modal('show');
		$('body').addClass('modal-open');

		var self = this; // save context
		$iframe.load(function(){
			var content = $(this).contents();

			// handle value select
			content.on('click', '.button-select', function(){
				self.setValue($(this).data('user-value'), $(this).data('user-name'));
				self.modalClose();
				$('body').removeClass('modal-open');
			});
		});
	};

	// close modal
	$.fieldUser.prototype.modalClose = function() {
		this.$modal.modal('hide');
		this.$modalBody.empty();
		$('body').removeClass('modal-open');
	};

	// close modal
	$.fieldUser.prototype.removeIframe = function() {
		this.$modalBody.empty();
		$('body').removeClass('modal-open');
	};

	// set the value
	$.fieldUser.prototype.setValue = function(value, name) {
		this.$input.val(value).trigger('change');
		this.$inputName.val(name || value).trigger('change');
	};

	// default options
	$.fieldUser.defaults = {
		buttonSelect: '.button-select', // selector for button to change the value
		input: '.field-user-input', // selector for the input for the user id
		inputName: '.field-user-input-name', // selector for the input for the user name
		modal: '.modal', // modal selector
		url : 'index.php?option=com_users&view=users&layout=modal&tmpl=component',
		modalWidth: '100%', // modal width
		modalHeight: '300px' // modal height
	};

	$.fn.fieldUser = function(options){
		return this.each(function(){
			var $el = $(this), instance = $el.data('fieldUser');
			if(!instance){
				var options = options || {},
					data = $el.data();

				// Check options in the element
				for (var p in data) {
					if (data.hasOwnProperty(p)) {
						options[p] = data[p];
					}
				}

				instance = new $.fieldUser(this, options);
				$el.data('fieldUser', instance);
			}
		});
	};

	// Initialise all defaults
	$(document).ready(function(){
		$('.field-user-wrapper').fieldUser();
	});

})(jQuery);

// Compatibility with mootools modal layout
function jSelectUser(element) {
	var $el = jQuery(element),
		value = $el.data('user-value'),
		name  = $el.data('user-name'),
		fieldId = $el.data('user-field'),
		$inputValue = jQuery('#' + fieldId + '_id'),
		$inputName  = jQuery('#' + fieldId);

	if (!$inputValue.length) {
		// The input not found
		return;
	}

	// Update the value
	$inputValue.val(value).trigger('change');
	$inputName.val(name || value).trigger('change');

	// Check for onchange callback,
	var onchangeStr = $inputValue.attr('data-onchange'), onchangeCallback;
	if(onchangeStr) {
		onchangeCallback = new Function(onchangeStr);
		onchangeCallback.call($inputValue[0]);
	}
	jModalClose();
}
