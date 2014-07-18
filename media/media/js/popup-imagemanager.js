/**
 * @copyright	Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JImageManager behavior for media component
 *
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */

(function($, doc) {
	'use strict';

	window.ImageManager = {

		initialize: function()
		{
			var o = this.getUriObject(window.self.location.href),
				q = this.getQueryObject(o.query);

			this.editor = decodeURIComponent(q.e_name);

			// Setup image manager fields object
			this.fields = {
				'url':     doc.getElementById("f_url"),
				'alt':     doc.getElementById("f_alt"),
				'align':   doc.getElementById("f_align"),
				'title':   doc.getElementById("f_title"),
				'caption': doc.getElementById("f_caption"),
				'c_class': doc.getElementById("f_caption_class")
			};

			// Setup image listing objects
			this.folderlist = doc.getElementById('folderlist');
			this.frame      = window.frames.imageframe;
			this.frameurl   = this.frame.location.href;

			// Setup image listing frame
			$('#imageframe').on('load', function(){ ImageManager.onloadimageview(); });

			// Setup folder up button
			$('#upbutton').off('click').on('click', function(){ ImageManager.upFolder(); });
		},

		onloadimageview: function()
		{
			var folder = this.getImageFolder(),
				$form = $('uploadForm'),
				portString = '',
				i, l, a, q;

			// Update the frame url
			this.frameurl = this.frame.location.href;
			this.setFolder(folder);

			a = this.getUriObject($form.attr('action'));
			q = this.getQueryObject(a.query);
			q.folder = folder;
			a.query = $.param(q);

			if (typeof(a.port) !== 'undefined' && a.port != 80)
			{
				portString = ':' + a.port;
			}

			$form.attr('action', a.scheme + '://' + a.domain + portString + a.path + '?' + a.query);
		},

		getImageFolder: function()
		{
			return this.getUriObject(this.frame.location.search.substring(1)).folder;
		},

		/* Called from outside. */
		onok: function()
		{
			var tag     = '',
				attr    = [],
				figclass = '',
				captionclass = '',
				// Get the image tag field information
				url     = this.fields.url.value,
				alt     = this.fields.alt.value,
				align   = this.fields.align.value,
				title   = this.fields.title.value,
				caption = this.fields.caption.value,
				c_class = this.fields.c_class.value;

			if (url) {
				// Set alt attribute
				attr.push('alt="' + alt + '"');

				// Set align attribute
				if (align && !caption)
				{
					attr.push('class="pull-' + align + '"');
				}

				// Set title attribute
				if (title)
				{
					attr.push('title="' + title + '"');
				}

				tag = '<img src="' + url + '" ' + attr.join(' ') + '/>';

				// Process caption
				if (caption)
				{
					if (align)
					{
						figclass = ' class="pull-' + align + '"';
					}

					if (c_class)
					{
						captionclass = ' class="' + c_class + '"';
					}

					tag = '<figure' + figclass + '>' + tag + '<figcaption' + captionclass + '>' + caption + '</figcaption></figure>';
				}
			}

			window.parent.jInsertEditorText(tag, this.editor);

			return true;
		},

		/* Called from outside. */
		setFolder: function(folder, asset, author)
		{
			for(var i = 0, l = this.folderlist.length; i < l; i++)
			{
				if (folder == this.folderlist.options[i].value)
				{
					this.folderlist.selectedIndex = i;
					$(this.folderlist)
						.trigger('liszt:updated') // Mootools
						.trigger('chosen:updated'); // jQuery

					break;
				}
			}

			if (!!asset || !!author)
			{
				this.setFrameUrl(folder, asset, author);
			}
		},

		upFolder: function()
		{
			var path = this.folderlist.value.split('/'),
				search;

			path.pop();
			search = path.join('/');

			this.setFolder(search);
			this.setFrameUrl(search);
		},

		/* Called from outside. */
		populateFields: function(file)
		{
			$("#f_url").val(image_base_path + file);
		},

		/* Does not appear to be used at all */
		showMessage: function(text)
		{
			var $message = $('#message');

			$message.find('>:first-child').remove();
			$message.append(text);
			$('#messages').css('display', 'block');
		},

		/* Does not appear to be used at all */
		refreshFrame: function()
		{
			this.frame.location.href = this.frameurl;
		},

		setFrameUrl: function(folder, asset, author)
		{
			var qs = {
					option: 'com_media',
					view:   'imagesList',
					tmpl:   'component',
					folder: folder,
					asset:  asset,
					author: author
				};

			this.frameurl = 'index.php?' + $.param(qs);
			this.frame.location.href = this.frameurl;
		},

		getQueryObject: function(q)
		{
			var rs = {};

			$.each((q || '').split(/[&;]/), function (key, val) {
				var keys = val.split('=');

				if (keys.length == 2)
				{
					rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
				}
			});

			return rs;
		},

		getUriObject: function(u)
		{
			var bitsAssociate = {},
				bits = u.match(/^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/);

			if (!bits)
			{
				return null;
			}

			$.each(['uri', 'scheme', 'authority', 'domain', 'port', 'path', 'directory', 'file', 'query', 'fragment'], function(key, index) {
				bitsAssociate[key] = bits[index];
			});

			return bitsAssociate;
		}
	};

	$(function () { window.ImageManager.initialize(); });

}(jQuery, document));
