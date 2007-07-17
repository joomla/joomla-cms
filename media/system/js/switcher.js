/**
* @version		$Id$
* @package		Joomla
* @subpackage	Config
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Switcher behavior for configuration component
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Extensions
 * @subpackage	Config
 * @since		1.5
 */
var JSwitcher = new Class({
	initialize: function(toggler, element)
	{
		var self = this;

		togglers = $ES('a', toggler);
		for (i=0; i < togglers.length; i++) {
			togglers[i].addEvent( 'click', function() { self.switchTo(this.getAttribute('id')); } );
		}

		//hide all
		elements = $ES('div', element);
		for (i=0; i < elements.length; i++) {
			this.hide(elements[i])
		}
	},

	switchTo: function(id)
	{
		toggler = $(id);
		element = $('page-'+id);

		if(element)
		{
			//hide old element
			if(this.active) {
				this.hide(this.active);
			}

			//show new element
			this.show(element);

			toggler.addClass('active');
			if (this.test) {
				$(this.test).removeClass('active');
			}
			this.active = element;
			this.test = id;
		}
	},

	hide: function(element) {
		element.setStyle('display', 'none');
	},

	show: function (element) {
		element.setStyle('display', 'block');
	}
});

document.switcher = null;
Window.onDomReady(function(){
 	toggler = $('submenu')
  	element = $('config-document')
  	if(element) {
  		document.switcher = new JSwitcher(toggler, element)
  	 	document.switcher.switchTo('site');
  	}
});