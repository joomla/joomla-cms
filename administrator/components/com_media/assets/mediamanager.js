/**
* @version		$Id: popup-imagemanager.js 3604 2006-05-24 00:23:00Z Jinx $
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
 * JMediaManager behavior for media component
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */
var JMediaManager = new Class({
	initialize: function()
	{
		this.folderframe  	= $('folderframe');
		this.folderpath  	= $('folderpath');
	},

	submit: function(task)
	{
		var form = window.frames['folderframe'].document.getElementById('mediamanager-form');
		form.task.value = task;
		if ($('username')) {
			form.username.value = $('username').value;
			form.password.value = $('password').value;
		}
		form.submit();
	},

	onloadframe: function()
	{
		var folder = this.getFolder();
		if (folder) {
			this.folderpath.value = basepath+'/'+folder;
		} else {
			this.folderpath.value = basepath;
		}
		var node = d.getNodeByTitle(folder);
		d.openTo(node, true, true);
		$(viewstyle).addClass('active');
	},

	oncreatefolder: function()
	{
		if ($('foldername').value.length) {
			$('dirpath').value = this.getFolder();
			submitbutton('createfolder');
		}
	},

	onuploadfiles: function()
	{
		$('dirpath').value = this.getFolder();
		submitbutton('uploadbatch');
	},

	setViewType: function(type)
	{
		$(type).addClass('active');
		$(viewstyle).removeClass('active');
		viewstyle = type;
		var folder = this.getFolder();
		window.frames['folderframe'].location.href='index.php?option=com_media&task=list&tmpl=component&folder='+folder+'&listStyle='+type;
	},

	getFolder: function()
	{
		var url 	= window.frames['folderframe'].location.search.substring(1);
		var args	= this.parseQuery(url);

		return args['folder'];
	},

	addFile: function()
	{
		var upload = $('uploads').getFirst().clone();
		upload.injectInside($('uploads'));
		return false;
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

document.mediamanager = null;
Window.onDomReady(function(){
 	document.mediamanager = new JMediaManager();
 	// Added to populate data on iframe load
 	$('folderframe').onload = function() {document.mediamanager.onloadframe();}
 	document.mediamanager.onloadframe();
});