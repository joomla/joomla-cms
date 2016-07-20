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