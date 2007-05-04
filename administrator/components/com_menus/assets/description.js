/**
* @version		$Id: $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Unobtrusive Javascript Description Switcher library
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Menu Manager
 * @since		1.5
 */
JDescriptionSwitcher = function() { this.constructor.apply(this, arguments);}
JDescriptionSwitcher.prototype =
{
	constructor: function() 
	{	
		var self = this;
		
		this.target	= document.getElementById('jdescription');
		this.base	= document.getElementById('jdescription').innerHTML;

		var a = document.getElementsByTagName('A');
		for(var no=0;no<a.length;no++){
			if(a[no].hasAttribute('title')){
				a[no].onmouseover = function(){document.descriptionswitcher.show(this.title);};
				a[no].onmouseout = function(){document.descriptionswitcher.revert();};
			}			
		}
	},

	show: function(message)
	{
		this.target.innerHTML = message;
	},

	revert: function()
	{
		this.target.innerHTML = this.base;
	}
}

Window.onDomReady(function(){
	document.descriptionswitcher = new JDescriptionSwitcher();
});