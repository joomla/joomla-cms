/**
* @version $Id:  $
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
 
JImageManager = function() { this.initialize.apply(this, arguments);}
JImageManager.prototype = {

	initialize: function() 
	{	
		var self = this;
		
		var imageview  = null;
		var folderlist = null;	
		
		this.imageview  = document.getElementById('imageview');
		this.folderlist = document.getElementById('folderlist');
	},
	
	onok: function() 
	{
		// Get the image tag field information
		var url		= document.getElementById("f_url").value;
		var alt		= document.getElementById("f_alt").value;
		var border	= document.getElementById("f_border").value;
		var vert	= document.getElementById("f_vert").value;
		var horiz	= document.getElementById("f_horiz").value;
		var width	= document.getElementById("f_width").value;
		var height	= document.getElementById("f_height").value;
		var align	= document.getElementById("f_align").value;

		if (url != '') {
			// Set alt attribute
			if (alt != '') {
				alt = "alt='"+alt+"' ";
			}
			// Set border attribute
			if (border != '') {
				border = "border='"+border+"' ";
			}
			// Set width attribute
			if (width != '') {
				width = "width='"+width+"' ";
			}
			// Set height attribute
			if (height != '') {
				height = "height='"+height+"' ";
			}
			// Set align attribute
			if (align != '') {
				align = "align='"+align+"' ";
			}

			var tag = "<img src='"+url+"' "+alt+border+width+height+align+"/>";
		}
		
		window.parent.jInsertEditorText(tag);
		return false;
	},
		
	setFolder: function(directory)  
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
	
function toggleConstrains(constrains) 
{
	var lockImage = document.getElementById('imgLock');
	var constrains = document.getElementById('constrain_prop');

	if(constrains.checked) 
	{
		lockImage.src = "img/locked.gif";	
		checkConstrains('width') 
	}
	else
	{
		lockImage.src = "img/unlocked.gif";	
	}
}

function checkConstrains(changed) 
{
	//alert(document.form1.constrain_prop);
	var constrains = document.getElementById('constrain_prop');
		
	if(constrains.checked) 
	{
		var obj = document.getElementById('orginal_width');
		var orginal_width = parseInt(obj.value);
		var obj = document.getElementById('orginal_height');
		var orginal_height = parseInt(obj.value);

		var widthObj = document.getElementById('f_width');
		var heightObj = document.getElementById('f_height');
			
		var width = parseInt(widthObj.value);
		var height = parseInt(heightObj.value);

		if(orginal_width > 0 && orginal_height > 0) 
		{
			if(changed == 'width' && width > 0) {
				heightObj.value = parseInt((width/orginal_width)*orginal_height);
			}

			if(changed == 'height' && height > 0) {
				widthObj.value = parseInt((height/orginal_height)*orginal_width);
			}
		}			
	}
}