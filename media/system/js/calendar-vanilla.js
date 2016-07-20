/**
 * @copyright   Dimitris Grammatikogiannis <d.grammatikogmail.com>
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */
var JoomlaCalendar = function (selector) {
	'use strict';
	var createInstance, elements, i,  instances = [];

	JoomlaCalendar.prototype = JoomlaCalendar.init.prototype;

	createInstance = function (element) {
		if (element._joomlaCalendar) {
			element._joomlaCalendar.destroy();
		}
		element._joomlaCalendar = new JoomlaCalendar.init(element);
		return element._joomlaCalendar;
	};

	if (selector.nodeName) {
		return createInstance(selector);
	}

	elements = document.getElementsByClassName(selector);

	if (elements.length === 1) {
		return createInstance(elements[0]);
	}

	for (i = 0; i < elements.length; i++) {
		instances.push(createInstance(elements[i]));
	}
	return instances;
};

// /** The Calendar object constructor. */
JoomlaCalendar.init = function (elem) {
	'use strict';
	var self = this,
		element = elem.getElementsByTagName("button")[0],
		defaultParams = {
			inputField      : null,                // The input element parentNode.getElementsByTagName('INPUT')[0]
			firstDayOfWeek  : 0,                   // 0 for Sunday, 1 for Monday, etc.
			time24          : false,               // Use 24/12 hour format
			showsOthers     : true,                // Display previous/next month days as disables
			showsTime       : false,               // Shows hours and minutes drop downs
			weekNumbers     : false,               // Shows the week number as first column
			showsTodayBtn   : true,                // Display a today button
			compressedHeader: false,               // Use one line for year
			minYear         : 1970,                // The minimum year
			maxYear         : 2050,                // The maximum year
			dateFormat      : "%Y-%m-%d %H:%M:%S", // The date format
			dateType        : 'gregorian',         // The calendar type
			direction       : 'ltr'                // The direction of the document
		},
		instanceParams = {
			dateType        : 'gregorian',
			inputField      : element.parentNode.getElementsByTagName('INPUT')[0],
			firstDayOfWeek  : element.getAttribute("data-firstday") ? parseInt(element.getAttribute("data-firstday")) : 0,
			time24          : (parseInt(element.getAttribute("data-time_24")) == 24) ? true : false,
			showsOthers     : (parseInt(element.getAttribute("data-show_others")) == 0) ? false : true,
			showsTime       : (parseInt(element.getAttribute("data-shows_time")) == 1) ? true : false,
			weekNumbers     : (parseInt(element.getAttribute("data-week_numbers")) == 1) ? true : false,
			showsTodayBtn   : (parseInt(element.getAttribute("data-today_btn")) == 0) ? false : true,
			compressedHeader: (parseInt(element.getAttribute("data-only_months_nav")) == 1) ? true : false,
			minYear         : element.getAttribute("data-min_year") ? parseInt(element.getAttribute("data-min_year")) : 1970,
			maxYear         : element.getAttribute("data-max_year") ? parseInt(element.getAttribute("data-max_year")) : 2050,
			dateFormat      : element.getAttribute("data-dayformat") ? element.getAttribute("data-dayformat") : "%Y-%m-%d %H:%M:%S",
			direction       : (document.dir != undefined) ? document.dir : document.getElementsByTagName("html")[0].getAttribute("dir")
		};

		Date.stringDN = ( element.getAttribute("data-weekdays_full")
		&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-weekdays_full").split('_') :
			["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];                                      // Translated full day names
		Date.stringSDN = (element.getAttribute("data-weekdays_short")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-weekdays_short").split('_') :
			["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"];                                                                      // Translated short day names
		Date.stringMN  = (element.getAttribute("data-months_long")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-months_long").split('_') :
			["January","February","March","April","May","June","July","August","September","October","November","December"];        // Translated full month names
		Date.stringSMN = (element.getAttribute("data-months_short")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-months_short").split('_') :
			["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];                                              // Translated short month names
		Date.stringTODAY  = (element.getAttribute("data-today_trans")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-today_trans") : "Today";                       // Translated string for Today
		Date.stringWEEKEND = (element.getAttribute("data-weekend")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-weekend").split(',').map(Number) :
			[0,6];                                                                                                                  // integers comma separated 0,6
		Date.stringWK   = (element.getAttribute("data-wk")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-wk") : "wk";                                   // Translated string for wk
		Date.stringTIME  = (element.getAttribute("data-time")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-time") : "Time:";                              // Translated string for Time:
		Date.stringTIMEAM = (element.getAttribute("data-time_am")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-time_am") : "AM";                              // Translated string for AM
		Date.stringTIMEPM = (element.getAttribute("data-time_pm")
			&& instanceParams.dateType == 'gregorian' ) ? element.getAttribute("data-time_pm") : "PM";                              // Translated string for PM

	/** COMPATIBILITY WITH IE 8 **/
	var hasClass = function (element, className) {
		return (' ' + element.className + ' ').indexOf(' ' + className + ' ') > -1;
	};
	var addClass = function (element, className) {
		removeClass(element, className);
		element.className += " " + className;
	};
	var removeClass = function (element, className) {
		if (!(element && element.className)) {
			return;
		}
		var cls = element.className.split(" "), ar = new Array();
		for (var i = cls.length; i > 0;) {
			if (cls[--i] != className) ar[ar.length] = cls[i];
		}
		element.className = ar.join(" ");
	};

	var stopCalEvent = function (ev) {
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

	var addCalEvent = function (el, evname, func) {
		if (el.attachEvent) { // IE
			el.attachEvent("on" + evname, func);
		} else if (el.addEventListener) { // Gecko / W3C
			el.addEventListener(evname, func, true);
		} else {
			el["on" + evname] = func;
		}
	};

	var removeCalEvent = function (el, evname, func) {
		if (el.detachEvent) { // IE
			el.detachEvent("on" + evname, func);
		} else if (el.removeEventListener) { // Gecko / W3C
			el.removeEventListener(evname, func, true);
		} else {
			el["on" + evname] = null;
		}
	};

	var createElement = function (type, parent) {
		var el = null;
		el = document.createElement(type);
		if (typeof parent != "undefined") {
			parent.appendChild(el);
		}
		return el;
	};
	/** END OF COMPATIBILITY WITH IE 8 **/

	var findMonth = function (el) {
		if (typeof el.month != "undefined") {
			return el;
		} else if (typeof el.parentNode.month != "undefined") {
			return el.parentNode;
		}
		return null;
	};

	var findYear = function (el) {
		if (typeof el.year != "undefined") {
			return el;
		} else if (typeof el.parentNode.year != "undefined") {
			return el.parentNode;
		}
		return null;
	};

	var convertNumbers = function(str) {
		var str = str.toString();
		if (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]') str = str.convertNumbers();
		return str;
	};

	/** Time Control */
	var updateTime = function (hours, mins, ampm) {
		var date = self.date;
		if (ampm) {
			if (/pm/i.test(ampm) && hours < 12)
				hours = parseInt(hours) + 12;
			else if (/am/i.test(ampm) && hours == 12)
				hours = 0;
		}
		var d = self.date.getDate();
		var m = self.date.getMonth();
		var y = self.date.getFullYear();
		date.setHours(hours);
		date.setMinutes(parseInt(mins, 10));
		date.setFullYear(y);
		date.setMonth(m);
		date.setDate(d);
		self.dateClicked = false;
		callHandler();
	};

	/**
	 *  Calls _init function above for going to a certain date (but only if the
	 *  date is different than the currently selected one).
	 */
	var setDate = function (date) {
		if (!date.equalsTo(self.date)) {
			self.date = date;
			refresh();
		}
	};

	/**
	 *  Refreshes the JoomlaCalendar.  Useful if the "disabledHandler" function is
	 *  dynamic, meaning that the list of disabled date can change at runtime.
	 *  Just * call this function if you think that the list of disabled dates
	 *  should * change.
	 */
	var refresh = function () {
		_init(self.params.firstDayOfWeek, self.date);
	};

	/** Calls the first user handler (selectedHandler). */
	var callHandler = function () {
		// Method to set the value for the input field
		var update = self.dateClicked;

		if (self.params.dateType == 'gregorian') {
			self.params.inputField.value = self.date.print(self.params.dateFormat, self.params.dateType);
			self.params.inputField.setAttribute('data-alt-value', self.date.print('%Y-%m-%d %H:%M:%S', self.params.dateType));
		} else {
			self.jGregorianDate = Date.convertFromLocal(date);
			self.params.inputField.value = self.jGregorianDate.print(self.params.dateFormat, self.params.dateType);
			self.params.inputField.setAttribute('data-alt-value', self.jGregorianDate.print('%Y-%m-%d %H:%M:%S', self.params.dateType));
		}

		if (typeof self.params.inputField.onchange == "function")
			self.params.inputField.onchange();

		if (update && typeof self.params.onUpdate == "function")
			self.params.onUpdate(self);

		if (update && self.dateClicked)
			close();
	};

	/** Calls the second user handler (close). */
	var close = function () {
		hide();
	};

	/** Removes the calendar object from the DOM tree and destroys it. */
	var destroy = function () {
		var el = self.element.parentNode;
		if (el) el.removeChild(self.element);
	};

	/**
	 * This gets called when the user presses a mouse button anywhere in the
	 * document, if the calendar is shown. If the click was outside the open
	 * calendar this function closes it.
	 */
	var documentClick = function (ev) {
		if (!self) {
			return false;
		}
		var el = ev.target.parentNode;
		for (; el != null && el != self.element; el = el.parentNode);
		if (el == null) {
			// calls close which should hide the calendar.
			close();
			return stopCalEvent(ev);
		}
	};

	/** Shows the calendar. */
	var show = function () {
		var rows = self.table.getElementsByTagName("tr");
		for (var i = rows.length; i > 0;) {
			var row = rows[--i];
			var cells = row.getElementsByTagName("td");
			for (var j = cells.length; j > 0;) {
				var cell = cells[--j];
				removeClass(cell, 'alert-info');
				removeClass(cell, 'alert-success');
			}
		}
		self.element.style.display = "block";
		self.hidden = false;
		addCalEvent(document, "keydown", _keyEvent);
		addCalEvent(document, "keypress", _keyEvent);
		addCalEvent(document, "mousedown", documentClick);

		// Check if user changed the date and reset it
		if (self.params.inputField.value != self.date ) {
			self.date = new Date();
		}
		refresh();
	};

	/**
	 *  Hides the calendar.  Also removes any "hilite" from the class of any TD
	 *  element.
	 */
	var hide = function () {
		removeCalEvent(document, "keydown", _keyEvent);
		removeCalEvent(document, "keypress", _keyEvent);
		removeCalEvent(document, "mousedown", documentClick);
		self.element.style.display = "none";
		self.hidden = true;
	};

	/**
	 *  Tries to identify the date represented in a string.  If successful it also
	 *  calls this.setDate which moves the calendar to the given date.
	 */
	var parseDate = function(str, fmt, dateType) {
		if (!fmt) fmt = self.params.dateFormat;
		if (!dateType) dateType = self.params.dateType;
		setDate(Date.parseDate(str, fmt, dateType));
	};


	/** event handlers **/
	var dayMouseDown = function (ev) {
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
				if (hasClass(el, 'dropdown-menu')) {
					el = el.getElementsByTagName('table');
				}
			}
		} else {
			// Check that the td doesn't have a bootstrap button in it (and is not a day td) - if so ingore the event
			if (!(hasClass(target, 'btn')) && !hasClass(el, 'day') && !hasClass(el, 'title')) {
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

			removeClass(el, "alert-success");
			if (target == el || target.parentNode == el) {
				cellClick(el, ev);
			}
			var mon = findMonth(target);
			var date = null;
			if (mon) {
				date = new Date(self.date);
				if (mon.month != date.getMonth()) {
					date.setMonth(mon.month);
					setDate(date);
					self.dateClicked = false;
					callHandler();
				}
			} else {
				var year = findYear(target);
				if (year) {
					date = new Date(self.date);
					if (year.year != date.getFullYear()) {
						date.setFullYear(year.year);
						setDate(date);
						self.dateClicked = false;
						callHandler();
					}
				}
			}
		}
		return stopCalEvent(ev);
	};

	/** Click handler */
	var cellClick = function (el, ev) {
		var closing = false, newdate = false, date = null;

		if (typeof el.navtype == "undefined") {
			if (self.currentDateEl) {
				removeClass(self.currentDateEl, "selected");
				removeClass(self.currentDateEl, "alert-success");
				addClass(el, "selected alert-success");
				self.currentDateEl = el;

				closing = (self.currentDateEl == el);
				if (!closing) {
					self.currentDateEl = el;
				}
			}
			self.date.setDateOnly(el.caldate);
			date = self.date;
			var other_month = !(self.dateClicked = !el.otherMonth);
			if (self.currentDateEl)
				newdate = !el.disabled;
			if (other_month)
				_init(self.params.firstDayOfWeek, date);
		} else {
			if (el.navtype == 200) {
				removeClass(el, "hilite");
				close();
				return;
			}
			date = new Date(self.date);
			if (el.navtype == 0) {
				self.date.setDateOnly(new Date()); // TODAY
				callHandler();
				close();
				return;
			}
			// unless "today" was clicked, we assume no date was clicked so
			// the selected handler will know not to close the calendar
			self.dateClicked = false;
			var year = date.getLocalFullYear(self.params.dateType);
			var mon = date.getLocalMonth(self.params.dateType);
			switch (el.navtype) {
				case 400:
					break;
				case -2:
					if (!self.params.compressedHeader)
						if (year > self.params.minYear) {
							date._calSetLocalFullYear(self.params.dateType, year - 1);
						}
					break;
				case -1:
					var day = date.getDate();
					if (mon > 0) {
						var max = date.getLocalMonthDays(self.params.dateType, mon - 1);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setMonth(mon - 1);

					} else if (year-- > self.params.minYear) {
						date._calSetLocalFullYear(self.params.dateType, year);
						var max = date.getLocalMonthDays(self.params.dateType, 11);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setMonth(11);
					}
					break;
				case 1:
					var day = date.getDate();
					if (mon < 11) {
						var max = date.getLocalMonthDays(self.params.dateType, mon + 1);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setMonth(mon + 1);
					} else if (year < self.params.maxYear) {
						date._calSetLocalFullYear(self.params.dateType, year + 1);
						var max = date.getLocalMonthDays(self.params.dateType, 0);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setMonth(0);
					}
					break;
				case 2:
					if (!self.params.compressedHeader)
						if (year < self.params.maxYear) {
							date._calSetLocalFullYear(self.params.dateType, year + 1);
						}
					break;
				case 0:
					// TODAY will bring us here
					if ((typeof self.getDateStatus == "function") &&
						self.getDateStatus(date, date.getLocalFullYear(self.params.dateType),
							date.getLocalMonth(self.params.dateType),
							date.getLocalDate(self.params.dateType))) {
						return false;
					}
					break;
			}
			if (!date.equalsTo(self.date)) {
				setDate(date);
				newdate = true;
			} else if (el.navtype == 0)
				newdate = closing = true;
		}
		if (newdate) {
			ev && callHandler();
		}
		if (closing) {
			removeClass(el, "hilite");
			ev && close();
		}
	};

	/** This function creates the calendar inside the parent of the given input. */
	var create = function (parent) {
		// this.date = this.params.dateStr ? new Date(this.params.dateStr) : new Date();

		// Get the input date (from the data-alt-value
		if (self.params.inputField.getAttribute('data-alt-value') != '0000-00-00 00:00:00') {
			if (self.params.dateType == 'gregorian') {
				self.date = Date.parseDate(self.params.inputField.getAttribute('data-alt-value'), '%Y-%m-%d %H:%M:%S', self.params.dateType);
			} else {

				self.date = Date.parseDate(self.params.inputField.getAttribute('data-alt-value'), '%Y-%m-%d %H:%M:%S', self.params.dateType);
				self.date = DateToLocal(tmpLocalDate);
			}
		} else {
			self.date = new Date();
			if (self.params.dateType != 'gregorian') self.date = DateToLocal(self.date);
		}

		var table = createElement("table");
		self.table = table;
		table.className = 'table';
		table.cellSpacing = 0;
		table.cellPadding = 0;
		table.calendar = self;
		table.style.marginBottom = 0;
		addCalEvent(table, "mousedown", self.tableMouseDown);

		var div = createElement("div");
		self.element = div;
		if (self.params.direction) {
			self.element.style.direction = self.params.direction;
		}

		div.className = 'dropdown-menu j-calendar';
		div.style.position = "absolute";
		div.style.boxShadow = "0px 0px 70px 0px rgba(0,0,0,0.67)";
		div.style.minWidth = parent.width;
		div.style.padding = '0';
		div.style.left = "auto";
		div.style.top = "auto";

		self.wrapper = createElement('div');
		self.wrapper.className = 'calendar-container';
		div.appendChild(self.wrapper);
		self.wrapper.appendChild(table);

		var thead = createElement("thead", table);
		thead.className = 'calendar-header';
		var cell = null;
		var row = null;

		var cal = self;
		var hh = function (text, cs, navtype, node, styles, classes) {
			node = node ? node : "td";
			classes = classes ? 'class="' + classes + '"' : '';
			styles = styles ? styles : {};
			cell = createElement(node, row);
			cell.colSpan = cs;
			// cell.className = "button";
			for (var key in styles) {
				cell.style[key] = styles[key];
			}
			if (navtype != 0 && Math.abs(navtype) <= 2) {
				cell.className += " nav";
			}

			//this._add_evs(cell);
			addCalEvent(cell, "mousedown", dayMouseDown);
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
		if (self.params.compressedHeader == false) {
			row = createElement("tr", thead);
			row.className = "calendar-head-row";

			// Previous year button
			self._nav_py = hh("<", 1, -2, '', '', 'btn btn-small btn-default pull-left prev_month_btn');
			// Year
			self.title = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', self.params.weekNumbers ? 6 : 5, 300);
			self.title.className = "title";
			// Next year button
			self._nav_ny = hh(">", 1, 2, '', '', 'btn btn-small btn-default pull-right');
		}

		// Head - month
		row = createElement("tr", thead);
		row.className = "calendar-head-row";

		// Previous month button
		self._nav_pm = hh("<", 1, -1, '', '', 'btn btn-small btn-default pull-left next_month_btn');
		// Month
		self._nav_month = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', self.params.weekNumbers ? 6 : 5, 888, 'td', {'textAlign': 'center'});
		self._nav_month.className = "title";
		// Next month button
		self._nav_nm = hh(">", 1, 1, '', '', 'btn btn-small btn-default pull-right');

		// Head - today
		if (self.params.showsTodayBtn) {
			row = createElement("tr", thead);
			row.className = "headrow";
			self._nav_now = hh('<a class="btn btn-small btn-success" data-action="today" style="display:block;padding:2px 6px;">' + Date.stringTODAY + '</a>', self.params.weekNumbers ? 8 : 7, 0, 'td', {'textAlign': 'center'});
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

			addCalEvent(todaya, 'click', function (e) {
				var el = todaya.parentNode.parentNode;
				if (el.tagName === 'TD') {
					cellClick(self._nav_now);
				}
			});
		}

		// day names
		row = createElement("tr", thead);
		row.className = "daynames";
		if (self.params.weekNumbers) {
			cell = createElement("td", row);
			cell.className = "name wn";
			cell.innerHTML = Date.stringWK;
		}
		for (var i = 7; i > 0; --i) {
			cell = createElement("td", row);
			if (!i) {
				cell.calendar = self;
			}
		}
		self.firstdayname = (self.params.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;

		// weekdays
		var fdow = self.params.firstDayOfWeek;
		var cell = self.firstdayname;
		var weekend = Date.stringWEEKEND;
		for (var i = 0; i < 7; ++i) {
			cell.className = "day name";
			cell.style.textAlign = 'center';
			var realday = (i + fdow) % 7;
			if (i) {
				cell.calendar = self;
				cell.fdow = realday;
			}
			if (weekend.indexOf(realday.toString()) != -1) {
				addClass(cell, "weekend");
			}
			cell.innerHTML = Date.stringSDN[(i + fdow) % 7];
			cell = cell.nextSibling;
		}

		var tbody = createElement("tbody", table);
		self.tbody = tbody;

		for (i = 6; i > 0; --i) {
			row = createElement("tr", tbody);
			if (self.params.weekNumbers) {
				cell = createElement("td", row);
			}
			for (var j = 7; j > 0; --j) {
				cell = createElement("td", row);
				cell.calendar = self;
				addCalEvent(cell, "mousedown", dayMouseDown);
			}
		}

		if (self.params.showsTime) {
			row = createElement("tr", tbody);
			row.className = "time";

			cell = createElement("td", row);
			cell.className = "time time-title";
			cell.colSpan = 1;
			cell.style.verticalAlign = 'middle';
			cell.innerHTML = Date.stringTIME || "&#160;";

			var cell1 = createElement("td", row);
			cell1.className = "time hours-select";
			cell1.colSpan = 2;

			var cell2 = createElement("td", row);
			cell2.className = "time minutes-select";
			cell2.colSpan = 2;

			(function () {
				function makeTimePart(className, selected, range_start, range_end, cellTml) {
					var part = createElement("select", cellTml);
					part.calendar = self;
					part.className = "no-chozen-here " + className;
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

				var hrs = self.date.getHours();
				var mins = self.date.getMinutes();
				var t12 = !self.params.time24;
				var pm = (hrs > 12);
				if (t12 && pm) hrs -= 12;
				var H = makeTimePart("time hour", hrs, t12 ? 1 : 0, t12 ? 12 : 23, cell1);
				var M = makeTimePart("time minutes", mins, 0, 59, cell2);
				var AP = null;

				cell = createElement("td", row);
				cell.className = "time ampm";
				cell.colSpan = self.params.weekNumbers ? 1 : 2;
				if (t12) {
					var selAttr = '';
					if (pm) selAttr = true;
					var part = createElement("select", cell);
					part.style.width = '100%';
					part.options.add(new Option(Date.stringTIMEPM, "pm", pm ? selAttr : '', pm ? selAttr : ''));
					part.options.add(new Option(Date.stringTIMEAM, "am", pm ? '' : selAttr, pm ? '' : selAttr));
					AP = part;

					// Event listener for the am/pm select
					if (AP.attachEvent) { // IE
						AP.attachEvent("onchange", function () {
							updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value, event.target.parentNode.parentNode.childNodes[2].childNodes[0].value, event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
						}, false);
					} else { // W3C
						AP.addEventListener("change", function () {
							updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value, event.target.parentNode.parentNode.childNodes[2].childNodes[0].value, event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
						}, false);
					}
				} else
					cell.innerHTML = "&#160;";

				addCalEvent(H, "change", updateTime);
				// Event listeners for the hour select and minutes select
				if (H.attachEvent) { // IE
					H.attachEvent("onchange", function () {
						updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value, event.target.parentNode.parentNode.childNodes[2].childNodes[0].value, event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
					}, false);
					M.attachEvent("onchange", function (event) {
						updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value, event.target.parentNode.parentNode.childNodes[2].childNodes[0].value, event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
					}, false);
				} else { // W3C
					H.addEventListener("change", function (event) {
						updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value, event.target.parentNode.parentNode.childNodes[2].childNodes[0].value, event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
					}, false);
					M.addEventListener("change", function () {
						updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value, event.target.parentNode.parentNode.childNodes[2].childNodes[0].value, event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
					}, false);
				}
				self.onSetTime = function () {
				};

				self.onUpdateTime = function (hours, mins) {
				};
			})();
		} else {
			self.onSetTime = self.onUpdateTime = function () {
			};
		}

		_init(self.params.firstDayOfWeek, self.date);
		parent.parentNode.parentNode.appendChild(self.element);
	};

	/** keyboard navigation */
	var _keyEvent = function (ev) {
		if (!self)
			return false;

		function prevDay() {
			var date = new Date(self.date);
			date.setLocalDate(self.params.dateType, date.getLocalDate(self.params.dateType) - step);
			setDate(date);
		}

		function nextDay() {
			var date = new Date(self.date);
			date.setLocalDate(self.params.dateType, date.getLocalDate(self.params.dateType) + step);
			setDate(date);
		}

		function prevWeek() {
			var date = new Date(self.date);
			date.setDate(date.getDate() - step);
			setDate(date);
		}

		function nextWeek() {
			var date = new Date(self.date);
			date.setDate(date.getDate() + step);
			setDate(date);
		}

		//ev = window.event ? event : e;
		var K = ev.keyCode;

		if (self.params.direction == 'rtl') {
			if (K === 37) K = 39;
			if (K === 39) K = 37;
		}

		var step = (K === 37 || K === 39) ? 1 : 7;

		switch (K) {
			case 32: // KEY space (now)
				cellClick(self._nav_now);
				break;
			case 27: // KEY esc
				close();
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
				cellClick(self.currentDateEl, ev);
				break;

			default:
				return false;
		}
		return stopCalEvent(ev);
	};

	/** (RE)Initializes the calendar to the given date and firstDayOfWeek */
	var _init = function (firstDayOfWeek, date) {
		var today = new Date(),
			TY = today.getLocalFullYear(self.params.dateType),
			TM = today.getLocalMonth(self.params.dateType),
			TD = today.getLocalDate(self.params.dateType);

		self.table.style.visibility = "hidden";
		var year = date.getLocalFullYear(self.params.dateType);

		// Check min,max year
		if (year < self.params.minYear) {
			year = self.params.minYear;
			date._calSetLocalFullYear(self.params.dateType, year);
		} else if (year > self.params.maxYear) {
			year = self.params.maxYear;
			date._calSetLocalFullYear(self.params.dateType, year);
		}

		self.params.firstDayOfWeek = firstDayOfWeek;
		self.date = new Date(date);
		var month = date.getLocalMonth(self.params.dateType);
		var mday = date.getLocalDate(self.params.dateType);
		var no_days = date.getLocalMonthDays(self.params.dateType);

		// Compute the first day that would actually be displayed in the calendar, even if it's from the previous month.
		date.setLocalDate(self.params.dateType, 1);
		var day1 = (date.getDay() - self.params.firstDayOfWeek) % 7;
		if (day1 < 0)
			day1 += 7;
		date.setLocalDate(self.params.dateType, - day1);
		date.setLocalDate(self.params.dateType, date.getLocalDate(self.params.dateType) + 1);

		var row = self.tbody.firstChild;
		var ar_days = self.ar_days = new Array();
		var weekend = Date.stringWEEKEND;
		for (var i = 0; i < 6; ++i, row = row.nextSibling) {
			var cell = row.firstChild;
			if (self.params.weekNumbers) {
				cell.className = "day wn";
				cell.innerHTML = (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && self.params.dateType != 'gregorian') ? String.convertNumbers(date.getLocalWeekNumber(self.params.dateType)) : date.getLocalWeekNumber(self.params.dateType);
				cell = cell.nextSibling;
			}
			row.className = "daysrow";
			var hasdays = false, iday, dpos = ar_days[i] = [];
			for (var j = 0; j < 7; ++j, cell = cell.nextSibling, date.setLocalDate(self.params.dateType, iday + 1)) {
				iday = date.getLocalDate(self.params.dateType);
				var wday = date.getDay();
				cell.className = "day";
				cell.style['textAlign'] = 'center';
				cell.pos = i << 4 | j;
				dpos[j] = cell;
				var current_month = (date.getLocalMonth(self.params.dateType) == month);
				if (!current_month) {
					if (self.params.showsOthers) {
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
				cell.innerHTML = (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && self.params.dateType != 'gregorian') ? String.convertNumbers(iday) : iday; // translated day number for each cell
				if (self.getDateStatus) {
					var status = self.getDateStatus(date, year, month, iday);
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
						self.currentDateEl = cell;
					}
					if (date.getLocalFullYear(self.params.dateType) == TY && date.getLocalMonth(self.params.dateType) == TM && iday == TD) {
						cell.className += " today";
						cell.className += " table-warning";
					}
					if (weekend.indexOf(wday.toString()) != -1)
						cell.className += cell.otherMonth ? " oweekend" : " weekend";
				}
			}
			if (!(hasdays || self.params.showsOthers)) {
				row.style.display = 'none';
				row.className = "emptyrow";
			} else {
				row.style.display = '';
			}
		}
		if (!self.params.compressedHeader) {
			self._nav_month.getElementsByTagName('span')[0].innerHTML = Date.stringMN[month];
			self.title.getElementsByTagName('span')[0].innerHTML =
				(Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && self.params.dateType != 'gregorian') ? String.convertNumbers(year) : year;
		} else {
			var tmpYear =(Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]' && self.params.dateType != 'gregorian') ? String.convertNumbers(year) : year;
			self._nav_month.getElementsByTagName('span')[0].innerHTML = " " + Date.stringMN[month] + ' - ' + tmpYear;
		}

		self.onSetTime();
		self.table.style.visibility = "visible";
	};

	var bind = function (element) {
		addCalEvent(element, 'click', show, false);
	};

	var init = function() {
		self.destroy = destroy;
		self.element = elem;
		self.params = {};

		// Initialize only if the button and input field are set
		if (!(elem.parentNode.getElementsByTagName('INPUT')[0] || elem.getElementsByTagName("button")[0])) {
			console.log("Calendar.setup:\n  Nothing to setup (no fields found). Please check your code");
			return false;
		}

		for (var param in defaultParams) {
			self.params[param] = instanceParams[param] || defaultParams[param];
		}

		create(elem.parentNode.getElementsByTagName('INPUT')[0]);
		bind(element);
	};

	init();

	return self;
};

String.prototype.convertNumbers = function() {
	'use strict';
	var str = this.toString();
	if (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]') {
		for (var i = 0; i < Date.localLangNumbers.length; i++) {
			str = str.replace(new RegExp(i, 'g'), Date.localLangNumbers[i]);
		}
	}
	return str;
};

// Instantiate all the calendar fields when the document is ready
document.onreadystatechange = function () {
	if (document.readyState == "interactive") {
		JoomlaCalendar("field-calendar");

		// Destroy chosen if added in time selectors
		if (typeof window.jQuery != "undefined") {
			jQuery(document).ready(function() {
				if (jQuery().chosen) {
					jQuery.each(jQuery('.no-chozen-here'), function (index, value) {
						jQuery(value).chosen('destroy');
					});
				}
			})
		}
	}
};

// var getValuesFromAlt = function() {
// 	console.log('called');
// 	var calendars = document.getElementsByClassName("field-calendar");
//
// 	for (var i = 0; i < calendars.length; i++) {
// 		var input = calendars[i].getElementsByTagName('INPUT')[0],
// 			value = input.value,
// 			alt = input.getAttribute("data-alt-value") ? input.getAttribute("data-alt-value") : "0000-00-00 00:00:00";
// 		value = (alt === value) ? value : alt;
// 	}
// };
//
// var ele = document.getElementsByTagName('FORM')
//
// if (ele.length > 1) ele = ele[0];
// if (ele.addEventListener) ele.addEventListener("submitbutton", getValuesFromAlt, false);
// else if (ele.attachEvent) ele.attachEvent('onsubmit', getValuesFromAlt);
