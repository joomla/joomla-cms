/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

Joomla.editors = {};
// An object to hold each editor instance on page
Joomla.editors.instances = {};

/**
 * Generic submit form
 */
Joomla.submitform = function(task, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');
	}

	if (typeof(task) !== 'undefined') {
		form.task.value = task;
	}

	// Submit the form.
	if (typeof form.onsubmit == 'function') {
		form.onsubmit();
	}
	if (typeof form.fireEvent == "function") {
		form.fireEvent('submit');
	}
	form.submit();
};

/**
 * Default function. Usually would be overriden by the component
 */
Joomla.submitbutton = function(pressbutton) {
	Joomla.submitform(pressbutton);
}

/**
 * Custom behavior for JavaScript I18N in Joomla! 1.6
 *
 * Allows you to call Joomla.JText._() to get a translated JavaScript string pushed in with JText::script() in Joomla.
 */
Joomla.JText = {
	strings: {},
	'_': function(key, def) {
		return typeof this.strings[key.toUpperCase()] !== 'undefined' ? this.strings[key.toUpperCase()] : def;
	},
	load: function(object) {
		for (var key in object) {
			this.strings[key.toUpperCase()] = object[key];
		}
		return this;
	}
};

/**
 * Method to replace all request tokens on the page with a new one.
 */
Joomla.replaceTokens = function(n) {
	var els = document.getElementsByTagName('input');
	for (var i = 0; i < els.length; i++) {
		if ((els[i].type == 'hidden') && (els[i].name.length == 32) && els[i].value == '1') {
			els[i].name = n;
		}
	}
};

/**
 * USED IN: administrator/components/com_banners/views/client/tmpl/default.php
 *
 * Verifies if the string is in a valid email format
 *
 * @param string
 * @return boolean
 */
Joomla.isEmail = function(text) {
	var regex = new RegExp("^[\\w-_\.]*[\\w-_\.]\@[\\w]\.+[\\w]+[\\w]$");
	return regex.test(text);
};

/**
 * USED IN: all list forms.
 *
 * Toggles the check state of a group of boxes
 *
 * Checkboxes must have an id attribute in the form cb0, cb1...
 *
 * @param	mixed	The number of box to 'check', for a checkbox element
 * @param	string	An alternative field name
 */
Joomla.checkAll = function(checkbox, stub) {
	if (!stub) {
		stub = 'cb';
	}
	if (checkbox.form) {
		var c = 0;
		for (var i = 0, n = checkbox.form.elements.length; i < n; i++) {
			var e = checkbox.form.elements[i];
			if (e.type == checkbox.type) {
				if ((stub && e.id.indexOf(stub) == 0) || !stub) {
					e.checked = checkbox.checked;
					c += (e.checked == true ? 1 : 0);
				}
			}
		}
		if (checkbox.form.boxchecked) {
			checkbox.form.boxchecked.value = c;
		}
		return true;
	}
	return false;
}

/**
 * Render messages send via JSON
 *
 * @param	object	messages	JavaScript object containing the messages to render
 * @return	void
 */
Joomla.renderMessages = function(messages) {
	Joomla.removeMessages();
	var container = document.id('system-message-container');

	Object.each(messages, function (item, type) {
		var div = new Element('div', {
			id: 'system-message',
			'class': 'alert alert-' + type
		});
		div.inject(container);
		var h4 = new Element('h4', {
			'class' : 'alert-heading',
			html: Joomla.JText._(type)
		});
		h4.inject(div);
		var divList = new Element('div');
		Array.each(item, function (item, index, object) {
			var p = new Element('p', {
				html: item
			});
			p.inject(divList);
		}, this);
		divList.inject(div);
	}, this);
};


/**
 * Remove messages
 *
 * @return	void
 */
Joomla.removeMessages = function() {
	var children = $$('#system-message-container > *');
	children.destroy();
}

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
Joomla.isChecked = function(isitchecked, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');
	}

	if (isitchecked == true) {
		form.boxchecked.value++;
	} else {
		form.boxchecked.value--;
	}
}

/**
 * USED IN: libraries/joomla/html/toolbar/button/help.php
 *
 * Pops up a new window in the middle of the screen
 */
Joomla.popupWindow = function(mypage, myname, w, h, scroll) {
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	var winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl
			+ ',scrollbars=' + scroll + ',resizable'
	var win = window.open(mypage, myname, winprops)
	win.window.focus();
}

/**
 * USED IN: libraries/joomla/html/html/grid.php
 */
Joomla.tableOrdering = function(order, dir, task, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');
	}

	form.filter_order.value = order;
	form.filter_order_Dir.value = dir;
	Joomla.submitform(task, form);
}

/**
 * USED IN: administrator/components/com_modules/views/module/tmpl/default.php
 *
 * Writes a dynamically generated list
 *
 * @param string
 *			The parameters to insert into the <select> tag
 * @param array
 *			A javascript array of list options in the form [key,value,text]
 * @param string
 *			The key to display for the initial state of the list
 * @param string
 *			The original key that was selected
 * @param string
 *			The original item value that was selected
 */
function writeDynaList(selectParams, source, key, orig_key, orig_val) {
	var html = '\n	<select ' + selectParams + '>';
	var i = 0;
	for (x in source) {
		if (source[x][0] == key) {
			var selected = '';
			if ((orig_key == key && orig_val == source[x][1])
					|| (i == 0 && orig_key != key)) {
				selected = 'selected="selected"';
			}
			html += '\n		<option value="' + source[x][1] + '" ' + selected
					+ '>' + source[x][2] + '</option>';
		}
		i++;
	}
	html += '\n	</select>';

	document.writeln(html);
}

/**
 * USED IN: administrator/components/com_content/views/article/view.html.php
 *
 * Changes a dynamically generated list
 *
 * @param string
 *			The name of the list to change
 * @param array
 *			A javascript array of list options in the form [key,value,text]
 * @param string
 *			The key to display
 * @param string
 *			The original key that was selected
 * @param string
 *			The original item value that was selected
 */
function changeDynaList(listname, source, key, orig_key, orig_val) {
	var list = document.adminForm[listname];

	// empty the list
	for (i in list.options.length) {
		list.options[i] = null;
	}
	i = 0;
	for (x in source) {
		if (source[x][0] == key) {
			opt = new Option();
			opt.value = source[x][1];
			opt.text = source[x][2];

			if ((orig_key == key && orig_val == opt.value) || i == 0) {
				opt.selected = true;
			}
			list.options[i++] = opt;
		}
	}
	list.length = i;
}

/**
 * USED IN: administrator/components/com_menus/views/menus/tmpl/default.php
 *
 * @param radioObj
 * @return
 */
// return the value of the radio button that is checked
// return an empty string if none are checked, or
// there are no radio buttons
function radioGetCheckedValue(radioObj) {
	if (!radioObj) {
		return '';
	}
	var n = radioObj.length;
	if (n == undefined) {
		if (radioObj.checked) {
			return radioObj.value;
		} else {
			return '';
		}
	}
	for ( var i = 0; i < n; i++) {
		if (radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return '';
}

/**
 * USED IN: administrator/components/com_banners/views/banner/tmpl/default/php
 * administrator/components/com_categories/views/category/tmpl/default.php
 * administrator/components/com_categories/views/copyselect/tmpl/default.php
 * administrator/components/com_content/views/copyselect/tmpl/default.php
 * administrator/components/com_massmail/views/massmail/tmpl/default.php
 * administrator/components/com_menus/views/list/tmpl/copy.php
 * administrator/components/com_menus/views/list/tmpl/move.php
 * administrator/components/com_messages/views/message/tmpl/default_form.php
 * administrator/components/com_newsfeeds/views/newsfeed/tmpl/default.php
 * components/com_content/views/article/tmpl/form.php
 * templates/beez/html/com_content/article/form.php
 *
 * @param frmName
 * @param srcListName
 * @return
 */
function getSelectedValue(frmName, srcListName) {
	var form = document[frmName];
	var srcList = form[srcListName];

	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		return srcList.options[i].value;
	} else {
		return null;
	}
}

/**
 * USED IN: all over :)
 *
 * @param id
 * @param task
 * @return
 */
function listItemTask(id, task) {
	var f = document.adminForm;
	var cb = f[id];
	if (cb) {
		for (var i = 0; true; i++) {
			var cbx = f['cb'+i];
			if (!cbx)
				break;
			cbx.checked = false;
		} // for
		cb.checked = true;
		f.boxchecked.value = 1;
		submitbutton(task);
	}
	return false;
}

/**
 * Default function. Usually would be overriden by the component
 *
 * @deprecated	12.1 This function will be removed in a future version. Use Joomla.submitbutton() instead.
 */
function submitbutton(pressbutton) {
	submitform(pressbutton);
}

/**
 * Submit the admin form
 *
 * @deprecated	12.1 This function will be removed in a future version. Use Joomla.submitform() instead.
 */
function submitform(pressbutton) {
	if (pressbutton) {
		document.adminForm.task.value = pressbutton;
	}
	if (typeof document.adminForm.onsubmit == "function") {
		document.adminForm.onsubmit();
	}
	if (typeof document.adminForm.fireEvent == "function") {
		document.adminForm.fireEvent('submit');
	}
	document.adminForm.submit();
}

// needed for Table Column ordering
/**
 * USED IN: libraries/joomla/html/html/grid.php
 */
function saveorder(n, task) {
	checkAll_button(n, task);
}

function checkAll_button(n, task) {
	if (!task) {
		task = 'saveorder';
	}

	for (var j = 0; j <= n; j++) {
		var box = document.adminForm['cb'+j];
		if (box) {
			if (box.checked == false) {
				box.checked = true;
			}
		} else {
			alert("You cannot change the order of items, as an item in the list is `Checked Out`");
			return;
		}
	}
	submitform(task);
}

/**
 * Extend Objects function
 */
Joomla.extend = function(destination, source) {
	for(var p in source) {
		destination[p] = source[p];
	}
	return destination;
}

/**
 * fallbacks
 */
if (!Function.prototype.bind) {
	Function.prototype.bind = function(bind) {
		var self = this;
		var args = Array.prototype.slice.call(arguments, 1);
		return function(){
			return self.apply(bind, args);
		};
	};
}
//https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/IndexOf#Compatibility
if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(searchElement /* , fromIndex */) {
		"use strict";
		if (this == null) {
			throw new TypeError();
		}
		var t = Object(this);
		var len = t.length >>> 0;
		if (len === 0) {
			return -1;
		}
		var n = 0;
		if (arguments.length > 1) {
			n = Number(arguments[1]);
			if (n != n) { // shortcut for verifying if it's NaN
				n = 0;
			} else if (n != 0 && n != Infinity && n != -Infinity) {
				n = (n > 0 || -1) * Math.floor(Math.abs(n));
			}
		}
		if (n >= len) {
			return -1;
		}
		var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
		for (; k < len; k++) {
			if (k in t && t[k] === searchElement) {
				return k;
			}
		}
		return -1;
	}
}

/**
 * One way scripts initialisation.
 *
 * For current implementation NOT required a MooTools or other js framework. So in theory also need make
 * "core" behavior JHtml::_('behavior.core') and then 'behavior.framework' can change for load needed framework.
 *
 * Links:
 * https://groups.google.com/d/topic/joomla-dev-platform/dWUbRsOAtNw/discussion
 * http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=28119
 * https://groups.google.com/d/topic/joomla-dev-cms/jyKt5VE5PWw/discussion
 *
 */

/**
 * init options storage
 */

Joomla.optionsStorage = {};

/**
 * init events storage
 */

Joomla.eventsStorage = {};

/**
 * marker used for check whether domready was called-in
 */

Joomla.readyCalled = false;

/**
 * domready listener
 * 	based on contentloaded.js http://javascript.nwbox.com/ContentLoaded/
 * 	written by Diego Perini (diego.perini at gmail.com)
 */

Joomla.contentLoaded = function(fn) {
	//init variables
	var done = false, top = true,

	win = window, doc = win.document, root = doc.documentElement,

	add = doc.addEventListener ? 'addEventListener' : 'attachEvent',
	rem = doc.addEventListener ? 'removeEventListener' : 'detachEvent',
	pre = doc.addEventListener ? '' : 'on',

	init = function(e) {
		if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
		(e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
		if (!done && (done = true)) fn.call(win, e.type || e);
	},

	poll = function() {
		try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
		init('poll');
	};

	//do action
	if (doc.readyState == 'complete') fn.call(win, 'lazy');
	else {
		if (doc.createEventObject && root.doScroll) {
			try { top = !win.frameElement; } catch(e) { }
			if (top) poll();
		}
		doc[add](pre + 'DOMContentLoaded', init, false);
		doc[add](pre + 'readystatechange', init, false);
		win[add](pre + 'load', init, false);
	}

}

/**
 * add events for init
 * use like:
 * 		Joomla.addEvent('domready.extension_name', callback); // for domready, and the "load" that comes after first "load"
 * 		Joomla.addEvent('load.extension_name', callback); // for load
 * 		Joomla.addEvent('unload.extension_name', callback); // for unload only
 *
 *		all "domready" have subscription for "load" event automatically - it important for keep
 *		the script working after DOM manipulation by other scripts, or for init scripts
 *		that added "on fly"
 *
 * @param event - string, event name
 * @param fn - callback function
 *
 */

Joomla.addEvent = function (event, fn) {
	// Get event type and extension name
	var names = event.split('.'), nameBase = names[0], nameSpace = names[1];

	//decide which events we will use

	//if event == "domready"  and "domready" already fired
	//subscrib it on the "load" event
	if(nameBase.indexOf('ready') !== -1 && Joomla.readyCalled) {
		event = event.replace(nameBase, 'load');
		nameBase = 'load';
	}

	var events = [];
	//base event eg: domready, load
	events.push(nameBase);

	//namespaced event eg: load.extension_name,
	//but skip domready
	if(nameSpace && nameBase.indexOf('ready') === -1) {
		events.push(event);
	}

	//store the callbacs in the storage, event => callbacks array
	for (var i = 0; i < events.length; i++) {
		//prepare events storage
		if (!Joomla.eventsStorage[events[i]]) {
			Joomla.eventsStorage[events[i]] = [];
		}

		//add only once
		if (Joomla.eventsStorage[events[i]].indexOf(fn) === -1) {
			Joomla.eventsStorage[events[i]].push(fn);
		}
	}

	//callback for execure all callbacs in event
	var callback = Joomla.fireEvent.bind(window, nameBase, document);

	//add to eventlisteners, but only real events
	if (nameBase.indexOf('ready') !== -1) {
		Joomla.contentLoaded(callback);

		//subscrib domredy to "load" event after first "load" fired
		Joomla.addEvent('load', function(){
			//remove from "domready" storage
			Joomla.removeEvent(nameBase, fn);
			//subscrib to "load", keep nameSpace
			Joomla.addEvent((!nameSpace ? 'load' : 'load.' + nameSpace), fn);
			//self destroy (:
			Joomla.removeEvent('load', arguments.callee);
			//set marker called, here we sure on 100%
			Joomla.readyCalled = true;
		})
	}
	else if (window.addEventListener) { // W3C DOM
		window.addEventListener(nameBase, callback);
	}
	else if (window.attachEvent) { // IE DOM
		window.attachEvent('on' + nameBase, callback);
	}

	return Joomla;
};

/**
 * remove events
 *
 * @param event - string, event name
 * @param fn - callback function that need to remove
 *
 */
Joomla.removeEvent = function (event, fn) {
	// Get event type and extension name
	var names = event.split('.'), nameBase = names[0], nameSpace = names[1];

	//decide which events we will use

	var events = [];
	//base event eg: load
	events.push(nameBase);

	//namespaced event eg: load.extension_name
	if(nameSpace) {
		events.push(event);
	}

	//do removing from eventsStorage
	for (var i = 0; i < events.length; i++) {
		if (Joomla.eventsStorage[events[i]].length) {
			var index = Joomla.eventsStorage[events[i]].indexOf(fn);
			if( index !== -1) {
				//use splice(k, 1) for keep length
				delete Joomla.eventsStorage[events[i]][index];
				Joomla.eventsStorage[events[i]].splice(index, 1)
			}
		}
	}

	//and remove from eventlisteners, but only real events
	if (nameBase.indexOf('ready') === -1) {
		//callback in same way as was in addEvent()
		var callback = Joomla.fireEvent.bind(window, nameBase, document);

		if (window.removeEventListener) { // W3C DOM
			window.removeEventListener(nameBase, callback);
		}
		else if (window.detachEvent) { // IE DOM
			window.detachEvent("on" + nameBase, callback);
		}
	}

	return Joomla;
}

/**
 * fire event before/after domchanged
 * use like:
 * 		Joomla.fireEvent('unload', 'unloaded-element'); //fires for all unload subscribers
 * 		Joomla.fireEvent('unload.extension_name', 'unloaded-element'); //fires only for specified exstension
 *
 * 		Joomla.fireEvent('load', 'changed-element'); //fires for all domready, load subscribers
 * 		Joomla.fireEvent('load.extension_name', 'changed-element'); //fires only for specified exstension
 *
 * @param event - string, event name
 * @param element - element DOM object or ID of element
 *
 */

Joomla.fireEvent = function(event, element) {
	if (!Joomla.eventsStorage[event]) return;
	if (!element) element = document;
	//call functions
	for (var i = 0; i < Joomla.eventsStorage[event].length; i++) {
		//try do not break site if some script is buggy
		try {
			Joomla.eventsStorage[event][i].call(window, event, element);
		} catch (e) {
			if(window.console){ console.log(e); console.log(e.stack);}
		}
	}

	return Joomla;
}

