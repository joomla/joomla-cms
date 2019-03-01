/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field media
 */
;(function($){
	'use strict';

	if (!Function.prototype.bind) {
		Function.prototype.bind = function(oThis) {
			if (typeof this !== 'function') {
				// closest thing possible to the ECMAScript 5
				// internal IsCallable function
				throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
			}

			var aArgs   = Array.prototype.slice.call(arguments, 1),
			    fToBind = this,
			    fNOP    = function() {},
			    fBound  = function() {
				    return fToBind.apply(this instanceof fNOP && oThis
						    ? this
						    : oThis,
					    aArgs.concat(Array.prototype.slice.call(arguments)));
			    };

			fNOP.prototype = this.prototype;
			fBound.prototype = new fNOP();

			return fBound;
		};
	}

	$.fieldMedia = function(container, options){
		// Merge options with defaults
		this.options = $.extend({}, $.fieldMedia.defaults, options);

		// Set up elements
		this.$container = $(container);
		this.$modal = this.$container.find(this.options.modal);
		this.$modalBody = this.$modal.children('.modal-body');
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
		var $iframe = $('<iframe>', {
			name: 'field-media-modal',
			src: this.options.url.replace('{field-media-id}', this.$input.attr('id')),
			width: this.options.modalWidth,
			height: this.options.modalHeight
		});
		this.$modalBody.append($iframe);
		this.$modal.modal('show');
		$('body').addClass('modal-open');

		var self = this; // save context
		$iframe.load(function(){
			var content = $(this).contents();

			// bind insert
			content.on('click', self.options.buttonSaveSelected, function(){
				var value = content.find('#f_url').val();
				if (value) {
					self.setValue(value);
				}
				self.modalClose.call(self);
			});

			// bind cancel
			content.on('click', '.button-cancel', function(){
				$('body').removeClass('modal-open');
				self.modalClose.bind(self);
			});
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

		if (this.options.preview && !this.options.previewAsTooltip) {
			var value = this.$input.val();

			if (!value) {
				this.$containerPreview.append('');
			} else {
				var imgPreview = new Image();
				imgPreview.src = this.options.basepath + value;
				if (imgPreview.width > imgPreview.height) {
					this.$containerPreview.html($('<img>',{src: imgPreview.src, style: 'width: ' + this.options.previewWidth + 'px'}));
				} else {
					this.$containerPreview.html($('<img>',{src: imgPreview.src, style: 'height: ' + this.options.previewHeight + 'px'}));
				}
			}
		} else {
			// Reset tooltip and preview
			try {
				this.$containerPreview.popover('destroy');
				this.$input.tooltip('destroy');
			} catch(err) {
				// Doing this so that jQueryUI tooltips and popover can exist alongside Bootstrap.
			}

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

// Compatibility with mootools modal layout
function jInsertFieldValue(value, id) {
	var $ = jQuery.noConflict();
	var old_value = $("#" + id).val();
	if (old_value != value) {
		var $elem = $("#" + id);
		$elem.val(value);
		$elem.trigger("change");
		if (typeof($elem.get(0).onchange) === "function") {
			$elem.get(0).onchange();
		}
		jMediaRefreshPreview(id);
	}
}

function jMediaRefreshPreview(id) {
	var $ = jQuery.noConflict();
	var value = $("#" + id).val();
	var $img = $("#" + id + "_preview");
	var basepath = $("#" + id).data("basepath");

	if ($img.length) {
		if (value) {
			$img.attr("src", basepath + value);
			$("#" + id + "_preview_empty").hide();
			$("#" + id + "_preview_img").show()
		} else {
			$img.attr("src", "");
			$("#" + id + "_preview_empty").show();
			$("#" + id + "_preview_img").hide();
		}
	}
}

function jMediaRefreshPreviewTip(tip)
{
	var $ = jQuery.noConflict();
	var $tip = $(tip);
	var $img = $tip.find("img.media-preview");

	$img.each(function(index, value) {
		$tip.find("div.tip").css("max-width", "none");
		var id = $(this).attr("id");
		id = id.substring(0, id.length - "_preview".length);
		jMediaRefreshPreview(id);
		$tip.show(this);
	});
}

// JQuery for tooltip for INPUT showing whole image path
function jMediaRefreshImgpathTip(tip, els)
{
	var $ = jQuery.noConflict();
	var $tip = $(tip);
	$tip.css("max-width", "none");
	var $imgpath = $(els).val();
	$("#TipImgpath").html($imgpath);

	if ($imgpath.length) {
		$tip.show();
	} else {
		$tip.hide();
	}
}
