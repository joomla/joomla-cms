/**
 * @version      $Id$
 * @package      Joomla
 * @copyright    Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license      GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * JMediaManager behavior for media component
 * 
 * @package Joomla.Extensions
 * @subpackage Media
 * @since 1.5
 */
var MediaManager = {

	initialize: function()
	{
		this.folderpath = $('folderpath');
		this.updatepaths = $$('input.update-folder');

		this.frame = new IFrame('folderframe').addEvent('load', function(){
			MediaManager.onloadframe();
		});
		this.frameurl = this.frame.contentWindow.location.href;

		this.tree = new MooTreeControl({
			div :'media-tree_tree',
			mode :'folders',
			grid :true,
			theme :'components/com_media/assets/mootree.gif',
			onClick: function(node) {
				target = $chk(node.data.target) ? node.data.target
						: '_self';
				window.frames[target].location.href = node.data.url;
			}
		},
		{
			text :'Media',
			open :true,
			data : {
				url :'index.php?option=com_media&view=mediaList&tmpl=component',
				target :'folderframe'
			}
		});
		this.tree.adopt('media-tree');
	},

	submit: function(task)
	{
		form = window.frames['folderframe'].document.getElementById('mediamanager-form');
		form.task.value = task;
		if ($('username')) {
			form.username.value = $('username').value;
			form.password.value = $('password').value;
		}
		form.submit();
	},

	onloadframe: function()
	{
		// Update the frame url
		this.frameurl = this.frame.contentWindow.location.href;

		var folder = this.getFolder();
		if (folder) {
			this.updatepaths.each(function(path) {
				path.set('value', folder);
			});
			this.folderpath.value = basepath+'/'+folder;
			node = this.tree.get('node_'+folder);
			node.toggle(false, true);
		}
		else {
			this.updatepaths.each( function(path) {
				path.set('value', '');
			});
			this.folderpath.value = basepath;
			node = this.tree.root;
		}
		console.log(this.frameurl);return;

		if (node) {
			this.tree.select(node, true);
		}

		$(viewstyle).addClass('active');

		a = this._getUriObject($('uploadForm').getProperty('action'));
		q = $H(this._getQueryObject(a.query));
		q.set('folder', folder);
		var query = [];
		q.each(function(v, k) {
			if ($chk(v)) {
				this.push(k + '=' + v);
			}
		}, query);
		a.query = query.join('&');
		if (a.port) {
			$('uploadForm').setProperty(
				'action',
				a.scheme+'://'+a.domain+':'+a.port+a.path+'?'+a.query
			);
		}
		else {
			$('uploadForm').setProperty(
				'action',
				a.scheme+'://'+a.domain+a.path+'?'+a.query
			);
		}
	},

	oncreatefolder: function()
	{
		if ($('foldername').value.length) {
			$('dirpath').value = this.getFolder();
			submitbutton('createfolder');
		}
	},

	setViewType: function(type)
	{
		$(type).addClass('active');
		$(viewstyle).removeClass('active');
		viewstyle = type;
		var folder = this.getFolder();
		this._setFrameUrl('index.php?option=com_media&view=mediaList&tmpl=component&folder='+folder+'&layout='+type);
	},

	refreshFrame: function()
	{
		this._setFrameUrl();
	},

	getFolder: function()
	{
		var url = this.frame.contentWindow.location.search.substring(1);
		var args = this.parseQuery(url);
		console.log(args.get('folder'));
		return args.get('folder');
	},

	parseQuery: function(query)
	{
		var params = new Hash();
		if (!query) {
			return params;
		}
		var pairs = query.split(/[;&]/);
		for ( var i = 0; i < pairs.length; i++)
		{
			var KeyVal = pairs[i].split('=');
			if (!KeyVal || KeyVal.length != 2) {
				continue;
			}
			params.set(unescape(KeyVal[0]), unescape(KeyVal[1]).replace(/\+ /g, ' '));
		}
		return params;
	},

	_setFrameUrl: function(url) {
		if ($chk(url)) {
			this.frameurl = url;
		}
		this.frame.contentWindow.location.href = this.frameurl;
	},

	_getQueryObject: function(q)
	{
		var vars = q.split(/[&;]/);
		var rs = {};
		if (vars.length) {
			vars.each(function(val) {
				var keys = val.split('=');
				if (keys.length && keys.length == 2) {
					rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
				}
			});
		}
		return rs;
	},

	_getUriObject: function(u)
	{
		var bits = u.match(/^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/);
		return (bits) ? bits.associate([ 'uri', 'scheme', 'authority', 'domain', 'port', 'path', 'directory', 'file', 'query', 'fragment' ]) : null;
	}
};

window.addEvent('domready', function() {
	MediaManager.initialize();
});
