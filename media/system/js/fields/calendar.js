/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
!(function(window, document){
	'use strict';

	/** Method to convert numbers to local symbols. */
	Date.convertNumbers = function(str) {
		var str = str.toString();

		if (Object.prototype.toString.call(JoomlaCalLocale.localLangNumbers) === '[object Array]') {
			for (var i = 0; i < JoomlaCalLocale.localLangNumbers.length; i++) {
				str = str.replace(new RegExp(i, 'g'), JoomlaCalLocale.localLangNumbers[i]);
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

	var JoomlaCalendar = function (element) {

		// Initialize only if the element exists
		if (!element) {
			throw new Error("Calendar setup failed:\n  No valid element found, Please check your code");
		}

		if (typeof Date.parseFieldDate !== 'function') {
			throw new Error("Calendar setup failed:\n  No valid date helper, Please check your code");
		}

		if (element._joomlaCalendar) {
			throw new Error('JoomlaCalendar instance already exists for the element');
		}

		element._joomlaCalendar = this;

		this.writable   = true;
		this.params     = {};
		this.element    = element;
		this.inputField = element.getElementsByTagName('input')[0];
		this.button     = element.getElementsByTagName('button')[0];

		if (!this.inputField) {
			throw new Error("Calendar setup failed:\n  No valid input found, Please check your code");
		}

		// Prepare the parameters
		this.params = {
			debug: false,
			clicked: false,
			element: {style: {display: "none"}},
			writable: true
		};

		var self = this,
			btn  = this.button,
			instanceParams = {
				inputField      : this.inputField,
				dateType        : JoomlaCalLocale.dateType ? JoomlaCalLocale.dateType : 'gregorian',
				direction       : (document.dir != undefined) ? document.dir : document.getElementsByTagName("html")[0].getAttribute("dir"),
				firstDayOfWeek  : btn.getAttribute("data-firstday") ? parseInt(btn.getAttribute("data-firstday")) : 0,
				dateFormat      : "%Y-%m-%d %H:%M:%S",
				weekend         : JoomlaCalLocale.weekend ? JoomlaCalLocale.weekend : [0,6],
				minYear         : JoomlaCalLocale.minYear ? JoomlaCalLocale.minYear : 1900,
				maxYear         : JoomlaCalLocale.maxYear ? JoomlaCalLocale.maxYear : 2100,
				minYearTmp      : btn.getAttribute("data-min-year"),
				maxYearTmp      : btn.getAttribute("data-max-year"),
				weekendTmp      : btn.getAttribute("data-weekend"),
				time24          : true,
				showsOthers     : (parseInt(btn.getAttribute("data-show-others")) === 1) ? true : false,
				showsTime       : true,
				weekNumbers     : (parseInt(btn.getAttribute("data-week-numbers")) === 1) ? true : false,
				showsTodayBtn   : true,
				compressedHeader: (parseInt(btn.getAttribute("data-only-months-nav")) === 1) ? true : false,
			};

		// Keep B/C
		if (btn.getAttribute("data-dayformat")) {
			instanceParams.dateFormat = btn.getAttribute("data-dayformat") ? btn.getAttribute("data-dayformat") : "%Y-%m-%d %H:%M:%S";
		}

		if (btn.getAttribute("data-time-24")) {
			instanceParams.time24 = parseInt(btn.getAttribute("data-time-24")) === 24 ? true : false;
		}

		if (btn.getAttribute("data-show-time")) {
			instanceParams.showsTime = parseInt(btn.getAttribute("data-show-time")) === 1 ? true : false;
		}

		if (btn.getAttribute("data-today-btn")) {
			instanceParams.showsTodayBtn = parseInt(btn.getAttribute("data-today-btn")) === 1 ? true : false;
		}

		// Merge the parameters
		for (var param in instanceParams) {
			this.params[param] = instanceParams[param];
		}

		// Evaluate the min year
		if (isInt(self.params.minYearTmp)) {
			self.params.minYear = getBoundary(parseInt(self.params.minYearTmp), self.params.dateType);
		}
		// Evaluate the max year
		if (isInt(self.params.maxYearTmp)) {
			self.params.maxYear = getBoundary(parseInt(self.params.maxYearTmp), self.params.dateType);
		}
		// Evaluate the weekend days
		if (self.params.weekendTmp !== "undefined") {
			self.params.weekend = self.params.weekendTmp.split(',').map(function(item) { return parseInt(item, 10); });
		}

		// Event handler need to define here, to be able access in current context
		this._dayMouseDown = function(event) {
			return self._handleDayMouseDown(event);
		};
		this._calKeyEvent = function(event) {
			return self._handleCalKeyEvent(event);
		};
		this._documentClick = function(event) {
			return self._handleDocumentClick(event);
		};

		// Set it up
		this.checkInputs();

		// For the fields with readonly tag calendar will not initiate fully
		if (this.inputField.getAttribute('readonly')) {
			return;
		}

		this._create();
		this._bindEvents();
	};

	JoomlaCalendar.prototype.checkInputs = function () {
		// Get the date from the input
		var inputAltValueDate = Date.parseFieldDate(this.inputField.getAttribute('data-alt-value'), this.params.dateFormat, 'gregorian');

		if (this.inputField.value.length) {
			this.date = inputAltValueDate;
			this.inputField.value = inputAltValueDate.print(this.params.dateFormat, this.params.dateType, true);
		} else {
			this.date = new Date();
		}
	};

	/** Time Control */
	JoomlaCalendar.prototype.updateTime = function (hours, mins, secs) {
		var self = this,
			date = self.date;

		var d = self.date.getLocalDate(self.params.dateType),
			m = self.date.getLocalMonth(self.params.dateType),
			y = self.date.getLocalFullYear(self.params.dateType),
			ampm = this.inputField.parentNode.parentNode.querySelectorAll('.time-ampm')[0];

		if (!self.params.time24) {
			if (/pm/i.test(ampm.value) && hours < 12) {
				hours = parseInt(hours) + 12;
			} else if (/am/i.test(ampm.value) && hours == 12) {
				hours = 0;
			}
		}

		date.setHours(hours);
		date.setMinutes(parseInt(mins, 10));
		date.setSeconds(date.getSeconds());
		date.setLocalFullYear(self.params.dateType, y);
		date.setLocalMonth(self.params.dateType, m);
		date.setLocalDate(self.params.dateType, d);
		self.dateClicked = false;

		this.callHandler();
	};

	/** Method to set the date to the given date object */
	JoomlaCalendar.prototype.setDate = function (date) {
		if (!date.equalsTo(this.date)) {
			this.date = date;
			this.processCalendar(this.params.firstDayOfWeek, date);
		}
	};

	/** Method to set the current date by a number, step */
	JoomlaCalendar.prototype.moveCursorBy = function (step) {
		var date = new Date(this.date);
		date.setDate(date.getDate() - step);
		this.setDate(date);
	};

	/** Reset select element */
	JoomlaCalendar.prototype.resetSelected = function (element) {
		var options = element.options;
		var i = options.length;
		while (i--) {
			var current = options[i];
			if (current.selected) {
				current.selected = false;
			}
		}
	};

	/** Method to set the value for the input field */
	JoomlaCalendar.prototype.callHandler = function () {
		/** Output the date **/
		if (this.params.dateType == 'gregorian') {
			this.inputField.setAttribute('data-alt-value', this.date.print(this.params.dateFormat, this.params.dateType, false));
			if (this.inputField.getAttribute('data-alt-value') && this.inputField.getAttribute('data-alt-value') != '0000-00-00 00:00:00') {
				this.inputField.value = this.date.print(this.params.dateFormat, this.params.dateType, true);
			}
		} else {
			this.inputField.setAttribute('data-alt-value', this.date.print(this.params.dateFormat, 'gregorian', false));
			this.inputField.setAttribute('data-local-value', this.date.print(this.params.dateFormat, this.params.dateType, false));
			this.inputField.value = this.date.print(this.params.dateFormat, this.params.dateType, true);
		}

		if (typeof this.inputField.onchange == "function") {
			this.inputField.onchange();
		}

		if (this.dateClicked && typeof this.params.onUpdate == "function") {
			this.params.onUpdate(this);
		}

		if (this.dateClicked) {
			this.close();
		} else {
			this.processCalendar();
		}
	};

	/** Method to close/hide the calendar */
	JoomlaCalendar.prototype.close = function () {
		document.activeElement.blur();
		this.hide();
	};

	/** Method to show the calendar. */
	JoomlaCalendar.prototype.show = function () {
		/** This is needed for IE8 */
		if (navigator.appName.indexOf("Internet Explorer")!=-1) {
			var badBrowser = (
				navigator.appVersion.indexOf("MSIE 9")==-1 &&
				navigator.appVersion.indexOf("MSIE 1")==-1
			);

			if (badBrowser) {
				if (window.jQuery && jQuery().chosen) {
					var selItems = this.element.getElementsByTagName('select');
					for (var i = 0; i < selItems.length; i++) {
						jQuery(selItems[i]).chosen('destroy');
					}
				}
			}
		}

		this.checkInputs();
		this.inputField.focus();
		var rows = this.table.getElementsByTagName("tr");
		for (var i = rows.length; i > 0;) {
			var row = rows[--i];
			var cells = row.getElementsByTagName("td");
			for (var j = cells.length; j > 0;) {
				var cell = cells[--j];
			}
		}

		this.dropdownElement.style.display = "block";
		this.hidden = false;

		document.addEventListener("keydown", this._calKeyEvent, true);
		document.addEventListener("keypress", this._calKeyEvent, true);
		document.addEventListener("mousedown", this._documentClick, true);

		/** Move the calendar to top position if it doesn't fit below. */
		var containerTmp = this.element.querySelector('.js-calendar');

		if ((window.innerHeight + window.scrollY) < containerTmp.getBoundingClientRect().bottom + 20) {
			containerTmp.style.marginTop = - (containerTmp.getBoundingClientRect().height + this.inputField.getBoundingClientRect().height) + "px";
		}

		this.processCalendar();
	};

	/** Method to hide the calendar. */
	JoomlaCalendar.prototype.hide = function () {
		document.removeEventListener("keydown", this._calKeyEvent, true);
		document.removeEventListener("keypress", this._calKeyEvent, true);
		document.removeEventListener("mousedown", this._documentClick, true);

		this.dropdownElement.style.display = "none";
		this.hidden = true;
	};

	/** Method to catch clicks outside of the calendar (used as close call) */
	JoomlaCalendar.prototype._handleDocumentClick = function (ev) {
		var el = ev.target;

		if (el !== null && !el.classList.contains('time')) {
			for (; el !== null && el !== this.element; el = el.parentNode);
		}

		if (el === null) {
			document.activeElement.blur();
			this.hide();
			return stopCalEvent(ev);
		}
	};

	/** Method to handle mouse click events (menus, buttons) **/
	JoomlaCalendar.prototype._handleDayMouseDown = function (ev) {
		var self = this,
			el = ev.currentTarget,
			target = ev.target || ev.srcElement;
		if (ev.srcElement && ev.srcElement.hasAttribute('data-action')) {
			return;
		}

		if (el.nodeName !== 'TD') {                         // A bootstrap inner button was pressed?
			var testel = el.getParent('TD');
			if (testel.nodeName === 'TD') {                 // Yes so use that element's td
				el = testel;
			} else {                                        // No - try to find the table this way
				el = el.getParent('TD');
				if (el.classList.contains('js-calendar')) {
					el = el.getElementsByTagName('table')[0];
				}
			}
		} else {                                            // Check that doesn't have a button and is not a day td
			if (!(target.classList.contains('js-btn')) && !el.classList.contains('day') && !el.classList.contains('title')) {
				return;
			}
		}

		if (!el || el.disabled) {
			return false;
		}

		if (typeof el.navtype === "undefined" || el.navtype != 300) {
			if (el.navtype == 50) { el._current = el.innerHTML; }

			if (target == el || target.parentNode == el) { self.cellClick(el, ev); }

			var mon = null;
			if (typeof el.month != "undefined") {
				mon = el;
			}
			if (typeof el.parentNode.month != "undefined") {
				mon = el.parentNode;
			}
			var date = null;
			if (mon) {
				date = new Date(self.date);
				if (mon.month != date.getLocalMonth(self.params.dateType)) {
					date.setLocalMonth(self.params.dateType, mon.month);
					self.setDate(date);
					self.dateClicked = false;
					this.callHandler();
				}
			} else {
				var year = null;
				if (typeof el.year != "undefined") {
					year = target;
				}
				if (typeof el.parentNode.year != "undefined") {
					year = target.parentNode;
				}
				if (year) {
					date = new Date(self.date);
					if (year.year != date.getLocalFullYear(self.params.dateType)) {
						date.setFullYear(self.params.dateType, year.year);
						self.setDate(date);
						self.dateClicked = false;
						this.callHandler();
					}
				}
			}
		}

		return stopCalEvent(ev);
	};

	/** Method to handle mouse click events (dates) **/
	JoomlaCalendar.prototype.cellClick = function (el, ev) {
		var self = this,
			closing = false,
			newdate = false,
			date = null;

		if (typeof el.navtype == "undefined") {
			if (self.currentDateEl) {
				el.classList.add("selected");
				self.currentDateEl = el.caldate;
				closing = (self.currentDateEl == el.caldate);
				if (!closing) {
					self.currentDateEl = el.caldate;
				}
			}
			self.date.setLocalDateOnly('gregorian', el.caldate);
			date = self.date;
			var other_month = !(self.dateClicked = !el.otherMonth);
			if (self.currentDateEl) { newdate = !el.disabled; }
			if (other_month) {
				this.processCalendar();
			}
		} else {
			date = new Date(self.date);
			self.dateClicked = false;
			var year = date.getOtherFullYear(self.params.dateType), mon = date.getLocalMonth(self.params.dateType);
			switch (el.navtype) {
				case 400:
					break;
				case -2:                                                                             // Prev year
					if (!self.params.compressedHeader) {
						if (year > self.params.minYear) {
							date.setOtherFullYear(self.params.dateType, year - 1);
						}
					}
					break;
				case -1:                                                                             // Prev month
					var day = date.getLocalDate(self.params.dateType);
					if (mon > 0) {
						var max = date.getLocalMonthDays(self.params.dateType, mon - 1);
						if (day > max) {
							date.setLocalDate(self.params.dateType, max);
						}
						date.setLocalMonth(self.params.dateType, mon - 1);
					} else if (year-- > self.params.minYear) {
						date.setOtherFullYear(self.params.dateType, year);
						var max = date.getLocalMonthDays(self.params.dateType, 11);
						if (day > max) {
							date.setLocalDate(self.params.dateType, max);
						}
						date.setLocalMonth(self.params.dateType, 11);
					}
					break;
				case 1:                                                                             // Next month
					var day = date.getLocalDate(self.params.dateType);
					if (mon < 11) {
						var max = date.getLocalMonthDays(self.params.dateType, mon + 1);
						if (day > max) {
							date.setLocalDate(self.params.dateType, max);
						}
						date.setLocalMonth(self.params.dateType, mon + 1);
					} else if (year < self.params.maxYear) {
						date.setOtherFullYear(self.params.dateType, year + 1);
						var max = date.getLocalMonthDays(self.params.dateType, 0);
						if (day > max) {
							date.setLocalDate(self.params.dateType, max);
						}
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
				this.setDate(date);
				newdate = true;
			} else if (el.navtype == 0) {
				newdate = closing = true;
			}
		}

		if (newdate) {
			if (self.params.showsTime) {
				this.dateClicked = false;
			}
			ev && this.callHandler();
		}

		el.classList.remove("hilite");

		if (closing && !self.params.showsTime) {
			self.dateClicked = false;
			ev && this.close();
		}
	};

	/** Method to handle keyboard click events **/
	JoomlaCalendar.prototype._handleCalKeyEvent = function (ev) {
		var self = this,
			K = parseInt(ev.keyCode);

		// Get value from input
		if (ev.target === this.inputField && (K === 13 || K === 9) && !ev.shiftKey) {
			if (this.inputField.value != '') {
				self.date = Date.parseFieldDate(this.inputField.value, self.params.dateFormat, self.params.dateType);
				this.processCalendar(self.params.firstDayOfWeek, self.date);
				this.cellClick(self.currentDateEl, ev);
			} else {
				this.inputField.value = '';
				this.inputField.setAttribute('value', '');
				this.inputField.setAttribute('data-alt-value', '0000-00-00 00:00:00');
			}
			this.close();
		}

		if (self.params.direction == 'rtl') {
			if (K == 37) {
				K = 39;
			} else if (K == 39) {
				K = 37;
			}
		}

		if (ev.shiftKey && K === 32) {                  // KEY Shift + space (now)
			this.cellClick(self._nav_now, ev);
		}
		if (K === 27) {                                // KEY esc (close);
			this.close();
		}
		if (ev.shiftKey && K === 13) {                 // KEY enter (select and close)
			if (this.params.showsTime) {
				this.dateClicked = false;
			}
			this.cellClick(self.currentDateEl, ev);
		}
		if (K === 38) {                                // KEY up (previous week)
			this.moveCursorBy(7);
		}
		if (K === 40) {                                // KEY down (next week)
			this.moveCursorBy( -7);
		}
		if (K === 37) {                                // KEY left (previous day)
			this.moveCursorBy(1);
		}
		if (K === 39) {                                // KEY right (next day)
			this.moveCursorBy( -1);
		}
		if (ev.target === this.inputField && !(K>48 || K<57 || K===186 || K===189 || K===190 || K === 32)) {
			return stopCalEvent(ev);
		}
	};

	/** Method to create the html stracture of the calendar */
	JoomlaCalendar.prototype._create = function () {
		var self   = this,
			parent = this.element,
			table  = createElement("table"),
			div    = createElement("div");

		this.table = table;
		table.className = 'table';
		table.cellSpacing = 0;
		table.cellPadding = 0;
		table.style.marginBottom = 0;

		this.dropdownElement = div;
		parent.appendChild(div);

		if (this.params.direction) {
			div.style.direction = this.params.direction;
		}

		div.className = 'js-calendar';
		div.style.position = "absolute";
		div.style.boxShadow = "0px 0px 70px 0px rgba(0,0,0,0.67)";
		div.style.minWidth = this.inputField.width;
		div.style.padding = '0';
		div.style.display = "none";
		div.style.left = "auto";
		div.style.top = "auto";
		div.style.zIndex = 1060;
		div.style.borderRadius = "20px";

		this.wrapper = createElement('div');
		this.wrapper.className = 'calendar-container';
		div.appendChild(this.wrapper);
		this.wrapper.appendChild(table);

		var thead = createElement("thead", table);
		thead.className = 'calendar-header';

		var cell = null,
			row  = null,
			cal  = this,
			hh   = function (text, cs, navtype, node, styles, classes, dataAttr) {
				node = node ? node : "td";
				styles = styles ? styles : {};
				cell = createElement(node, row);
				if (cs) {
					classes = classes ? 'class="' + classes + '"' : '';
				cell.colSpan = cs;
				}

				for (var key in styles) {
					cell.style[key] = styles[key];
				}
				for (var key in dataAttr) {
					cell.setAttribute(key, dataAttr[key]);
				}
				if (navtype != 0 && Math.abs(navtype) <= 2) {
					cell.className += " nav";
				}

				if (cs) {
					cell.addEventListener("mousedown", self._dayMouseDown, true);
				}

				cell.calendar = cal;
				cell.navtype = navtype;
				if (navtype != 0 && Math.abs(navtype) <= 2) {
					cell.innerHTML = "<a " + classes + " style='display:inline;padding:2px 6px;cursor:pointer;text-decoration:none;' unselectable='on'>" + text + "</a>";
				} else {
					cell.innerHTML = cs ? "<div unselectable='on'" + classes + ">" + text + "</div>" : text;
					if (!cs && classes) {
						cell.className = classes;
					}
				}
				return cell;
			};

		if (this.params.compressedHeader === false) {                                                        // Head - year
			row = createElement("tr", thead);
			row.className = "calendar-head-row";
			this._nav_py = hh("&lsaquo;", 1, -2, '', {"text-align": "center", "font-size": "2em", "line-height": "1em"}, 'js-btn btn-prev-year');                   // Previous year button
			this.title = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', this.params.weekNumbers ? 6 : 5, 300);
			this.title.className = "title";
			this._nav_ny = hh(" &rsaquo;", 1, 2, '', {"text-align": "center", "font-size": "2em", "line-height": "1em"}, 'js-btn btn-next-year');                   // Next year button
		}

		row = createElement("tr", thead);                                                                   // Head - month
		row.className = "calendar-head-row";
		this._nav_pm = hh("&lsaquo;", 1, -1, '', {"text-align": "center", "font-size": "2em", "line-height": "1em"}, 'js-btn btn-prev-month');                       // Previous month button
		this._nav_month = hh('<div style="text-align:center;font-size:1.2em"><span></span></div>', this.params.weekNumbers ? 6 : 5, 888, 'td', {'textAlign': 'center'});
		this._nav_month.className = "title";
		this._nav_nm = hh(" &rsaquo;", 1, 1, '', {"text-align": "center", "font-size": "2em", "line-height": "1em"}, 'js-btn btn-next-month');                       // Next month button

		row = createElement("tr", thead);                                                                   // day names
		row.className = self.params.weekNumbers ? "daynames wk" : "daynames";
		if (this.params.weekNumbers) {
			cell = createElement("td", row);
			cell.className = "day-name wn";
			cell.innerHTML = JoomlaCalLocale.wk;
		}
		for (var i = 7; i > 0; --i) {
			cell = createElement("td", row);
			if (!i) {
				cell.calendar = self;
			}
		}
		this.firstdayname = (this.params.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;

		var fdow = this.params.firstDayOfWeek,
			cell = this.firstdayname,
			weekend = JoomlaCalLocale.weekend;

		for (var i = 0; i < 7; ++i) {
			var realday = (i + fdow) % 7;
			cell.classList.add("day-name");
			this.params.weekNumbers ? cell.classList.add('day-name-week') : '';

			if (i) {
				cell.calendar = self;
				cell.fdow = realday;
			}
			if (weekend.indexOf(weekend) != -1) {
				cell.classList.add("weekend");
			}

			cell.innerHTML = JoomlaCalLocale.shortDays[(i + fdow) % 7];
			cell = cell.nextSibling;
		}

		var tbody = createElement("tbody", table);
		this.tbody = tbody;
		for (i = 6; i > 0; --i) {
			row = createElement("tr", tbody);
			if (this.params.weekNumbers) {
				cell = createElement("td", row);
			}

			for (var j = 7; j > 0; --j) {
				cell = createElement("td", row);
				cell.calendar = this;
				cell.addEventListener("mousedown", this._dayMouseDown, true);
			}
		}

		if (this.params.showsTime) {
			row = createElement("tr", tbody);
			row.className = "time";

			cell = createElement("td", row);
			cell.className = "time time-title";
			cell.colSpan = 1;
			cell.style.verticalAlign = 'middle';
			cell.innerHTML = JoomlaCalLocale.time || "&#160;";

			var cell1 = createElement("td", row);
			cell1.className = "time hours-select";
			cell1.colSpan = 2;

			var cell2 = createElement("td", row);
			cell2.className = "time minutes-select";
			cell2.colSpan = 2;

			(function () {
				function makeTimePart(className, selected, range_start, range_end, cellTml) {
					var part = createElement("select", cellTml), num;
					part.calendar  = self;
					part.className =  className;
					part.setAttribute('data-chosen', true); // avoid Chosen, hack
					part.style.width = '100%';
					part.navtype = 50;
					part._range = [];
					for (var i = range_start; i <= range_end; ++i) {
						var txt, selAttr = '';
						if (i == selected) {
							selAttr = true;
						}
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
				var hrs  = self.date.getHours(),
					mins = self.date.getMinutes(),
					t12  = !self.params.time24,
					pm   = (self.date.getHours() > 12);

				if (t12 && pm) {
					hrs -= 12;
				}

				var H = makeTimePart("time time-hours", hrs, t12 ? 1 : 0, t12 ? 12 : 23, cell1),
					M = makeTimePart("time time-minutes", mins, 0, 59, cell2),
					AP = null;

				cell = createElement("td", row);
				cell.className = "time ampm-select";
				cell.colSpan = self.params.weekNumbers ? 1 : 2;

				if (t12) {
					var selAttr = true,
						altDate = Date.parseFieldDate(self.inputField.getAttribute('data-alt-value'), self.params.dateFormat, 'gregorian');
					pm = (altDate.getHours() > 12);

					var part = createElement("select", cell);
					part.className = "time-ampm";
					part.style.width = '100%';
					part.options.add(new Option(JoomlaCalLocale.PM, "pm", pm ? selAttr : '', pm ? selAttr : ''));
					part.options.add(new Option(JoomlaCalLocale.AM, "am", pm ? '' : selAttr, pm ? '' : selAttr));
					AP = part;

					// Event listener for the am/pm select
					AP.addEventListener("change", function (event) {
						self.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
							event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
					}, false);
				} else {
					cell.innerHTML = "&#160;";
					cell.colSpan = self.params.weekNumbers ? 3 : 2;
				}

				H.addEventListener("change", function (event) {
					self.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
						event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
						event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
				}, false);
				M.addEventListener("change", function (event) {
					self.updateTime(event.target.parentNode.parentNode.childNodes[1].childNodes[0].value,
						event.target.parentNode.parentNode.childNodes[2].childNodes[0].value,
						event.target.parentNode.parentNode.childNodes[3].childNodes[0].value);
				}, false);
			})();
		}

		if (this.params.showsTodayBtn) {                                                                    // Head - today
			row = createElement("div", this.wrapper);
			row.className = "buttons-wrapper btn-group";

			this._nav_save = hh(JoomlaCalLocale.save, '', 100, 'a', '', 'js-btn btn btn-clear', {"data-action": "clear"});

			if (!this.inputField.hasAttribute('required')) {
				var savea = row.querySelector('[data-action="clear"]');
				savea.addEventListener("click",
					function (e) {
						var days = self.table.querySelectorAll('td');
						for (var i = 0; i < days.length; i++) {
							if (days[i].classList.contains('selected')) {
								days[i].classList.remove('selected');
								break;
							}
						}
						self.inputField.setAttribute('data-alt-value', "0000-00-00 00:00:00");
						self.inputField.setAttribute('value', '');
						self.inputField.value = '';
					});
			}

			this._nav_now = hh(JoomlaCalLocale.today, '', 0, 'a', '', 'js-btn btn btn-today', {"data-action": "today"});
			var todaya = row.querySelector('[data-action="today"]');
			todaya.addEventListener('click', function () {
				self.date.setLocalDateOnly('gregorian', new Date());                                  // TODAY
				self.dateClicked = true;
				self.callHandler();
				self.close();
			});
		}

		this._nav_exit = hh(JoomlaCalLocale.exit, '', 999, 'a', '', 'js-btn btn btn-exit', {"data-action": "exit"});
		var exita = row.querySelector('[data-action="exit"]');
		exita.addEventListener('click', function () { self.close(); });

		this.processCalendar();
	};

	/** Method to append numbers to the calendar table */
	JoomlaCalendar.prototype.processCalendar = function () {
		this.table.style.visibility = "hidden";

		var firstDayOfWeek = this.params.firstDayOfWeek,
			date  = this.date,
			today = new Date(),
			TY    = today.getLocalFullYear(this.params.dateType),
			TM    = today.getLocalMonth(this.params.dateType),
			TD    = today.getLocalDate(this.params.dateType),
			year  = date.getOtherFullYear(this.params.dateType),
			hrs   = date.getHours(),
			mins  = date.getMinutes(),
			secs  = date.getSeconds(),
			t12   = !this.params.time24;

		if (year < this.params.minYear) {                                                                   // Check min,max year
			year = this.params.minYear;
			date.getOtherFullYear(this.params.dateType, year);
		} else if (year > this.params.maxYear) {
			year = this.params.maxYear;
			date.getOtherFullYear(this.params.dateType, year);
		}

		this.params.firstDayOfWeek = firstDayOfWeek;
		this.date = new Date(date);

		var month = date.getLocalMonth(this.params.dateType);
		var mday  = date.getLocalDate(this.params.dateType);

		// Compute the first day that would actually be displayed in the calendar, even if it's from the previous month.
		date.setLocalDate(this.params.dateType, 1);
		var day1 = (date.getLocalDay(this.params.dateType) - this.params.firstDayOfWeek) % 7;

		if (day1 < 0) {
			day1 += 7;
		}

		date.setLocalDate(this.params.dateType, - day1);
		date.setLocalDate(this.params.dateType, date.getLocalDate(this.params.dateType) + 1);

		var row = this.tbody.firstChild,
			ar_days = this.ar_days = new Array(),
			weekend = JoomlaCalLocale.weekend,
			monthDays = parseInt(date.getLocalWeekDays(this.params.dateType));

		/** Fill the table **/
		for (var i = 0; i < monthDays; ++i, row = row.nextSibling) {
			var cell = row.firstChild;
			if (this.params.weekNumbers) {
				cell.className = "day wn";
				cell.innerHTML = date.getLocalWeekNumber(this.params.dateType); //date.convertNumbers();
				cell = cell.nextSibling;
			}

			row.className = this.params.weekNumbers ? "daysrow wk" : "daysrow";
			var hasdays = false, iday,
				dpos = ar_days[i] = [],
				totalDays = monthDays + 1;

			for (var j = 0; j < totalDays; ++j, cell = cell.nextSibling, date.setLocalDate(this.params.dateType, iday + 1)) {
				cell.className = "day";
				cell.style['textAlign'] = 'center';
				iday = date.getLocalDate(this.params.dateType);
				var wday = date.getLocalDay(this.params.dateType);
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
				cell.innerHTML = this.params.debug ? iday : Date.convertNumbers(iday);                     // translated day number for each cell
				if (!cell.disabled) {
					cell.caldate = new Date(date);
					if (current_month && iday == mday) {
						cell.className += " selected";
						this.currentDateEl = cell;
					}
					if (date.getLocalFullYear(this.params.dateType) == TY && date.getLocalMonth(this.params.dateType) == TM && iday == TD) {
						cell.className += " today";
					}
					if (weekend.indexOf(wday) != -1)
						cell.className += " weekend";
				}
			}
			if (!(hasdays || this.params.showsOthers)) {
				row.style.display = 'none';
				row.className = "emptyrow";
			} else {
				row.style.display = '';
			}
		}

		/* Set the time */
		if (this.params.showsTime) {
			if (hrs > 12 && t12) {
				hrs -= 12;
			}

			hrs = (hrs < 10) ? "0" + hrs : hrs;
			mins = (mins < 10) ? "0" + mins : mins;

			var hoursEl = this.table.querySelector('.time-hours'),
				minsEl = this.table.querySelector('.time-minutes');

			/* remove the selected class  for the hours*/
			this.resetSelected(hoursEl);
			hoursEl.value = hrs;

			/* remove the selected class  for the minutes*/
			this.resetSelected(minsEl);
			minsEl.value = mins;

			if (!this.params.time24)
			{
				var dateAlt = new Date(this.inputField.getAttribute('data-alt-value')),
					ampmEl = this.table.querySelector('.time-ampm'),
					hrsAlt = dateAlt.getHours();

				if (hrsAlt > 12) {
					/* remove the selected class  for the am-pm*/
					this.resetSelected(ampmEl);
					ampmEl.value = 'pm';
				}
			}
		}

		if (!this.params.compressedHeader) {
			this._nav_month.getElementsByTagName('span')[0].innerHTML = this.params.debug ? month + ' ' + JoomlaCalLocale.months[month] : JoomlaCalLocale.months[month];
			this.title.getElementsByTagName('span')[0].innerHTML = this.params.debug ? year + ' ' +  Date.convertNumbers(year.toString()) : Date.convertNumbers(year.toString());
		} else {
			var tmpYear = Date.convertNumbers(year.toString());
			this._nav_month.getElementsByTagName('span')[0].innerHTML = !this.params.monthBefore  ? JoomlaCalLocale.months[month] + ' - ' + tmpYear : tmpYear + ' - ' + JoomlaCalLocale.months[month] ;
		}
		this.table.style.visibility = "visible";
	};

	/** Method to listen for the click event on the input button. **/
	JoomlaCalendar.prototype._bindEvents = function () {
		var self = this;
		this.inputField.addEventListener('focus', function() {
			self.show();
		}, true);
		this.inputField.addEventListener('blur', function(event) {
			if (event.relatedTarget != null && (event.relatedTarget.classList.contains('time-hours') || event.relatedTarget.classList.contains('time-minutes') || event.relatedTarget.classList.contains('time-ampm'))) {
				return;
			}
			var elem = event.target;
			while (elem.parentNode) {
				elem = elem.parentNode;
				if (elem.classList.contains('field-calendar')) {
					return;
				}
			}
			self.close();
		}, true);
		this.button.addEventListener('click', function() {
			self.show();
		}, false);
	};

	/** Helpers **/
	var stopCalEvent = function (ev) { ev || (ev = window.event);  ev.preventDefault(); ev.stopPropagation(); return false; };
	var createElement = function (type, parent) { var el = null; el = document.createElement(type); if (typeof parent != "undefined") { parent.appendChild(el); } return el; };
	var isInt = function (input) { return !isNaN(input) && (function(x) { return (x | 0) === x; })(parseFloat(input)) };
	var getBoundary = function (input, type) { var date = new Date(); var y = date.getLocalFullYear(type); return y + input; };
	/**
	 * IE8 polyfill for indexOf()
	 */
	if (!Array.prototype.indexOf) {
		Array.prototype.indexOf = function(elt) {
			var len = this.length >>> 0,
				from = Number(arguments[1]) || 0;

			from = (from < 0) ? Math.ceil(from) : Math.floor(from);

			if (from < 0) {
				from += len;
			}

			for (; from < len; from++) {
				if (from in this && this[from] === elt) {
					return from;
				}
			}
			return -1;
		};
	}

	/** Method to get the active calendar element through any descendant element. */
	JoomlaCalendar.getCalObject = function(element) {
		if (!element) {
			return false;
		}
		while (element.parentNode) {
			element = element.parentNode;
			if (element.classList.contains('field-calendar')) {
				return element;
			}
		}
		return false;
	};

	/** Method to change input values with the data-alt-value values. **/
	JoomlaCalendar.prototype.setAltValue = function() {
		var input = this.inputField;

		if (input.getAttribute("data-alt-value")) {
			input.value = input.getAttribute('data-alt-value');
		} else {
			input.value = '';
		}
	};

	/**
	 * Init the Calendars on the page
	 *
	 * @param {String}      className  The field class name (required)
	 * @param {HTMLElement} container  The field container (optional)
	 */
	JoomlaCalendar.init = function (className, container) {
		var elements, i, instance;

		elements = (container || document).querySelectorAll(className);

		// Fall back for translation strings
		window.JoomlaCalLocale           = window.JoomlaCalLocale ? JoomlaCalLocale : {};
		JoomlaCalLocale.today            = JoomlaCalLocale.today ? JoomlaCalLocale.today : 'today';
		JoomlaCalLocale.weekend          = JoomlaCalLocale.weekend ? JoomlaCalLocale.weekend : [0, 6];
		JoomlaCalLocale.localLangNumbers = JoomlaCalLocale.localLangNumbers ? JoomlaCalLocale.localLangNumbers : [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
		JoomlaCalLocale.wk               = JoomlaCalLocale.wk ? JoomlaCalLocale.wk : 'wk';
		JoomlaCalLocale.AM               = JoomlaCalLocale.AM ? JoomlaCalLocale.AM : 'AM';
		JoomlaCalLocale.PM               = JoomlaCalLocale.PM ? JoomlaCalLocale.PM : 'PM';
		JoomlaCalLocale.am               = JoomlaCalLocale.am ? JoomlaCalLocale.am : 'am';
		JoomlaCalLocale.pm               = JoomlaCalLocale.pm ? JoomlaCalLocale.pm : 'pm';
		JoomlaCalLocale.dateType         = JoomlaCalLocale.dateType ? JoomlaCalLocale.dateType : 'gregorian';
		JoomlaCalLocale.time             = JoomlaCalLocale.time ? JoomlaCalLocale.time : 'time';
		JoomlaCalLocale.days             = JoomlaCalLocale.days ? JoomlaCalLocale.days : '["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]';
		JoomlaCalLocale.shortDays        = JoomlaCalLocale.shortDays ? JoomlaCalLocale.shortDays : ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
		JoomlaCalLocale.months           = JoomlaCalLocale.months ? JoomlaCalLocale.months : ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		JoomlaCalLocale.shortMonths      = JoomlaCalLocale.shortMonths ? JoomlaCalLocale.shortMonths : ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		JoomlaCalLocale.minYear          = JoomlaCalLocale.minYear ? JoomlaCalLocale.minYear : 1900;
		JoomlaCalLocale.maxYear          = JoomlaCalLocale.maxYear ? JoomlaCalLocale.maxYear : 2100;
		JoomlaCalLocale.exit             = JoomlaCalLocale.exit ? JoomlaCalLocale.exit : 'Cancel';
		JoomlaCalLocale.clear            = JoomlaCalLocale.clear ? JoomlaCalLocale.clear : 'Clear';

		for (i = 0; i < elements.length; i++) {
			var element  = elements[i];
			instance = element._joomlaCalendar;
			if (!instance) {
				instance = new JoomlaCalendar(element);
			}
		}

		var onSubmit = function () {
			var elements = (container || document).querySelectorAll(".field-calendar");

			for (i = 0; i < elements.length; i++) {
				var element  = elements[i],
				    inputs   = element.getElementsByTagName('input')[0],
				    buttton  = element.getElementsByTagName('button')[0],
				    instance = element._joomlaCalendar;

				if (instance) {
					instance.setAltValue();
				}
			}
		};

		if (elements[0] && elements[0].getElementsByTagName('input')[0] && elements[0].getElementsByTagName('input')[0].form) {
			elements[0].getElementsByTagName('input')[0].form.addEventListener('submit', onSubmit);
		}
	};

	window.JoomlaCalendar = JoomlaCalendar;

	/** Instantiate all the calendar fields when the document is ready */
	document.addEventListener("DOMContentLoaded", function() {
		JoomlaCalendar.init(".field-calendar");

		window.jQuery && jQuery(document).on("subform-row-add", function (event, row) {
			JoomlaCalendar.init(".field-calendar", row);
		});
	});
})(window, document);
