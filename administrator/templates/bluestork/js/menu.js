/**
 * @package		Joomla.Administrator
 * @subpackage	templates.bluestork
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

var Joomla = Joomla || {};

/**
 * Joomla Menu javascript behavior
 */
Joomla.Menu = new Class({
	Implements: [Options],

	options: {
		disabled: false
	},

	initialize: function(element, options) {
		this.setOptions(options);
		this.element = document.id(element);

		// equalize width of the child LI elements
		this.element.getElements('li').filter('.node').getElement('ul').each(this._equalizeWidths);

		if (!this.options.disabled) {
			this._addMouseEvents();
		}

		this.element.store('menu', this);
	},

	disable: function() {
		var elements = this.element.getElements('li');
		$$(this.element, elements).addClass('disabled');
		elements.removeEvents('mouseenter').removeEvents('mouseleave');
	},

	enable: function() {
		$$(this.element, this.element.getElements('li')).removeClass('disabled');
		this._addMouseEvents();
	},

	_addMouseEvents: function() {
		this.element.getElements('li')
			.removeEvents('mouseenter')
			.removeEvents('mouseleave')
			.addEvents({
				'mouseenter': function() {
					var ul = this.getElement('ul');
					if (ul) { ul.fireEvent('show'); }
					this.addClass('hover');
				},
				'mouseleave': function() {
					var ul = this.getElement('ul');
					if (ul) { ul.fireEvent('hide'); }
					this.removeClass('hover');
				}
			});
	},

	_equalizeWidths: function(el) {
		var offsetWidth  = 0;
		var children = el.getElements('li');

		//find longest child
		children.each(function(node) {
			offsetWidth = (offsetWidth >= node.offsetWidth) ? offsetWidth :  node.offsetWidth;
		});

		$$(children, el).setStyle('width', offsetWidth);
	}
});

window.addEvent('domready', function() {
	var el = document.id('menu');
	new Joomla.Menu(el, (el.hasClass('disabled') ? {disabled: true} : {}));
});