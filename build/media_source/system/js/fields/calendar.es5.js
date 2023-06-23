/**
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
!(function(window, document){
	'use strict';

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

		var self = this;

		this.writable   = true;
		this.hidden     = true;
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
			writable: true,
		};

		// Localisation strings
		var _t = Joomla.Text._;
		this.strings = {
			today: _t('JLIB_HTML_BEHAVIOR_TODAY', 'Today'),
			wk: _t('JLIB_HTML_BEHAVIOR_WK', 'wk'),
			// ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
			days: ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'],
			// ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
			shortDays: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
			// ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
			months: ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'],
			// ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
			shortMonths: ['JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT',
				'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT',],
			am: _t('JLIB_HTML_BEHAVIOR_AM', 'am'),
			pm: _t('JLIB_HTML_BEHAVIOR_PM', 'pm'),
			exit: _t('JCLOSE', 'Close'),
			clear: _t('JCLEAR', 'Clear')
		};

		// Translate lists of Days, Months
		this.strings.days = this.strings.days.map(function (c){
			return _t(c);
		});
		this.strings.shortDays = this.strings.shortDays.map(function (c){
			return _t(c);
		});
		this.strings.months = this.strings.months.map(function (c){
			return _t(c);
		});
		this.strings.shortMonths = this.strings.shortMonths.map(function (c){
			return _t(c);
		});

		var btn = this.button,
			instanceParams = {
				inputField      : this.inputField,
				dateType        : btn.dataset.dateType || 'gregorian',
				direction       : document.dir ? document.dir : document.getElementsByTagName("html")[0].getAttribute("dir"),
				firstDayOfWeek  : btn.dataset.firstday ? parseInt(btn.dataset.firstday, 10) : 0,
				dateFormat      : btn.dataset.dateFormat || "%Y-%m-%d %H:%M:%S",
				weekend         : [0,6],
				minYear         : 1000,
				maxYear         : 2100,
				time24          : true,
				showsOthers     : true,
				showsTime       : true,
				weekNumbers     : true,
				showsTodayBtn   : true,
				compressedHeader: false,
			};

		if ('showOthers' in btn.dataset) {
			instanceParams.showsOthers = parseInt(btn.dataset.showOthers, 10) === 1;
		}

		if ('weekNumbers' in btn.dataset) {
			instanceParams.weekNumbers = parseInt(btn.dataset.weekNumbers, 10) === 1;
		}

		if ('onlyMonthsNav' in btn.dataset) {
			instanceParams.compressedHeader = parseInt(btn.dataset.onlyMonthsNav, 10) === 1;
		}

		if ('time24' in btn.dataset) {
			instanceParams.time24 = parseInt(btn.dataset.time24 , 10) === 24;
		}

		if ('showTime' in btn.dataset) {
			instanceParams.showsTime = parseInt(btn.dataset.showTime, 10) === 1;
		}

		if ('todayBtn' in btn.dataset) {
			instanceParams.showsTodayBtn = parseInt(btn.dataset.todayBtn, 10) === 1;
		}

		// Merge the parameters
		for (var param in instanceParams) {
			this.params[param] = instanceParams[param];
		}

		// Evaluate the min year
		if (btn.dataset.minYear) {
			self.params.minYear = getBoundary(parseInt(btn.dataset.minYear, 10), self.params.dateType);
		}
		// Evaluate the max year
		if (btn.dataset.maxYear) {
			self.params.maxYear = getBoundary(parseInt(btn.dataset.maxYear, 10), self.params.dateType);
		}
		// Evaluate the weekend days
		if (btn.dataset.weekend) {
			self.params.weekend = btn.dataset.weekend.split(',').map(function(item) { return parseInt(item, 10); });
		}

		// Legacy thing, days for RTL is reversed
		if (this.params.direction === 'rtl') {
			this.strings.days = this.strings.days.reverse();
			this.strings.shortDays = this.strings.shortDays.reverse();
		}

		// Other calendar may have a different order for months
		this.strings.months = Date.monthsToLocalOrder(this.strings.months, this.params.dateType);
		this.strings.shortMonths = Date.monthsToLocalOrder(this.strings.shortMonths, this.params.dateType);

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
		var inputAltValueDate = Date.parseFieldDate(this.inputField.getAttribute('data-alt-value'), this.params.dateFormat, 'gregorian', this.strings);

		if (this.inputField.value !== '') {
			this.date = inputAltValueDate;
			this.inputField.value = inputAltValueDate.print(this.params.dateFormat, this.params.dateType, true, this.strings);
		} else {
			this.date = new Date();
		}
	};

	/** Removes the calendar object from the DOM tree and destroys it and then recreates it. */
	JoomlaCalendar.prototype.recreate = function () {
		var element = this.element, el = element.querySelector('.js-calendar');
		if (el) {
			element._joomlaCalendar = null;
			el.parentNode.removeChild(el);
			new JoomlaCalendar(element);
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
		this.inputField.setAttribute('data-alt-value', this.date.print(this.params.dateFormat, 'gregorian', false, this.strings));

		if (this.inputField.getAttribute('data-alt-value') && this.inputField.getAttribute('data-alt-value') !== '0000-00-00 00:00:00') {
			this.inputField.value = this.date.print(this.params.dateFormat, this.params.dateType, true, this.strings);
			if (this.params.dateType !== 'gregorian') {
				this.inputField.setAttribute('data-local-value', this.date.print(this.params.dateFormat, this.params.dateType, true, this.strings));
			}
		}
		this.inputField.value = this.date.print(this.params.dateFormat, this.params.dateType, true, this.strings);

		if (this.dateClicked && typeof this.params.onUpdate === "function") {
			this.params.onUpdate(this);
		}

		this.inputField.dispatchEvent(new CustomEvent('change', {bubbles: true, cancelable: true}));

		if (this.dateClicked) {
			this.close();
		} else {
			this.processCalendar();
		}
	};

	/** Method to close/hide the calendar */
	JoomlaCalendar.prototype.close = function () {
		this.hide();
	};

	/** Method to show the calendar. */
	JoomlaCalendar.prototype.show = function () {
		this.checkInputs();
		this.inputField.focus();
		this.dropdownElement.classList.add('open');
		this.dropdownElement.removeAttribute('hidden');
		this.hidden = false;

		document.addEventListener("keydown", this._calKeyEvent, true);
		document.addEventListener("keypress", this._calKeyEvent, true);
		document.addEventListener("mousedown", this._documentClick, true);

		/** Move the calendar to top position if it doesn't fit below. */
		var containerTmp = this.element.querySelector('.js-calendar');

		if (window.innerHeight < containerTmp.getBoundingClientRect().bottom + 20) {
			containerTmp.style.marginTop = - (containerTmp.getBoundingClientRect().height + this.inputField.getBoundingClientRect().height) + "px";
		}

		this.processCalendar();
	};

	/** Method to hide the calendar. */
	JoomlaCalendar.prototype.hide = function () {
		document.removeEventListener("keydown", this._calKeyEvent, true);
		document.removeEventListener("keypress", this._calKeyEvent, true);
		document.removeEventListener("mousedown", this._documentClick, true);

		this.dropdownElement.classList.remove('open');
		this.dropdownElement.setAttribute('hidden', '');
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

		if (target && target.hasAttribute('data-action')) {
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

		if (typeof el.navtype === "undefined" || el.navtype !== 300) {
			if (el.navtype === 50) { el._current = el.innerHTML; }

			if (target === el || target.parentNode === el) { self.cellClick(el, ev); }

			var mon = null;
			if (typeof el.month !== "undefined") {
				mon = el;
			}
			if (typeof el.parentNode.month !== "undefined") {
				mon = el.parentNode;
			}
			var date = null;
			if (mon) {
				date = new Date(self.date);
				if (mon.month !== date.getLocalMonth(self.params.dateType)) {
					date.setLocalMonth(self.params.dateType, mon.month);
					self.setDate(date);
					self.dateClicked = false;
					this.callHandler();
				}
			} else {
				var year = null;
				if (typeof el.year !== "undefined") {
					year = target;
				}
				if (typeof el.parentNode.year !== "undefined") {
					year = target.parentNode;
				}
				if (year) {
					date = new Date(self.date);
					if (year.year !== date.getLocalFullYear(self.params.dateType)) {
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

		if (typeof el.navtype === "undefined") {
			if (self.currentDateEl) {
				el.classList.add("selected");
				self.currentDateEl = el.caldate;
				closing = (self.currentDateEl === el.caldate);
				if (!closing) {
					self.currentDateEl = el.caldate;
				}
			}
			self.date.setLocalDateOnly('gregorian', el.caldate);
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
			} else if (el.navtype === 0) {
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
			K = ev.keyCode;

		// Get value from input
		if (ev.target === this.inputField && (K === 13 || K === 9)) {
			this.close();
		}

		if (self.params.direction === 'rtl') {
			if (K === 37) {
				K = 39;
			} else if (K === 39) {
				K = 37;
			}
		}

		if (K === 32) {                                // KEY Shift + space (now)
			if (ev.shiftKey) {
				ev.preventDefault();
				this.cellClick(self._nav_now, ev);
				self.close();
			}
		}
		if (K === 27) {                                // KEY esc (close);
			this.close();
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
		if (ev.target === this.inputField && !(K>48 || K<57 || K===186 || K===189 || K===190 || K===32)) {
			return stopCalEvent(ev);
		}
	};

	/** Method to create the html structure of the calendar */
	JoomlaCalendar.prototype._create = function () {
		var self   = this,
			parent = this.element,
			table  = createElement("table"),
			div    = createElement("div");

		this.table = table;
		table.className = 'table';
		table.style.marginBottom = 0;

		this.dropdownElement = div;
		parent.appendChild(div);

		if (this.params.direction) {
			div.style.direction = this.params.direction;
		}

		div.className = 'js-calendar';
		div.style.position = "absolute";
		div.style.boxShadow = "0 0 70px 0 rgba(0,0,0,0.67)";
		div.style.minWidth = this.inputField.width;
		div.style.padding = '0';
		div.setAttribute('hidden', '');
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
			hh   = function (text, cs, navtype, node, styles, classes, attributes) {
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
				for (var key in attributes) {
					cell.setAttribute(key, attributes[key]);
				}
				if (navtype !== 0 && Math.abs(navtype) <= 2) {
					cell.className += " nav";
				}

				if (cs) {
					cell.addEventListener("mousedown", self._dayMouseDown, true);
				}

				cell.calendar = cal;
				cell.navtype = navtype;
				if (navtype !== 0 && Math.abs(navtype) <= 2) {
					cell.innerHTML = Joomla.sanitizeHtml("<a " + classes + " style='display:inline;padding:2px 6px;cursor:pointer;text-decoration:none;' unselectable='on'>" + text + "</a>");
				} else {
					cell.innerHTML = cs ? Joomla.sanitizeHtml("<div unselectable='on'" + classes + ">" + text + "</div>") : Joomla.sanitizeHtml(text);
					if (!cs && classes) {
						cell.className = classes;
					}
				}
				return cell;
			};

		if (this.params.compressedHeader === false) {                                                        // Head - year
			row = createElement("tr", thead);
			row.className = "calendar-head-row";
			this._nav_py = hh("&lsaquo;", 1, -2, '', {"text-align": "center", "font-size": "18px", "line-height": "18px"}, 'js-btn btn-prev-year');                   // Previous year button
			this.title = hh('<div style="text-align:center;font-size:18px"><span></span></div>', this.params.weekNumbers ? 6 : 5, 300);
			this.title.className = "title";
			this._nav_ny = hh(" &rsaquo;", 1, 2, '', {"text-align": "center", "font-size": "18px", "line-height": "18px"}, 'js-btn btn-next-year');                   // Next year button
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
			cell.textContent = self.strings.wk;
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
			weekend = this.params.weekend;

		for (var i = 0; i < 7; ++i) {
			var realday = (i + fdow) % 7;
			cell.classList.add("day-name");
			this.params.weekNumbers ? cell.classList.add('day-name-week') : '';

			if (i) {
				cell.calendar = self;
				cell.fdow = realday;
			}
			if (weekend.indexOf(weekend) !== -1) {
				cell.classList.add("weekend");
			}

			cell.textContent = this.strings.shortDays[(i + fdow) % 7];
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
			cell.innerHTML = " ";

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
						if (i === selected) {
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
				cell.colSpan = self.params.weekNumbers ? 2 : 3;

				if (t12) {
					var selAttr = true,
						altDate = Date.parseFieldDate(self.inputField.getAttribute('data-alt-value'), self.params.dateFormat, 'gregorian', self.strings);
					pm = (altDate.getHours() >= 12);

					var part = createElement("select", cell);
					part.className = "time-ampm";
					part.style.width = '100%';
					part.options.add(new Option(self.strings.pm, "pm", pm ? selAttr : '', pm ? selAttr : ''));
					part.options.add(new Option(self.strings.am, "am", pm ? '' : selAttr, pm ? '' : selAttr));
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

		row = createElement("div", this.wrapper);
		row.className = "buttons-wrapper btn-group";

		this._nav_clear = hh(this.strings.clear, '', 100, 'button', '', 'js-btn btn btn-clear', {"type": "button", "data-action": "clear"});

			var cleara = row.querySelector('[data-action="clear"]');
			cleara.addEventListener("click", function (e) {
				e.preventDefault();
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
				self.inputField.dispatchEvent(new CustomEvent('change', {bubbles: true, cancelable: true}));
			});

		if (this.params.showsTodayBtn) {
			this._nav_now = hh(this.strings.today, '', 0, 'button', '', 'js-btn btn btn-today', {"type": "button", "data-action": "today"});

			var todaya = this.wrapper.querySelector('[data-action="today"]');
			todaya.addEventListener('click', function (e) {
				e.preventDefault();
				self.date.setLocalDateOnly('gregorian', new Date());                                  // TODAY
				self.dateClicked = true;
				self.callHandler();
				self.close();
			});
		}

		this._nav_exit = hh(this.strings.exit, '', 999, 'button', '', 'js-btn btn btn-exit', {"type": "button", "data-action": "exit"});
		var exita = this.wrapper.querySelector('[data-action="exit"]');
		exita.addEventListener('click', function (e) {
			e.preventDefault();
			if (!self.dateClicked) {
				if (self.inputField.value) {
					if (self.params.dateType !== 'gregorian') {
						self.inputField.setAttribute('data-local-value', self.inputField.value);
					}
					if (typeof self.dateClicked === 'undefined') {
						// value needs to be validated
						self.inputField.setAttribute('data-alt-value', Date.parseFieldDate(self.inputField.value, self.params.dateFormat, self.params.dateType, self.strings)
							.print(self.params.dateFormat, 'gregorian', false, self.strings));
					} else {
						self.inputField.setAttribute('data-alt-value', self.date.print(self.params.dateFormat, 'gregorian', false, self.strings));
					}
				} else {
					self.inputField.setAttribute('data-alt-value', '0000-00-00 00:00:00');
				}
				self.date = Date.parseFieldDate(self.inputField.getAttribute('data-alt-value'), self.params.dateFormat, self.params.dateType, self.strings);
			}
			self.close();
		});

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
			date.setOtherFullYear(this.params.dateType, year);
		} else if (year > this.params.maxYear) {
			year = this.params.maxYear;
			date.setOtherFullYear(this.params.dateType, year);
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
			weekend = this.params.weekend,
			monthDays = parseInt(date.getLocalWeekDays(this.params.dateType));

		/** Fill the table **/
		for (var i = 0; i < monthDays; ++i, row = row.nextSibling) {
			var cell = row.firstChild;
			if (this.params.weekNumbers) {
				cell.className = "day wn";
				cell.textContent = date.getLocalWeekNumber(this.params.dateType);
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
				var current_month = (date.getLocalMonth(this.params.dateType) === month);
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
				cell.textContent = this.params.debug ? iday : Date.convertNumbers(iday); // translated day number for each cell
				if (!cell.disabled) {
					cell.caldate = new Date(date);
					if (current_month && iday === mday) {
						cell.className += " selected";
						this.currentDateEl = cell;
					}
					if (date.getLocalFullYear(this.params.dateType) === TY && date.getLocalMonth(this.params.dateType) === TM && iday === TD) {
						cell.className += " today";
					}
					if (weekend.indexOf(wday) !== -1)
						cell.className += " weekend";
				}
			}
			if (!(hasdays || this.params.showsOthers)) {
				row.classList.add('hidden');
				row.setAttribute('hidden', '');
				row.className = "emptyrow";
			} else {
				row.classList.remove('hidden');
				row.removeAttribute('hidden', '');
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
			if (!this.params.time24)
			{
				hoursEl.value = (hrs == "00") ? "12" : hrs;
			}
			else
			{
				hoursEl.value = hrs;
			}

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
			this._nav_month.getElementsByTagName('span')[0].textContent = this.params.debug ? month + ' ' + this.strings.months[month] : this.strings.months[month];
			this.title.getElementsByTagName('span')[0].textContent = this.params.debug ? year + ' ' +  Date.convertNumbers(year.toString()) : Date.convertNumbers(year.toString());
		} else {
			var tmpYear = Date.convertNumbers(year.toString());
			this._nav_month.getElementsByTagName('span')[0].textContent = !this.params.monthBefore  ? this.strings.months[month] + ' - ' + tmpYear : tmpYear + ' - ' + this.strings.months[month] ;
		}
		this.table.style.visibility = "visible";
	};

	/** Method to listen for the click event on the input button. **/
	JoomlaCalendar.prototype._bindEvents = function () {
		var self = this;
		this.inputField.addEventListener('blur', function(event) {
			var calObj = JoomlaCalendar.getCalObject(this)._joomlaCalendar;

			// If calendar is open we will handle the event elsewhere
			if (!calObj.dropdownElement.hasAttribute('hidden')) {
				event.preventDefault();
				return;
			}

			if (calObj) {
				if (calObj.inputField.value) {
					if (typeof calObj.params.dateClicked === 'undefined') {
						calObj.inputField.setAttribute('data-local-value', calObj.inputField.value);

						if (calObj.params.dateType !== 'gregorian') {
							// We need to transform the date for the data-alt-value
							var ndate, date = Date.parseFieldDate(calObj.inputField.value, calObj.params.dateFormat, calObj.params.dateType, calObj.strings);
							ndate = Date.localCalToGregorian(date.getFullYear(), date.getMonth(), date.getDate());
							date.setFullYear(ndate[0]);
							date.setMonth(ndate[1]);
							date.setDate(ndate[2]);
							calObj.inputField.setAttribute('data-alt-value', date.print(calObj.params.dateFormat, 'gregorian', false, calObj.strings));
						} else {
							calObj.inputField.setAttribute('data-alt-value', Date.parseFieldDate(calObj.inputField.value, calObj.params.dateFormat, calObj.params.dateType, calObj.strings)
								.print(calObj.params.dateFormat, 'gregorian', false, calObj.strings));
						}
					} else {
						calObj.inputField.setAttribute('data-alt-value', calObj.date.print(calObj.params.dateFormat, 'gregorian', false, calObj.strings));
					}
				} else {
					calObj.inputField.setAttribute('data-alt-value', '0000-00-00 00:00:00');
				}
				calObj.date = Date.parseFieldDate(calObj.inputField.getAttribute('data-alt-value'), calObj.params.dateFormat, calObj.params.dateType, calObj.strings);
			}

			self.close();
		}, true);
		this.button.addEventListener('click', function() {
			self.show();
		}, false);
	};

	/** Helpers **/
	var stopCalEvent = function (ev) { ev || (ev = window.event);  ev.preventDefault(); ev.stopPropagation(); return false; };
	var createElement = function (type, parent) { var el = null; el = document.createElement(type); if (typeof parent !== "undefined") { parent.appendChild(el); } return el; };
	var isInt = function (input) { return !isNaN(input) && (function(x) { return (x | 0) === x; })(parseFloat(input)) };
	var getBoundary = function (input, type) { var date = new Date(); var y = date.getLocalFullYear(type); return y + input; };

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

	/**
	 * Method to change input values with the data-alt-value values. This method is e.g. being called
	 * by the onSubmit handler of the calendar fields form.
	 */
	JoomlaCalendar.prototype.setAltValue = function() {
		var input = this.inputField;
		if (input.getAttribute('disabled')) return;

		// Set the value to the data-alt-value attribute, but only if it really has a value.
		input.value = (
			input.getAttribute('data-alt-value') && input.getAttribute('data-alt-value') !== '0000-00-00 00:00:00'
			? input.getAttribute('data-alt-value')
			: ''
		);
	};

	/** Method to change the inputs before submit. **/
	JoomlaCalendar.onSubmit = function() {
		Joomla = window.Joomla || {};
		if (!Joomla.calendarProcessed) {
			Joomla.calendarProcessed = true;
			var elements = document.querySelectorAll(".field-calendar");

			for (var i = 0; i < elements.length; i++) {
				var element  = elements[i],
				    instance = element._joomlaCalendar;

				if (instance) {
					instance.setAltValue();
				}
			}
		}
	};

	/**
	 * Init the Calendars on the page
	 *
	 * @param {Node}        element    The element node
	 * @param {HTMLElement} container  The field container (optional)
	 */
	JoomlaCalendar.init = function (element, container) {

		var instance = element._joomlaCalendar;
		if (!instance) {
			new JoomlaCalendar(element);
		} else {
			instance.recreate();
		}

		if (element && element.getElementsByTagName('input')[0] && element.getElementsByTagName('input')[0].form && !element.getElementsByTagName('input')[0].disabled) {
			element.getElementsByTagName('input')[0].form.addEventListener('submit', JoomlaCalendar.onSubmit);
		}
	};

	window.JoomlaCalendar = JoomlaCalendar;

	/**
	 * Instantiate all the calendar fields when the document is ready/updated
	 * @param {Event} event
	 * @private
	 */
	function _initCalendars(event) {
		var elements = event.target.querySelectorAll(".field-calendar");

		for (var i = 0, l = elements.length; i < l; i++) {
			JoomlaCalendar.init(elements[i]);
		}
	}
	document.addEventListener("DOMContentLoaded", _initCalendars);
	document.addEventListener("joomla:updated", _initCalendars);

		/** B/C related code
		 *
		 *  @deprecated   4.0 will be removed in 6.0
		 *                Use JoomlaCalendar.init instead
		 */
		window.Calendar = {};

		/** B/C related code
		 *
		 *  @deprecated   4.0 will be removed in 6.0
		 *                Use JoomlaCalendar.init instead
		 */
		Calendar.setup = function(obj) {

			if (obj.inputField && document.getElementById(obj.inputField)) {
				var element = document.getElementById(obj.inputField),
					cal = element.parentNode.querySelectorAll('button')[0];

				for (var property in obj) {
					if (obj.hasOwnProperty(property)) {
						switch (property) {
							case 'ifFormat':
								if (cal) cal.setAttribute('data-dayformat', obj.ifFormat);
								break;

							case 'firstDay':
								if (cal) cal.setAttribute('data-firstday', parseInt(obj.firstDay));
								break;

							case 'weekNumbers':
								if (cal) cal.setAttribute('data-week-numbers', (obj.weekNumbers === "true" || obj.weekNumbers === true) ? '1' : '0');
								break;

							case 'showOthers':
								if (cal) cal.setAttribute('data-show-others', (obj.showOthers === "true" || obj.showOthers === true) ? '1' : '0');
								break;

							case 'showsTime':
								if (cal) cal.setAttribute('data-show-time', (obj.showsTime === "true" || obj.showsTime === true) ? '1' : '0');
								break;

							case 'timeFormat':
								if (cal) cal.setAttribute('data-time-24', parseInt(obj.timeFormat));
								break;

							case 'displayArea':
							case 'inputField':
							case 'button':
							case 'eventName':
							case 'daFormat':
							case 'disableFunc':
							case 'dateStatusFunc':
							case 'dateTooltipFunc':
							case 'dateText':
							case 'align':
							case 'range':
							case 'flat':
							case 'flatCallback':
							case 'onSelect':
							case 'onClose':
							case 'onUpdate':
							case 'date':
							case 'electric':
							case 'step':
							case 'position':
							case 'cache':
							case 'multiple':
								break;
						}


					}
				}
				JoomlaCalendar.init(element.parentNode.parentNode);
			}
			return null;
		};

})(window, document);
