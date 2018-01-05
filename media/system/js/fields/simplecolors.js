/**
 * LOOSELY BASED ON:
 * Very simple jQuery Color Picker
 * Copyright (C) 2012 Tanguy Krotoff
 * Licensed under the MIT license
 *
 * ADAPTED BY:
 * Copyright (C) 2013 Peter van Westen
 */

(function($) {
	var SimpleColorPicker = function(element, options) {
		this.select = $(element);
		this.options = $.extend({}, $.fn.simplecolors.defaults, options);

		this.select.hide();

		// Build the list of colors
		var list = '';
		$('option', this.select).each(function() {
			var option = $(this);
			var color = option.val();
			if (option.text() == '-') {
				list += '<br>';
			} else {
				var clss = 'simplecolors-swatch';
				if (color == 'none') {
					clss += ' nocolor';
					color = 'transparent';
				}
				if (option.attr('selected')) {
					clss += ' active';
				}
				list += '<span class="' + clss + '"><span style="background-color: ' + color + ';" tabindex="0"></span></span>';
			}
		});

		var color = this.select.val();
		var clss = 'simplecolors-swatch';
		if (color == 'none') {
			clss += ' nocolor';
			color = 'transparent';
		}
		this.icon = $('<span class="' + clss + '"><span style="background-color: ' + color + ';" tabindex="0"></span></span>').insertAfter(this.select);
		this.icon.on('click', $.proxy(this.show, this));

		this.panel = $('<span class="simplecolors-panel"></span>').appendTo(document.body);
		this.panel.html(list);
		this.panel.on('click', $.proxy(this.click, this));

		// Hide panel when clicking outside
		$(document).on('mousedown', $.proxy(this.hide, this));
		this.panel.on('mousedown', $.proxy(this.mousedown, this));

	};

	/**
	 * SimpleColorPicker class
	 */
	SimpleColorPicker.prototype = {
		constructor: SimpleColorPicker,

		show: function() {
			var panelpadding = 7; // Empirical value
			var pos = this.icon.offset();
			switch (this.select.attr('data-position')) {
				case 'top':
					this.panel.css({
						left: pos.left - panelpadding,
						top: pos.top - this.panel.outerHeight() - 1
					});
					break;
				case 'bottom':
					this.panel.css({
						left: pos.left - panelpadding,
						top: pos.top + this.icon.outerHeight()
					});
					break;
				case 'left':
					this.panel.css({
						left: pos.left - this.panel.outerWidth(),
						top: pos.top - ( (this.panel.outerHeight() - this.icon.outerHeight() ) / 2 ) - 1
					});
					break;
				case 'right':
				default:
					this.panel.css({
						left: pos.left + this.icon.outerWidth(),
						top: pos.top - ( (this.panel.outerHeight() - this.icon.outerHeight() ) / 2 ) - 1
					});
					break;
			}

			this.panel.show(this.options.delay);
		},

		hide: function() {
			if (this.panel.css('display') != 'none') {
				this.panel.hide(this.options.delay);
			}
		},

		click: function(e) {
			var target = $(e.target);
			if (target.length === 1) {
				if (target[0].nodeName.toLowerCase() === 'span') {
					// When you click on a color

					var color = '';
					var bgcolor = '';
					var clss = '';
					if (target.parent().hasClass('nocolor')) {
						color = 'none';
						bgcolor = 'transparent';
						clss = 'nocolor';
					} else {
						color = this.rgb2hex(target.css('background-color'));
						bgcolor = color;
					}

					// Mark this div as the selected one
					target.parent().siblings().removeClass('active');
					target.parent().addClass('active');

					this.icon.removeClass('nocolor').addClass(clss);
					this.icon.find('span').css('background-color', bgcolor);

					// Hide the panel
					this.hide();

					// Change select value
					this.select.val(color).change();
				}
			}
		},

		/**
		 * Prevents the mousedown event from "eating" the click event.
		 */
		mousedown: function(e) {
			e.stopPropagation();
			e.preventDefault();
		},

		/**
		 * Converts a RGB color to its hexadecimal value.
		 *
		 * See http://stackoverflow.com/questions/1740700/get-hex-value-rather-than-rgb-value-using-$
		 */
		rgb2hex: function(rgb) {
			function hex(x) {
				return ("0" + parseInt(x, 10).toString(16)).slice(-2);
			}

			var matches = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
			if (matches === null) {
				// Fix for Internet Explorer < 9
				// Variable rgb is already a hexadecimal value
				return rgb;
			} else {
				return '#' + hex(matches[1]) + hex(matches[2]) + hex(matches[3]);
			}
		}
	};

	/**
	 * Plugin definition.
	 */
	$.fn.simplecolors = function(option) {
		// For HTML element passed to the plugin
		return this.each(function() {
			var $this = $(this),
				data = $this.data('simplecolors'),
				options = typeof option === 'object' && option;
			if (!data) {
				$this.data('simplecolors', (data = new SimpleColorPicker(this, options)));
			}
			if (typeof option === 'string') {
				data[option]();
			}
		});
	};

	$.fn.simplecolors.Constructor = SimpleColorPicker;

	/**
	 * Default options.
	 */
	$.fn.simplecolors.defaults = {
		// Animation delay
		delay: 0
	};
})(jQuery);
