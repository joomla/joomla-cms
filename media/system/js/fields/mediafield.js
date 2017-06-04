/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field media
 */
;(function($){
	'use strict';

	$.fieldMedia = function(container, options){
		// Merge options with defaults
		this.options = $.extend({}, $.fieldMedia.defaults, options);

		// Set up elements
		this.$container = $(container);
		this.$modal = this.$container.find(this.options.modal);
		this.$modalBody = this.$modal.find('.modal-body');
		this.$input = this.$container.find(this.options.input);
		this.$containerPreview = this.$container.find(this.options.previewContainer);
		this.$buttonSelect = this.$container.find(this.options.buttonSelect);
		this.$buttonClear  = this.$container.find(this.options.buttonClear);

		// Bind events
		this.$buttonSelect.on('click', this.modalOpen.bind(this));
		this.$buttonClear.on('click', this.clearValue.bind(this));
		this.$modal.on('hide', this.removeIframe.bind(this));

		// Update preview for existing value
		this.updatePreview();
	};

	// display modal for select the file
	$.fieldMedia.prototype.modalOpen = function() {
		this.$modal.modal('show');
		$('body').addClass('modal-open');

		var self = this; // save context

		this.$container.find(this.options.buttonSaveSelected).on('click', function(e){
			e.stopPropagation();
			if (self.selectedPath) {
				self.setValue(self.options.rootFolder + self.selectedPath);
			} else {
				self.setValue('');
			}

			self.modalClose();
			return false;
		});


		window.document.addEventListener('onMediaFileSelected', function (e) {
			var path = e.item.path;

			if (path.match(/.jpg|.jpeg|.gif|.png/)) {
				self.selectedPath = e.item.path;
			} else {
				self.selectedPath = '';
			}

		});
	};

	// close modal
	$.fieldMedia.prototype.modalClose = function() {
		this.$modal.modal('hide');
		$('body').removeClass('modal-open');
		this.$modalBody.empty();
	};

	// Clear the iframe
	$.fieldMedia.prototype.removeIframe = function() {
		this.$modalBody.empty();
		$('body').removeClass('modal-open');
	};

	// set the value
	$.fieldMedia.prototype.setValue = function(value) {
		this.$input.val(value).trigger('change');
		this.updatePreview();
	};

	// clear the value
	$.fieldMedia.prototype.clearValue = function() {
		this.setValue('');
	};

	// update preview
	$.fieldMedia.prototype.updatePreview = function() {
		if (!this.options.preview) {
			return;
		}

		// Reset tooltip and preview
		if (this.options.preview) {
			this.$containerPreview.popover('dispose');
			this.$input.tooltip('dispose');

			var value = this.$input.val();

			if (!value) {
				this.$containerPreview.popover();
			} else {
				var imgPreview = new Image(this.options.previewWidth, this.options.previewHeight);
				imgPreview.src = this.options.basepath + value;

				this.$containerPreview.popover({content: imgPreview});
				this.$input.tooltip({placement: 'top', title: value});
			}
		}
	};

	// default options
	$.fieldMedia.defaults = {
		basepath: '', // base path to file
		rootFolder: '', // the root folder
		buttonClear: '.button-clear', // selector for button to clear the value
		buttonSelect: '.button-select', // selector for button to change the value
		buttonSaveSelected: '.button-save-selected', // selector for button to save the selected value
		input: '.field-media-input', // selector for the input
		preview: true, // whether use the preview
		previewAsTooltip: true, // whether use the preview
		previewContainer: '.field-media-preview', // selector for the preview container
		previewWidth: 200, // preview width
		previewHeight: 200, // preview height
		url: 'index.php?option=com_media&tmpl=component', // url for load the iframe
		modal: '.modal', // modal selector
		modalWidth: '100%', // modal width
		modalHeight: '300px', // modal height
	};

	$.fn.fieldMedia = function(options){
		return this.each(function(){
			var $el = $(this), instance = $el.data('fieldMedia');
			if(!instance){
				var options = options || {},
				    data = $el.data();

				// Check options in the element
				for (var p in data) {
					if (data.hasOwnProperty(p)) {
						options[p] = data[p];
					}
				}

				instance = new $.fieldMedia(this, options);
				$el.data('fieldMedia', instance);
			}
		});
	};

	// Initialise all defaults
	$(document).ready(function(){
		$('.field-media-wrapper').fieldMedia();
	});

})(jQuery);
