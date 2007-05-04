/**
* @version		$Id: popup-imageupload.js 3997 2006-06-12 03:59:51Z spacemonkey $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JImageUpload behavior for media component
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */
 
JImageUpload = function() { this.constructor.apply(this, arguments);}
JImageUpload.prototype = {

	constructor: function() 
	{	
		var self = this;
		
		this.upload  = document.getElementById('upload');
		this.message = document.getElementById('message');
		
		//Setup events
		this.registerEvent(this.upload, 'click');
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
	
	onclick: function(event, args)  
	{
		this.message.style.display = 'none';	
	},
	
	onupload: function() 
	{
		if(window.parent.document) {
			var folder = window.parent.document.imagemanager.getFolder();
			document.adminForm.dirPath.value=folder;
		}
		
		submitform('upload');
		
		$('upload').addClass('uploading');
		$('upload').setProperty('disabled', 'disabled');	
	}
}

document.imageupload = null;
Window.onDomReady(function(){
	document.imageupload = new JImageUpload();
});