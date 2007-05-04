/**
* @version		$Id$
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
 var JImageUpload = new Class({
	initialize: function()
	{
		var self = this;

		this.upload  = $('upload');
		this.message = $('message');

		//Setup events
		this.upload.addEvent('click', function(){
			self.message.setStyle('display', 'none');
		});
	},

	onupload: function()
	{
		if(window.parent.document.imagemanager) {
			var folder = window.parent.document.imagemanager.getFolder();
			document.adminForm.dirPath.value=folder;
		}

		submitform('upload');

		this.upload.addClass('uploading');
		this.upload.setAttribute('disabled', 'disabled');
	}
});

document.imageupload = null;
Window.onDomReady(function(){
	document.imageupload = new JImageUpload();
});