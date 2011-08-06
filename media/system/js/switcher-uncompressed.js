/**
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Switcher behavior
 *
 * @package		Joomla
 * @since		1.5
 */
var JSwitcher = new Class({
	Implements: [Options, Events],

	togglers: null,
	elements: null,
	current: null,

	options : {
		onShow: function(){},
		onHide: function(){},
		cookieName: 'switcher',
		togglerSelector: 'a',
		elementSelector: 'div.tab',
		elementPrefix: 'page-'
	},

	initialize: function(toggler, element, options) {
		this.setOptions(options);
		this.togglers = document.id(toggler).getElements(this.options.togglerSelector);
		this.elements = document.id(element).getElements(this.options.elementSelector);

		if ((this.togglers.length == 0) || (this.togglers.length != this.elements.length)) {
			return;
		}

		this.hideAll();

		this.togglers.each(function(el) {
			el.addEvent('click', this.display.bind(this, el.id));
		}.bind(this));

		var first = [Cookie.read(this.options.cookieName), this.togglers[0].id].pick();
		this.display(first);
	},

	display: function(togglerID) {
		var toggler = document.id(togglerID);
		var element = document.id(this.options.elementPrefix+togglerID);

		if (toggler == null || element == null || toggler == this.current) {
			return this;
		}

		if (this.current != null) {
			this.hide(document.id(this.options.elementPrefix+this.current));
			document.id(this.current).removeClass('active');
		}

		this.show(element);
		toggler.addClass('active');

		this.current = toggler.id;
		Cookie.write(this.options.cookieName, this.current);
	},

	hide: function(element) {
		this.fireEvent('hide', element);
		element.setStyle('display', 'none');
	},

	hideAll: function() {
		this.elements.setStyle('display', 'none');
		this.togglers.removeClass('active');
	},

	show: function (element) {
		this.fireEvent('show', element);
		element.setStyle('display', 'block');
	}
});
