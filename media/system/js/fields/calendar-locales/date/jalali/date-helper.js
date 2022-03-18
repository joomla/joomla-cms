/** BEGIN: DATE OBJECT PATCHES **/
/** Adds the number of days array to the Date object. */
Date.gregorian_MD = [31,28,31,30,31,30,31,31,30,31,30,31];
Date.local_MD     = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

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
		return this.setJalaliDate(d);
	} else {
		return this.setDate(d);
	}
};

/** Sets the month for the current date. */
Date.prototype.setLocalMonth = function (dateType, m, d) {
	if (dateType != 'gregorian') {
		/** Modify to match the current calendar when overriding **/
		return this.setJalaliMonth(m, d);
	} else {
		if (d == undefined) this.getDate();
		return this.setMonth(m);
	}
};

/** Sets the year for the current date. */
Date.prototype.setOtherFullYear = function(dateType, y) {
	if (dateType != 'gregorian') {
		/** Modify to match the current calendar when overriding **/
		var date = new Date(this);
		date.setLocalFullYear(y);
		if (date.getLocalMonth('jalali') != this.getLocalMonth('jalali')) this.setLocalDate('jalali', 29);
		return this.setLocalFullYear('jalali', y);
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
		return this.setJalaliFullYear(y);
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
		return this.getJalaliFullYear();
	} else {
		return this.getFullYear();
	}
};

/** Returns the year for the current date. */
Date.prototype.getLocalFullYear = function (dateType) {
	if (dateType != 'gregorian') {
		/** Modify to match the current calendar when overriding **/
		return this.getJalaliFullYear();
	} else {
		return this.getFullYear();
	}
};

/** Returns the month the date. */
Date.prototype.getLocalMonth = function (dateType) {
	if (dateType != 'gregorian') {
		/** Modify to match the current calendar when overriding **/
		return this.getJalaliMonth();
	} else {
		return this.getMonth();
	}
};

/** Returns the date. */
Date.prototype.getLocalDate = function (dateType) {
	if (dateType != 'gregorian') {
		/** Modify to match the current calendar when overriding **/
		return this.getJalaliDate();
	} else {
		return this.getDate();
	}
};

/** Returns the number of day in the year. */
Date.prototype.getLocalDay = function(dateType) {
	if (dateType  != 'gregorian') {
		return this.getJalaliDay();
	} else {
		return this.getDay();
	}
};

/** Returns the number of days in the current month */
Date.prototype.getLocalMonthDays = function(dateType, month) {
	if (dateType != 'gregorian') {
		/** Modify to match the current calendar when overriding **/
		var year = this.getLocalFullYear('jalali');
		if (typeof month == "undefined") {
			month = this.getLocalMonth('jalali');
		}
		if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
			return 29;
		} else {
			return Date.local_MD[month];
		}
	} else {
		var year = this.getFullYear();
		if (typeof month == "undefined") {
			month = this.getMonth();
		}
		if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
			return 29;
		} else {
			return Date.gregorian_MD[month];
		}
	}
};

/** Returns the week number for the current date. */
Date.prototype.getLocalWeekNumber = function(dateType) {
	if (dateType != 'gregorian') {
		var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
		var DoW = d.getDay();
		d.setDate(d.getDate() - (DoW + 6) % 7 + 3);                                     // Nearest Thu
		var ms = d.valueOf();                                                           // GMT
		d.setMonth(0);
		d.setDate(4);                                                                   // Thu in Week 1
		return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
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
		var now = new Date(this.getOtherFullYear(dateType), this.getLocalMonth(dateType), this.getLocalDate(dateType), 0, 0, 0);
		var then = new Date(this.getOtherFullYear(dateType), 0, 0, 0, 0, 0);
		var time = now - then;
		return Math.floor(time / Date.DAY);
	} else {
		var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
		var then = new Date(this.getFullYear(), 0, 0, 0, 0, 0);
		var time = now - then;
		return Math.floor(time / Date.DAY);
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
		if (Date.dateType != 'gregorian') {
			return Date.local_MD[month];
		} else {
			return Date.gregorian_MD[month];
		}
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
	return JalaliDate.jalaliToGregorian(y, m, d);
};

/** Converts gregorian date to foreign date. */
Date.gregorianToLocalCal = function(y, m, d) {
	/** Modify to match the current calendar when overriding **/
	return JalaliDate.gregorianToJalali(y, m, d);
};

/** Method to convert numbers from local symbols to English numbers. */
Date.numbersToIso = function(str) {
	var i, nums =[0,1,2,3,4,5,6,7,8,9];
	str = str.toString();

	if (Object.prototype.toString.call(JoomlaCalLocale.localLangNumbers) === '[object Array]') {
		for (i = 0; i < nums.length; i++) {
			str = str.replace(new RegExp(JoomlaCalLocale.localLangNumbers[i], 'g'), nums[i]);
		}
	}
	return str;
};
/** INTERFACE METHODS FOR THE CALENDAR PICKER **/
/************* END **************/

/** Prints the date in a string according to the given format. */
Date.prototype.print = function (str, dateType, translate) {
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
	var w = this.getLocalDay(dateType);
	var s = {};
	var hr = this.getHours();
	var pm = (hr >= 12);
	var ir = (pm) ? (hr - 12) : hr;
	var dy = this.getLocalDayOfYear(dateType);
	if (ir == 0)
		ir = 12;
	var min = this.getMinutes();
	var sec = this.getSeconds();
	s["%a"] = JoomlaCalLocale.shortDays[w];                                                     // abbreviated weekday name
	s["%A"] = JoomlaCalLocale.days[w];                                                          // full weekday name
	s["%b"] = JoomlaCalLocale.shortMonths[m];                                                   // abbreviated month name
	s["%B"] = JoomlaCalLocale.months[m];                                                        // full month name
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
	s["%p"] = pm ? JoomlaCalLocale.PM : JoomlaCalLocale.AM;
	s["%P"] = pm ? JoomlaCalLocale.pm : JoomlaCalLocale.am;
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
	if (Object.prototype.toString.call(JoomlaCalLocale.localLangNumbers) === '[object Array]' && translate)
		tmpDate = Date.convertNumbers(tmpDate);

	return tmpDate;
};

Date.parseFieldDate = function(str, fmt, dateType) {
	str = Date.numbersToIso(str);

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
						if (JoomlaCalLocale.months[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
					} else {
						if (JoomlaCalLocale.months[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
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
	if (y != 0 && m != -1 && d != 0)
		return new Date(y, m, d, hr, min, 0);
	y = 0; m = -1; d = 0;
	for (i = 0; i < a.length; ++i) {
		if (a[i].search(/[a-zA-Z]+/) != -1) {
			var t = -1;
			for (j = 0; j < 12; ++j) {
				if (dateType != 'gregorian') {
					if (JoomlaCalLocale.months[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
				} else {
					if (JoomlaCalLocale.months[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
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

/*
 * JalaliJSCalendar - Jalali Extension for Date Object
 * Copyright (c) 2008 Ali Farhadi (http://farhadi.ir/)
 * Released under the terms of the GNU General Public License.
 * See the GPL for details (http://www.gnu.org/licenses/gpl.html).
 *
 * Based on code from http://farsiweb.info
 */

JalaliDate = {
	g_days_in_month: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
	j_days_in_month: [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29]
};

JalaliDate.jalaliToGregorian = function(j_y, j_m, j_d)
{
	j_y = parseInt(j_y);
	j_m = parseInt(j_m);
	j_d = parseInt(j_d);
	var jy = j_y-979;
	var jm = j_m-1;
	var jd = j_d-1;

	var j_day_no = 365*jy + parseInt(jy / 33)*8 + parseInt((jy%33+3) / 4);
	for (var i=0; i < jm; ++i) j_day_no += JalaliDate.j_days_in_month[i];

	j_day_no += jd;

	var g_day_no = j_day_no+79;

	var gy = 1600 + 400 * parseInt(g_day_no / 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */
	g_day_no = g_day_no % 146097;

	var leap = true;
	if (g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */
	{
		g_day_no--;
		gy += 100*parseInt(g_day_no/  36524); /* 36524 = 365*100 + 100/4 - 100/100 */
		g_day_no = g_day_no % 36524;

		if (g_day_no >= 365)
			g_day_no++;
		else
			leap = false;
	}

	gy += 4*parseInt(g_day_no/ 1461); /* 1461 = 365*4 + 4/4 */
	g_day_no %= 1461;

	if (g_day_no >= 366) {
		leap = false;

		g_day_no--;
		gy += parseInt(g_day_no/ 365);
		g_day_no = g_day_no % 365;
	}

	for (var i = 0; g_day_no >= JalaliDate.g_days_in_month[i] + (i == 1 && leap); i++)
		g_day_no -= JalaliDate.g_days_in_month[i] + (i == 1 && leap);
	var gm = i+1;
	var gd = g_day_no+1;

	return [gy, gm, gd];
};

JalaliDate.checkDate = function(j_y, j_m, j_d)
{
	return !(j_y < 0 || j_y > 32767 || j_m < 1 || j_m > 12 || j_d < 1 || j_d >
	(JalaliDate.j_days_in_month[j_m-1] + (j_m == 12 && !((j_y-979)%33%4))));
};

JalaliDate.gregorianToJalali = function(g_y, g_m, g_d)
{
	g_y = parseInt(g_y);
	g_m = parseInt(g_m);
	g_d = parseInt(g_d);
	var gy = g_y-1600;
	var gm = g_m-1;
	var gd = g_d-1;

	var g_day_no = 365*gy+parseInt((gy+3) / 4)-parseInt((gy+99)/100)+parseInt((gy+399)/400);

	for (var i=0; i < gm; ++i)
		g_day_no += JalaliDate.g_days_in_month[i];
	if (gm>1 && ((gy%4==0 && gy%100!=0) || (gy%400==0)))
	/* leap and after Feb */
		++g_day_no;
	g_day_no += gd;

	var j_day_no = g_day_no-79;

	var j_np = parseInt(j_day_no/ 12053);
	j_day_no %= 12053;

	var jy = 979+33*j_np+4*parseInt(j_day_no/1461);

	j_day_no %= 1461;

	if (j_day_no >= 366) {
		jy += parseInt((j_day_no-1)/ 365);
		j_day_no = (j_day_no-1)%365;
	}

	for (var i = 0; i < 11 && j_day_no >= JalaliDate.j_days_in_month[i]; ++i) {
		j_day_no -= JalaliDate.j_days_in_month[i];
	}
	var jm = i+1;
	var jd = j_day_no+1;


	return [jy, jm, jd];
};

Date.prototype.setJalaliFullYear = function(y, m, d) {
	var gd = this.getDate();
	var gm = this.getMonth();
	var gy = this.getFullYear();
	var j = JalaliDate.gregorianToJalali(gy, gm+1, gd);
	if (y < 100) y += 1300;
	j[0] = y;
	if (m != undefined) {
		if (m > 11) {
			j[0] += Math.floor(m / 12);
			m = m % 12;
		}
		j[1] = m + 1;
	}
	if (d != undefined) j[2] = d;
	var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
	return this.setFullYear(g[0], g[1]-1, g[2]);
};

Date.prototype.setJalaliMonth = function(m, d) {
	var gd = this.getDate();
	var gm = this.getMonth();
	var gy = this.getFullYear();
	var j = JalaliDate.gregorianToJalali(gy, gm+1, gd);
	if (m > 11) {
		j[0] += Math.floor(m / 12);
		m = m % 12;
	}
	j[1] = m+1;
	if (d != undefined) j[2] = d;
	var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
	return this.setFullYear(g[0], g[1]-1, g[2]);
};

Date.prototype.setJalaliDate = function(d) {
	var gd = this.getDate();
	var gm = this.getMonth();
	var gy = this.getFullYear();
	var j = JalaliDate.gregorianToJalali(gy, gm+1, gd);
	j[2] = d;
	var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
	return this.setFullYear(g[0], g[1]-1, g[2]);
};

Date.prototype.getJalaliFullYear = function() {
	var gd = this.getDate();
	var gm = this.getMonth();
	var gy = this.getFullYear();
	var j = JalaliDate.gregorianToJalali(gy, gm+1, gd);
	return j[0];
};

Date.prototype.getJalaliMonth = function() {
	var gd = this.getDate();
	var gm = this.getMonth();
	var gy = this.getFullYear();
	var j = JalaliDate.gregorianToJalali(gy, gm+1, gd);
	return j[1]-1;
};

Date.prototype.getJalaliDate = function() {
	var gd = this.getDate();
	var gm = this.getMonth();
	var gy = this.getFullYear();
	var j = JalaliDate.gregorianToJalali(gy, gm+1, gd);
	return j[2];
};

Date.prototype.getJalaliDay = function() {
	var day = this.getDay();
	day = (day) % 7;
	return day;
};
