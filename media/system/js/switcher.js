/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
		onShow: $empty,
		onHide: $empty,
		cookieName: 'switcher',
		togglerSelector: 'a',
		elementSelector: 'div',
		elementPrefix: 'page-'
	},

	initialize: function(toggler, element, options) {
		this.setOptions(options);
		this.togglers = $(toggler).getElements(this.options.togglerSelector);
		this.elements = $(element).getElements(this.options.elementSelector);

		if ((this.togglers.length == 0) || (this.togglers.length != this.elements.length)) {
			return;
		}

		this.hideAll();

		this.togglers.each(function(el) {
			el.addEvent('click', this.display.bind(this, el.id));
		}.bind(this));

		var first = $pick(Cookie.read(this.options.cookieName), this.togglers[0].id);
		this.display(first);
	},
	
	display: function(togglerID) {
		var toggler = $(togglerID);
		var element = $(this.options.elementPrefix+togglerID);

		if (!$chk(toggler) || !$chk(element) || toggler == this.current) {
			return this;
		}

		if ($chk(this.current)) {
			this.hide($(this.options.elementPrefix+this.current));
			$(this.current).removeClass('active');
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
		this.elements.each(function(el) {
			el.setStyle('display', 'none');
		});
	},

	show: function (element) {
		this.fireEvent('show', element);
		element.setStyle('display', 'block');
	}
});