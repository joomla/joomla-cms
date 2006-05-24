/**
* @version $Id$
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
 * JImageManager behavior for media component
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */
 
JImageManager = function() { this.constructor.apply(this, arguments);}
JImageManager.prototype = {

	constructor: function() 
	{	
		var self = this;
		
		var imageview  = null;
		var folderlist = null;	
		
		this.imageview  	= document.getElementById('imageview');
		this.folderlist 	= document.getElementById('folderlist');
		this.uploadtoggler  = document.getElementById('uploadtoggler');
				
		//Setup events
		this.registerEvent(this.uploadtoggler, 'click');
		
		//Setup effect
		this.uploadpane = new fx.Height(document.getElementById('uploadpane'), {opacity:true, duration: 200});
		this.uploadpane.hide();
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
		if(Element.hasClassName(event.element, 'toggler-down')) {
			Element.removeClassName(event.element, 'toggler-down');
			window.top.document.popup.decreaseHeight(50);
		} else {
			Element.addClassName(event.element, 'toggler-down');
			window.top.document.popup.increaseHeight(50);
		}
		
		this.uploadpane.toggle();
	},
	
	onok: function() 
	{
		// Get the image tag field information
		var url		= document.getElementById("f_url").value;
		var alt		= document.getElementById("f_alt").value;
		var align	= document.getElementById("f_align").value;

		if (url != '') {
			// Set alt attribute
			if (alt != '') {
				alt = "alt='"+alt+"' ";
			}
			// Set align attribute
			if (align != '') {
				align = "align='"+align+"' ";
			}

			var tag = "<img src='"+url+"' "+alt+align+"/>";
		}
		
		window.parent.jInsertEditorText(tag);
		return false;
	},
		
	setFolder: function(directory, refresh)  
	{
		//this.showMessage('Loading');
		
		for(var i = 0; i < this.folderlist.length; i++)
		{
			var folder = this.folderlist.options[i].text;
			if(folder == directory) {
				this.folderlist.selectedIndex = i;
				break;
			}
		}
		
		this.imageview.src   = 'index.php?option=com_media&task=imgManagerList&tmpl=component.html&folder=' + directory;		
	
		if(refresh) {
			this.imageview.location.reload(true);
		}
	},
	
	getFolder: function() {
		return this.folderlist.options[this.folderlist.selectedIndex].text;
	},
	
	upFolder: function() 
	{
		var currentFolder = this.folderlist.options[this.folderlist.selectedIndex].text;
		if(currentFolder.length < 2)
			return false;
		
		var folders = currentFolder.split('/');
			
		var search = '/';

		for(var i = 0; i < folders.length - 1; i++) {
			search += folders[i];
		}
	
		for(var i = 0; i < this.folderlist.length; i++)
		{
			var thisFolder = this.folderlist.options[i].text;
			if(thisFolder == search)
			{
				this.folderlist.selectedIndex = i;
				var newFolder = this.folderlist.options[i].value;
				this.setFolder(newFolder);
				break;
			}
		}
	},
	
	populateFields: function(file) {
		document.getElementById("f_url").value = "images"+file;
	},
	
	showMessage: function(text) 
	{
		var message  = document.getElementById('message');
		var messages = document.getElementById('messages');
		
		if(message.firstChild)
			message.removeChild(message.firstChild);

		message.appendChild(document.createTextNode(text));
		messages.style.display = "block";
	}
	
}

document.imagemanager = null;
document.addLoadEvent(function() {
 	document.imagemanager = new JImageManager();
});