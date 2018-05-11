/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
Joomla = window.Joomla || {};

// Only define editors if not defined
Joomla.editors = Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
Joomla.editors.instances = Joomla.editors.instances || {
	/**
	 * *****************************************************************
	 * All Editors MUST register, per instance, the following callbacks:
	 * *****************************************************************
	 *
	 * getValue         Type  Function  Should return the complete data from the editor
	 *                                  Example: function () { return this.element.value; }
	 * setValue         Type  Function  Should replace the complete data of the editor
	 *                                  Example: function (text) { return this.element.value = text; }
	 * replaceSelection Type  Function  Should replace the selected text of the editor
	 *                                  If nothing selected, will insert the data at the cursor
	 *                                  Example: function (text) { return insertAtCursor(this.element, text); }
	 *
	 * USAGE (assuming that jform_articletext is the textarea id)
	 * {
	 *   To get the current editor value:
	 *      Joomla.editors.instances['jform_articletext'].getValue();
	 *   To set the current editor value:
	 *      Joomla.editors.instances['jform_articletext'].setValue('Joomla! rocks');
	 *   To replace(selection) or insert a value at  the current editor cursor:
	 *      replaceSelection: Joomla.editors.instances['jform_articletext'].replaceSelection('Joomla! rocks')
	 * }
	 *
	 * *********************************************************
	 * ANY INTERACTION WITH THE EDITORS SHOULD USE THE ABOVE API
	 * *********************************************************
	 *
	 * jInsertEditorText() @deprecated 4.0
	 */
};

(function( Joomla, document ) {
	"use strict";

	/**
	 * Generic submit form
	 *
	 * @param  {String}  task      The given task
	 * @param  {node}    form      The form element
	 * @param  {bool}    validate  The form element
	 *
	 * @returns  {void}
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

		if (!validate) {
			form.setAttribute('novalidate', '');
		} else if (form.hasAttribute('novalidate')) {
			form.removeAttribute('novalidate');
		}

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
	 * Default function. Can be overriden by the component to add custom logic
	 *
	 * @param  {bool}  task  The given task
	 *
	 * @returns {void}
	 */
	Joomla.submitbutton = function( task ) {
		var form = document.querySelectorAll( 'form.form-validate' );

		if (form.length > 0) {
			for (var i = 0, j = form.length; i < j; i++) {
				var pressbutton = task.split('.'),
				    cancelTask = form[i].getAttribute( 'data-cancel-task' );

				if (!cancelTask) {
					cancelTask = pressbutton[0] + '.cancel';
				}

				if ((task == cancelTask ) || document.formvalidator.isValid( form[i] ))
				{
					Joomla.submitform( task, form[i] );
				}
			}
		} else {
			Joomla.submitform( task );
		}
	};

	/**
	 * Custom behavior for JavaScript I18N in Joomla! 1.6
	 *
	 * @type {{}}
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
	 * @param  {String}  key  Name in Storage
	 * @param  {mixed}   def  Default value if nothing found
	 *
	 * @return {mixed}
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
	 * @param  {Object|undefined}  options  The options object to load. Eg {"com_foobar" : {"option1": 1, "option2": 2}}
	 *
	 * @since 3.7.0
	 */
	Joomla.loadOptions = function( options ) {
		// Load form the script container
		if (!options) {
			var elements = document.querySelectorAll('.joomla-script-options.new'),
				str, element, option, counter = 0;

			for (var i = 0, l = elements.length; i < l; i++) {
				element = elements[i];
				str     = element.text || element.textContent;
				option  = JSON.parse(str);

				if (option) {
					Joomla.loadOptions(option);
					counter++;
				}

				element.className = element.className.replace(' new', ' loaded');
			}

			if (counter) {
				return;
			}
		}

		// Initial loading
		if (!Joomla.optionsStorage) {
			Joomla.optionsStorage = options || {};
		}
		// Merge with existing
		else if ( options ) {
			for (var p in options) {
				if (options.hasOwnProperty(p)) {
					/**
					 * If both existing and new options are objects, merge them with Joomla.extend().  But test for new
					 * option being null, as null is an object, but we want to allow clearing of options with ...
					 *
					 * Joomla.loadOptions({'joomla.jtext': null});
					 */
					if (options[p] !== null && typeof Joomla.optionsStorage[p] === 'object' && typeof options[p] === 'object') {
						Joomla.optionsStorage[p] = Joomla.extend(Joomla.optionsStorage[p], options[p]);
					} else {
						Joomla.optionsStorage[p] = options[p];
					}
	            }
            }
        }
	};

	/**
	 * Method to replace all request tokens on the page with a new one.
	 *
	 * @param {String}  newToken  The token
	 *
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
	 * @param  {string}  text  The text for validation
	 *
	 * @return {boolean}
	 *
	 * @deprecated  4.0 No replacement. Use formvalidator
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
	 * @param   {mixed}   checkbox  The number of box to 'check', for a checkbox element
	 * @param   {string}  stub      An alternative field name
	 *
	 * @return  {boolean}
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
	 * Toggles the check state of a group of boxes
	 *
	 * Checkboxes must have an id attribute in the form cb0, cb1...
	 *
	 * @param   {node}     item      The form
	 * @param   {string}   stub      An alternative field name
	 *
	 * @return  {boolean}
	 */
	Joomla.uncheckAll = function( item, stub ) {
		if (!item.form) return false;

		stub = stub ? stub : 'cb';

		var c = 0,
			i, e, n;

		for ( i = 0, n = item.form.elements.length; i < n; i++ ) {
			e = item.form.elements[ i ];

			if ( e.type === 'checkbox' && e.id.indexOf( stub ) === 0 ) {
				e.checked = false;
			}
		}

		if ( item.form.boxchecked ) {
			item.form.boxchecked.value = c;
		}

		return true;
	};

	/**
	 * Toggles the check state of a group of boxes
	 *
	 * Checkboxes must have an id attribute in the form cb0, cb1...
	 *
	 * @param   {node}     el        The form item
	 * @param   {bool}     cond      An alternative value to set checkbox
	 *
	 * @return  {boolean}
	 */
	Joomla.toggleOne = function( el, cond ) {
		if (!el.form) return false;

		var item = el;

		while (item = item.parentNode) {
			if (item.tagName.toLowerCase() === 'tr') {
				break;
			}
		}

		var checkbox = item.querySelector('input[name="cid[]"]');

		if (checkbox) {
			checkbox.checked = cond ? cond : !checkbox.checked;
			if (checkbox.checked) {
				cond = checkbox.checked;
			}
		}

		if ( el.form.boxchecked && cond) {
			el.form.boxchecked.value = parseInt(el.form.boxchecked.value) + 1;
		}

		return true;
	};

	/**
	 * Render messages send via JSON
	 * Used by some javascripts such as validate.js
	 *
	 * @param   {object}  messages    JavaScript object containing the messages to render. Example:
	 *                              var messages = {
	 *                                  "message": ["Message one", "Message two"],
	 *                                  "error": ["Error one", "Error two"]
	 *                              };
	 * @param  {string} selector     The selector of the container where the message will be rendered
	 * @param  {bool}   keepOld      If we shall discard old messages
	 * @param  {int}    timeout      The milliseconds before the message self destruct
	 * @return  void
	 */
	Joomla.renderMessages = function( messages, selector, keepOld, timeout ) {
		var messageContainer, type, typeMessages, messagesBox, title, titleWrapper, i, messageWrapper, alertClass;

		if (typeof selector === 'undefined' || selector && selector === '#system-message-container') {
			messageContainer = document.getElementById( 'system-message-container' );
		} else {
			messageContainer = document.querySelector( selector );
		}

		if (typeof keepOld === 'undefined' || keepOld && keepOld === false) {
			Joomla.removeMessages( messageContainer );
		}

		for ( type in messages ) {
			if ( !messages.hasOwnProperty( type ) ) { continue; }
			// Array of messages of this type
			typeMessages = messages[ type ];

			if (typeof window.customElements === 'object' && typeof window.customElements.get('joomla-alert') === 'function') {
				messagesBox = document.createElement( 'joomla-alert' );

				if (['notice','message', 'error'].indexOf(type) > -1) {
					alertClass = (type === 'notice') ? 'info' : type;
					alertClass = (type === 'message') ? 'success' : alertClass;
					alertClass = (type === 'error') ? 'danger' : alertClass;
				} else {
					alertClass = 'info';
				}

				messagesBox.setAttribute('type', alertClass);
				messagesBox.setAttribute('dismiss', 'true');

				if (timeout && parseInt(timeout) > 0) {
					messagesBox.setAttribute('autodismiss', timeout);
				}
			} else {
				// Create the alert box
				messagesBox = document.createElement( 'div' );

				// Message class
				if (['notice','message', 'error'].indexOf(type) > -1) {
					alertClass = (type === 'notice') ? 'info' : type;
					alertClass = (type === 'message') ? 'success' : alertClass;
					alertClass = (type === 'error') ? 'danger' : alertClass;
				} else {
					alertClass = 'info';
				}

				messagesBox.className = 'alert ' + alertClass;

				// Close button
				var buttonWrapper = document.createElement( 'button' );
				buttonWrapper.setAttribute('type', 'button');
				buttonWrapper.setAttribute('data-dismiss', 'alert');
				buttonWrapper.className = 'close';
				buttonWrapper.innerHTML = '×';
				messagesBox.appendChild( buttonWrapper );
			}

			// Title
			title = Joomla.JText._( type );

			// Skip titles with untranslated strings
			if ( typeof title != 'undefined' ) {
				titleWrapper = document.createElement( 'h4' );
				titleWrapper.className = 'alert-heading';
				titleWrapper.innerHTML = Joomla.JText._( type ) ? Joomla.JText._( type ) : type;
				messagesBox.appendChild( titleWrapper );
			}

			// Add messages to the message box
			for ( i = typeMessages.length - 1; i >= 0; i-- ) {
				messageWrapper = document.createElement( 'div' );
				messageWrapper.innerHTML = typeMessages[ i ];
				messagesBox.appendChild( messageWrapper );
			}

			messageContainer.appendChild( messagesBox );

			if (typeof window.customElements !== 'object' && typeof window.customElements.get('joomla-alert') !== 'function') {
				if (timeout && parseInt(timeout) > 0) {
					setTimeout(function () {
						Joomla.removeMessages(messageContainer);
					}, timeout);
				}
			}
		}
	};


	/**
	 * Remove messages
	 *
	 * @param  {element} container    The element of the container of the message to be removed
	 *
	 * @return  {void}
	 */
	Joomla.removeMessages = function( container ) {
		var messageContainer;

		if (container) {
			messageContainer = container;
		} else {
			messageContainer = document.getElementById( 'system-message-container' );
		}

		if (typeof window.customElements === 'object' && window.customElements.get('joomla-alert')) {
			var messages = messageContainer.querySelectorAll('joomla-alert');
			if (messages.length) {
				for (var i = 0, l = messages.length; i < l; i++) {
					messages[i].close();
				}
			}
		} else {
			// Empty container with a while for Chrome performance issues
			while ( messageContainer.firstChild ) messageContainer.removeChild( messageContainer.firstChild );

			// Fix Chrome bug not updating element height
			messageContainer.style.display = 'none';
			messageContainer.offsetHeight;
			messageContainer.style.display = '';
		}
	};

	/**
	 * Treat AJAX errors.
	 * Used by some javascripts such as sendtestmail.js and permissions.js
	 *
	 * @param   {object}  xhr         XHR object.
	 * @param   {string}  textStatus  Type of error that occurred.
	 * @param   {string}  error       Textual portion of the HTTP status.
	 *
	 * @return  {object}  JavaScript object containing the system error message.
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
	 * @param  {boolean}  isitchecked  Flag for checked
	 * @param  {node}     form         The form
	 *
	 * @return  {void}
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
	 * USED IN: libraries/joomla/html/html/grid.php
	 * In other words, on any reorderable table
	 *
	 * @param  {string}  order  The order value
	 * @param  {string}  dir    The direction
	 * @param  {string}  task   The task
	 * @param  {node}    form   The form
	 *
	 * return  {void}
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
	 * USED IN: administrator/components/com_users/views/mail/tmpl/default.php
	 * Let's get rid of this and kill it
	 *
	 * @param frmName
	 * @param srcListName
	 * @return
	 *
	 * @deprecated  4.0 No replacement
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
     *
     * @deprecated 4.0  Use Joomla.listItemTask() instead
     */
    window.listItemTask = function ( id, task ) {
        return Joomla.listItemTask( id, task );
    };

    /**
     * USED IN: all over :)
     *
     * @param  {string}  id    The id
     * @param  {string}  task  The task
     *
     * @return {boolean}
     */
    Joomla.listItemTask = function ( id, task ) {
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
	 * @deprecated 4.0  Use Joomla.submitbutton() instead.
	 */
	window.submitbutton = function ( pressbutton ) {
		Joomla.submitbutton( pressbutton );
	};

	/**
	 * Submit the admin form
	 *
	 * @deprecated 4.0  Use Joomla.submitform() instead.
	 */
	window.submitform = function ( pressbutton ) {
		Joomla.submitform(pressbutton);
	};

	// needed for Table Column ordering
	/**
	 * USED IN: libraries/joomla/html/html/grid.php
	 * There's a better way to do this now, can we try to kill it?
	 *
	 * @deprecated 4.0  No replacement
	 */
	window.saveorder = function ( n, task ) {
		window.checkAll_button( n, task );
	};

	/**
	 * Checks all the boxes unless one is missing then it assumes it's checked out.
	 * Weird. Probably only used by ^saveorder
	 *
	 * @param   {int}      n     The total number of checkboxes expected
	 * @param   {string}   task  The task to perform
	 *
	 * @return  void
	 *
	 * @deprecated 4.0  No replacement
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
	 * @param   {String}       task           The task to do [load, show, hide] (defaults to show).
	 * @param   {HTMLElement}  parentElement  The HTML element where we are appending the layer (defaults to body).
	 *
	 * @return  {HTMLElement}  The HTML loading layer element.
	 *
	 * @since  3.6.0
	 */
	Joomla.loadingLayer = function(task, parentElement) {
		// Set default values.
		task          = task || 'show';
		parentElement = parentElement || document.body;

		// Create the loading layer (hidden by default).
		if (task === 'load')
		{
			// Gets the site base path
			var systemPaths = Joomla.getOptions('system.paths') || {},
				basePath    = systemPaths.root || '';

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
			loadingDiv.style['background-image']      = 'url("' + basePath + '/media/system/images/ajax-loader.gif")';
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
		/**
		 * Technically null is an object, but trying to treat the destination as one in this context will error out.
		 * So emulate jQuery.extend(), and treat a destination null as an empty object.
 		 */
		if (destination === null) {
			destination = {};
		}
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

		// Set up XMLHttpRequest instance
		try{
			var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('MSXML2.XMLHTTP.3.0');

			xhr.open(options.method, options.url, true);

			// Set the headers
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('X-Ajax-Engine', 'Joomla!');

			if (options.method !== 'GET') {
				var token = Joomla.getOptions('csrf.token', '');

				if (token) {
					xhr.setRequestHeader('X-CSRF-Token', token);
				}

				if (!options.headers || !options.headers['Content-Type']) {
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				}
			}

			// Custom headers
			if (options.headers){
				for (var p in options.headers) {
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

	/**
	 * Check if HTML5 localStorage enabled on the browser
	 *
	 * @since   4.0.0
	 */
	Joomla.localStorageEnabled = function() {
		var test = 'joomla-cms';
		try {
			localStorage.setItem(test, test);
			localStorage.removeItem(test);
			return true;
		} catch(e) {
			return false;
		}
	};

	/**
	 * Loads any needed polyfill for web components and async load any web components
	 *
	 * @since   4.0.0
	 */
	Joomla.WebComponents = function() {
		var polyfills = [];
		var wc = Joomla.getOptions('webcomponents');

		/* Check if ES6 then apply the shim */
		var checkES6 = function () {
			try {
				new Function("(a = 0) => a");
				return true;
			}
			catch (err) {
				return false;
			}
		};

		/* Load web components async */
		var loadWC = function () {
			if (wc && wc.length) {
				wc.forEach(function(component) {
					if (component.match(/\.js/g)) {
						var el = document.createElement('script');
						if (!checkES6()) {
							var es5;
							// Browser is not ES6!
							if (component.match(/\.min\.js/g)) {
								es5 = component.replace(/\.min\.js/g, '-es5.min.js')
							} else if (component.match(/\.js/g)) {
								es5 = component.replace(/\.js/g, '-es5.js')
							}
							el.src = es5;
						} else {
							el.src = component;
						}
					}
					if (el) {
						document.head.appendChild(el);
					}
				});
			}
		};

		if (!('import' in document.createElement('link'))) {
			polyfills.push('hi');
		}
		if (!('attachShadow' in Element.prototype && 'getRootNode' in Element.prototype) || (window.ShadyDOM && window.ShadyDOM.force)) {
			polyfills.push('sd');
		}
		if (!window.customElements || window.customElements.forcePolyfill) {
			polyfills.push('ce');
		}
		if (!('content' in document.createElement('template')) || !window.Promise || !Array.from || !(document.createDocumentFragment().cloneNode() instanceof DocumentFragment)) {
			polyfills = ['lite'];
		}

		if (polyfills.length && wc && wc.length) {
			var name = "core.min.js";
			var script = document.querySelector('script[src*="' + name + '"]');

			if (!script) {
				name = "core.js";
				script = document.querySelector('script[src*="' + name + '"]')
			}

			if (!script) {
				throw new Error('core(.min).js is not registered correctly!')
			}

			var newScript = document.createElement('script');
			var replacement = 'media/vendor/webcomponentsjs/js/webcomponents-' + polyfills.join('-') + '.min.js';
			var mediaVersion = script.src.match(/\?.*/)[0];
			var base = Joomla.getOptions('system.paths');

			if (!base) {
				throw new Error('core(.min).js is not registered correctly!')
			}

			newScript.src = base.rootFull + replacement + (mediaVersion ? mediaVersion : '');
			document.head.appendChild(newScript);

			document.addEventListener('WebComponentsReady', function () {
				loadWC();
			});
		} else {
			if (!wc || !wc.length) {
				return;
			}

			var fire = function () {
				requestAnimationFrame(function () {
					document.dispatchEvent(new CustomEvent('WebComponentsReady', { bubbles: true }));
					loadWC();
				});
			};

			if (document.readyState !== 'loading') {
				fire();
			} else {
				document.addEventListener('readystatechange', function wait() {
					fire();
					document.removeEventListener('readystatechange', wait);
				});
			}
		}
	};
}( Joomla, document ));

/**
 * Joomla! Custom events
 *
 * @since  4.0.0
 */
(function( window, Joomla ) {
	"use strict";

	if (Joomla.Event) {
		return;
	}

	Joomla.Event = {};

	/**
	 * Dispatch custom event.
	 *
	 * An event name convention:
	 * 		The event name has at least two part, separated ":", eg `foo:bar`. Where the first part is an "event supporter",
	 * 		and second part is the event name which happened.
	 * 		Which is allow us to avoid possible collisions with another scripts and native DOM events.
	 * 		Joomla! CMS standard events should start from `joomla:`.
	 *
	 * Joomla! events:
	 * 		`joomla:updated`  Dispatch it over the changed container, example after the content was updated via ajax
	 * 		`joomla:removed`  The container was removed
	 *
	 * @param {HTMLElement|string}  element  DOM element, the event target. Or the event name, then the target will be a Window
	 * @param {String|Object}       name     The event name, or an optional parameters in case when "element" is an event name
	 * @param {Object}              params   An optional parameters. Allow to send a custom data through the event.
	 *
	 * @example
	 *
	 * 	Joomla.Event.dispatch(myElement, 'joomla:updated', {for: 'bar', foo2: 'bar2'}); // Will dispatch event to myElement
	 * 	or:
	 * 	Joomla.Event.dispatch('joomla:updated', {for: 'bar', foo2: 'bar2'}); // Will dispatch event to Window
	 *
	 * @since   4.0.0
	 */
	Joomla.Event.dispatch = function(element, name, params) {
		if (typeof element === 'string') {
			params  = name;
			name    = element;
			element = window;
		}
		params = params || {};

		var event;

		if (window.CustomEvent && typeof(window.CustomEvent) === 'function') {
			event = new CustomEvent(name, {
				detail:     params,
				bubbles:    true,
				cancelable: true
			});
		}
		// IE trap
		else {
			event = document.createEvent('Event');
			event.initEvent(name, true, true);
			event.detail = params;
		}

		element.dispatchEvent(event);
	};

	/**
	 * Once listener. Add EventListener to the Element and auto-remove it after the event was dispatched.
	 *
	 * @param {HTMLElement}  element   DOM element
	 * @param {String}       name      The event name
	 * @param {Function}     callback  The event callback
	 *
	 * @since   4.0.0
	 */
	Joomla.Event.listenOnce = function (element, name, callback) {
		var onceCallback = function(event){
			element.removeEventListener(name, onceCallback);
			return callback.call(element, event)
		};

		element.addEventListener(name, onceCallback);
	};
})( window, Joomla );

/**
 * Load any web components and any polyfills required
 */
document.addEventListener('DOMContentLoaded', function() {
	Joomla.WebComponents();
});
