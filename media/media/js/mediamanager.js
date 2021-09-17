/**
 * @copyright	(C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
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

			var self = this;

			$(this.frame).one('load', function (e) {
				self.frameurl = self.frame.location.href;
			});
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

			if ( $( '#username' ).length ) {
				form.username.value = $( '#username' ).val();
				form.password.value = $( '#password' ).val();
			}

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
				a = getUriObject( $( '#uploadForm' ).prop( 'action' ) ),
				q = getQueryObject( a.query ),
				k, v;

			this.updatepaths.each( function( path, el ) {
				el.value = folder;
			} );

			this.folderpath.value = scope.basepath + (folder ? '/' + folder : '');

			q.folder = folder;

			for ( k in q ) {
				if (!q.hasOwnProperty( k )) { continue; }

				v = q[ k ];
				query.push(encodeURIComponent(k) + (v === null ? '' : '=' + encodeURIComponent(v)));
			}

			a.query = query.join( '&' );
			a.fragment = null;

			$( '#uploadForm' ).prop( 'action', buildUri(a) );
			$( '#' + scope.viewstyle ).addClass( 'active' );
		},

		/**
		 * Switch the view type
		 *
		 * @param  string  type  'thumbs' || 'details'
		 */
		setViewType: function( type ) {
			$( '#' + type ).addClass( 'active' );
			$( '#' + scope.viewstyle ).removeClass( 'active' );
			scope.viewstyle = type;
			var folder = this.getFolder();

			folder = encodeURIComponent(folder);
			type = encodeURIComponent(type);

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
			$(MediaManager.frame).one('load', function() {
				MediaManager.onloadframe();
			});
		};

		document.updateUploader();
	});

}( jQuery, window ));

