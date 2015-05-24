/**
 * @copyright	Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JMediaManager behavior for media component
 *
 * @package		Joomla.Extensions
 * @subpackage  Media
 * @since		1.5
 */
;(function( $, scope ) {
	"use strict";

	var MediaManager = scope.MediaManager = {

		/**
		 * Basic setup
		 *
		 * @return  void
		 */
		initialize: function() {
			this.folderpath = $( '#folderpath' );

			this.updatepaths = $( 'input.update-folder' );

			this.frame = window.frames.folderframe;
			this.frameurl = this.frame.location.href;

			this.setTreeviewState();
		},

		/**
		 * Called from outside. Only ever called with task 'folder.delete'
		 *
		 * @param   string  task  [description]
		 *
		 * @return  void
		 */
		submit: function( task ) {
			var form = this.frame.document.getElementById( 'mediamanager-form' );
			form.task.value = task;
			form.action += ('&controller=' + task);

			if ( $( '#username' ).length ) {
				form.username.value = $( '#username' ).val();
				form.password.value = $( '#password' ).val();
			}

			form.submit();
		},

		/**
		 * Called when copy and move execution
		 *
		 * @param   string  task  [description]
		 *
		 * @return  void
		 */
		submitWithTargetPath: function(task)
		{
			var form = window.frames['folderframe'].document.getElementById('mediamanager-form');
			form.task.value = task;
			form.action += ('&controller=' + task);
			if ($('#username').length) {
				form.username.value = $('#username').val();
				form.password.value = $('#password').val();
			}
			var inp = document.createElement("input");
		    inp.type = "hidden";
		    inp.name = "targetPath";

		    var method = task.split('.')[1];
		    if (method == "copy") {
		    	inp.value = $('#copyTarget #folderlist').find(":selected").text();
		    } else if (method == "move") {
		    	inp.value = $('#moveTarget #folderlist').find(":selected").text();
		    }

		    form.appendChild(inp);
			form.submit();
		},

		/**
		 * [onloadframe description]
		 *
		 * @return  {[type]}
		 */
		onloadframe: function() {
			// Update the frame url
			this.frameurl = this.frame.location.href;

			var folder = this.getFolder() || '',
				query = [],
				a = getUriObject( $( '#uploadForm' ).attr( 'action' ) ),
				q = getQueryObject( a.query ),
				k, v;

			this.updatepaths.each( function( path, el ) {
				el.value = folder;
			} );

			this.folderpath.val(basepath + (folder ? '/' + folder : '/'));

			q.folder = folder;

			for ( k in q ) {
				if (!q.hasOwnProperty( k )) { continue; }

				v = q[ k ];
				query.push( k + (v === null ? '' : '=' + v) );
			}

			a.query = query.join( '&' );
			a.fragment = null;

//			$( '#uploadForm' ).attr( 'action', buildUri(a) );
			$( '#' + viewstyle ).addClass( 'active' );
		},

		/**
		 * Switch the view type
		 *
		 * @param  string  type  'thumbs' || 'details'
		 */
		setViewType: function( type ) {
			$( '#' + type ).addClass( 'active' );
			$( '#' + viewstyle ).removeClass( 'active' );
			viewstyle = type;
			var folder = this.getFolder();

			this.setFrameUrl( 'index.php?option=com_media&view=mediaList&tmpl=component&folder=' + folder + '&layout=' + type );
		},

		refreshFrame: function() {
			this.setFrameUrl();
		},

		getFolder: function() {
			var args = getQueryObject( this.frame.location.search.substring( 1 ) );

			args.folder = args.folder === undefined ? '' : args.folder;

			return args.folder;
		},

		setFrameUrl: function( url ) {
			if ( url !== null ) {
				this.frameurl = url;
			}

			this.frame.location.href = this.frameurl;
		},

		setTreeviewState: function(){
			// Load the value from localStorage
			if (typeof(Storage) !== "undefined")
			{
				var $visible = localStorage.getItem('jsidebar');
			}

			// Need to convert the value to a boolean
			$visible = ($visible == 'true') ? true : false;

			// Toggle according to j-sidebar class status or storage saved status
			var classStatus = jQuery('#j-sidebar-container').attr('class');
			if(classStatus.contains('j-toggle-hidden') || $visible)
			{
				jQuery('#treeview').attr('hidden', true);
			}
			else
			{
				jQuery('#treeview').attr('hidden', false);
			}
		},
	};

	/**
	 * Convert a query string to an object
	 *
	 * @param   string  q  A query string (no leading ?)
	 *
	 * @return  object
	 */
	function getQueryObject( q ) {
		var rs = {};

		q = q || '';

		$.each( q.split( /[&;]/ ),
			function( key, val ) {
				var keys = val.split( '=' );

				rs[ decodeURIComponent(keys[ 0 ]) ] = keys.length == 2 ? decodeURIComponent(keys[ 1 ]) : null;
			});

		return rs;
	}

	/**
	 * Break a url into its component parts
	 *
	 * @param   string  u  URL
	 *
	 * @return  object
	 */
	function getUriObject( u ) {
		var bitsAssociate = {},
			bits = u.match( /^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/ );

		$.each([ 'uri', 'scheme', 'authority', 'domain', 'port', 'path', 'directory', 'file', 'query', 'fragment' ],
			function( key, index ) {
				bitsAssociate[ index ] = ( !!bits && !!bits[ key ] ) ? bits[ key ] : '';
			});

		return bitsAssociate;
	}

	/**
	 * Build a url from component parts
	 *
	 * @param   object  o  Such as the return value of `getUriObject()`
	 *
	 * @return  string
	 */
	function buildUri ( o ) {
		return o.scheme + '://' + o.domain +
			(o.port ? ':' + o.port : '') +
			(o.path ? o.path : '/') +
			(o.query ? '?' + o.query : '') +
			(o.fragment ? '#' + o.fragment : '');
	}

	$(function() {
		// Added to populate data on iframe load
		MediaManager.initialize();

		document.updateUploader = function() {
			MediaManager.onloadframe();
		};

		MediaManager.onloadframe();
	});

}( jQuery, window ));
