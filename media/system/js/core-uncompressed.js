/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
		/**
		 * Added to ensure Joomla 1.5 compatibility
		 */
		if(!form){
			form = document.adminForm;
		}
	}

	if (typeof(task) !== 'undefined' && '' !== task) {
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

	var dl = new Element('dl', {
		id: 'system-message',
		role: 'alert'
	});
	Object.each(messages, function (item, type) {
		var dt = new Element('dt', {
			'class': type,
			html: type
		});
		dt.inject(dl);
		var dd = new Element('dd', {
			'class': type
		});
		dd.addClass('message');
		var list = new Element('ul');

		Array.each(item, function (item, index, object) {
			var li = new Element('li', {
				html: item
			});
			li.inject(list);
		}, this);
		list.inject(dd);
		dd.inject(dl);
	}, this);
	dl.inject(container);
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
		/**
		 * Added to ensure Joomla 1.5 compatibility
		 */
		if(!form){
			form = document.adminForm;
		}
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
		/**
		 * Added to ensure Joomla 1.5 compatibility
		 */
		if(!form){
			form = document.adminForm;
		}
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
 * USED IN: all list forms.
 *
 * Toggles the check state of a group of boxes
 *
 * Checkboxes must have an id attribute in the form cb0, cb1...
 *
 * @param	mixed	The number of box to 'check', for a checkbox element
 * @param	string	An alternative field name
 *
 * @deprecated	12.1 This function will be removed in a future version. Use Joomla.checkAll() instead.
 */
function checkAll(checkbox, stub) {
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
	} else {
		// The old way of doing it
		var f = document.adminForm;
		var c = f.toggle.checked;
		var n = checkbox;
		var n2 = 0;
		for (var i = 0; i < n; i++) {
			var cb = f[stub+''+i];
			if (cb) {
				cb.checked = c;
				n2++;
			}
		}
		if (c) {
			document.adminForm.boxchecked.value = n2;
		} else {
			document.adminForm.boxchecked.value = 0;
		}
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
 * USED IN: administrator/components/com_cache/views/cache/tmpl/default.php
 * administrator/components/com_installer/views/discover/tmpl/default_item.php
 * administrator/components/com_installer/views/update/tmpl/default_item.php
 * administrator/components/com_languages/helpers/html/languages.php
 * libraries/joomla/html/html/grid.php
 *
 * @deprecated	12.1 This function will be removed in a future version. Use Joomla.isChecked() instead.
 *
 * @param isitchecked
 * @return
 *
 */
function isChecked(isitchecked) {
	if (isitchecked == true) {
		document.adminForm.boxchecked.value++;
	} else {
		document.adminForm.boxchecked.value--;
	}
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

/**
 * USED IN: libraries/joomla/html/toolbar/button/help.php
 *
 * Pops up a new window in the middle of the screen
 *
 * @deprecated	12.1 This function will be removed in a future version. Use Joomla.popupWindow() instead.
 */
function popupWindow(mypage, myname, w, h, scroll) {
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl
			+ ',scrollbars=' + scroll + ',resizable'
	win = window.open(mypage, myname, winprops)
	if (parseInt(navigator.appVersion) >= 4) {
		win.window.focus();
	}
}

// needed for Table Column ordering
/**
 * USED IN: libraries/joomla/html/html/grid.php
 *
 * @deprecated	12.1 This function will be removed in a future version. Use Joomla.tableOrdering() instead.
 */
function tableOrdering(order, dir, task) {
	var form = document.adminForm;

	form.filter_order.value = order;
	form.filter_order_Dir.value = dir;
	submitform(task);
}

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
