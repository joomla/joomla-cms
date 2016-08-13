/**
 * @copyright   Dimitris Grammatikogiannis <d.grammatiko±gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
!(function(){
	'use strict';

	/** Method to convert numbers to local symbols. */
	Date.convertNumbers = function(str) {
		var str = str.toString();

		if (Object.prototype.toString.call(Date.localLangNumbers) === '[object Array]') {
			for (var i = 0; i < Date.localLangNumbers.length; i++) {
				str = str.replace(new RegExp(i, 'g'), Date.localLangNumbers[i]);
			}
		}
		return str;
	};

	/** Traslates to english numbers a string. */
	Date.toEnglish = function(str) {
		str = this.toString();
		var nums = [0,1,2,3,4,5,6,7,8,9];
		for (var i = 0; i < 10; i++) {
			str = str.replace(new RegExp(nums[i], 'g'), i);
		}
		return str;
	};

	/** Global method to change input values with the data-alt-value values. **/
	window.getJoomlaCalendarValuesFromAlt = function() {

		var calendars = document.getElementsByClassName("field-calendar");

		for (var i = 0; i < calendars.length; i++) {
			var self = JoomlaCalendar.getCalObject(calendars[i].childNodes[0])._joomlaCalendar,
				input = calendars[i].getElementsByTagName('INPUT')[0],
				alt = input.getAttribute("data-alt-value") ? input.getAttribute("data-alt-value") : "0000-00-00 00:00:00",
				altDate = new Date(Date.parse(alt)),
				selfDateInp = Date.parseFieldDate(input.value, self.params.dateFormat, self.params.dateType),
				selfDate = new Date(selfDateInp);
			input.value = alt;
			// Logic for check if user entered value with keyb or make the field read only
		}
	};

	var JoomlaCalendar = function (element) {
		element._joomlaCalendar = {
			writable: true
		};
		element._joomlaCalendar.params = {};
		var btn = element.getElementsByTagName('button')[0],
			defaultParams = {
				inputField      : null,                // The input element parentNode.getElementsByTagName('INPUT')[0]
				firstDayOfWeek  : 0,                   // 0 for Sunday, 1 for Monday, etc.
				time24          : false,               // Use 24/12 hour format
				showsOthers     : true,                // Display previous/next month days as disables
				showsTime       : false,               // Shows hours and minutes drop downs
				weekNumbers     : false,               // Shows the week number as first column
				showsTodayBtn   : true,                // Display a today button
				compressedHeader: false,               // Use one line for year
				monthBefore     : false,               // Displays the month before the year
				minYear         : 1970,                // The minimum year
				maxYear         : 2050,                // The maximum year
				dateFormat      : '%Y-%m-%d %H:%M:%S', // The date format
				dateType        : 'gregorian',         // The calendar type
				direction       : 'ltr',               // The direction of the document
				debug           : false,
				clicked         : false,
				element         : { style : { display : "none" } },
				classes         : {
					containerClass : 'dropdown-menu j-calendar',
					wrapperClass   : 'calendar-container',
					day            : 'day'
					// Add more classes for front end devs

				},
				writable: true
			},
			instanceParams = {
				dateType        : btn.getAttribute("data-cal-type") ? btn.getAttribute("data-cal-type") : 'gregorian',
				inputField      : btn.parentNode.getElementsByTagName('INPUT')[0],
				firstDayOfWeek  : btn.getAttribute("data-firstday") ? parseInt(btn.getAttribute("data-firstday")) : 0,
				time24          : (parseInt(btn.getAttribute("data-time-24")) == 24) ? true : false,
				showsOthers     : (parseInt(btn.getAttribute("data-show-others")) == 0) ? false : true,
				showsTime       : (parseInt(btn.getAttribute("data-show-time")) == 1) ? true : false,
				weekNumbers     : (parseInt(btn.getAttribute("data-week-numbers")) == 1) ? true : false,
				showsTodayBtn   : (parseInt(btn.getAttribute("data-today-btn")) == 0) ? false : true,
				compressedHeader: (parseInt(btn.getAttribute("data-only-months-nav")) == 1) ? true : false,
				minYear         : btn.getAttribute("data-min-year") ? parseInt(btn.getAttribute("data-min-year")) : 1970,
				maxYear         : btn.getAttribute("data-max-year") ? parseInt(btn.getAttribute("data-max-year")) : 2050,
				dateFormat      : btn.getAttribute("data-dayformat") ? btn.getAttribute("data-dayformat") : "%Y-%m-%d %H:%M:%S",
				direction       : (document.dir != undefined) ? document.dir : document.getElementsByTagName("html")[0].getAttribute("dir")
			};

		// Initialize only if the element exists
		if (!element) {
			console.log("Calendar setup failed:\n  No valid fields found, Please check your code");
			return false;
		}

		for (var param in defaultParams) {
			element._joomlaCalendar.params[param] = instanceParams[param] || defaultParams[param];
		}
		JoomlaCalendar.checkInputs(element._joomlaCalendar);
		JoomlaCalendar.create(element);
		JoomlaCalendar.bind(element);

		return element._joomlaCalendar;
	};

	JoomlaCalendar.checkInputs = function (obj) {
		var self = obj;
		// Get the date from the input
		var inputAltValueDate = Date.parseFieldDate(self.params.inputField.getAttribute('data-alt-value'), '%Y-%m-%d %H:%M:%S', 'gregorian');
		if (self.params.inputField.value.length) {
			if (self.params.inputField.value) {
				if (self.params.dateType != 'gregorian') {
					var date = new Date(inputAltValueDate);
					self.params.inputField.value = date.print(self.params.dateFormat, self.params.dateType, true);
				}
				self.date = new Date(inputAltValueDate);
			}
		} else {
			self.date = new Date();
		}
	};
	/** Time Control */
	JoomlaCalendar.updateTime = function (hours, mins, secs, self) {
		var date = self.date;
		if (!self.params.time24) {
			if (/pm/i.test(ampm) && hours < 12)
				hours = parseInt(hours) + 12;
			else if (/am/i.test(ampm) && hours == 12)
				hours = 0;
		}
		var d = self.date.getLocalDate(self.params.dateType);
		var m = self.date.getLocalMonth(self.params.dateType);
		var y = self.date.getLocalFullYear(self.params.dateType);
		date.setHours(hours);
		date.setMinutes(parseInt(mins, 10));
		date.setSeconds(date.getSeconds());
		date.setLocalFullYear(self.params.dateType, y);
		date.setLocalMonth(self.params.dateType, m);
		date.setLocalDate(self.params.dateType, d);
		self.dateClicked = false;
		JoomlaCalendar.callHandler(self);
	};

	/** Method to set the date to the given date object */
	JoomlaCalendar.setDate = function (date) {
		var self = JoomlaCalendar.getActiveCalObject();
		if (!date.equalsTo(self.date)) {
			self.date = date;
			JoomlaCalendar.processCalendar(self.params.firstDayOfWeek, date, self);
		}
	};

	/** Method to set the current date by a number, step */
	JoomlaCalendar.moveCursorBy = function (step) {
		var self = JoomlaCalendar.getActiveCalObject();
		var date = new Date(self.date); // self.date.getLocalDate(self.params.dateType)
		date.setDate(date.getDate() - step);
		JoomlaCalendar.setDate(date);
	};

	/** Method to set the value for the input field */
	JoomlaCalendar.callHandler = function (self) {
		/** Output the date **/
		if (self.params.dateType == 'gregorian') {
			self.params.inputField.setAttribute('data-alt-value', self.date.print('%Y-%m-%d %H:%M:%S', self.params.dateType, false));
			if (self.params.inputField.getAttribute('data-alt-value') && self.params.inputField.getAttribute('data-alt-value') != '0000-00-00 00:00:00') {
				self.params.inputField.value = self.date.print(self.params.dateFormat, self.params.dateType, true);
			}
		} else {
			self.params.inputField.setAttribute('data-alt-value', self.date.print('%Y-%m-%d %H:%M:%S', 'gregorian', false));
			self.params.inputField.setAttribute('data-local-value', self.date.print(self.params.dateFormat, self.params.dateType, false));
			self.params.inputField.value = self.date.print(self.params.dateFormat, self.params.dateType, true);
		}

		if (typeof self.params.inputField.onchange == "function")
			self.params.inputField.onchange();

		if (self.dateClicked && typeof self.params.onUpdate == "function") {
			self.params.onUpdate(self);
		}

		if (self.dateClicked) {
			JoomlaCalendar.close(self);
		} else {
			JoomlaCalendar.processCalendar(self.params.firstDayOfWeek, self.date, self);
		}
	};

	/** Method to close/hide the calendar */
	JoomlaCalendar.close = function (element) {
		document.activeElement.blur();
		JoomlaCalendar.hide(element);
	};

	/** Method to destroy the calendar */
	JoomlaCalendar.destroy = function () {
		var el = self.element.parentNode;
		if (el) { el.removeChild(self.element); }
	};

	/** Method to catch clicks outside of the calendar (used as close call) */
	JoomlaCalendar.documentClick = function (ev) {
		var el = ev.target.parentNode;
		var self = JoomlaCalendar.getCalObject(el)._joomlaCalendar;
		if (!self) {
			self = JoomlaCalendar.getActiveCalObject();
			JoomlaCalendar.hide(self);
			return JoomlaCalendar.stopCalEvent(ev);
		}

		var el = ev.target.parentNode;
		for (; el != null && el != self.element; el = el.parentNode);
		if (el == null) {
			document.activeElement.blur();
			JoomlaCalendar.hide(self);
			return JoomlaCalendar.stopCalEvent(ev);
		}
	};

	/** Method to show the calendar. */
	JoomlaCalendar.show = function (ev) {
		var el = ev.target,
			self = JoomlaCalendar.getCalObject(el)._joomlaCalendar;
		JoomlaCalendar.checkInputs(self);
		var rows = self.table.getElementsByTagName("tr");
		for (var i = rows.length; i > 0;) {
			var row = rows[--i];
			var cells = row.getElementsByTagName("td");
			for (var j = cells.length; j > 0;) {
				var cell = cells[--j];
				JoomlaCalendar.removeClass(cell, 'alert-info');
				JoomlaCalendar.removeClass(cell, 'alert-success');
			}
		}

		self.element.style.display = "block";
		self.hidden = false;
		JoomlaCalendar.addCalEvent(document, "keydown", JoomlaCalendar.calKeyEvent);
		JoomlaCalendar.addCalEvent(document, "keypress", JoomlaCalendar.calKeyEvent);
		JoomlaCalendar.addCalEvent(document, "mousedown", JoomlaCalendar.documentClick);
		JoomlaCalendar.processCalendar(self.params.firstDayOfWeek, self.date, self);
	};

	/** Method to hide the calendar. */
	JoomlaCalendar.hide = function (self) {
		if (!self) self = JoomlaCalendar.getActiveCalObject()._joomlaCalendar;
		JoomlaCalendar.removeCalEvent(document, "keydown", JoomlaCalendar.calKeyEvent);
		JoomlaCalendar.removeCalEvent(document, "keypress", JoomlaCalendar.calKeyEvent);
		JoomlaCalendar.removeCalEvent(document, "mousedown", JoomlaCalendar.documentClick);
		self.element.style.display = "none";
		self.hidden = true;
	};

	/** Method to handle mouse click events (menus, buttons) **/
	JoomlaCalendar.dayMouseDown = function (ev) {
		var el = ev.currentTarget, target = ev.target || ev.srcElement;
		var element = JoomlaCalendar.getCalObject(el);
		var self = element._joomlaCalendar;
		if (el.nodeName !== 'TD') {                         // A bootstrap inner button was pressed?
			var testel = el.getParent('TD');
			if (testel.nodeName === 'TD') {                 // Yes so use that element's td
				el = testel;
			} else {                                        // No - try to find the table this way
				el = el.getParent('TD');
				if (JoomlaCalendar.hasClass(el, 'dropdown-menu')) { el = el.getElementsByTagName('table'); }
			}
		} else {                                            // Check that doesn't have a button and is not a day td
			if (!(JoomlaCalendar.hasClass(target, 'btn')) && !JoomlaCalendar.hasClass(el, 'day') && !JoomlaCalendar.hasClass(el, 'title')) { return; }
		}
		if (!el || el.disabled)
			return false;

		if (typeof el.navtype === "undefined" || el.navtype != 300) {
			if (el.navtype == 50) { el._current = el.innerHTML; }

			JoomlaCalendar.removeClass(el, "alert-success");
			if (target == el || target.parentNode == el) { JoomlaCalendar.cellClick(el, ev); }

			var mon = null;
			if (typeof el.month != "undefined") mon = el;
			if (typeof el.parentNode.month != "undefined") mon = el.parentNode;
			var date = null;
			if (mon) {
				date = new Date(self.date);
				if (mon.month != date.getLocalMonth(self.params.dateType)) {
					date.setLocalMonth(self.params.dateType, mon.month);
					JoomlaCalendar.setDate(date);
					self.dateClicked = false;
					JoomlaCalendar.callHandler(self);
				}
			} else {
				var year = null;
				if (typeof el.year != "undefined") year = target;
				if (typeof el.parentNode.year != "undefined") year = target.parentNode;
				if (year) {
					date = new Date(self.date);
					if (year.year != date.getLocalFullYear(self.params.dateType)) {
						date.setFullYear(self.params.dateType, year.year);
						JoomlaCalendar.setDate(date);
						self.dateClicked = false;
						JoomlaCalendar.callHandler(self);
					}
				}
			}
		}
		return JoomlaCalendar.stopCalEvent(ev);
	};

	/** Method to handle mouse click events (dates) **/
	JoomlaCalendar.cellClick = function (el, ev) {
		var self = JoomlaCalendar.getActiveCalObject();
		var closing = false, newdate = false, date = null;
		if (typeof el.navtype == "undefined") {
			if (self.currentDateEl) {
				JoomlaCalendar.removeClass(self.currentDateEl, "selected");
				JoomlaCalendar.removeClass(self.currentDateEl, "alert-success");
				JoomlaCalendar.addClass(el, "selected alert-success");
				self.currentDateEl = el.caldate;
				closing = (self.currentDateEl == el.caldate);
				if (!closing) { self.currentDateEl = el.caldate; }
			}
			self.date.setLocalDateOnly('gregorian', el.caldate);
			date = self.date;
			var other_month = !(self.dateClicked = !el.otherMonth);
			if (self.currentDateEl) { newdate = !el.disabled; }
			if (other_month) { JoomlaCalendar.processCalendar(self.params.firstDayOfWeek, date, self); }
		} else {
			if (el.navtype == 200) {
				JoomlaCalendar.removeClass(el, "hilite");
				JoomlaCalendar.close(self);
				return;
			}
			date = new Date(self.date);
			if (el.navtype == 0) {
				self.date.setLocalDateOnly('gregorian', new Date());                                // TODAY
				self.dateClicked = true;
				JoomlaCalendar.callHandler(self);
				JoomlaCalendar.close(self);
				return;
			}

			self.dateClicked = false;
			var year = date.getOtherFullYear(self.params.dateType), mon = date.getLocalMonth(self.params.dateType);
			switch (el.navtype) {
				case 400:
					break;
				case -2:                                                                             // Prev year
					if (!self.params.compressedHeader)
						if (year > self.params.minYear) {
							date.setOtherFullYear(self.params.dateType, year - 1);
						}
					break;

				case -1:                                                                             // Prev month
					var day = date.getLocalDate(self.params.dateType);
					if (mon > 0) {
						var max = date.getLocalMonthDays(self.params.dateType, mon - 1);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setLocalMonth(self.params.dateType, mon - 1);
					} else if (year-- > self.params.minYear) {
						date.setOtherFullYear(self.params.dateType, year);
						var max = date.getLocalMonthDays(self.params.dateType, 11);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setLocalMonth(self.params.dateType, 11);
					}
					break;
				case 1:                                                                             // Next month
					var day = date.getLocalDate(self.params.dateType);
					if (mon < 11) {
						var max = date.getLocalMonthDays(self.params.dateType, mon + 1);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setLocalMonth(self.params.dateType, mon + 1);
					} else if (year < self.params.maxYear) {
						date.setOtherFullYear(self.params.dateType, year + 1);
						var max = date.getLocalMonthDays(self.params.dateType, 0);
						if (day > max) date.setLocalDate(self.params.dateType, max);
						date.setLocalMonth(self.params.dateType, 0);
					}
					break;
				case 2:                                                                             // Next year
					if (!self.params.compressedHeader)
						if (year < self.params.maxYear) {
							date.setOtherFullYear(self.params.dateType, year + 1);
						}
					break;
				case 0:                                                                             // Today
					break;
			}
			if (!date.equalsTo(self.date)) {
				JoomlaCalendar.setDate(date);
				newdate = true;
			} else if (el.navtype == 0) {
				newdate = closing = true;
			}
		}
		if (newdate) { ev && JoomlaCalendar.callHandler(self); }
		if (closing) { JoomlaCalendar.removeClass(el, "hilite"); ev && JoomlaCalendar.close(self); }
	};

	/** Method to handle keyboard click events **/
	JoomlaCalendar.calKeyEvent = function (ev) {
		ev = window.event ? event : e;
		var self = JoomlaCalendar.getActiveCalObject();
		if (!self)
			return false;

		var K = parseInt(ev.keyCode);

		if (self.params.direction == 'rtl') {
			if (K == 37) K = 39;
			else if (K == 39) K = 37;
		}

		if (K === 32) {                                // KEY space (now)
			JoomlaCalendar.cellClick(self._nav_now, ev);
		}
		if (K === 27) {                                // KEY esc (close)
			JoomlaCalendar.close(self);
		}
		if (K === 13) {                                // KEY enter (select and close)
			JoomlaCalendar.cellClick(self.currentDateEl, ev);
		}
		if (K === 38) {                                // KEY up (previous week)
			JoomlaCalendar.moveCursorBy(7);
		}
		if (K === 40) {                                // KEY down (next week)
			JoomlaCalendar.moveCursorBy( -7);
		}
		if (K === 37) {                                // KEY left (previous day)
			JoomlaCalendar.moveCursorBy(1);
		}
		if (K === 39) {                                // KEY right (next day)
			JoomlaCalendar.moveCursorBy( -1);
		}
		return JoomlaCalendar.stopCalEvent(ev);
	};

	/** Method to create the html stracture of the calendar */
	JoomlaCalendar.create = function (element) {
		var self = element._joomlaCalendar;
		var parent = element.parentNode.getElementsByTagName('INPUT')[0];
		var table = JoomlaCalendar.createElement("table");
		self.table = table;
		table.className = 'table';
		table.cellSpacing = 0;
		table.cellPadding = 0;
		table.style.marginBottom = 0;
		JoomlaCalendar.addCalEvent(table, "mousedown", self.tableMouseDown);
		var div = JoomlaCalendar.createElement("div");
		self.element = div;
		if (self.params.direction) { self.element.style.direction = self.params.direction; }
		div.className = self.params.classes.containerClass;
		div.style.position = "absolute";
		div.style.boxShadow = "0px 0px 70px 0px rgba(0,0,0,0.67)";
		div.style.minWidth = parent.width;
		div.style.padding = '0';
		div.style.left = "auto";
		div.style.top = "auto";
		self.wrapper = JoomlaCalendar.createElement('div');
		self.wrapper.className = self.params.classes.wrapperClass;
		div.appendChild(self.wrapper);
		self.wrapper.appendChild(table);
		var thead = JoomlaCalendar.createElement("thead", table);
		thead.className = 'calendar-header';
		var cell = null;
		var row = null;
		var cal = self;
		var hh = function (text, cs, navtype, node, styles, classes) {
			node = node ? node : "td";
			classes = classes ? 'class="' + classes + '"' : '';
			styles = styles ? styles : {};
			cell = JoomlaCalendar.createElement(node, row);
			cell.colSpan = cs;
			for (var key in styles) {
				cell.style[key] = styles[key];
			}
			if (navtype != 0 && Math.abs(navtype) <= 2) { cell.className += " nav"; }
			JoomlaCalendar.addCalEvent(cell, "mousedown", JoomlaCalendar.dayMouseDown);
			cell.calendar = cal;
			cell.navtype = navtype;
			if (navtype != 0 && Math.abs(navtype) <= 2) {
				cell.innerHTML = "<a " + classes + " style='display:inline;padding:2px 6px;cursor:pointer;text-decoration:none;' unselectable='on'>" + text + "</a>";
			} else {
				cell.innerHTML = "<div unselectable='on'" + classes + ">" + text + "</div>";
			}
			return cell;
		};

		if (self.params.compressedHeader == false) {                                                        // Head - year
			row = JoomlaCalendar.createElement("tr", thead);
			row.className = "calendar-head-row";
			self._nav_py = hh("<", 1, -2, '', '', 'btn btn-small btn-default pull-left');    // Previous year button
			self.title = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', self.params.weekNumbers ? 6 : 5, 300);
			self.title.className = "title";
			self._nav_ny = hh(">", 1, 2, '', '', 'btn btn-small btn-default pull-right');                   // Next year button
		}

		row = JoomlaCalendar.createElement("tr", thead);                                                    // Head - month
		row.className = "calendar-head-row";
		self._nav_pm = hh("<", 1, -1, '', '', 'btn btn-small btn-default pull-left');        // Previous month button
		self._nav_month = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', self.params.weekNumbers ? 6 : 5, 888, 'td', {'textAlign': 'center'});
		self._nav_month.className = "title";
		self._nav_nm = hh(">", 1, 1, '', '', 'btn btn-small btn-default pull-right');                       // Next month button

		if (self.params.showsTodayBtn) {                                                                    // Head - today
			row = JoomlaCalendar.createElement("tr", thead);
			row.className = "headrow";
			self._nav_now = hh('<a class="btn btn-small btn-success" data-action="today" style="display:block;padding:2px 6px;">'
				+ JoomlaCalLocale.today + '</a>', self.params.weekNumbers ? 8 : 7, 0, 'td', {'textAlign': 'center'});
			var todaya = row.querySelector('a[data-action=today]');                                         // HTML5 version
			if (typeof todaya == "undefined") {                                                             // Support IE8
				var tempElem = row.getElementsByTagName("A"), i, todaya = null;
				for (i = 0; i < tempElem.length; i++) {
					if (tempElem[i].getAttribute("data-action") == "today")
						todaya = tempElem[i];
				}
			}
			JoomlaCalendar.addCalEvent(todaya, 'click', function (e) {
				var el = todaya.parentNode.parentNode;
				if (el.tagName === 'TD') { JoomlaCalendar.cellClick(self._nav_now, e); }
			});
		}

		row = JoomlaCalendar.createElement("tr", thead);                                                      // day names
		row.className = "daynames";
		if (self.params.weekNumbers) {
			cell = JoomlaCalendar.createElement("td", row);
			cell.className = "name wn";
			cell.innerHTML = JoomlaCalLocale.wk;
		}
		for (var i = 7; i > 0; --i) {
			cell = JoomlaCalendar.createElement("td", row);
			if (!i) { cell.calendar = self; }
		}
		self.firstdayname = (self.params.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;

		var fdow = self.params.firstDayOfWeek;                                                              // weekdays
		var cell = self.firstdayname;
		var weekend = JoomlaCalLocale.weekend;
		for (var i = 0; i < 7; ++i) {
			cell.className = "day name";
			cell.style.textAlign = 'center';
			var realday = (i + fdow) % 7;
			if (i) {
				cell.calendar = self;
				cell.fdow = realday;
			}
			if (weekend.indexOf(realday.toString()) != -1) { JoomlaCalendar.addClass(cell, "weekend"); }

			cell.innerHTML = JoomlaCalLocale.shortDays[(i + fdow) % 7];
			cell = cell.nextSibling;
		}

		var tbody = JoomlaCalendar.createElement("tbody", table);
		self.tbody = tbody;
		for (i = 6; i > 0; --i) {
			row = JoomlaCalendar.createElement("tr", tbody);
			if (self.params.weekNumbers) { cell = JoomlaCalendar.createElement("td", row); }

			for (var j = 7; j > 0; --j) {
				cell = JoomlaCalendar.createElement("td", row);
				cell.calendar = self;
				JoomlaCalendar.addCalEvent(cell, "mousedown", JoomlaCalendar.dayMouseDown);
			}
		}

		if (self.params.showsTime) {
			row = JoomlaCalendar.createElement("tr", tbody);
			row.className = "time";
			cell = JoomlaCalendar.createElement("td", row);
			cell.className = "time time-title";
			cell.colSpan = 1;
			cell.style.verticalAlign = 'middle';
			cell.innerHTML = JoomlaCalLocale.time || "&#160;";
			var cell1 = JoomlaCalendar.createElement("td", row);
			cell1.className = "time hours-select";
			cell1.colSpan = 2;
			var cell2 = JoomlaCalendar.createElement("td", row);
			cell2.className = "time minutes-select";
			cell2.colSpan = 2;

			(function () {
				function makeTimePart(className, selected, range_start, range_end, cellTml) {
					var part = JoomlaCalendar.createElement("select", cellTml), num;
					part.calendar = self;
					part.className = "no-chozen-here " + className;
					part.style.width = '100%';
					part.navtype = 50;
					part._range = [];
					for (var i = range_start; i <= range_end; ++i) {
						var txt, selAttr = '';
						if (i == selected)
							selAttr = true;
						if (i < 10 && range_end >= 10) {
							num = '0' + i;
							txt = Date.convertNumbers('0') + Date.convertNumbers(i);
						} else {
							num = '' + i;
							txt = '' + Date.convertNumbers(i);
						}
						part.options.add(new Option(txt, num, selAttr, selAttr));
					}
					return part;
				}
				var hrs = self.date.getHours();
				var mins = self.date.getMinutes();
				var t12 = !self.params.time24;
				var pm = (hrs > 12);
				if (t12 && pm) { hrs -= 12; }
				var H = makeTimePart("time hour", hrs, t12 ? 1 : 0, t12 ? 12 : 23, cell1);
				var M = makeTimePart("time minutes", mins, 0, 59, cell2);
				var AP = null;
				cell = JoomlaCalendar.createElement("td", row);
				cell.className = "time ampm";
				cell.colSpan = self.params.weekNumbers ? 1 : 2;
				if (t12) {
					var selAttr = '';
					if (pm) { selAttr = true; }
					var part = JoomlaCalendar.createElement("select", cell);
					part.style.width = '100%';
					part.options.add(new Option(JoomlaCalLocale.PM, "pm", pm ? selAttr : '', pm ? selAttr : ''));
					part.options.add(new Option(JoomlaCalLocale.AM, "am", pm ? '' : selAttr, pm ? '' : selAttr));
					AP = part;

					// Event listener for the am/pm select
					if (AP.attachEvent) { // IE
						AP.attachEvent("onchange", function () {
							JoomlaCalendar.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
								event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
								event.target.parentNode.parentNode.childNodes[3].childNodes[0].value, self);
						}, false);
					} else { // W3C
						AP.addEventListener("change", function () {
							JoomlaCalendar.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
								event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
								event.target.parentNode.parentNode.childNodes[3].childNodes[0].value, self);
						}, false);
					}
				} else {
					cell.innerHTML = "&#160;";
				}

				if (H.attachEvent) { // Event listeners for the hour select and minutes select IE
					H.attachEvent("onchange", function () {
						JoomlaCalendar.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[3].childNodes[0].value, self);
					}, false);
					M.attachEvent("onchange", function (event) {
						JoomlaCalendar.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[3].childNodes[0].value, self);
					}, false);
				} else { // W3C
					H.addEventListener("change", function (event) {
						JoomlaCalendar.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[3].childNodes[0].value, self);
					}, false);
					M.addEventListener("change", function () {
						JoomlaCalendar.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[3].childNodes[0].value, self);
					}, false);
				}
			})();
		}

		JoomlaCalendar.processCalendar(self.params.firstDayOfWeek, self.date, self);
		parent.parentNode.appendChild(self.element);
	};

	/** Method to append numbers to the calendar table */
	JoomlaCalendar.processCalendar = function (firstDayOfWeek, date, self) {
		self.table.style.visibility = "hidden";
		var today = new Date(),
			TY = today.getLocalFullYear(self.params.dateType),
			TM = today.getLocalMonth(self.params.dateType),
			TD = today.getLocalDate(self.params.dateType);
		var year = date.getOtherFullYear(self.params.dateType);

		if (year < self.params.minYear) {                                                                   // Check min,max year
			year = self.params.minYear;
			date.getOtherFullYear(self.params.dateType, year);
		} else if (year > self.params.maxYear) {
			year = self.params.maxYear;
			date.getOtherFullYear(self.params.dateType, year);
		}

		self.params.firstDayOfWeek = firstDayOfWeek;
		self.date = new Date(date);
		var month = date.getLocalMonth(self.params.dateType);
		var mday = date.getLocalDate(self.params.dateType);

		// Compute the first day that would actually be displayed in the calendar, even if it's from the previous month.
		date.setLocalDate(self.params.dateType, 1);
		var day1 = (date.getLocalDay(self.params.dateType) - self.params.firstDayOfWeek) % 7;
		if (day1 < 0) { day1 += 7; }
		date.setLocalDate(self.params.dateType, - day1);
		date.setLocalDate(self.params.dateType, date.getLocalDate(self.params.dateType) + 1);

		var row = self.tbody.firstChild;
		var ar_days = self.ar_days = new Array();
		var weekend = JoomlaCalLocale.weekend;
		var monthDays = parseInt(date.getLocalWeekDays(self.params.dateType));

		/** Fill the table **/
		for (var i = 0; i < monthDays; ++i, row = row.nextSibling) {
			var cell = row.firstChild;
			if (self.params.weekNumbers) {
				cell.className = "day wn";
				cell.innerHTML = date.convertNumbers(date.getLocalWeekNumber(self.params.dateType));
				cell = cell.nextSibling;
			}
			row.className = "daysrow";
			var hasdays = false, iday, dpos = ar_days[i] = [], totalDays = monthDays +1;
			for (var j = 0; j < totalDays; ++j, cell = cell.nextSibling, date.setLocalDate(self.params.dateType, iday + 1)) {
				cell.className = "day";
				cell.style['textAlign'] = 'center';
				iday = date.getLocalDate(self.params.dateType);
				var wday = date.getLocalDate(self.params.dateType);
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
				cell.innerHTML = self.params.debug ? iday : Date.convertNumbers(iday);                     // translated day number for each cell
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
			self._nav_month.getElementsByTagName('span')[0].innerHTML = self.params.debug ? month + ' ' + JoomlaCalLocale.months[month] : JoomlaCalLocale.months[month];
			self.title.getElementsByTagName('span')[0].innerHTML = self.params.debug ? year + ' ' +  Date.convertNumbers(year.toString()) : Date.convertNumbers(year.toString());
		} else {
			var tmpYear = Date.convertNumbers(year.toString());
			self._nav_month.getElementsByTagName('span')[0].innerHTML = !self.params.monthBefore  ? JoomlaCalLocale.months[month] + ' - ' + tmpYear : tmpYear + ' - ' + JoomlaCalLocale.months[month] ;
		}
		self.table.style.visibility = "visible";
	};

	/** Method to listen for the click event on the input button. **/
	JoomlaCalendar.bind = function (element) {
		var btn = element.getElementsByTagName('button')[0],
			input = element.getElementsByTagName('input')[0];
		JoomlaCalendar.addCalEvent(input, 'focus', JoomlaCalendar.show, true);
		JoomlaCalendar.addCalEvent(btn, 'click', JoomlaCalendar.show, false);
		JoomlaCalendar.addCalEvent(input.form, 'submit', getJoomlaCalendarValuesFromAlt, true);
	};

	/** Method to get the active calendar element through any descendant element. */
	JoomlaCalendar.getCalObject = function(element) {
		if (!element) {
			return false;
		}
		while (element.parentNode) {
			element = element.parentNode;
			if (JoomlaCalendar.hasClass(element, 'field-calendar')) {
				return element;
			}
		}
		return false;
	};

	/** Method to get the currently active(shown) calendar object. */
	JoomlaCalendar.getActiveCalObject = function() {
		var calendars = document.getElementsByClassName("field-calendar");

		for (var i = 0; i < calendars.length; i++) {
			if (calendars[i]._joomlaCalendar && calendars[i]._joomlaCalendar.hidden == false)
				return calendars[i]._joomlaCalendar;
		}
	};

	/** The Calendar object constructor. */
	JoomlaCalendar.init = function (selector) {
		var elements, i;

		elements = document.getElementsByClassName(selector);

		if (typeof JoomlaCalLocale === 'undefined') {                                         // Init the translations only once
			var elem = (elements.length === 1) ? elements : elements[0],
				element = elem.getElementsByTagName("button")[0];

			window.JoomlaCalLocale = {
				today      : element.getAttribute("data-today-trans") ? element.getAttribute("data-today-trans") : "Today",
				weekend    : element.getAttribute("data-weekend") ? element.getAttribute("data-weekend").split(',').map(Number) : [0, 6],
				wk         : element.getAttribute("data-wk") ? element.getAttribute("data-wk") : "wk",
				time       : element.getAttribute("data-time") ? element.getAttribute("data-time") : "Time:",
				days       : element.getAttribute("data-weekdays-full") ? element.getAttribute("data-weekdays-full").split('_') : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
				shortDays  : element.getAttribute("data-weekdays-short") ? element.getAttribute("data-weekdays-short").split('_') : ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
				months     : element.getAttribute("data-months-long") ? element.getAttribute("data-months-long").split('_') : ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
				shortMonths: element.getAttribute("data-months-short") ? element.getAttribute("data-months-short").split('_') : ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
				AM         : (element.getAttribute("data-time-am") && element.getAttribute("data-cal-type") == 'gregorian' ) ? element.getAttribute("data-time-am") : "AM",
				PM         : (element.getAttribute("data-time-pm") && element.getAttribute("data-cal-type") == 'gregorian' ) ? element.getAttribute("data-time-pm") : "PM",
				am         : (element.getAttribute("data-time-am-lower") && element.getAttribute("data-cal-type") == 'gregorian' ) ? element.getAttribute("data-time-am-lower") : "am",
				pm         : (element.getAttribute("data-time-pm-lower") && element.getAttribute("data-time-pm-lower") == 'gregorian' ) ? element.getAttribute("data-time-pm-lower") : "pm",
				dateType   : element.getAttribute("data-cal-type") ? element.getAttribute("data-cal-type") : "gregorian"
			};
		}

		for (i = 0; i < elements.length; i++) {
			if (!elements[i]._joomlaCalendar) {
				elements[i]._joomlaCalendar = new JoomlaCalendar(elements[i]);
			}
		}
	};

	/** COMPATIBILITY WITH IE 8 **/
	JoomlaCalendar.hasClass = function (element, className) { return (' ' + element.className + ' ').indexOf(' ' + className + ' ') > -1; };
	JoomlaCalendar.addClass  = function (element, className) { JoomlaCalendar.removeClass(element, className); element.className += " " + className; };
	JoomlaCalendar.removeClass = function (element, className) { if (!(element && element.className)) { return; } var cls = element.className.split(" "), ar = new Array(); for (var i = cls.length; i > 0;) { if (cls[--i] != className) ar[ar.length] = cls[i]; } element.className = ar.join(" "); };
	JoomlaCalendar.stopCalEvent = function (ev) { ev || (ev = window.event); if (/msie/i.test(navigator.userAgent)) { ev.cancelBubble = true; ev.returnValue = false; } else { ev.preventDefault(); ev.stopPropagation(); } return false; };
	JoomlaCalendar.addCalEvent = function (el, evname, func) { if (el.attachEvent) { el.attachEvent("on" + evname, func); } else if (el.addEventListener) { el.addEventListener(evname, func, true); } else { el["on" + evname] = func; } };
	JoomlaCalendar.removeCalEvent = function (el, evname, func) { if (el.detachEvent) { el.detachEvent("on" + evname, func); } else if (el.removeEventListener) { el.removeEventListener(evname, func, true); } else { el["on" + evname] = null; } };
	JoomlaCalendar.createElement = function (type, parent) { var el = null; el = document.createElement(type); if (typeof parent != "undefined") { parent.appendChild(el); } return el; };
	/** END OF COMPATIBILITY WITH IE 8 **/

	/** Instantiate all the calendar fields when the document is ready */
	document.onreadystatechange = function () {
		if (document.readyState == "interactive") {
			JoomlaCalendar.init("field-calendar");           // One line setup
			// Destroy chosen if added in time selectors
			if (typeof window.jQuery != "undefined") {
				jQuery(document).ready(function() {
					if (jQuery().chosen) {
						jQuery.each(jQuery('.no-chozen-here'), function (index, value) {
							jQuery(value).chosen('destroy');
						});
					}
				});
			}
		}
	};
})();
