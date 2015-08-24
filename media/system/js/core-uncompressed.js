/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
		strings: {},
		'_': function( key, def ) {
			return typeof this.strings[ key.toUpperCase() ] !== 'undefined' ? this.strings[ key.toUpperCase() ] : def;
		},
		load: function( object ) {
			for ( var key in object ) {
				if (!object.hasOwnProperty(key)) continue;
				this.strings[ key.toUpperCase() ] = object[ key ];
			}
			return this;
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
			type, typeMessages, messagesBox, title, titleWrapper, i, messageWrapper;

		for ( type in messages ) {
			if ( !messages.hasOwnProperty( type ) ) { continue; }
			// Array of messages of this type
			typeMessages = messages[ type ];

			// Create the alert box
			messagesBox = document.createElement( 'div' );
			messagesBox.className = 'alert alert-' + type;

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
				messageWrapper = document.createElement( 'p' );
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

		form.boxchecked.value += isitchecked ? 1 : -1;

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
	 */
	window.writeDynaList = function ( selectParams, source, key, orig_key, orig_val ) {
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

		document.writeln( html );
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

}( Joomla, document ));
