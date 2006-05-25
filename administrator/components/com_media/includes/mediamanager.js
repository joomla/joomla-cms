/**
* @version $Id: popup-imagemanager.js 3604 2006-05-24 00:23:00Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JMediaManager behavior for media component
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */
 
JMediaManager = function() { this.constructor.apply(this, arguments);}
JMediaManager.prototype = {

	constructor: function() 
	{	
		var self = this;
		
		this.fileview  	= document.getElementById('fileview');
	},
	
	registerEvent: function(target,type,args) 
	{
		//use a closure to keep scope
		var self = this;
			
		if (target.addEventListener)   { 
    		target.addEventListener(type,onEvent,true);
		} else if (target.attachEvent) { 
	  		target.attachEvent('on'+type,onEvent);
		} 
		
		function onEvent(e)	{
			e = e||window.event;
			e.element = target;
			return self["on"+type](e, args);
		}
	},
	
	onload: function()
	{
		var url 	= window.frames['fileview'].location.search.substring(1);
		var folder  = url.substring(url.indexOf('cFolder=/')+9);
			
		d.openToByName(folder, true);
	},
	
	setViewType: function(type) 
	{
		var url    = window.frames['fileview'].location.search.substring(1);
		var folder = url.substring(url.indexOf('cFolder=')+8);
		window.frames['fileview'].location.href='index.php?option=com_media&task=list&tmpl=component.html&cFolder=' + folder + '&listStyle=' + type;
	}
}

document.mediamanager = null;
document.addLoadEvent(function() {
 	document.mediamanager = new JMediaManager();
});