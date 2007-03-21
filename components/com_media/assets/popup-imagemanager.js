/**
* @version		$Id: popup-imagemanager.js 3997 2006-06-12 03:59:51Z spacemonkey $
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
		this.popup = window.top.document.popup;

		// Setup image manager fields object
		this.fields			= new Object();
		this.fields.url		= $("f_url");
		this.fields.alt		= $("f_alt");
		this.fields.align	= $("f_align");
		this.fields.title	= $("f_title");
		this.fields.caption	= $("f_caption");

		// Setup image listing objects
		this.folderlist = $('folderlist');

		// Setup image listing frame
		this.imageframe = $('imageframe');
		this.imageframe.manager = this;
		this.imageframe.addEvent('load', function(){ this.manager.onloadimageview(); });

		// Setup folder up button
		this.upbutton = $('upbutton');
		this.upbutton.manager = this;
		this.upbutton.addEvent('click', function(){ this.manager.upFolder(); });

		// Setup upload form objects
		this.uploadtoggler = $('uploadtoggler');
		this.uploadtoggler.manager = this;
		this.uploadtoggler.addEvent('click', function(){
			if(this.hasClass('toggler-down')) {
				this.removeClass('toggler-down');
//				this.manager.popup.decreaseHeight(50);
			} else {
				this.addClass('toggler-down');
//				this.manager.popup.increaseHeight(50);
			}
			this.manager.uploadpanefx.toggle();
		});

		//Setup effect
		this.uploadpane = $('uploadpane');
		this.uploadpane.setProperty('height', this.uploadpane.getElement('iframe').getProperty('height'));
		this.uploadpanefx = new Fx.Slide(this.uploadpane, {opacity:true, duration: 200});
		this.uploadpanefx.hide();
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
		
		this.imageview.src   = 'index.php?option=com_media&task=imgManagerList&tmpl=component&folder=' + directory;		
	
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
		document.getElementById("f_url").value = "images/stories"+this.getFolder()+file;
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
document.addLoadEvent = function() {
 	document.imagemanager = new JImageManager();
};