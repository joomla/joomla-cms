'use strict';

// /** The Calendar object constructor. */
window.JoomlaCalendar = function (onSelect, onClose, params) {

	this.params = {};

	this.onSelected = onSelect || null;                                                                // Function for the event onSelected
	this.onClose = onClose || function (cal) { cal.hide(); };                                          // Function for the event onClose

	this.params.dateStr = params.dateStr ? params.dateStr : '';                                        // The date string
	this.params.firstDayOfWeek = params.firstDayOfWeek ? parseInt(params.firstDayOfWeek) : 0;          // 0 for Sunday, 1 for Monday, etc.
	this.params.time24 = params.time24 ? Boolean(params.time24) : false;                      // Use 24/12 hour format
	this.params.showsOthers = true; //params.showsOthers ? Boolean(params.showsOthers) : true;  // Display previous/next month days as disables
	this.params.showsTime = params.showsTime ? Boolean(params.showsTime) : false;              // Shows hours and minutes drop downs
	this.params.weekNumbers = params.weekNumbers ? Boolean(params.weekNumbers) : false;                // Shows the week number as first column
	this.params.showsTodayBtn = params.showsTodayBtn ? Boolean(params.showsTodayBtn) : true;                          // Display a today button
	this.params.compressedHeader = params.compressedHeader ? Boolean(params.compressedHeader) : false;                   // Use one line for year month
	this.params.minYear = params.minYear ? params.minYear : 1970;                                      // The minimum year
	this.params.maxYear = params.maxYear ? params.maxYear : 2050;                                      // The maximum year
	this.params.dateFormat = params.dateFormat ? params.dateFormat : "%Y-%m-%d";                       // The date format
	/***
	 * The translated strings come from PHP
	 */
	this.params.stringFD = params.stringFD ? params.stringFD : 0;                                      // 0=>Sun, 1=>Mon etc
	this.params.stringTODAY = params.stringTODAY ? params.stringTODAY : "Today";                       // Today
	this.params.stringWEEKEND = params.stringWEEKEND ? params.stringWEEKEND : [0, 6];                  // 0,6
	this.params.stringWK = params.stringWK ? params.stringWK : "wk";                                   // wk
	this.params.stringTIME = params.stringTIME ? params.stringTIME : "Time:";                          // Time:
	this.params.stringTIMEAM = params.stringTIMEAM ? params.stringTIMEAM : "AM";
	this.params.stringTIMEPM = params.stringTIMEPM ? params.stringTIMEPM : "PM";
	this.params.stringDN = params.stringDN ? params.stringDN : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
	this.params.stringSDN = params.stringSDN ? params.stringSDN : ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
	this.params.stringMN = params.stringMN ? params.stringMN : ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	this.params.stringSMN = params.stringSMN ? params.stringSMN : ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

	/**
	 * Support for different calendars, e.g.: jalali
	 */
	this.params.dateType = params.dateType ? params.dateType : 'gregorian';

	// LTR <> RTL
	(document.dir != undefined) ? this.params.direction = document.dir
		: this.params.direction = document.getElementsByTagName("html")[0].getAttribute("dir");
};

/** COMPATIBILITY WITH IE 8 **/
// The functions bellow should be replaced with native javascript once IE 8 is dead
// removeClass == classList.removeClass
// addClass    == classList.addClass
// hasClass    == classList.contains
// addEvent    == addEventListener
// removeEvent == removeEventListener
JoomlaCalendar.removeClass = function (el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.className = ar.join(" ");
};

JoomlaCalendar.addClass = function (el, className) {
	JoomlaCalendar.removeClass(el, className);
	el.className += " " + className;
};

JoomlaCalendar.hasClass = function (element, cls) {
	return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
};

JoomlaCalendar.stopEvent = function (ev) {
	ev || (ev = window.event);
	if (/msie/i.test(navigator.userAgent)) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
	return false;
};

JoomlaCalendar.addEvent = function (el, evname, func) {
	if (el.attachEvent) { // IE
		el.attachEvent("on" + evname, func);
	} else if (el.addEventListener) { // Gecko / W3C
		el.addEventListener(evname, func, true);
	} else {
		el["on" + evname] = func;
	}
};

JoomlaCalendar.removeEvent = function (el, evname, func) {
	if (el.detachEvent) { // IE
		el.detachEvent("on" + evname, func);
	} else if (el.removeEventListener) { // Gecko / W3C
		el.removeEventListener(evname, func, true);
	} else {
		el["on" + evname] = null;
	}
};

JoomlaCalendar.createElement = function (type, parent) {
	var el = null;
	el = document.createElement(type);
	if (typeof parent != "undefined") {
		parent.appendChild(el);
	}
	return el;
};
/** END OF COMPATIBILITY WITH IE 8 **/

JoomlaCalendar.findMonth = function (el) {
	if (typeof el.month != "undefined") {
		return el;
	} else if (typeof el.parentNode.month != "undefined") {
		return el.parentNode;
	}
	return null;
};

JoomlaCalendar.findYear = function (el) {
	if (typeof el.year != "undefined") {
		return el;
	} else if (typeof el.parentNode.year != "undefined") {
		return el.parentNode;
	}
	return null;
};

JoomlaCalendar.prototype.convertNumbers = function(str) {
	var str = str.toString();
	if (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]') str = str.convertNumbers();
	return str;
};

String.prototype.convertNumbers = function() {
	var str = this.toString();
	if (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]') {
		for (var i = 0; i < Date.localLangNumbers.length; i++) {
			str = str.replace(new RegExp(i, 'g'), Date.localLangNumbers[i]);
		}
	}
	return str;
};

/** Time Control */
JoomlaCalendar.updateTime = function (hours, mins, ampm) {
	var cal = jCalendar;
	var date = cal.date;
	if (ampm) {
		if (/pm/i.test(ampm) && hours < 12)
			hours = parseInt(hours) + 12;
		else if (/am/i.test(ampm) && hours == 12)
			hours = 0;
	}
	var d = cal.date.getDate();
	var m = cal.date.getMonth();
	var y = cal.date.getFullYear();
	date.setHours(hours);
	date.setMinutes(parseInt(mins, 10));
	date.setFullYear(y);
	date.setMonth(m);
	date.setDate(d);
	cal.dateClicked = false;
	cal.callHandler();
};

/** Dynamically changes weekNumbers property */
JoomlaCalendar.prototype.setWeekNumbers = function (weekNumbers) {
	this.params.weekNumbers = weekNumbers;
	this.recreate();
};

/** Dynamically changes showsOthers property */
JoomlaCalendar.prototype.setOtherMonths = function (showsOthers) {
	this.params.showsOthers = showsOthers;
	this.refresh();
};

/** Dynamically changes langNumbers property */
JoomlaCalendar.prototype.setLocallangNumbers = function (localLangNumbers) {
	this.localLangNumbers = localLangNumbers;
	this.refresh();
};

/** Dynamically changes dateType property */
JoomlaCalendar.prototype.setDateType = function (dateType) {
	this.params.dateType = dateType;
	this.recreate();
};

/** Dynamically changes showsTime property */
JoomlaCalendar.prototype.setShowsTime = function (showsTime) {
	this.params.showsTime = showsTime;
	this.recreate();
};

/** Dynamically changes time24 property */
JoomlaCalendar.prototype.setTime24 = function (time24) {
	this.params.time24 = time24;
	this.recreate();
};

/**
 *  Calls _init function above for going to a certain date (but only if the
 *  date is different than the currently selected one).
 */
JoomlaCalendar.prototype.setDate = function (date) {
	if (!date.equalsTo(this.date)) {
		this.date = date;
		this.refresh();
	}
};

/**
 *  Refreshes the JoomlaCalendar.  Useful if the "disabledHandler" function is
 *  dynamic, meaning that the list of disabled date can change at runtime.
 *  Just * call this function if you think that the list of disabled dates
 *  should * change.
 */
JoomlaCalendar.prototype.refresh = function () {
	this._init(this.params.firstDayOfWeek, this.date);
};

/** Modifies the "firstDayOfWeek" parameter (pass 0 for Synday, 1 for Monday, etc.). */
JoomlaCalendar.prototype.setFirstDayOfWeek = function (firstDayOfWeek) {
	this._init(firstDayOfWeek, this.date);
	this._displayWeekdays();
};

/**
 *  Allows customization of what dates are enabled.  The "unaryFunction"
 *  parameter must be a function object that receives the date (as a JS Date
 *  object) and returns a boolean value.  If the returned value is true then
 *  the passed date will be marked as disabled.
 */
JoomlaCalendar.prototype.setDateStatusHandler = function (unaryFunction) {
	this.getDateStatus = unaryFunction;
};

/** Calls the first user handler (selectedHandler). */
JoomlaCalendar.prototype.callHandler = function () {
	if (this.onSelected) {
		this.onSelected(this, this.date.print(this.params.dateFormat, this.params.dateType));
	}
};

/** Calls the second user handler (closeHandler). */
JoomlaCalendar.prototype.callCloseHandler = function () {
	if (this.onClose) {
		this.onClose(this);
	}
	this.destroy();
};

/** Removes the calendar object from the DOM tree and destroys it. */
JoomlaCalendar.prototype.destroy = function () {
	var el = this.element.parentNode;
	if (el) {
		el.removeChild(this.element);
		window.jCalendar = null;
	}
};

/**
 * This gets called when the user presses a mouse button anywhere in the
 * document, if the calendar is shown. If the click was outside the open
 * calendar this function closes it.
 */
JoomlaCalendar._checkCalendar = function (ev) {
	var calendar = window.jCalendar;
	if (!calendar) {
		return false;
	}
	var el = ev.target.parentNode;
	for (; el != null && el != calendar.element; el = el.parentNode);
	if (el == null) {
		// calls closeHandler which should hide the calendar.
		calendar.callCloseHandler();
		return JoomlaCalendar.stopEvent(ev);
	}
};

/** Shows the calendar. */
JoomlaCalendar.prototype.show = function () {
	var rows = this.table.getElementsByTagName("tr");
	for (var i = rows.length; i > 0;) {
		var row = rows[--i];
		var cells = row.getElementsByTagName("td");
		for (var j = cells.length; j > 0;) {
			var cell = cells[--j];
			JoomlaCalendar.removeClass(cell, 'alert-info');
			JoomlaCalendar.removeClass(cell, 'alert-success');
		}
	}
	this.element.style.display = "block";
	this.hidden = false;
	window.jCalendar = this;
	JoomlaCalendar.addEvent(document, "keydown", JoomlaCalendar._keyEvent);
	JoomlaCalendar.addEvent(document, "keypress", JoomlaCalendar._keyEvent);
	JoomlaCalendar.addEvent(document, "mousedown", JoomlaCalendar._checkCalendar);
};

/**
 *  Hides the calendar.  Also removes any "hilite" from the class of any TD
 *  element.
 */
JoomlaCalendar.prototype.hide = function () {
	JoomlaCalendar.removeEvent(document, "keydown", JoomlaCalendar._keyEvent);
	JoomlaCalendar.removeEvent(document, "keypress", JoomlaCalendar._keyEvent);
	JoomlaCalendar.removeEvent(document, "mousedown", JoomlaCalendar._checkCalendar);
	this.element.style.display = "none";
	this.hidden = true;
};

/** Customizes the date format. */
JoomlaCalendar.prototype.setDateFormat = function (str) {
	this.params.dateFormat = str;
};

/**
 *  Tries to identify the date represented in a string.  If successful it also
 *  calls this.setDate which moves the calendar to the given date.
 */
JoomlaCalendar.prototype.parseDate = function(str, fmt, dateType) {
	if (!fmt) fmt = this.params.dateFormat;
	if (!dateType) dateType = this.params.dateType;
	this.setDate(Date.parseDate(str, fmt, dateType));
};

/** Internal function; it displays the bar with the names of the weekday. */
JoomlaCalendar.prototype._displayWeekdays = function () {
	var fdow = this.params.firstDayOfWeek;
	var cell = this.firstdayname;
	var weekend = Date.stringWEEKEND;
	for (var i = 0; i < 7; ++i) {
		cell.className = "day name";
		cell.style.textAlign = 'center';
		var realday = (i + fdow) % 7;
		if (i) {
			cell.calendar = this;
			cell.fdow = realday;
		}
		if (weekend.indexOf(realday.toString()) != -1) {
			JoomlaCalendar.addClass(cell, "weekend");
		}
		cell.innerHTML = Date.stringSDN[(i + fdow) % 7];
		cell = cell.nextSibling;
	}
};

/** event handlers **/
JoomlaCalendar.dayMouseDown = function (ev) {
	var el = ev.currentTarget;
	var target = ev.target || ev.srcElement;

	if (el.nodeName !== 'TD') {
		// A bootstrap inner button was pressed?
		var testel = el.getParent('TD');
		if (testel.nodeName === 'TD') {
			// Yes so use that element's td
			el = testel;
		} else {
			// No - try to find the table this way
			el = el.getParent('TD');
			if (JoomlaCalendar.hasClass(el, 'dropdown-menu')) {
				el = el.getElementsByTagName('table');
			}
		}
	} else {
		// Check that the td doesn't have a bootstrap button in it (and is not a day td) - if so ingore the event
		if (!(JoomlaCalendar.hasClass(target, 'btn')) && !JoomlaCalendar.hasClass(el, 'day') && !JoomlaCalendar.hasClass(el, 'title')) {
			return;
		}
	}
	if (!el || el.disabled) {
		return false;
	}
	var cal = el.calendar;

	if (typeof el.navtype === "undefined" || el.navtype != 300) {
		if (el.navtype == 50)
			el._current = el.innerHTML;

		JoomlaCalendar.removeClass(el, "alert-success");
		if (target == el || target.parentNode == el) {
			JoomlaCalendar.cellClick(el, ev);
		}
		var mon = JoomlaCalendar.findMonth(target);
		var date = null;
		if (mon) {
			date = new Date(cal.date);
			if (mon.month != date.getMonth()) {
				date.setMonth(mon.month);
				cal.setDate(date);
				cal.dateClicked = false;
				cal.callHandler();
			}
		} else {
			var year = JoomlaCalendar.findYear(target);
			if (year) {
				date = new Date(cal.date);
				if (year.year != date.getFullYear()) {
					date.setFullYear(year.year);
					cal.setDate(date);
					cal.dateClicked = false;
					cal.callHandler();
				}
			}
		}
	}
	return JoomlaCalendar.stopEvent(ev);
};

/** Click handler */
JoomlaCalendar.cellClick = function (el, ev) {
	var cal = el.calendar, closing = false, newdate = false, date = null;

	if (typeof el.navtype == "undefined") {
		if (cal.currentDateEl) {
			JoomlaCalendar.removeClass(cal.currentDateEl, "selected");
			JoomlaCalendar.removeClass(cal.currentDateEl, "alert-success");
			JoomlaCalendar.addClass(el, "selected alert-success");
			cal.currentDateEl = el;

			closing = (cal.currentDateEl == el);
			if (!closing) {
				cal.currentDateEl = el;
			}
		}
		cal.date.setDateOnly(el.caldate);
		date = cal.date;
		var other_month = !(cal.dateClicked = !el.otherMonth);
		if (cal.currentDateEl)
			newdate = !el.disabled;
		if (other_month)
			cal._init(cal.params.firstDayOfWeek, date);
	} else {
		if (el.navtype == 200) {
			JoomlaCalendar.removeClass(el, "hilite");
			cal.callCloseHandler();
			return;
		}
		date = new Date(cal.date);
		if (el.navtype == 0) {
			cal.date.setDateOnly(new Date()); // TODAY
			cal.callHandler();
			cal.callCloseHandler();
			return;
		}
		// unless "today" was clicked, we assume no date was clicked so
		// the selected handler will know not to close the calendar
		cal.dateClicked = false;
		var year = date.getLocalFullYear(cal.params.dateType);
		var mon = date.getLocalMonth(cal.params.dateType);
		switch (el.navtype) {
			case 400:
				break;
			case -2:
				if (!cal.params.onlyMonths)
					if (year > cal.params.minYear) {
						date._calSetLocalFullYear(cal.params.dateType, year - 1);
					}
				break;
			case -1:
				var day = date.getDate();
				if (mon > 0) {
					var max = date.getLocalMonthDays(cal.params.dateType, mon - 1);
					if (day > max) date.setLocalDate(cal.params.dateType, max);
					date.setMonth(mon - 1);

				} else if (year-- > cal.params.minYear) {
					date._calSetLocalFullYear(cal.params.dateType, year);
					var max = date.getLocalMonthDays(cal.params.dateType, 11);
					if (day > max) date.setLocalDate(cal.params.dateType, max);
					date.setMonth(11);
				}
				break;
			case 1:
				var day = date.getDate();
				if (mon < 11) {
					var max = date.getLocalMonthDays(cal.params.dateType, mon + 1);
					if (day > max) date.setLocalDate(cal.params.dateType, max);
					date.setMonth(mon + 1);
				} else if (year < cal.params.maxYear) {
					date._calSetLocalFullYear(cal.params.dateType, year + 1);
					var max = date.getLocalMonthDays(cal.params.dateType, 0);
					if (day > max) date.setLocalDate(cal.params.dateType, max);
					date.setMonth(0);
				}
				break;
			case 2:
				if (!cal.params.onlyMonths)
					if (year < cal.params.maxYear) {
						date._calSetLocalFullYear(cal.params.dateType, year + 1);
					}
				break;
			case 0:
				// TODAY will bring us here
				if ((typeof cal.getDateStatus == "function") &&
					cal.getDateStatus(date, date.getLocalFullYear(cal.params.dateType),
						date.getLocalMonth(cal.params.dateType),
						date.getLocalDate(cal.params.dateType))) {
					return false;
				}
				break;
		}
		if (!date.equalsTo(cal.date)) {
			cal.setDate(date);
			newdate = true;
		} else if (el.navtype == 0)
			newdate = closing = true;
	}
	if (newdate) {
		ev && cal.callHandler();
	}
	if (closing) {
		JoomlaCalendar.removeClass(el, "hilite");
		ev && cal.callCloseHandler();
	}
};

/** This function creates the calendar inside the parent of the given input. */
JoomlaCalendar.prototype.create = function (parent) {
	this.date = this.params.dateStr ? new Date(this.params.dateStr) : new Date();

	var table = JoomlaCalendar.createElement("table");
	this.table = table;
	table.className = 'table';
	table.cellSpacing = 0;
	table.cellPadding = 0;
	table.calendar = this;
	table.style.marginBottom = 0;
	JoomlaCalendar.addEvent(table, "mousedown", JoomlaCalendar.tableMouseDown);

	var div = JoomlaCalendar.createElement("div");
	this.element = div;
	if (this.params.direction) {
		this.element.style.direction = this.params.direction;
	}

	div.className = 'dropdown-menu j-calendar';
	div.style.position = "fixed";
	div.style.boxShadow = "0px 0px 70px 0px rgba(0,0,0,0.67)";
	div.style.minWidth = parent.width; //this.params.weekNumbers ? '340px' : '320px';
	div.style.padding = '0';

	// Move the calendar to top position if it doesn't fit below
	var elementRect = div.getBoundingClientRect();
	var inputRect = parent.parentNode.getElementsByTagName('INPUT')[0].getBoundingClientRect();

	// Set the left margin
	div.style.left = inputRect.left - elementRect.left + "px";
	div.style.top = inputRect.bottom + 3 + "px"; // - elementRect.left;

	this.wrapper = JoomlaCalendar.createElement('div');
	this.wrapper.className = 'calendar-container';
	div.appendChild(this.wrapper);
	this.wrapper.appendChild(table);

	var thead = JoomlaCalendar.createElement("thead", table);
	thead.className = 'calendar-header';
	var cell = null;
	var row = null;

	var cal = this;
	var hh = function (text, cs, navtype, node, styles, classes) {
		node = node ? node : "td";
		classes = classes ? 'class="' + classes + '"' : '';
		styles = styles ? styles : {};
		cell = JoomlaCalendar.createElement(node, row);
		cell.colSpan = cs;
		// cell.className = "button";
		for (var key in styles) {
			cell.style[key] = styles[key];
		}
		if (navtype != 0 && Math.abs(navtype) <= 2) {
			cell.className += " nav";
		}

		//JoomlaCalendar._add_evs(cell);
		JoomlaCalendar.addEvent(cell, "mousedown", JoomlaCalendar.dayMouseDown);
		cell.calendar = cal;
		cell.navtype = navtype;
		if (navtype != 0 && Math.abs(navtype) <= 2) {
			cell.innerHTML = "<a " + classes + " style='display:inline;padding:2px 6px;cursor:pointer;text-decoration:none;' unselectable='on'>" + text + "</a>";
		} else {
			cell.innerHTML = "<div unselectable='on'" + classes + ">" + text + "</div>";
		}

		return cell;
	};

	// Head - year
	if (this.params.compressedHeader == false) {
		row = JoomlaCalendar.createElement("tr", thead);
		row.className = "calendar-head-row";

		// Previous year button
		this._nav_py = hh("<", 1, -2, '', '', 'btn btn-small btn-default pull-left');
		// Year
		this.title = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', this.params.weekNumbers ? 6 : 5, 300);
		this.title.className = "title";
		// Next year button
		this._nav_ny = hh(">", 1, 2, '', '', 'btn btn-small btn-default pull-right');
	}

	// Head - month
	row = JoomlaCalendar.createElement("tr", thead);
	row.className = "calendar-head-row";

	// Previous month button
	this._nav_pm = hh("<", 1, -1, '', '', 'btn btn-small btn-default pull-left');
	// Month
	this._nav_month = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', this.params.weekNumbers ? 6 : 5, 888, 'td', {'textAlign': 'center'});
	this._nav_month.className = "title";
	// Next month button
	this._nav_nm = hh(">", 1, 1, '', '', 'btn btn-small btn-default pull-right');

	// Head - today
	if (this.params.showsTodayBtn) {
		row = JoomlaCalendar.createElement("tr", thead);
		row.className = "headrow";
		this._nav_now = hh('<a class="btn btn-small btn-success" data-action="today" style="display:block;padding:2px 6px;">' + Date.stringTODAY + '</a>', this.params.weekNumbers ? 8 : 7, 0, 'td', {'textAlign': 'center'});
		// HTML5 version
		var todaya = row.querySelector('a[data-action=today]');
		// Support IE8
		if (typeof todaya == "undefined") {
			var tempElem = row.getElementsByTagName("A"), i, todaya = null;
			for (i = 0; i < tempElem.length; i++) {
				if (tempElem[i].getAttribute("data-action") == "today")
					todaya = tempElem[i];
			}
		}

		JoomlaCalendar.addEvent(todaya, 'click', function (e) {
			var el = todaya.parentNode.parentNode;
			if (el.tagName === 'TD') {
				var cal = el.calendar;
				JoomlaCalendar.cellClick(cal._nav_now);
			}
		});
	}

	// day names
	row = JoomlaCalendar.createElement("tr", thead);
	row.className = "daynames";
	if (this.params.weekNumbers) {
		cell = JoomlaCalendar.createElement("td", row);
		cell.className = "name wn";
		cell.innerHTML = Date.stringWK;
	}
	for (var i = 7; i > 0; --i) {
		cell = JoomlaCalendar.createElement("td", row);
		if (!i) {
			cell.calendar = this;
		}
	}
	this.firstdayname = (this.params.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;
	this._displayWeekdays();

	var tbody = JoomlaCalendar.createElement("tbody", table);
	this.tbody = tbody;

	for (i = 6; i > 0; --i) {
		row = JoomlaCalendar.createElement("tr", tbody);
		if (this.params.weekNumbers) {
			cell = JoomlaCalendar.createElement("td", row);
		}
		for (var j = 7; j > 0; --j) {
			cell = JoomlaCalendar.createElement("td", row);
			cell.calendar = this;
			JoomlaCalendar.addEvent(cell, "mousedown", JoomlaCalendar.dayMouseDown);
		}
	}

	if (this.params.showsTime) {
		row = JoomlaCalendar.createElement("tr", tbody);
		row.className = "time";

		cell = JoomlaCalendar.createElement("td", row);
		cell.className = "time time-title";
		cell.colSpan = 1;
		cell.style.verticalAlign = 'middle';
		cell.innerHTML = Date.stringTIME || "&#160;";

		var cell1 = JoomlaCalendar.createElement("td", row);
		cell1.className = "time hours-select";
		cell1.colSpan = 2;

		var cell2 = JoomlaCalendar.createElement("td", row);
		cell2.className = "time minutes-select";
		cell2.colSpan = 2;


		(function () {
			function makeTimePart(className, selected, range_start, range_end, cellTml) {
				var part = JoomlaCalendar.createElement("select", cellTml);
				part.calendar = cal;
				part.style.width = '100%';
				part.navtype = 50;
				part._range = [];
				for (var i = range_start; i <= range_end; ++i) {
					var txt, selAttr = '';
					if (i == selected)
						selAttr = true;
					if (i < 10 && range_end >= 10) txt = '0' + i;
					else txt = '' + i;
					part.options.add(new Option(txt, txt, selAttr, selAttr));
				}
				return part;
			}

			var hrs = cal.date.getHours();
			var mins = cal.date.getMinutes();
			var t12 = !cal.params.time24;
			var pm = (hrs > 12);
			if (t12 && pm) hrs -= 12;
			var H = makeTimePart("time hour", hrs, t12 ? 1 : 0, t12 ? 12 : 23, cell1);
			var M = makeTimePart("time minutes", mins, 0, 59, cell2);
			var AP = null;

			cell = JoomlaCalendar.createElement("td", row);
			cell.className = "time ampm";
			cell.colSpan = cal.params.weekNumbers ? 1 : 2;
			if (t12) {
				var selAttr = '';
				if (pm) selAttr = true;
				var part = JoomlaCalendar.createElement("select", cell);
				part.style.width = '100%';
				part.options.add(new Option(Date.stringTIMEPM, "pm", pm ? true : '', pm ? true : ''));
				part.options.add(new Option(Date.stringTIMEAM, "am", pm ? '' : true, pm ? '' : true));

				AP = part;

				// Event listener for the am/pm select
				if (AP.attachEvent) { // IE
					AP.attachEvent("onchange", function () {
						JoomlaCalendar.updateTime(this.parentNode.parentNode.childNodes[1].childNodes[0].value, this.parentNode.parentNode.childNodes[2].childNodes[0].value, this.parentNode.parentNode.childNodes[3].childNodes[0].value);
					}, false);
				} else {
					AP.addEventListener("change", function () {
						JoomlaCalendar.updateTime(this.parentNode.parentNode.childNodes[1].childNodes[0].value, this.parentNode.parentNode.childNodes[2].childNodes[0].value, this.parentNode.parentNode.childNodes[3].childNodes[0].value);
					}, false);
				}
			} else
				cell.innerHTML = "&#160;";

			// Event listeners for the hour select and minutes select
			if (H.attachEvent) { // IE
				H.attachEvent("onchange", function () {
					JoomlaCalendar.updateTime(this.parentNode.parentNode.childNodes[1].childNodes[0].value, this.parentNode.parentNode.childNodes[2].childNodes[0].value, this.parentNode.parentNode.childNodes[3].childNodes[0].value);
				}, false);
				M.attachEvent("onchange", function () {
					JoomlaCalendar.updateTime(this.parentNode.parentNode.childNodes[1].childNodes[0].value, this.parentNode.parentNode.childNodes[2].childNodes[0].value, this.parentNode.parentNode.childNodes[3].childNodes[0].value);
				}, false);
			} else {
				H.addEventListener("change", function () {
					JoomlaCalendar.updateTime(this.parentNode.parentNode.childNodes[1].childNodes[0].value, this.parentNode.parentNode.childNodes[2].childNodes[0].value, this.parentNode.parentNode.childNodes[3].childNodes[0].value);
				}, false);
				M.addEventListener("change", function () {
					JoomlaCalendar.updateTime(this.parentNode.parentNode.childNodes[1].childNodes[0].value, this.parentNode.parentNode.childNodes[2].childNodes[0].value, this.parentNode.parentNode.childNodes[3].childNodes[0].value);
				}, false);
			}

			cal.onSetTime = function () {
			};

			cal.onUpdateTime = function (hours, mins) {
			};
		})();
	} else {
		this.onSetTime = this.onUpdateTime = function () {
		};
	}

	this._init(this.params.firstDayOfWeek, this.date);
	parent.parentNode.parentNode.appendChild(this.element);
	this.show();

	// placement fix for fields near page end
	// this.containerTop = this.element.getBoundingClientRect().top;
	// this.containerBottom = this.element.getBoundingClientRect().bottom;
	// console.log(inputTop);
	// if ((window.innerHeight + window.scrollY) < this.containerHeight + 20) {
	// 	topPosition = window.innerHeight - this.containerHeight  + window.scrollY;
	// 	bottomPosition = topPosition + this.containerHeight;
	// 	if (bottomPosition <= inputTop) this.element.style.top = inputTop - topPosition + "px";
	// 	else this.element.style.top = inputTop - topPosition + "px";
	//
	// 	console.log(window.innerHeight );
	// 	console.log(window.scrollY );
	// 	console.log(window.innerHeight + window.scrollY );
	// 	console.log(this.containerHeight );
	// 	console.log(this.element.style.top );
	// }
};

/** Recreate the calendar */
JoomlaCalendar.prototype.recreate = function () {
	if (this.element) {
		var parent = this.element.parentNode;
		parent.removeChild(this.element);
		this.create(parent);
		this.show();
	} else this.create();
};

/** keyboard navigation */
JoomlaCalendar._keyEvent = function (ev) {
	function prevDay() {
		var date = new Date(cal.date);
		date.setLocalDate(cal.params.dateType, date.getLocalDate(cal.params.dateType) - step);
		cal.setDate(date);
	}

	function nextDay() {
		var date = new Date(cal.date);
		date.setLocalDate(cal.params.dateType, date.getLocalDate(cal.params.dateType) + step);
		cal.setDate(date);
	}

	function prevWeek() {
		var date = new Date(cal.date);
		date.setDate(date.getDate() - step);
		cal.setDate(date);
	}

	function nextWeek() {
		var date = new Date(cal.date);
		date.setDate(date.getDate() + step);
		cal.setDate(date);
	}

	var cal = window.jCalendar;

	if (!cal)
		return false;

	ev = window.event ? event : e;
	var K = ev.keyCode;

	if (cal.params.direction == 'rtl') {
		if (K == 37) K = 39;
		else if (K == 39) K = 37;
	}

	if ([37, 38, 39, 40].indexOf(ev.keyCode)) {
		var step = (K == 37 || K == 39) ? 1 : 7;
	}

	switch (K) {
		case 32: // KEY space (now)
			JoomlaCalendar.cellClick(cal._nav_now);
			break;
		case 27: // KEY esc
			cal.callCloseHandler();
			break;
		case 37: // KEY left
			prevDay();
			break;
		case 38: // KEY up
			prevWeek();
			break;
		case 39: // KEY right
			nextDay();
			break;
		case 40: // KEY down
			nextWeek();
			break;
		case 13: // KEY enter
			JoomlaCalendar.cellClick(cal.currentDateEl, ev);
			break;

		default:
			return false;
	}
	return JoomlaCalendar.stopEvent(ev);
};

/** (RE)Initializes the calendar to the given date and firstDayOfWeek */
JoomlaCalendar.prototype._init = function (firstDayOfWeek, date) {
	var today = new Date(),
		TY = today.getLocalFullYear(this.params.dateType),
		TM = today.getLocalMonth(this.params.dateType),
		TD = today.getLocalDate(this.params.dateType);

	this.table.style.visibility = "hidden";
	var year = date.getLocalFullYear(this.params.dateType);

	// Check min,max year
	if (year < this.params.minYear) {
		year = this.params.minYear;
		date._calSetLocalFullYear(this.params.dateType, year);
	} else if (year > this.params.maxYear) {
		year = this.params.maxYear;
		date._calSetLocalFullYear(this.params.dateType, year);
	}

	this.params.firstDayOfWeek = firstDayOfWeek;
	this.date = new Date(date);
	var month = date.getLocalMonth(this.params.dateType);
	var mday = date.getLocalDate(this.params.dateType);
	var no_days = date.getLocalMonthDays(this.params.dateType);

	// Compute the first day that would actually be displayed in the calendar, even if it's from the previous month.
	date.setLocalDate(this.params.dateType, 1);
	var day1 = (date.getDay() - this.params.firstDayOfWeek) % 7;
	if (day1 < 0)
		day1 += 7;
	date.setLocalDate(this.params.dateType, - day1);
	date.setLocalDate(this.params.dateType, date.getLocalDate(this.params.dateType) + 1);

	var row = this.tbody.firstChild;
	var ar_days = this.ar_days = new Array();
	var weekend = Date.stringWEEKEND;
	for (var i = 0; i < 6; ++i, row = row.nextSibling) {
		var cell = row.firstChild;
		if (this.params.weekNumbers) {
			cell.className = "day wn";
			cell.innerHTML = (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && this.params.dateType != 'gregorian') ? this.convertNumbers(date.getLocalWeekNumber(this.params.dateType)) :date.getLocalWeekNumber(this.params.dateType);
			cell = cell.nextSibling;
		}
		row.className = "daysrow";
		var hasdays = false, iday, dpos = ar_days[i] = [];
		for (var j = 0; j < 7; ++j, cell = cell.nextSibling, date.setLocalDate(this.params.dateType, iday + 1)) {
			iday = date.getLocalDate(this.params.dateType);
			var wday = date.getDay();
			cell.className = "day";
			cell.style['textAlign'] = 'center';
			cell.pos = i << 4 | j;
			dpos[j] = cell;
			var current_month = (date.getLocalMonth(this.params.dateType) == month);
			if (!current_month) {
				if (this.params.showsOthers) {
					cell.className += " disabled othermonth ";
					cell.otherMonth = true;
				} else {
					cell.className += " emptycell";
					cell.innerHTML = "&#160;";
					cell.disabled = true;
					continue;
				}
			} else {
				cell.otherMonth = false;
				hasdays = true;
				cell.style.cursor = "pointer";
			}
			cell.disabled = false;
			cell.innerHTML = (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && this.params.dateType != 'gregorian') ? this.convertNumbers(iday) : iday; // translated day number for each cell
			if (this.getDateStatus) {
				var status = this.getDateStatus(date, year, month, iday);
				if (status === true) {
					cell.className += " table-active disabled";
					cell.disabled = true;
				} else {
					if (/disabled/i.test(status))
						cell.disabled = true;
					cell.className += " " + status;
				}
			}
			if (!cell.disabled) {
				cell.caldate = new Date(date);
				if (current_month && iday == mday) {
					cell.className += " selected table-success alert alert-success";
					this.currentDateEl = cell;
				}
				if (date.getLocalFullYear(this.params.dateType) == TY && date.getLocalMonth(this.params.dateType) == TM && iday == TD) {
					cell.className += " today";
					cell.className += " table-warning";
				}
				if (weekend.indexOf(wday.toString()) != -1)
					cell.className += cell.otherMonth ? " oweekend" : " weekend";
			}
		}
		if (!(hasdays || this.params.showsOthers)) {
			row.style.display = 'none';
			row.className = "emptyrow";
		} else {
			row.style.display = '';
		}
	}
	if (!this.params.compressedHeader) {
		this._nav_month.getElementsByTagName('span')[0].innerHTML = Date.stringMN[month];
		this.title.getElementsByTagName('span')[0].innerHTML =
			(Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && this.params.dateType != 'gregorian') ? this.convertNumbers(year) : year;
	} else {
		var tmpYear =(Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && this.params.dateType != 'gregorian') ? this.convertNumbers(year) : year;
		this._nav_month.getElementsByTagName('span')[0].innerHTML = " " + Date.stringMN[month] + ' - ' + tmpYear;
	}

	this.onSetTime();
	this.table.style.visibility = "visible";
};

// Global object that remembers the calendar
window.jCalendar = null;

/** BEGIN: DATE OBJECT PATCHES **/
/** Adds the number of days array to the Date object. */
Date.gregorian_MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

/** Constants used for time computations */
Date.SECOND = 1000 /* milliseconds */;
Date.MINUTE = 60 * Date.SECOND;
Date.HOUR   = 60 * Date.MINUTE;
Date.DAY    = 24 * Date.HOUR;
Date.WEEK   =  7 * Date.DAY;

Date.parseDate = function(str, fmt, dateType) {
	var today = new Date();
	var y = 0;
	var m = -1;
	var d = 0;
	var a = str.split(/\W+/);
	var b = fmt.match(/%./g);
	var i = 0, j = 0;
	var hr = 0;
	var min = 0;
	for (i = 0; i < a.length; ++i) {
		if (!a[i])
			continue;
		switch (b[i]) {
			case "%d":
			case "%e":
				d = parseInt(a[i], 10);
				break;

			case "%m":
				m = parseInt(a[i], 10) - 1;
				break;

			case "%Y":
			case "%y":
				y = parseInt(a[i], 10);
				(y < 100) && (y += (y > 29) ? 1900 : 2000);
				break;

			case "%b":
			case "%B":
				for (j = 0; j < 12; ++j) {
					if (dateType != 'gregorian') {
						if (Date.stringLocalMN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
					} else {
						if (Date.stringMN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
					}
				}
				break;

			case "%H":
			case "%I":
			case "%k":
			case "%l":
				hr = parseInt(a[i], 10);
				break;

			case "%P":
			case "%p":
				if (/pm/i.test(a[i]) && hr < 12)
					hr += 12;
				else if (/am/i.test(a[i]) && hr >= 12)
					hr -= 12;
				break;

			case "%M":
				min = parseInt(a[i], 10);
				break;
		}
	}
	if (isNaN(y)) y = today.getFullYear();
	if (isNaN(m)) m = today.getMonth();
	if (isNaN(d)) d = today.getDate();
	if (isNaN(hr)) hr = today.getHours();
	if (isNaN(min)) min = today.getMinutes();
	if (y != 0 && m != -1 && d != 0)
		return new Date(y, m, d, hr, min, 0);
	y = 0; m = -1; d = 0;
	for (i = 0; i < a.length; ++i) {
		if (a[i].search(/[a-zA-Z]+/) != -1) {
			var t = -1;
			for (j = 0; j < 12; ++j) {
				if (dateType != 'gregorian') {
					if (Date.stringLocalMN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
				} else {
					if (Date.stringMN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
				}
			}
			if (t != -1) {
				if (m != -1) {
					d = m+1;
				}
				m = t;
			}
		} else if (parseInt(a[i], 10) <= 12 && m == -1) {
			m = a[i]-1;
		} else if (parseInt(a[i], 10) > 31 && y == 0) {
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
		} else if (d == 0) {
			d = a[i];
		}
	}
	if (y == 0)
		y = today.getFullYear();
	if (m != -1 && d != 0)
		return new Date(y, m, d, hr, min, 0);
	return today;
};

/** Returns the number of days in the current month */
Date.prototype.getMonthDays = function(month) {
	var year = this.getFullYear();
	if (typeof month == "undefined") {
		month = this.getMonth();
	}
	if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
		return 29;
	} else {
		if (Date.dateType != 'gregorian') {
			Date.localCal_MD[month];
		} else {
			return Date.gregorian_MD[month];
		}
	}
};

/** Returns the number of day in the year. */
Date.prototype.getDayOfYear = function() {
	var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var then = new Date(this.getFullYear(), 0, 0, 0, 0, 0);
	var time = now - then;
	return Math.floor(time / Date.DAY);
};

/** Returns the number of the week in year, as defined in ISO 8601. */
Date.prototype.getWeekNumber = function() {
	var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var DoW = d.getDay();
	d.setDate(d.getDate() - (DoW + 6) % 7 + 3);                 // Nearest Thu
	var ms = d.valueOf();                                       // GMT
	d.setMonth(0);
	d.setDate(4);                                               // Thu in Week 1
	return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};

/** Checks date and time equality */
Date.prototype.equalsTo = function(date) {
	return ((this.getFullYear() == date.getFullYear()) &&
	(this.getMonth() == date.getMonth()) &&
	(this.getDate() == date.getDate()) &&
	(this.getHours() == date.getHours()) &&
	(this.getMinutes() == date.getMinutes()));
};

/** Set only the year, month, date parts (keep existing time) */
Date.prototype.setDateOnly = function(date) {
	var tmp = new Date(date);
	this.setDate(1);
	this.setFullYear(tmp.getFullYear());
	this.setMonth(tmp.getMonth());
	this.setDate(tmp.getDate());
};

Date.prototype._calSetFullYear = function (y) {
	var date = new Date(this);
	date.setFullYear(y);
	if (date.getMonth() != this.getMonth()) this.setDate(28);
	return this.setFullYear(y);
};

Date.prototype._calSetLocalCalFullYear = function (y) {
	var date = new Date(this);
	date.setLocalCalFullYear(y);
	if (date.getLocalCalMonth() != this.getLocalCalMonth()) this.setLocalCalDate(29);
	return this.setLocalCalFullYear(y);
};

Date.prototype._calSetLocalFullYear = function (dateType, y) {
	if (dateType != 'gregorian') {
		return this._calSetLocalCalFullYear(y);
	} else {
		return this._calSetFullYear(y);
	}
};

Date.prototype.setLocalFullYear = function (dateType, y, m, d) {
	if (dateType != 'gregorian') {
		if (m == undefined) this.getLocalCalMonth();
		if (d == undefined) this.getLocalCalDate();
		return this.setLocalCalFullYear(y, m, d);
	} else {
		if (m == undefined) this.getMonth();
		if (d == undefined) this.getDate();
		return this.setFullYear(y, m, d);
	}
};

Date.prototype.setLocalMonth = function (dateType, m, d) {
	if (dateType != 'gregorian') {
		if (d == undefined) this.getLocalCalDate();
		return this.setLocalCalMonth(m, d);
	} else {
		if (d == undefined) this.getDate();
		return this.setMonth(m, d);
	}
};

Date.prototype.setLocalDate = function (dateType, d) {
	if (dateType != 'gregorian') {
		return this.setLocalCalDate(d);
	} else {
		return this.setDate(d);
	}
};

Date.prototype.getLocalFullYear = function (dateType) {
	if (dateType != 'gregorian') {
		return this.getLocalCalFullYear();
	} else {
		return this.getFullYear();
	}
};

Date.prototype.getLocalMonth = function (dateType) {
	if (dateType != 'gregorian') {
		return this.getLocalCalMonth();
	} else {
		return this.getMonth();
	}
};

Date.prototype.getLocalWeekNumber = function(dateType) {
	if (dateType != 'gregorian') {
		return this.getLocalCalWeekNumber();
	} else {
		return this.getWeekNumber();
	}
};

Date.prototype.getLocalDate = function (dateType) {
	if (dateType != 'gregorian') {
		return this.getLocalCalDate();
	} else {
		return this.getDate();
	}
};

/** Returns the number of days in the current month */
Date.prototype.getMonthDays = function(month) {
	var year = this.getFullYear();
	if (typeof month == "undefined") {
		month = this.getMonth();
	}
	if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
		return 29;
	} else {
		return Date.gregorian_MD[month];
	}
};

Date.prototype.getLocalMonthDays = function(dateType, month) {
	if (dateType != 'gregorian') {
		return this.getLocalCalMonthDays(month);
	} else {
		return this.getMonthDays(month);
	}
};

/** Returns the number of day in the year. */
Date.prototype.getDayOfYear = function() {
	var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var then = new Date(this.getFullYear(), 0, 0, 0, 0, 0);
	var time = now - then;
	return Math.floor(time / Date.DAY);
};

Date.prototype.getLocalDayOfYear = function(dateType) {
	if (dateType  != 'gregorian') {
		return this.getLocalCalDayOfYear();
	} else {
		return this.getDayOfYear();
	}
};

/** Returns the number of the week in year, as defined in ISO 8601. */
Date.prototype.getWeekNumber = function() {
	var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var DoW = d.getDay();
	d.setDate(d.getDate() - (DoW + 6) % 7 + 3);                                     // Nearest Thu
	var ms = d.valueOf();                                                           // GMT
	d.setMonth(0);
	d.setDate(4);                                                                   // Thu in Week 1
	return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};

/** Set only the year, month, date parts (keep existing time) */
Date.prototype.setDateOnly = function(date) {
	var tmp = new Date(date);
	this.setDate(1);
	this._calSetFullYear(tmp.getFullYear());
	this.setMonth(tmp.getMonth());
	this.setDate(tmp.getDate());
};

/** Prints the date in a string according to the given format. */
Date.prototype.print = function (str, dateType) {
	if (typeof dateType == "undefined") dateType = 'gregorian';
	var m = this.getLocalMonth(dateType);
	var d = this.getLocalDate(dateType);
	var y = this.getLocalFullYear(dateType);
	var wn = this.getLocalWeekNumber(dateType);
	var w = this.getDay();
	var s = {};
	var hr = this.getHours();
	var pm = (hr >= 12);
	var ir = (pm) ? (hr - 12) : hr;
	var dy = this.getLocalDayOfYear(dateType);
	if (ir == 0)
		ir = 12;
	var min = this.getMinutes();
	var sec = this.getSeconds();
	s["%a"] = (dateType == 'gregorian') ? Date.stringSDN[w] : Date.stringLocalSDN[w]; // abbreviated weekday name
	s["%A"] = (dateType == 'gregorian') ? Date.stringDN[w] : Date.stringLocalDN[w];   // full weekday name
	s["%b"] = (dateType == 'gregorian') ? Date.stringSMN[m] : Date.stringLocalSMN[m]; // abbreviated month name
	s["%B"] = (dateType == 'gregorian') ? Date.stringMN[m] : Date.stringLocalMN[m];   // full month name
	// FIXME: %c : preferred date and time representation for the current locale
	s["%C"] = 1 + Math.floor(y / 100);                                                          // the century number
	s["%d"] = (d < 10) ? ("0" + d) : d;                                                         // the day of the month (range 01 to 31)
	s["%e"] = d;                                                                                // the day of the month (range 1 to 31)
	// FIXME: %D : american date style: %m/%d/%y
	// FIXME: %E, %F, %G, %g, %h (man strftime)
	s["%H"] = (hr < 10) ? ("0" + hr) : hr;                                                      // hour, range 00 to 23 (24h format)
	s["%I"] = (ir < 10) ? ("0" + ir) : ir;                                                      // hour, range 01 to 12 (12h format)
	s["%j"] = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy;                         // day of the year (range 001 to 366)
	s["%k"] = hr;                                                                               // hour, range 0 to 23 (24h format)
	s["%l"] = ir;                                                                               // hour, range 1 to 12 (12h format)
	s["%m"] = (m < 9) ? ("0" + (1+m)) : (1+m);                                                  // month, range 01 to 12
	s["%M"] = (min < 10) ? ("0" + min) : min;                                                   // minute, range 00 to 59
	s["%n"] = "\n";                                                                             // a newline character
	s["%p"] = pm ? "PM" : "AM";
	s["%P"] = pm ? "pm" : "am";
	// FIXME: %r : the time in am/pm notation %I:%M:%S %p
	// FIXME: %R : the time in 24-hour notation %H:%M
	s["%s"] = Math.floor(this.getTime() / 1000);
	s["%S"] = (sec < 10) ? ("0" + sec) : sec;                                                   // seconds, range 00 to 59
	s["%t"] = "\t";                                                                             // a tab character
	// FIXME: %T : the time in 24-hour notation (%H:%M:%S)
	s["%U"] = s["%W"] = s["%V"] = (wn < 10) ? ("0" + wn) : wn;
	s["%u"] = w + 1;                                                                            // the day of the week (range 1 to 7, 1 = MON)
	s["%w"] = w;                                                                                // the day of the week (range 0 to 6, 0 = SUN)
	// FIXME: %x : preferred date representation for the current locale without the time
	// FIXME: %X : preferred time representation for the current locale without the date
	s["%y"] = ('' + y).substr(2, 2);                                                            // year without the century (range 00 to 99)
	s["%Y"] = y;                                                                                // year with the century
	s["%%"] = "%";                                                                              // a literal '%' character

	var re = /%./g;

	var tmpDate = str.replace(re, function (par) { return s[par] || par; });
	if (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && dateType != 'gregorian')
		tmpDate = tmpDate.convertNumbers();

	return tmpDate;
};
