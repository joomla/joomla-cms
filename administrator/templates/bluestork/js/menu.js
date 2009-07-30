/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.bluestork
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

var Joomla = Joomla || {};

/**
 * Joomla Menu javascript behavior
 */
Joomla.JMenu = new Class({
	initialize: function(element) {
		this.element = document.id(element);
		var elements = this.element.getElements('li');
		elements.each(function(el) {
			el.addEvent('mouseover', function(){ this.addClass('hover'); });
			el.addEvent('mouseout', function(){ this.removeClass('hover'); });

			//find nested UL
			var nested = el.getElement('ul');
			if (!nested) {
				return;
			}

			var offsetWidth  = 0;
			var children = nested.getElements('li');

			//find longest child
			children.each(function(node) {
				offsetWidth = (offsetWidth >= node.offsetWidth) ? offsetWidth :  node.offsetWidth;
			});

			children.setStyle('width', offsetWidth)
			nested.setStyle('width', offsetWidth);
		});
		this.element.store('menu', this);
	}
});

window.addEvent('domready', function() {
	var element = document.id('menu');
	if(!element.hasClass('disabled')) {
		new Joomla.JMenu(element);
	}
});