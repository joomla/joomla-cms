/**
 * @package    HikaShop for Joomla!
 * @version    2.6.0
 * @author     hikashop.com
 * @copyright  (C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
!function ($) {
	"use strict";

/* TOOLTIP PUBLIC CLASS DEFINITION
 * =============================== */
var HKTooltip = function (element, options) { this.init('hktooltip', element, options); };
HKTooltip.prototype = {
	constructor: HKTooltip,

	init: function (type, element, options) {
		var t = this, eventIn, eventOut, triggers, trigger, i;
		t.type = type;
		t.$element = $(element);
		t.options = t.getOptions(options);
		t.enabled = true;
		triggers = t.options.trigger.split(' ');

		for(i = triggers.length; i--;) {
			trigger = triggers[i];
			if (trigger == 'click') {
				t.$element.on('click.' + t.type, t.options.selector, $.proxy(t.toggle, t));
			} else if (trigger != 'manual') {
				eventIn = trigger == 'hover' ? 'mouseenter' : 'focus';
				eventOut = trigger == 'hover' ? 'mouseleave' : 'blur';
				t.$element.on(eventIn + '.' + t.type, t.options.selector, $.proxy(t.enter, t));
				t.$element.on(eventOut + '.' + t.type, t.options.selector, $.proxy(t.leave, t));
			}
		}

		t.options.selector ? (t._options = $.extend({}, t.options, { trigger: 'manual', selector: '' })) : t.fixTitle();
	},

	getOptions: function (options) {
		options = $.extend({}, $.fn[this.type].defaults, this.$element.data(), options);
		if (options.delay && typeof options.delay == 'number') {
			options.delay = { show: options.delay, hide: options.delay };
		}
		return options;
	},

	enter: function (e) {
		var defaults = $.fn[this.type].defaults, options = {}, self;

		this._options && $.each(this._options, function (key, value) {
			if (defaults[key] != value) options[key] = value;
		}, this);

		self = $(e.currentTarget)[this.type](options).data(this.type);

		if (!self.options.delay || !self.options.delay.show) return self.show();

		clearTimeout(this.timeout);
		self.hoverState = 'in';
		this.timeout = setTimeout(function() {
			if (self.hoverState == 'in') self.show();
		}, self.options.delay.show);
	},

	leave: function (e) {
		var self = $(e.currentTarget)[this.type](this._options).data(this.type);

		if (this.timeout) clearTimeout(this.timeout);
		if (!self.options.delay || !self.options.delay.hide) return self.hide();

		self.hoverState = 'out';
		this.timeout = setTimeout(function() {
		if (self.hoverState == 'out') self.hide();
		}, self.options.delay.hide);
	},

	show: function () {
		var $tip, pos, actualWidth, actualHeight, placement, tp, e = $.Event('show');

		if (!this.hasContent() || !this.enabled)
			return;

		this.$element.trigger(e);
		if (e.isDefaultPrevented()) return;
		$tip = this.tip();
		this.setContent();

		if (this.options.animation) {
			$tip.addClass('fade');
		}

		placement = typeof this.options.placement == 'function' ?
			this.options.placement.call(this, $tip[0], this.$element[0]) :
			this.options.placement;

		$tip
			.detach()
			.css({ top: 0, left: 0, display: 'block' });

		this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element);

		pos = this.getPosition();

		actualWidth = $tip[0].offsetWidth;
		actualHeight = $tip[0].offsetHeight;

		switch (placement) {
			case 'bottom':
				tp = {top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2};
				break;
			case 'top':
				tp = {top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2};
				break;
			case 'left':
				tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth};
				break;
			case 'right':
				tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width};
				break;
		}

		this.applyPlacement(tp, placement);
		this.$element.trigger('shown');
	},

	applyPlacement: function(offset, placement){
		var $tip = this.tip(), width = $tip[0].offsetWidth, height = $tip[0].offsetHeight, actualWidth, actualHeight, delta, replace;

		$tip
			.offset(offset)
			.addClass(placement)
			.addClass('in');

		actualWidth = $tip[0].offsetWidth;
		actualHeight = $tip[0].offsetHeight;

		if (placement == 'top' && actualHeight != height) {
			offset.top = offset.top + height - actualHeight;
			replace = true;
		}

		if (placement == 'bottom' || placement == 'top') {
			delta = 0;

			if (offset.left < 0){
				delta = offset.left * -2;
				offset.left = 0;
				$tip.offset(offset);
				actualWidth = $tip[0].offsetWidth;
				actualHeight = $tip[0].offsetHeight;
			}

			this.replaceArrow(delta - width + actualWidth, actualWidth, 'left');
		} else {
			this.replaceArrow(actualHeight - height, actualHeight, 'top');
		}

		if (replace) $tip.offset(offset);
	},

	replaceArrow: function(delta, dimension, position){
		this.arrow().css(position, delta ? (50 * (1 - delta / dimension) + "%") : '');
	},

	setContent: function () {
		var $tip = this.tip(), title = this.getTitle();
		$tip.find('.hk-tooltip-inner')[this.options.html ? 'html' : 'text'](title);
		$tip.removeClass('fade in top bottom left right');
	},

	hide: function () {
		// var that = this, $tip = this.tip(), e = $.Event('hide');
		var that = this, $tip = this.tip(), e = $.Event('hideme');

		this.$element.trigger(e);
		if (e.isDefaultPrevented()) return

		$tip.removeClass('in');

		function removeWithAnimation() {
			var timeout = setTimeout(function () {
				$tip.off($.support.transition.end).detach();
			}, 500);

			$tip.one($.support.transition.end, function () {
				clearTimeout(timeout);
				$tip.detach();
			});
		}

		$.support.transition && this.$tip.hasClass('fade') ?
			removeWithAnimation() :
			$tip.detach();

		this.$element.trigger('hidden');

		return this;
	},

	fixTitle: function () {
		var $e = this.$element;
		if ($e.attr('title') || typeof($e.attr('data-original-title')) != 'string') {
			$e.attr('data-original-title', $e.attr('title') || '').attr('title', '');
		}
	},

	hasContent: function () { return this.getTitle(); },

	getPosition: function () {
		var el = this.$element[0];
		return $.extend({}, (typeof el.getBoundingClientRect == 'function') ? el.getBoundingClientRect() : {
			width: el.offsetWidth
			, height: el.offsetHeight
		}, this.$element.offset());
	},

	getTitle: function () {
		var title, $e = this.$element, o = this.options;
		title = $e.attr('data-original-title') || (typeof o.title == 'function' ? o.title.call($e[0]) :  o.title);
		return title;
	},

	tip: function () { return this.$tip = this.$tip || $(this.options.template); },

	arrow: function() { return this.$arrow = this.$arrow || this.tip().find(".hk-tooltip-arrow"); },

	validate: function () {
		if (!this.$element[0].parentNode) {
			this.hide();
			this.$element = null;
			this.options = null;
		}
	},

	enable: function () { this.enabled = true; },
	disable: function () { this.enabled = false; },
	toggleEnabled: function () { this.enabled = !this.enabled; },

	toggle: function (e) {
		var self = e ? $(e.currentTarget)[this.type](this._options).data(this.type) : this;
		self.tip().hasClass('in') ? self.hide() : self.show();
	},

	destroy: function () {
		this.hide().$element.off('.' + this.type).removeData(this.type);
	}
};


/* TOOLTIP PLUGIN DEFINITION
 * ========================= */

var old = $.fn.hktooltip;

$.fn.hktooltip = function ( option ) {
	return this.each(function () {
		var $this = $(this), data = $this.data('hktooltip'), options = typeof option == 'object' && option;
		if (!data) $this.data('hktooltip', (data = new HKTooltip(this, options)));
		if (typeof option == 'string') data[option]();
	});
};

$.fn.hktooltip.Constructor = HKTooltip;

$.fn.hktooltip.defaults = {
	animation: true,
	placement: 'top',
	selector: false,
	template: '<div class="hk-tooltip"><div class="hk-tooltip-arrow"></div><div class="hk-tooltip-inner"></div></div>',
	trigger: 'hover focus',
	title: '',
	delay: 0,
	html: true,
	container: false
};


/* TOOLTIP NO CONFLICT
 * =================== */
$.fn.hktooltip.noConflict = function () {
	$.fn.hktooltip = old;
	return this;
};

}(window.jQuery);
