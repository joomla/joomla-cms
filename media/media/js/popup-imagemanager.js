/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JImageManager behavior for media component
 *
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */

(function() {
var ImageManager = this.ImageManager = {
	initialize: function()
	{
		o = this._getUriObject(window.self.location.href);
		//console.log(o);
		q = new Hash(this._getQueryObject(o.query));
		this.editor = decodeURIComponent(q.get('e_name'));

		// Setup image manager fields object
		this.fields			= new Object();
		this.fields.url		= document.id("f_url");
		this.fields.alt		= document.id("f_alt");
		this.fields.align	= document.id("f_align");
		this.fields.title	= document.id("f_title");
		this.fields.caption	= document.id("f_caption");

		// Setup image listing objects
		this.folderlist = document.id('folderlist');

		this.frame		= window.frames['imageframe'];
		this.frameurl	= this.frame.location.href;

		// Setup imave listing frame
		this.imageframe = document.id('imageframe');
		this.imageframe.manager = this;
		this.imageframe.addEvent('load', function(){ ImageManager.onloadimageview(); });

		// Setup folder up button
		this.upbutton = document.id('upbutton');
		this.upbutton.removeEvents('click');
		this.upbutton.addEvent('click', function(){ ImageManager.upFolder(); });
	},

	onloadimageview: function()
	{
		// Update the frame url
		this.frameurl = this.frame.location.href;

		var folder = this.getImageFolder();
		for(var i = 0; i < this.folderlist.length; i++)
		{
			if(folder == this.folderlist.options[i].value) {
				this.folderlist.selectedIndex = i;
				break;
			}
		}

		a = this._getUriObject(document.id('uploadForm').getProperty('action'));
		//console.log(a);
		q = new Hash(this._getQueryObject(a.query));
		q.set('folder', folder);
		var query = [];
		q.each(function(v, k){
			if (v !== null) {
				this.push(k+'='+v);
			}
		}, query);
		a.query = query.join('&');
		var portString = '';
		if (typeof(a.port) !== 'undefined' && a.port != 80) {
			portString = ':'+a.port;
		}
		document.id('uploadForm').setProperty('action', a.scheme+'://'+a.domain+portString+a.path+'?'+a.query);
	},

	getImageFolder: function()
	{
		var url 	= this.frame.location.search.substring(1);
		var args	= this.parseQuery(url);

		return args['folder'];
	},

	onok: function()
	{
		extra = '';
		// Get the image tag field information
		var url		= this.fields.url.get('value');
		var alt		= this.fields.alt.get('value');
		var align	= this.fields.align.get('value');
		var title	= this.fields.title.get('value');
		var caption	= this.fields.caption.get('value');

		if (url != '') {
			// Set alt attribute
			if (alt != '') {
				extra = extra + 'alt="'+alt+'" ';
			} else {
				extra = extra + 'alt="" ';
			}
			// Set align attribute
			if (align != '') {
				extra = extra + 'align="'+align+'" ';
			}
			// Set align attribute
			if (title != '') {
				extra = extra + 'title="'+title+'" ';
			}
			// Set align attribute
			if (caption != '') {
				extra = extra + 'class="caption" ';
			}

			var tag = "<img src=\""+url+"\" "+extra+"/>";
		}

		window.parent.jInsertEditorText(tag, this.editor);
		return false;
	},

	setFolder: function(folder,asset,author)
	{
		//this.showMessage('Loading');

		for(var i = 0; i < this.folderlist.length; i++)
		{
			if(folder == this.folderlist.options[i].value) {
				this.folderlist.selectedIndex = i;
				break;
			}
		}
		this.frame.location.href='index.php?option=com_media&view=imagesList&tmpl=component&folder=' + folder + '&asset=' + asset + '&author=' + author;
	},

	getFolder: function() {
		return this.folderlist.get('value');
	},

	upFolder: function()
	{
		var currentFolder = this.getFolder();

		if(currentFolder.length < 2) {
			return false;
		}

		var folders = currentFolder.split('/');
		var search = '';

		for(var i = 0; i < folders.length - 1; i++) {
			search += folders[i];
			search += '/';
		}

		// remove the trailing slash
		search = search.substring(0, search.length - 1);

		for(var i = 0; i < this.folderlist.length; i++)
		{
			var thisFolder = this.folderlist.options[i].value;

			if(thisFolder == search)
			{
				this.folderlist.selectedIndex = i;
				var newFolder = this.folderlist.options[i].value;
				this.setFolder(newFolder);
				break;
			}
		}
	},

	populateFields: function(file)
	{
		document.id("f_url").value = image_base_path+file;
	},

	showMessage: function(text)
	{
		var message  = document.id('message');
		var messages = document.id('messages');

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
	},

	refreshFrame: function()
	{
		this._setFrameUrl();
	},

	_setFrameUrl: function(url)
	{
		if (url != null) {
			this.frameurl = url;
		}
		this.frame.location.href = this.frameurl;
	},

	_getQueryObject: function(q) {
		var vars = q.split(/[&;]/);
		var rs = {};
		if (vars.length) vars.each(function(val) {
			var keys = val.split('=');
			if (keys.length && keys.length == 2) rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
		});
		return rs;
	},

	_getUriObject: function(u){
		var bits = u.match(/^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/);
		return (bits)
			? bits.associate(['uri', 'scheme', 'authority', 'domain', 'port', 'path', 'directory', 'file', 'query', 'fragment'])
			: null;
	}
};
})(document.id);

window.addEvent('domready', function(){
	ImageManager.initialize();
});
