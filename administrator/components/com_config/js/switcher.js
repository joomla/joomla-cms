/**
* @version $Id: admin.config.html.php 2851 2006-03-20 21:45:20Z Jinx $
* @package Joomla
* @subpackage Config
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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

JSwitcher = function() { this.initialize.apply(this, arguments);}
JSwitcher.prototype = {

	initialize: function(toggler, element) 
	{	
		var self = this;
		
		togglers = toggler.getElementsByTagName('A');
		for (i=0; i < togglers.length; i++) {
			togglers[i].onclick = function() {
				self.switchTo(this.getAttribute('id'));
			}
		}
		
		//hide all
		elements = element.getElementsByTagName('DIV');
		for (i=0; i < elements.length; i++) {
			this.hide(elements[i])
		}
	},
	
	switchTo: function(id)
	{
		toggler = document.getElementById(id);
		element = document.getElementById('page-'+id);
		
		if(element) 
		{
			//hide old element
			if(this.active) {
				this.hide(this.active);
			}
		
			//show new element
			this.show(element);
			
			toggler.className = 'active';
			if (this.test) {
				document.getElementById(this.test).className = '';
			}
			this.active = element;
			this.test = id;
		}	
	},

	hide: function(element) {
		this.setVisibility(element, false);
	},

	show: function (element) {
		this.setVisibility(element, true);
	},

	setVisibility: function(element, bShow) { 
		element.style.display = bShow ? "block" : "none"
	}
}

document.switcher = null;
document.addLoadEvent(function() {
 	toggler = document.getElementById('submenu')
  	element = document.getElementById('config-document')
  	if(element) {
  		document.switcher = new JSwitcher(toggler, element)
  	 	document.switcher.switchTo('site');
  	}
});