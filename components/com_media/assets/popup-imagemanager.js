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
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */
var JImageManager = new Class({
	initialize: function()
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

		// Setup imave listing frame
		this.imageframe = $('imageframe');
		this.imageframe.manager = this;
		this.imageframe.addEvent('load', function(){ this.manager.onloadimageframe(); });

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

	onloadimageframe: function()
	{
		var folder = this.getImageFolder();
		for(var i = 0; i < this.folderlist.length; i++)
		{
			if(folder == this.folderlist.options[i].value) {
				this.folderlist.selectedIndex = i;
				break;
			}
		}
	},

	getImageFolder: function()
	{
		var url 	= window.frames['imageframe'].location.search.substring(1);
		var args	= this.parseQuery(url);

		return args['folder'];
	},

	onok: function()
	{
		// Get the image tag field information
		var url		= this.fields.url.getValue();
		var alt		= this.fields.alt.getValue();
		var align	= this.fields.align.getValue();
		var title	= this.fields.title.getValue();
		var caption	= this.fields.caption.getValue();

		if (url != '') {
			// Set alt attribute
			if (alt != '') {
				var alt = "alt=\""+alt+"\" ";
			}
			// Set align attribute
			if (align != '') {
				align = "align=\""+align+"\" ";
			}
			// Set align attribute
			if (title != '') {
				title = "title=\""+title+"\" ";
			}
			// Set align attribute
			if (caption != '') {
				caption = 'class="caption"';
			}

			var tag = "<img src=\""+url+"\" "+alt+align+title+caption+" />";
		}

		window.parent.jInsertEditorText(tag);
		return false;
	},

	setFolder: function(folder)
	{
		//this.showMessage('Loading');

		for(var i = 0; i < this.folderlist.length; i++)
		{
			if(folder == this.folderlist.options[i].value) {
				this.folderlist.selectedIndex = i;
				break;
			}
		}
		window.frames['imageframe'].location.href='index.php?option=com_media&task=imgManagerList&tmpl=component&folder=' + folder;
	},

	getFolder: function() {
		return this.folderlist.getValue();
	},

	upFolder: function()
	{
		var currentFolder = this.getFolder();
		if(currentFolder.length < 2) {
			return false;
		}

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
		$("f_url").value = "images/stories"+this.folderlist.options[this.folderlist.selectedIndex].value+file;
	},

	showMessage: function(text)
	{
		var message  = $('message');
		var messages = $('messages');

		if(message.firstChild)
			message.removeChild(message.firstChild);

		message.appendChild(document.createTextNode(text));
		messages.style.display = "block";
	},

	parseQuery: function(query)
	{
		var params = new Object();
		if (!query) {
			return params;
		}
		var pairs = query.split(/[;&]/);
		for ( var i = 0; i < pairs.length; i++ )
		{
			var KeyVal = pairs[i].split('=');
			if ( ! KeyVal || KeyVal.length != 2 ) {
				continue;
			}
			var key = unescape( KeyVal[0] );
			var val = unescape( KeyVal[1] ).replace(/\+ /g, ' ');
			params[key] = val;
	   }
	   return params;
	}
});

document.imagemanager = null;
Window.onDomReady(function(){
	document.imagemanager = new JImageManager();
});