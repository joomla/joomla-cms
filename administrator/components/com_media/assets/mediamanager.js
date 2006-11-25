/**
* @version $Id: popup-imagemanager.js 3604 2006-05-24 00:23:00Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
		
		this.folderframe  	= document.getElementById('folderframe');
		this.folderpath  	= document.getElementById('folderpath');
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
	
	submit: function(task)
	{
		var form = window.frames['folderframe'].document.getElementById('mediamanager-form');
		form.task.value = task;
		form.submit();
	},
	
	onloadframe: function()
	{
		var folder = this.getFolder();

		this.folderpath.value = basepath + folder;
		var node = d.getNodeByTitle(folder);
		d.openTo(node, true, true);
		document.getElementById(cStyle).className = 'active';
	},
	
	oncreatefolder: function()
	{
		var dirpath = document.getElementById('dirpath');
		if (document.getElementById('foldername').value.length) {
			dirpath.value = '/'+this.getFolder()
			submitbutton('createfolder');
		}
	},
	
	onuploadfiles: function()
	{
		var dirpath    = document.getElementById('dirpath');
		dirpath.value = '/'+this.getFolder()
		submitbutton('uploadbatch');
	},
	
	setViewType: function(type) 
	{
		var url    = window.frames['folderframe'].location.search.substring(1);
		var folder = url.substring(url.indexOf('cFolder=')+8);
		document.getElementById(type).className = 'active';
		document.getElementById(cStyle).className = '';
		cStyle = type;
		window.frames['folderframe'].location.href='index.php?option=com_media&task=list&tmpl=component&cFolder='+folder+'&listStyle='+type;
	},
	
	getFolder: function()
	{
		var url 	= window.frames['folderframe'].location.search.substring(1);
		var folder  = url.substring(url.indexOf('cFolder=/')+9);
		var args	= new Object();

		// Split query at the comma
		var pairs = url.split("&"); 
		
		// Begin loop through the querystring
		for(var i = 0; i < pairs.length; i++) {
	
			// Look for "name=value"
			var pos = pairs[i].indexOf('='); 
			// if not found, skip to next
			if (pos == -1) continue; 
			// Extract the name
			var argname = pairs[i].substring(0,pos); 
			
			// Extract the value
			var value = pairs[i].substring(pos+1); 
			// Store as a property
			args[argname] = unescape(value); 
		}
		
		return args['cFolder'];
	},
	
	addFile: function() 
	{
		uploads = document.getElementById( 'uploads' );
		upload  = uploads.childNodes[1].cloneNode(true);
		uploads.appendChild( upload );
		return false;
	}
}

document.mediamanager = null;
document.addLoadEvent(function() {
 	document.mediamanager = new JMediaManager();
 	// Added to populate data on iframe load
 	$('folderframe').onload = function() {document.mediamanager.onloadframe();}
 	document.mediamanager.onloadframe();
});