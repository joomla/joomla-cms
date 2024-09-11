!(function(Date){
	'use strict';

	var localNumbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

	/****************** Gregorian dates ********************/
	/** Constants used for time computations */
	Date.SECOND = 1000 /* milliseconds */;
	Date.MINUTE = 60 * Date.SECOND;
	Date.HOUR   = 60 * Date.MINUTE;
	Date.DAY    = 24 * Date.HOUR;
	Date.WEEK   =  7 * Date.DAY;

	/** MODIFY ONLY THE MARKED PARTS OF THE METHODS **/
	/************ START *************/
	/** INTERFACE METHODS FOR THE CALENDAR PICKER **/

	/********************** *************************/
	/**************** SETTERS ***********************/
	/********************** *************************/

	/** Sets the date for the current date without h/m/s. */
	Date.prototype.setLocalDateOnly = function (dateType, date) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			var tmp = new Date(date);
			this.setDate(1);
			this.setFullYear(tmp.getFullYear());
			this.setMonth(tmp.getMonth());
			this.setDate(tmp.getDate());
		}
	};

	/** Sets the full date for the current date. */
	Date.prototype.setLocalDate = function (dateType, d) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			return this.setDate(d);
		}
	};

	/** Sets the month for the current date. */
	Date.prototype.setLocalMonth = function (dateType, m, d) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			if (d == undefined) this.getDate();
			return this.setMonth(m);
		}
	};

	/** Sets the year for the current date. */
	Date.prototype.setOtherFullYear = function(dateType, y) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			var date = new Date(this);
			date.setFullYear(y);
			if (date.getMonth() != this.getMonth()) this.setDate(28);
			return this.setUTCFullYear(y);
		}
	};

	/** Sets the year for the current date. */
	Date.prototype.setLocalFullYear = function (dateType, y) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			var date = new Date(this);
			date.setFullYear(y);
			if (date.getMonth() != this.getMonth()) this.setDate(28);
			return this.setFullYear(y);
		}
	};

	/********************** *************************/
	/**************** GETTERS ***********************/
	/********************** *************************/

	/** The number of days per week **/
	Date.prototype.getLocalWeekDays = function (dateType, y) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return 6;
		} else {
			return 6; // 7 days per week
		}
	};

	/** Returns the year for the current date. */
	Date.prototype.getOtherFullYear = function (dateType) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			return this.getFullYear();
		}
	};

	/** Returns the year for the current date. */
	Date.prototype.getLocalFullYear = function (dateType) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			return this.getFullYear();
		}
	};

	/** Returns the month the date. */
	Date.prototype.getLocalMonth = function (dateType) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			return this.getMonth();
		}
	};

	/** Returns the date. */
	Date.prototype.getLocalDate = function (dateType) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			return this.getDate();
		}
	};

	/** Returns the number of day in the year. */
	Date.prototype.getLocalDay = function(dateType) {
		if (dateType  != 'gregorian') {
			return '';
		} else {
			return this.getDay();
		}
	};

	/** Returns the number of days in the current month */
	Date.prototype.getLocalMonthDays = function(dateType, month) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			var year = this.getFullYear();
			if (typeof month == "undefined") {
				month = this.getMonth();
			}
			if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
				return 29;
			} else {
				return [31,28,31,30,31,30,31,31,30,31,30,31][month];
			}
		}
	};

	/** Returns the week number for the current date. */
	Date.prototype.getLocalWeekNumber = function(dateType) {
		if (dateType != 'gregorian') {
			/** Modify to match the current calendar when overriding **/
			return '';
		} else {
			var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
			var DoW = d.getDay();
			d.setDate(d.getDate() - (DoW + 6) % 7 + 3);                                     // Nearest Thu
			var ms = d.valueOf();                                                           // GMT
			d.setMonth(0);
			d.setDate(4);                                                                   // Thu in Week 1
			return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
		}
	};

	/** Returns the number of day in the year. */
	Date.prototype.getLocalDayOfYear = function(dateType) {
		if (dateType  != 'gregorian') {
			return '';
		} else {
			var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
			var then = new Date(this.getFullYear(), 0, 0, 0, 0, 0);
			var time = now - then;
			return Math.floor(time / Date.DAY);
		}
	};

	/** Checks date and time equality */
	Date.prototype.equalsTo = function(date) {
		return ((this.getFullYear() == date.getFullYear()) &&
		(this.getMonth() == date.getMonth()) &&
		(this.getDate() == date.getDate()) &&
		(this.getHours() == date.getHours()) &&
		(this.getMinutes() == date.getMinutes()));
	};

	/** Converts foreign date to gregorian date. */
	Date.localCalToGregorian = function(y, m, d) {
		/** Modify to match the current calendar when overriding **/
		return'';
	};

	/** Converts gregorian date to foreign date. */
	Date.gregorianToLocalCal = function(y, m, d) {
		/** Modify to match the current calendar when overriding **/
		return '';
	};

	/** Method to convert numbers to local symbols. */
	Date.convertNumbers = function(str) {
		str = str.toString();

		for (var i = 0, l = localNumbers.length; i < l; i++) {
			str = str.replace(new RegExp(i, 'g'), localNumbers[i]);
		}

		return str;
	};

	/** Translates to english numbers a string. */
	Date.toEnglish = function(str) {
		str = this.toString();
		var nums = [0,1,2,3,4,5,6,7,8,9];
		for (var i = 0; i < 10; i++) {
			str = str.replace(new RegExp(nums[i], 'g'), i);
		}
		return str;
	};

	/** Order the months from Gergorian to the calendar order */
	Date.monthsToLocalOrder = function(months) {
		return months;
	};

	/** INTERFACE METHODS FOR THE CALENDAR PICKER **/
	/************* END **************/

	/** Method to parse a string and return a date. **/
	Date.parseFieldDate = function(str, fmt, dateType, localStrings) {
		if (dateType != 'gregorian')
			str = Date.toEnglish(str);

		var today = new Date();
		var y = 0;
		var m = -1;
		var d = 0;
		var a = str.split(/\W+/);
		var b = fmt.match(/%./g);
		var i = 0, j = 0;
		var hr = 0;
		var min = 0;
		var sec = 0;
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
						if (localStrings.months[j].substring(0, a[i].length).toLowerCase() === a[i].toLowerCase()) {
							m = j;
							break;
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
				case "%S":
					sec = parseInt(a[i], 10);
					break;
			}
		}
		if (isNaN(y)) y = today.getFullYear();
		if (isNaN(m)) m = today.getMonth();
		if (isNaN(d)) d = today.getDate();
		if (isNaN(hr)) hr = today.getHours();
		if (isNaN(min)) min = today.getMinutes();
		if (isNaN(sec)) sec = today.getSeconds();
		if (y != 0 && m != -1 && d != 0)
			return new Date(y, m, d, hr, min, sec);
		y = 0; m = -1; d = 0;
		for (i = 0; i < a.length; ++i) {
			if (a[i].search(/[a-zA-Z]+/) != -1) {
				var t = -1;
				for (j = 0; j < 12; ++j) {
					if (localStrings.months[j].substring(0, a[i].length).toLowerCase() === a[i].toLowerCase()) {
						t = j;
						break;
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
			return new Date(y, m, d, hr, min, sec);
		return today;
	};

	/** Prints the date in a string according to the given format. */
	Date.prototype.print = function (str, dateType, translate, localStrings) {
		/** Handle calendar type **/
		if (typeof dateType !== 'string') str = '';
		if (!dateType) dateType = 'gregorian';

		/** Handle wrong format **/
		if (typeof str !== 'string') str = '';
		if (!str) return '';

		if (this.getLocalDate(dateType) == 'NaN' || !this.getLocalDate(dateType)) return '';
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
		s["%a"] = localStrings.shortDays[w];                                                        // abbreviated weekday name
		s["%A"] = localStrings.days[w];                                                             // full weekday name
		s["%b"] = localStrings.shortMonths[m];                                                      // abbreviated month name
		s["%B"] = localStrings.months[m];                                                           // full month name
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
		s["%p"] = pm ? localStrings.pm.toUpperCase() : localStrings.am.toUpperCase();
		s["%P"] = pm ? localStrings.pm : localStrings.am;
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
		s["%y"] = ('' + y).substring(2);                                                            // year without the century (range 00 to 99)
		s["%Y"] = y;                                                                                // year with the century
		s["%%"] = "%";                                                                              // a literal '%' character

		var re = /%./g;

		var tmpDate = str.replace(re, function (par) { return s[par] || par; });
		if (dateType != 'gregorian' && translate) {
			tmpDate = Date.convertNumbers(tmpDate);
		}

		return tmpDate;
	};
})(Date);
