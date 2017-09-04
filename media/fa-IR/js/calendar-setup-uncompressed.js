Calendar.setup = function(C) {
    function F(J, K) {
        if (typeof C[J] == "undefined") {
            C[J] = K
        }
    }
    F("inputField", "date_calendar");
    F("displayArea", null);
    F("button", "date_btn");
    F("eventName", "click");
    F("ifFormat", "%Y-%m-%d %H:%M:%S");
    F("daFormat", "%Y/%m/%d");
    F("singleClick", true);
    F("disableFunc", null);
    F("dateStatusFunc", C.disableFunc);
    F("dateText", null);
    F("firstDay", null);
    F("align", "Br");
    F("range", [1000, 3000]);
    F("weekNumbers", true);
    F("flat", null);
    F("flatCallback", null);
    F("onSelect", null);
    F("onClose", null);
    F("onUpdate", null);
    F("date", null);
    F("showsTime", true);
    F("timeFormat", "24");
    F("electric", true);
    F("step", 2);
    F("position", null);
    F("showOthers", true);
    F("multiple", null);
    F("dateType", "jalali");
    F("ifDateType", null);
    F("langNumbers", false);
    F("autoShowOnFocus", false);
    var D = ["inputField", "displayArea", "button"];
    for (var E in D) {
        if (typeof C[D[E]] == "string") {
            C[D[E]] = document.getElementById(C[D[E]])
        }
    }
    if (!(C.flat || C.multiple || C.inputField || C.displayArea || C.button)) {
        alert("Calendar.setup:\n Nothing to setup (no fields found). Please check your code");
        return false
    }
    var dateEl = C.inputField || C.displayArea;
    var dateFmt = C.inputField ? C.ifFormat : C.daFormat;
    if (dateEl && parseInt(dateEl.value) != 0 && dateEl.value != '') {
        var date = Date.parseDate(dateEl.value || dateEl.innerHTML, dateFmt, 'gregorian');
        C.inputField.value = date.print(C.ifFormat, 'jalali', C.langNumbers);
    } else {
        C.date = new Date();
    }
    jQuery(document).ready(function() {
        jQuery(C.inputField.form).on('submit', function() {
            var dateEl = C.inputField || C.displayArea;
            var dateFmt = C.inputField ? C.ifFormat : C.daFormat;
            if (dateEl && parseInt(dateEl.value) != 0 && dateEl.value != '') {
                var date = Date.parseDate(dateEl.value || dateEl.innerHTML, dateFmt, 'jalali');
                C.inputField.value = date.print(C.ifFormat, 'gregorian', C.langNumbers);
            }
        });
	});

    function H(K) {
        var J = K.params;
        var L = (K.dateClicked || J.electric);
        if (L && J.inputField) {
            J.inputField.value = K.date.print(K.dateFormat, this.params.ifDateType || K.dateType, K.langNumbers);
            if (typeof J.inputField.onchange == "function") {
                J.inputField.onchange()
            }
        }
        if (L && J.displayArea) {
            J.displayArea.innerHTML = K.date.print(J.daFormat, K.dateType, K.langNumbers)
        }
        if (L && typeof J.onUpdate == "function") {
            J.onUpdate(K)
        }
        if (L && J.flat) {
            if (typeof J.flatCallback == "function") {
                J.flatCallback(K)
            }
        }
        if (L && J.singleClick && K.dateClicked) {
            K.callCloseHandler()
        }
    }
    if (C.flat != null) {
        if (typeof C.flat == "string") {
            C.flat = document.getElementById(C.flat)
        }
        if (!C.flat) {
            alert("Calendar.setup:\n Flat specified but can't find parent.");
            return false
        }
        var A = new Calendar(C.firstDay, C.date, C.onSelect || H);
        A.showsOtherMonths = C.showOthers;
        A.showsTime = C.showsTime;
        A.time24 = (C.timeFormat == "24");
        A.params = C;
        A.weekNumbers = C.weekNumbers;
        A.setRange(C.range[0], C.range[1]);
        A.setDateStatusHandler(C.dateStatusFunc);
        A.getDateText = C.dateText;
        A.dateType = C.dateType;
        A.langNumbers = C.langNumbers;
        if (C.ifFormat) {
            A.setDateFormat(C.ifFormat)
        }
        A.create(C.flat);
        if (C.inputField && typeof C.inputField.value == "string") {
            A.parseDate(C.inputField.value, null, C.ifDateType || A.dateType)
        }
        A.show();
        return A
    }
    var A = new Calendar(C.firstDay, C.date, C.onSelect || H, C.onClose || function(J) {
        J.hide()
    });
    A.showsTime = C.showsTime;
    A.time24 = (C.timeFormat == "24");
    A.weekNumbers = C.weekNumbers;
    A.dateType = C.dateType;
    A.langNumbers = C.langNumbers;
    A.showsOtherMonths = C.showOthers;
    A.yearStep = C.step;
    A.setRange(C.range[0], C.range[1]);
    A.params = C;
    A.setDateStatusHandler(C.dateStatusFunc);
    A.getDateText = C.dateText;
    A.setDateFormat(C.inputField ? C.ifFormat : C.daFormat);
    if (C.multiple) {
        A.multiple = {};
        for (var E = C.multiple.length; --E >= 0;) {
            var G = C.multiple[E];
            var B = G.print("%Y%m%d", A.dateType, A.langNumbers);
            A.multiple[B] = G
        }
    }
    var I = C.button || C.displayArea || C.inputField;
    I["on" + C.eventName] = function() {
        if (!A.element) {
            A.create()
        }
        var J = C.inputField || C.displayArea;
        var K = C.inputField ? C.ifDateType || A.dateType : A.dateType;
        if (J && C.date == null) {
            C.date = Date.parseDate(J.value || J.innerHTML, A.dateFormat, K)
        }
        if (C.date) {
            A.setDate(C.date)
        }
        A.refresh();
        if (!C.position) {
            A.showAtElement(C.button || C.displayArea || C.inputField, C.align)
        } else {
            A.showAt(C.position[0], C.position[1])
        }
        return false
    };
    if (C.autoShowOnFocus && C.inputField) {
        C.inputField.onfocus = I["on" + C.eventName]
    }
    return A
};
/* calendar-dateconvert.js*/
JalaliDate = {
    g_days_in_month: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
    j_days_in_month: [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29]
};
JalaliDate.jalaliToGregorian = function(j_y, j_m, j_d) {
    j_y = parseInt(j_y);
    j_m = parseInt(j_m);
    j_d = parseInt(j_d);
    var jy = j_y - 979;
    var jm = j_m - 1;
    var jd = j_d - 1;
    var j_day_no = 365 * jy + parseInt(jy / 33) * 8 + parseInt((jy % 33 + 3) / 4);
    for (var i = 0; i < jm; ++i) {
        j_day_no += JalaliDate.j_days_in_month[i]
    }
    j_day_no += jd;
    var g_day_no = j_day_no + 79;
    var gy = 1600 + 400 * parseInt(g_day_no / 146097);
    g_day_no = g_day_no % 146097;
    var leap = true;
    if (g_day_no >= 36525) {
        g_day_no--;
        gy += 100 * parseInt(g_day_no / 36524);
        g_day_no = g_day_no % 36524;
        if (g_day_no >= 365) {
            g_day_no++
        } else {
            leap = false
        }
    }
    gy += 4 * parseInt(g_day_no / 1461);
    g_day_no %= 1461;
    if (g_day_no >= 366) {
        leap = false;
        g_day_no--;
        gy += parseInt(g_day_no / 365);
        g_day_no = g_day_no % 365
    }
    for (var i = 0; g_day_no >= JalaliDate.g_days_in_month[i] + (i == 1 && leap); i++) {
        g_day_no -= JalaliDate.g_days_in_month[i] + (i == 1 && leap)
    }
    var gm = i + 1;
    var gd = g_day_no + 1;
    return [gy, gm, gd]
};
JalaliDate.checkDate = function(j_y, j_m, j_d) {
    return !(j_y < 0 || j_y > 32767 || j_m < 1 || j_m > 12 || j_d < 1 || j_d > (JalaliDate.j_days_in_month[j_m - 1] + (j_m == 12 && !((j_y - 979) % 33 % 4))))
};
JalaliDate.gregorianToJalali = function(g_y, g_m, g_d) {
    g_y = parseInt(g_y);
    g_m = parseInt(g_m);
    g_d = parseInt(g_d);
    var gy = g_y - 1600;
    var gm = g_m - 1;
    var gd = g_d - 1;
    var g_day_no = 365 * gy + parseInt((gy + 3) / 4) - parseInt((gy + 99) / 100) + parseInt((gy + 399) / 400);
    for (var i = 0; i < gm; ++i) {
        g_day_no += JalaliDate.g_days_in_month[i]
    }
    if (gm > 1 && ((gy % 4 == 0 && gy % 100 != 0) || (gy % 400 == 0))) {
        ++g_day_no
    }
    g_day_no += gd;
    var j_day_no = g_day_no - 79;
    var j_np = parseInt(j_day_no / 12053);
    j_day_no %= 12053;
    var jy = 979 + 33 * j_np + 4 * parseInt(j_day_no / 1461);
    j_day_no %= 1461;
    if (j_day_no >= 366) {
        jy += parseInt((j_day_no - 1) / 365);
        j_day_no = (j_day_no - 1) % 365
    }
    for (var i = 0; i < 11 && j_day_no >= JalaliDate.j_days_in_month[i]; ++i) {
        j_day_no -= JalaliDate.j_days_in_month[i]
    }
    var jm = i + 1;
    var jd = j_day_no + 1;
    return [jy, jm, jd]
};
Date.prototype.setJalaliFullYear = function(y, m, d) {
    var gd = this.getDate();
    var gm = this.getMonth();
    var gy = this.getFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (y < 100) {
        y += 1300
    }
    j[0] = y;
    if (m != undefined) {
        if (m > 11) {
            j[0] += Math.floor(m / 12);
            m = m % 12
        }
        j[1] = m + 1
    }
    if (d != undefined) {
        j[2] = d
    }
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setFullYear(g[0], g[1] - 1, g[2])
};
Date.prototype.setJalaliMonth = function(m, d) {
    var gd = this.getDate();
    var gm = this.getMonth();
    var gy = this.getFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (m > 11) {
        j[0] += math.floor(m / 12);
        m = m % 12
    }
    j[1] = m + 1;
    if (d != undefined) {
        j[2] = d
    }
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setFullYear(g[0], g[1] - 1, g[2])
};
Date.prototype.setJalaliDate = function(d) {
    var gd = this.getDate();
    var gm = this.getMonth();
    var gy = this.getFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setFullYear(g[0], g[1] - 1, g[2])
};
Date.prototype.getJalaliFullYear = function() {
    var gd = this.getDate();
    var gm = this.getMonth();
    var gy = this.getFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[0]
};
Date.prototype.getJalaliMonth = function() {
    var gd = this.getDate();
    var gm = this.getMonth();
    var gy = this.getFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[1] - 1
};
Date.prototype.getJalaliDate = function() {
    var gd = this.getDate();
    var gm = this.getMonth();
    var gy = this.getFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[2]
};
Date.prototype.getJalaliDay = function() {
    var day = this.getDay();
    day = (day + 1) % 7;
    return day
};
Date.prototype.setJalaliUTCFullYear = function(y, m, d) {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (y < 100) {
        y += 1300
    }
    j[0] = y;
    if (m != undefined) {
        if (m > 11) {
            j[0] += Math.floor(m / 12);
            m = m % 12
        }
        j[1] = m + 1
    }
    if (d != undefined) {
        j[2] = d
    }
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setUTCFullYear(g[0], g[1] - 1, g[2])
};
Date.prototype.setJalaliUTCMonth = function(m, d) {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    if (m > 11) {
        j[0] += math.floor(m / 12);
        m = m % 12
    }
    j[1] = m + 1;
    if (d != undefined) {
        j[2] = d
    }
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setUTCFullYear(g[0], g[1] - 1, g[2])
};
Date.prototype.setJalaliUTCDate = function(d) {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    j[2] = d;
    var g = JalaliDate.jalaliToGregorian(j[0], j[1], j[2]);
    return this.setUTCFullYear(g[0], g[1] - 1, g[2])
};
Date.prototype.getJalaliUTCFullYear = function() {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[0]
};
Date.prototype.getJalaliUTCMonth = function() {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[1] - 1
};
Date.prototype.getJalaliUTCDate = function() {
    var gd = this.getUTCDate();
    var gm = this.getUTCMonth();
    var gy = this.getUTCFullYear();
    var j = JalaliDate.gregorianToJalali(gy, gm + 1, gd);
    return j[2]
};
Date.prototype.getJalaliUTCDay = function() {
    var day = this.getUTCDay();
    day = (day + 1) % 7;
    return day
};﻿ /* calendar-lang.js*/
Calendar._DN = new Array("یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنجشنبه", "جمعه", "شنبه", "یکشنبه");
Calendar._SDN = new Array("یک", "دو", "سه", "چهار", "پنج", "جمعه", "شنبه", "یک");
Calendar._FD = 6;
Calendar._MN = new Array("ژانویه", "فوریه", "مارس", "آوریل", "می", "جون", "جولای", "آگوست", "سپتامبر", "اکتبر", "نوامبر", "دسامبر");
Calendar._SMN = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
Calendar._JMN = new Array("فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند");
Calendar._JSMN = new Array("فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند");
Calendar._TT = {};
Calendar._TT.INFO = "درباره تقویم";
Calendar._TT.ABOUT = "JalaliJSCalendar\nCopyright (c) 2008 Ali Farhadi (http://farhadi.ir/)\nDistributed under GNU GPL. See http://gnu.org/licenses/gpl.html for details.\n\nBased on The DHTML Calendar developed by Dynarch.com.\n(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n\nEdited By ParsJoomla TEAM (http://parsjoomla.com) for Joomla CMS!\n\n\nDate selection:\n- Use the \xab, \xbb buttons to select year\n- Use the " + String.fromCharCode(8249) + ", " + String.fromCharCode(8250) + " buttons to select month\n- Hold mouse button on any of the above buttons for faster selection.";
Calendar._TT.ABOUT_TIME = "\n\nTime selection:\n- Click on any of the time parts to increase it\n- or Shift-click to decrease it\n- or click and drag for faster selection.";
Calendar._TT.PREV_YEAR = "سال قبل (hold for menu)";
Calendar._TT.PREV_MONTH = "ماه قبل (hold for menu)";
Calendar._TT.GO_TODAY = "رفتن به امروز";
Calendar._TT.NEXT_MONTH = "ماه بعد (hold for menu)";
Calendar._TT.NEXT_YEAR = "سال بعد (hold for menu)";
Calendar._TT.SEL_DATE = "انتخاب تاریخ";
Calendar._TT.DRAG_TO_MOVE = "Drag to move";
Calendar._TT.PART_TODAY = " (امروز)";
Calendar._TT.DAY_FIRST = "ابتدا %s نمایش داده شود";
Calendar._TT.WEEKEND = "5";
Calendar._TT.CLOSE = "بستن";
Calendar._TT.TODAY = "امروز";
Calendar._TT.TIME_PART = "(Shift-)Click or drag to change value";
Calendar._TT.DEF_DATE_FORMAT = "%Y-%m-%d";
Calendar._TT.TT_DATE_FORMAT = "%A, %e %b";
Calendar._TT.WK = "هفته";
Calendar._TT.TIME = "زمان :";
Calendar._TT.LAM = "ق.ظ.";
Calendar._TT.AM = "ق.ظ.";
Calendar._TT.LPM = "ب.ظ.";
Calendar._TT.PM = "ب.ظ.";
Calendar._NUMBERS = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
Calendar._DIR = "rtl";