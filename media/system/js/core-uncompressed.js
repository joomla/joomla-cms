/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
Joomla = window.Joomla || {};

// Only define editors if not defined
Joomla.editors = Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
Joomla.editors.instances = Joomla.editors.instances || {};

(function( Joomla, document ) {
	"use strict";

	/**
	 * Generic submit form
	 */
	Joomla.submitform = function(task, form, validate) {

		if (!form) {
			form = document.getElementById('adminForm');
		}

		if (task) {
			form.task.value = task;
		}

		// Toggle HTML5 validation
		form.noValidate = !validate;
		form.setAttribute('novalidate', !validate)

		// Submit the form.
		// Create the input type="submit"
		var button = document.createElement('input');
		button.style.display = 'none';
		button.type = 'submit';

		// Append it and click it
		form.appendChild(button).click();

		// If "submit" was prevented, make sure we don't get a build up of buttons
		form.removeChild(button);
	};

	/**
	 * Default function. Usually would be overriden by the component
	 */
	Joomla.submitbutton = function( pressbutton ) {
		Joomla.submitform( pressbutton );
	};

	/**
	 * Custom behavior for JavaScript I18N in Joomla! 1.6
	 *
	 * Allows you to call Joomla.JText._() to get a translated JavaScript string pushed in with JText::script() in Joomla.
	 */
	Joomla.JText = {
		strings:   {},

		/**
		 * Translates a string into the current language.
		 *
		 * @param {String} key   The string to translate
		 * @param {String} def   Default string
		 *
		 * @returns {String}
		 */
		'_': function( key, def ) {

			// Check for new strings in the optionsStorage, and load them
			var newStrings = Joomla.getOptions('joomla.jtext');
			if ( newStrings ) {
				this.load(newStrings);

				// Clean up the optionsStorage from useless data
				Joomla.loadOptions({'joomla.jtext': null});
			}

			def = def === undefined ? '' : def;
			key = key.toUpperCase();

			return this.strings[ key ] !== undefined ? this.strings[ key ] : def;
		},

		/**
		 * Load new strings in to Joomla.JText
		 *
		 * @param {Object} object  Object with new strings
		 * @returns {Joomla.JText}
		 */
		load: function( object ) {
			for ( var key in object ) {
				if (!object.hasOwnProperty(key)) continue;
				this.strings[ key.toUpperCase() ] = object[ key ];
			}

			return this;
		}
	};

	/**
	 * Joomla options storage
	 *
	 * @type {{}}
	 *
	 * @since 3.7.0
	 */
	Joomla.optionsStorage = Joomla.optionsStorage || null;

	/**
	 * Get script(s) options
	 *
	 * @param {String} key  Name in Storage
	 * @param mixed    def  Default value if nothing found
	 *
	 * @return mixed
	 *
	 * @since 3.7.0
	 */
	Joomla.getOptions = function( key, def ) {
		// Load options if they not exists
		if (!Joomla.optionsStorage) {
			Joomla.loadOptions();
		}

		return Joomla.optionsStorage[key] !== undefined ? Joomla.optionsStorage[key] : def;
	};

	/**
	 * Load new options from given options object or from Element
	 *
	 * @param {Object|undefined} options   The options object to load. Eg {"com_foobar" : {"option1": 1, "option2": 2}}
	 *
	 * @since 3.7.0
	 */
	Joomla.loadOptions = function( options ) {
		// Load form the script container
		if (!options) {
			var elements = document.querySelectorAll('.joomla-script-options.new'),
				str, element, option;

			for (var i = 0, l = elements.length; i < l; i++) {
				element = elements[i];
				str     = element.text || element.textContent;
				option  = JSON.parse(str);

				option ? Joomla.loadOptions(option) : null;

				element.className = element.className.replace(' new', ' loaded');
			}

			return;
		}

		// Initial loading
		if (!Joomla.optionsStorage) {
			Joomla.optionsStorage = options;
		}
		// Merge with existing
		else {
			for (var p in options) {
				if (options.hasOwnProperty(p)) {
					Joomla.optionsStorage[p] = options[p];
				}
			}
		}
	};

	/**
	 * Method to replace all request tokens on the page with a new one.
	 * Used in Joomla Installation
	 */
	Joomla.replaceTokens = function( newToken ) {
		if (!/^[0-9A-F]{32}$/i.test(newToken)) { return; }

		var els = document.getElementsByTagName( 'input' ),
			i, el, n;

		for ( i = 0, n = els.length; i < n; i++ ) {
			el = els[i];

			if ( el.type == 'hidden' && el.value == '1' && el.name.length == 32 ) {
				el.name = newToken;
			}
		}
	};

	/**
	 * USED IN: administrator/components/com_banners/views/client/tmpl/default.php
	 * Actually, probably not used anywhere. Can we deprecate in favor of <input type="email">?
	 *
	 * Verifies if the string is in a valid email format
	 *
	 * @param string
	 * @return boolean
	 */
	Joomla.isEmail = function( text ) {
		var regex = /^[\w.!#$%&‚Äô*+\/=?^`{|}~-]+@[a-z0-9-]+(?:\.[a-z0-9-]{2,})+$/i;
		return regex.test( text );
	};

	/**
	 * USED IN: all list forms.
	 *
	 * Toggles the check state of a group of boxes
	 *
	 * Checkboxes must have an id attribute in the form cb0, cb1...
	 *
	 * @param   mixed   The number of box to 'check', for a checkbox element
	 * @param   string  An alternative field name
	 */
	Joomla.checkAll = function( checkbox, stub ) {
		if (!checkbox.form) return false;

		stub = stub ? stub : 'cb';

		var c = 0,
			i, e, n;

		for ( i = 0, n = checkbox.form.elements.length; i < n; i++ ) {
			e = checkbox.form.elements[ i ];

			if ( e.type == checkbox.type && e.id.indexOf( stub ) === 0 ) {
				e.checked = checkbox.checked;
				c += e.checked ? 1 : 0;
			}
		}

		if ( checkbox.form.boxchecked ) {
			checkbox.form.boxchecked.value = c;
		}

		return true;
	};

	/**
	 * Render messages send via JSON
	 * Used by some javascripts such as validate.js
	 *
	 * @param   object  messages    JavaScript object containing the messages to render. Example:
	 *                              var messages = {
	 *                              	"message": ["Message one", "Message two"],
	 *                              	"error": ["Error one", "Error two"]
	 *                              };
	 * @return  void
	 */
	Joomla.renderMessages = function( messages ) {
		Joomla.removeMessages();

		var messageContainer = document.getElementById( 'system-message-container' ),
			type, typeMessages, messagesBox, title, titleWrapper, i, messageWrapper, alertClass;

		for ( type in messages ) {
			if ( !messages.hasOwnProperty( type ) ) { continue; }
			// Array of messages of this type
			typeMessages = messages[ type ];

			// Create the alert box
			messagesBox = document.createElement( 'div' );

			// Message class
			alertClass = (type == 'notice') ? 'alert-info' : 'alert-' + type;
			alertClass = (type == 'message') ? 'alert-success' : alertClass;

			messagesBox.className = 'alert ' + alertClass;

			// Close button
			var buttonWrapper = document.createElement( 'button' );
			buttonWrapper.setAttribute('type', 'button');
			buttonWrapper.setAttribute('data-dismiss', 'alert');
			buttonWrapper.className = 'close';
			buttonWrapper.innerHTML = '×';
			messagesBox.appendChild( buttonWrapper );

			// Title
			title = Joomla.JText._( type );

			// Skip titles with untranslated strings
			if ( typeof title != 'undefined' ) {
				titleWrapper = document.createElement( 'h4' );
				titleWrapper.className = 'alert-heading';
				titleWrapper.innerHTML = Joomla.JText._( type );
				messagesBox.appendChild( titleWrapper );
			}

			// Add messages to the message box
			for ( i = typeMessages.length - 1; i >= 0; i-- ) {
				messageWrapper = document.createElement( 'div' );
				messageWrapper.innerHTML = typeMessages[ i ];
				messagesBox.appendChild( messageWrapper );
			}

			messageContainer.appendChild( messagesBox );
		}
	};


	/**
	 * Remove messages
	 *
	 * @return  void
	 */
	Joomla.removeMessages = function() {
		var messageContainer = document.getElementById( 'system-message-container' );

		// Empty container with a while for Chrome performance issues
		while ( messageContainer.firstChild ) messageContainer.removeChild( messageContainer.firstChild );

		// Fix Chrome bug not updating element height
		messageContainer.style.display = 'none';
		messageContainer.offsetHeight;
		messageContainer.style.display = '';
	};

	/**
	 * Treat AJAX errors.
	 * Used by some javascripts such as sendtestmail.js and permissions.js
	 *
	 * @param   object  xhr          XHR object.
	 * @param   string  textStatus   Type of error that occurred.
	 * @param   string  error        Textual portion of the HTTP status.
	 *
	 * @return  object  JavaScript object containing the system error message.
	 *
	 * @since  3.6.0
	 */
	Joomla.ajaxErrorsMessages = function( xhr, textStatus, error ) {
		var msg = {};

		// For jQuery jqXHR
		if (textStatus === 'parsererror')
		{
			// Html entity encode.
			var encodedJson = xhr.responseText.trim();

			var buf = [];
			for (var i = encodedJson.length-1; i >= 0; i--) {
				buf.unshift( [ '&#', encodedJson[i].charCodeAt(), ';' ].join('') );
			}

			encodedJson = buf.join('');

			msg.error = [ Joomla.JText._('JLIB_JS_AJAX_ERROR_PARSE').replace('%s', encodedJson) ];
		}
		else if (textStatus === 'nocontent')
		{
			msg.error = [ Joomla.JText._('JLIB_JS_AJAX_ERROR_NO_CONTENT') ];
		}
		else if (textStatus === 'timeout')
		{
			msg.error = [ Joomla.JText._('JLIB_JS_AJAX_ERROR_TIMEOUT') ];
		}
		else if (textStatus === 'abort')
		{
			msg.error = [ Joomla.JText._('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT') ];
		}
		// For vannila XHR
		else if (xhr.responseJSON && xhr.responseJSON.message)
		{
			msg.error = [ Joomla.JText._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status) + ' <em>' + xhr.responseJSON.message + '</em>' ];
		}
		else if (xhr.statusText)
		{
			msg.error = [ Joomla.JText._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status) + ' <em>' + xhr.statusText + '</em>' ];
		}
		else
		{
			msg.error = [ Joomla.JText._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status) ];
		}

		return msg;
	};

	/**
	 * USED IN: administrator/components/com_cache/views/cache/tmpl/default.php
	 * administrator/components/com_installer/views/discover/tmpl/default_item.php
	 * administrator/components/com_installer/views/update/tmpl/default_item.php
	 * administrator/components/com_languages/helpers/html/languages.php
	 * libraries/joomla/html/html/grid.php
	 *
	 * @param isitchecked
	 * @param form
	 * @return
	 */
	Joomla.isChecked = function( isitchecked, form ) {
		if ( typeof form  === 'undefined' ) {
			form = document.getElementById( 'adminForm' );
		}

		form.boxchecked.value = isitchecked ? parseInt(form.boxchecked.value) + 1 : parseInt(form.boxchecked.value) - 1;

		// If we don't have a checkall-toggle, done.
		if ( !form.elements[ 'checkall-toggle' ] ) return;

		// Toggle main toggle checkbox depending on checkbox selection
		var c = true,
			i, e, n;

		for ( i = 0, n = form.elements.length; i < n; i++ ) {
			e = form.elements[ i ];

			if ( e.type == 'checkbox' && e.name != 'checkall-toggle' && !e.checked ) {
				c = false;
				break;
			}
		}

		form.elements[ 'checkall-toggle' ].checked = c;
	};

	/**
	 * USED IN: libraries/joomla/html/toolbar/button/help.php
	 *
	 * Pops up a new window in the middle of the screen
	 */
	Joomla.popupWindow = function( mypage, myname, w, h, scroll ) {
		var winl = ( screen.width - w ) / 2,
			wint = ( screen.height - h ) / 2,
			winprops = 'height=' + h +
				',width=' + w +
				',top=' + wint +
				',left=' + winl +
				',scrollbars=' + scroll +
				',resizable';

		window.open( mypage, myname, winprops )
			.window.focus();
	};

	/**
	 * USED IN: libraries/joomla/html/html/grid.php
	 * In other words, on any reorderable table
	 */
	Joomla.tableOrdering = function( order, dir, task, form ) {
		if ( typeof form  === 'undefined' ) {
			form = document.getElementById( 'adminForm' );
		}

		form.filter_order.value = order;
		form.filter_order_Dir.value = dir;
		Joomla.submitform( task, form );
	};

	/**
	 * USED IN: administrator/components/com_modules/views/module/tmpl/default.php
	 *
	 * Writes a dynamically generated list
	 *
	 * @param string
	 *          The parameters to insert into the <select> tag
	 * @param array
	 *          A javascript array of list options in the form [key,value,text]
	 * @param string
	 *          The key to display for the initial state of the list
	 * @param string
	 *          The original key that was selected
	 * @param string
	 *          The original item value that was selected
	 * @param string
	 *          The elem where the list will be written
	 */
	window.writeDynaList = function ( selectParams, source, key, orig_key, orig_val, element ) {
		var html = '<select ' + selectParams + '>',
			hasSelection = key == orig_key,
			i = 0,
			selected, x, item;

		for ( x in source ) {
			if (!source.hasOwnProperty(x)) { continue; }

			item = source[ x ];

			if ( item[ 0 ] != key ) { continue; }

			selected = '';

			if ( ( hasSelection && orig_val == item[ 1 ] ) || ( !hasSelection && i === 0 ) ) {
				selected = 'selected="selected"';
			}

			html += '<option value="' + item[ 1 ] + '" ' + selected + '>' + item[ 2 ] + '</option>';

			i++;
		}
		html += '</select>';

		if (element) {
			element.innerHTML = html;
		} else {
			document.writeln( html );
		}
	};

	/**
	 * USED IN: administrator/components/com_content/views/article/view.html.php
	 * actually, probably not used anywhere.
	 *
	 * Changes a dynamically generated list
	 *
	 * @param string
	 *          The name of the list to change
	 * @param array
	 *          A javascript array of list options in the form [key,value,text]
	 * @param string
	 *          The key to display
	 * @param string
	 *          The original key that was selected
	 * @param string
	 *          The original item value that was selected
	 */
	window.changeDynaList = function ( listname, source, key, orig_key, orig_val ) {
		var list = document.adminForm[ listname ],
			hasSelection = key == orig_key,
			i, x, item, opt;

		// empty the list
		while ( list.firstChild ) list.removeChild( list.firstChild );

		i = 0;

		for ( x in source ) {
			if (!source.hasOwnProperty(x)) { continue; }

			item = source[x];

			if ( item[ 0 ] != key ) { continue; }

			opt = new Option();
			opt.value = item[ 1 ];
			opt.text = item[ 2 ];

			if ( ( hasSelection && orig_val == opt.value ) || (!hasSelection && i === 0) ) {
				opt.selected = true;
			}

			list.options[ i++ ] = opt;
		}

		list.length = i;
	};

	/**
	 * USED IN: administrator/components/com_menus/views/menus/tmpl/default.php
	 * Probably not used at all
	 *
	 * @param radioObj
	 * @return
	 */
	// return the value of the radio button that is checked
	// return an empty string if none are checked, or
	// there are no radio buttons
	window.radioGetCheckedValue = function ( radioObj ) {
		if ( !radioObj ) { return ''; }

		var n = radioObj.length,
			i;

		if ( n === undefined ) {
			return radioObj.checked ? radioObj.value : '';
		}

		for ( i = 0; i < n; i++ ) {
			if ( radioObj[ i ].checked ) {
				return radioObj[ i ].value;
			}
		}

		return '';
	};

	/**
	 * USED IN: administrator/components/com_users/views/mail/tmpl/default.php
	 * Let's get rid of this and kill it
	 *
	 * @param frmName
	 * @param srcListName
	 * @return
	 */
	window.getSelectedValue = function ( frmName, srcListName ) {
		var srcList = document[ frmName ][ srcListName ],
			i = srcList.selectedIndex;

		if ( i !== null && i > -1 ) {
			return srcList.options[ i ].value;
		} else {
			return null;
		}
	};

	/**
	 * USED IN: all over :)
	 *
	 * @param id
	 * @param task
	 * @return
	 */
	window.listItemTask = function ( id, task ) {
		var f = document.adminForm,
			i = 0, cbx,
			cb = f[ id ];

		if ( !cb ) return false;

		while ( true ) {
			cbx = f[ 'cb' + i ];

			if ( !cbx ) break;

			cbx.checked = false;

			i++;
		}

		cb.checked = true;
		f.boxchecked.value = 1;
		window.submitform( task );

		return false;
	};

	/**
	 * Default function. Usually would be overriden by the component
	 *
	 * @deprecated  12.1 This function will be removed in a future version. Use Joomla.submitbutton() instead.
	 */
	window.submitbutton = function ( pressbutton ) {
		Joomla.submitbutton( pressbutton );
	};

	/**
	 * Submit the admin form
	 *
	 * @deprecated  12.1 This function will be removed in a future version. Use Joomla.submitform() instead.
	 */
	window.submitform = function ( pressbutton ) {
		Joomla.submitform(pressbutton);
	};

	// needed for Table Column ordering
	/**
	 * USED IN: libraries/joomla/html/html/grid.php
	 * There's a better way to do this now, can we try to kill it?
	 */
	window.saveorder = function ( n, task ) {
		window.checkAll_button( n, task );
	};

	/**
	 * Checks all the boxes unless one is missing then it assumes it's checked out.
	 * Weird. Probably only used by ^saveorder
	 *
	 * @param   integer  n     The total number of checkboxes expected
	 * @param   string   task  The task to perform
	 *
	 * @return  void
	 */
	window.checkAll_button = function ( n, task ) {
		task = task ? task : 'saveorder';

		var j, box;

		for ( j = 0; j <= n; j++ ) {
			box = document.adminForm[ 'cb' + j ];

			if ( box ) {
				box.checked = true;
			} else {
				alert( "You cannot change the order of items, as an item in the list is `Checked Out`" );
				return;
			}
		}

		Joomla.submitform( task );
	};

	/**
	 * Add Joomla! loading image layer.
	 *
	 * Used in: /administrator/components/com_installer/views/languages/tmpl/default.php
	 *          /installation/template/js/installation.js
	 *
	 * @param   string  task           The task to do [load, show, hide] (defaults to show).
	 * @param   object  parentElement  The HTML element where we are appending the layer (defaults to body).
	 *
	 * @return  object  The HTML loading layer element.
	 *
	 * @since  3.6.0
	 */
	Joomla.loadingLayer = function(task, parentElement) {
		// Set default values.
		task          = task || 'show';
		parentElement = parentElement || document.body;

		// Create the loading layer (hidden by default).
		if (task == 'load')
		{
			// Gets the site base path from the body element (defaults to empty - no subfolder)
			var basePath = document.getElementsByTagName('body')[0].getAttribute('data-basepath') || '';

			var loadingDiv = document.createElement('div');

			loadingDiv.id = 'loading-logo';

			// The loading layer CSS styles are JS hardcoded so they can be used without adding CSS.

			// Loading layer style and positioning.
			loadingDiv.style['position']              = 'fixed';
			loadingDiv.style['top']                   = '0';
			loadingDiv.style['left']                  = '0';
			loadingDiv.style['width']                 = '100%';
			loadingDiv.style['height']                = '100%';
			loadingDiv.style['opacity']               = '0.8';
			loadingDiv.style['filter']                = 'alpha(opacity=80)';
			loadingDiv.style['overflow']              = 'hidden';
			loadingDiv.style['z-index']               = '10000';
			loadingDiv.style['display']               = 'none';
			loadingDiv.style['background-color']      = '#fff';

			// Loading logo positioning.
			loadingDiv.style['background-image']      = 'url("' + basePath + '/media/jui/images/ajax-loader.gif")';
			loadingDiv.style['background-position']   = 'center';
			loadingDiv.style['background-repeat']     = 'no-repeat';
			loadingDiv.style['background-attachment'] = 'fixed';

			parentElement.appendChild(loadingDiv);
		}
		// Show or hide the layer.
		else
		{
			if (!document.getElementById('loading-logo'))
			{
				Joomla.loadingLayer('load', parentElement);
			}

			document.getElementById('loading-logo').style['display'] = (task == 'show') ? 'block' : 'none';
		}

		return document.getElementById('loading-logo');
	};

	/**
	 * Method to Extend Objects
	 *
	 * @param  {Object}  destination
	 * @param  {Object}  source
	 *
	 * @return Object
	 */
	Joomla.extend = function (destination, source) {
		for (var p in source) {
			if (source.hasOwnProperty(p)) {
				destination[p] = source[p];
			}
		}

		return destination;
	};

	/**
	 * Method to perform AJAX request
	 *
	 * @param {Object} options   Request options:
	 * {
	 *    url:       'index.php',  // Request URL
	 *    method:    'GET',        // Request method GET (default), POST
	 *    data:      null,         // Data to be sent, see https://developer.mozilla.org/docs/Web/API/XMLHttpRequest/send
	 *    perform:   true,         // Perform the request immediately, or return XMLHttpRequest instance and perform it later
	 *    headers:   null,         // Object of custom headers, eg {'X-Foo': 'Bar', 'X-Bar': 'Foo'}
	 *
	 *    onBefore:  function(xhr){}            // Callback on before the request
	 *    onSuccess: function(response, xhr){}, // Callback on the request success
	 *    onError:   function(xhr){},           // Callback on the request error
	 * }
	 *
	 * @return XMLHttpRequest|Boolean
	 *
	 * @example
	 *
	 * 	Joomla.request({
	 *		url: 'index.php?option=com_example&view=example',
	 *		onSuccess: function(response, xhr){
	 *			console.log(response);
	 *		}
	 * 	})
	 *
	 * @see    https://developer.mozilla.org/docs/Web/API/XMLHttpRequest
	 */
	Joomla.request = function (options) {

		// Prepare the options
		options = Joomla.extend({
			url:    '',
			method: 'GET',
			data:    null,
			perform: true
		}, options);

		// Use POST for send the data
		options.method = options.data ? 'POST' : options.method;

		// Set up XMLHttpRequest instance
		try{
			var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('MSXML2.XMLHTTP.3.0');
			xhr.open(options.method, options.url, true);

			// Set the headers
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('X-Ajax-Engine', 'Joomla!');

			if (options.method === 'POST' && (!options.headers || !options.headers['Content-Type'])) {
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			}

			// Custom headers
			if (options.headers){
				for (var p in options.headers){
					if (options.headers.hasOwnProperty(p)) {
						xhr.setRequestHeader(p, options.headers[p]);
					}
				}
			}

			xhr.onreadystatechange = function () {
				// Request not finished
				if (xhr.readyState !== 4) return;

				// Request finished and response is ready
				if (xhr.status === 200) {
					if(options.onSuccess) {
						options.onSuccess.call(window, xhr.responseText, xhr);
					}
				} else if(options.onError) {
					options.onError.call(window, xhr);
				}
			};

			// Do request
			if (options.perform) {
				if (options.onBefore && options.onBefore.call(window, xhr) === false) {
					// Request interrupted
					return xhr;
				}

				xhr.send(options.data);
			}

		} catch (error) {
			window.console ? console.log(error) : null;
			return false;
		}

		return xhr;
	};

}( Joomla, document ));
