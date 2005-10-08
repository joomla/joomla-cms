/*  Copyright Mihai Bazon, 2002  |  http://students.infoiasi.ro/~mishoo
 * ---------------------------------------------------------------------
 *
 * The DHTML Calendar, version 0.9.2 "The art of date selection"
 *
 * Details and latest version at:
 * http://students.infoiasi.ro/~mishoo/site/calendar.epl
 *
 * Feel free to use this script under the terms of the GNU Lesser General
 * Public License, as long as you do not remove or alter this notice.
 */

// $Id$

Calendar = function (mondayFirst, dateStr, onSelected, onClose) { this.activeDiv = null; this.currentDateEl = null; this.checkDisabled = null; this.timeout = null; this.onSelected = onSelected || null; this.onClose = onClose || null; this.dragging = false; this.hidden = false; this.minYear = 1970; this.maxYear = 2050; this.dateFormat = Calendar._TT["DEF_DATE_FORMAT"]; this.ttDateFormat = Calendar._TT["TT_DATE_FORMAT"]; this.isPopup = true; this.weekNumbers = true; this.mondayFirst = mondayFirst; this.dateStr = dateStr; this.ar_days = null; this.table = null; this.element = null; this.tbody = null; this.firstdayname = null; this.monthsCombo = null; this.yearsCombo = null; this.hilitedMonth = null; this.activeMonth = null; this.hilitedYear = null; this.activeYear = null; if (!Calendar._DN3) { var ar = new Array(); for (var i = 8; i > 0;) { ar[--i] = Calendar._DN[i].substr(0, 3);}
Calendar._DN3 = ar; ar = new Array(); for (var i = 12; i > 0;) { ar[--i] = Calendar._MN[i].substr(0, 3);}
Calendar._MN3 = ar;}
}; Calendar._C = null; Calendar.is_ie = ( (navigator.userAgent.toLowerCase().indexOf("msie") != -1) &&
(navigator.userAgent.toLowerCase().indexOf("opera") == -1) ); Calendar._DN3 = null; Calendar._MN3 = null; Calendar.getAbsolutePos = function(el) { var r = { x: el.offsetLeft, y: el.offsetTop }; if (el.offsetParent) { var tmp = Calendar.getAbsolutePos(el.offsetParent); r.x += tmp.x; r.y += tmp.y;}
return r;}; Calendar.isRelated = function (el, evt) { var related = evt.relatedTarget; if (!related) { var type = evt.type; if (type == "mouseover") { related = evt.fromElement;} else if (type == "mouseout") { related = evt.toElement;}
}
while (related) { if (related == el) { return true;}
related = related.parentNode;}
return false;}; Calendar.removeClass = function(el, className) { if (!(el && el.className)) { return;}
var cls = el.className.split(" "); var ar = new Array(); for (var i = cls.length; i > 0;) { if (cls[--i] != className) { ar[ar.length] = cls[i];}
}
el.className = ar.join(" ");}; Calendar.addClass = function(el, className) { Calendar.removeClass(el, className); el.className += " " + className;}; Calendar.getElement = function(ev) { if (Calendar.is_ie) { return window.event.srcElement;} else { return ev.currentTarget;}
}; Calendar.getTargetElement = function(ev) { if (Calendar.is_ie) { return window.event.srcElement;} else { return ev.target;}
}; Calendar.stopEvent = function(ev) { if (Calendar.is_ie) { window.event.cancelBubble = true; window.event.returnValue = false;} else { ev.preventDefault(); ev.stopPropagation();}
}; Calendar.addEvent = function(el, evname, func) { if (Calendar.is_ie) { el.attachEvent("on" + evname, func);} else { el.addEventListener(evname, func, true);}
}; Calendar.removeEvent = function(el, evname, func) { if (Calendar.is_ie) { el.detachEvent("on" + evname, func);} else { el.removeEventListener(evname, func, true);}
}; Calendar.createElement = function(type, parent) { var el = null; if (document.createElementNS) { el = document.createElementNS("http://www.w3.org/1999/xhtml", type);} else { el = document.createElement(type);}
if (typeof parent != "undefined") { parent.appendChild(el);}
return el;}; Calendar._add_evs = function(el) { with (Calendar) { addEvent(el, "mouseover", dayMouseOver); addEvent(el, "mousedown", dayMouseDown); addEvent(el, "mouseout", dayMouseOut); if (is_ie) { addEvent(el, "dblclick", dayMouseDblClick); el.setAttribute("unselectable", true);}
}
}; Calendar.findMonth = function(el) { if (typeof el.month != "undefined") { return el;} else if (typeof el.parentNode.month != "undefined") { return el.parentNode;}
return null;}; Calendar.findYear = function(el) { if (typeof el.year != "undefined") { return el;} else if (typeof el.parentNode.year != "undefined") { return el.parentNode;}
return null;}; Calendar.showMonthsCombo = function () { var cal = Calendar._C; if (!cal) { return false;}
var cal = cal; var cd = cal.activeDiv; var mc = cal.monthsCombo; if (cal.hilitedMonth) { Calendar.removeClass(cal.hilitedMonth, "hilite");}
if (cal.activeMonth) { Calendar.removeClass(cal.activeMonth, "active");}
var mon = cal.monthsCombo.getElementsByTagName("div")[cal.date.getMonth()]; Calendar.addClass(mon, "active"); cal.activeMonth = mon; mc.style.left = cd.offsetLeft + "px"; mc.style.top = (cd.offsetTop + cd.offsetHeight) + "px"; mc.style.display = "block";}; Calendar.showYearsCombo = function (fwd) { var cal = Calendar._C; if (!cal) { return false;}
var cal = cal; var cd = cal.activeDiv; var yc = cal.yearsCombo; if (cal.hilitedYear) { Calendar.removeClass(cal.hilitedYear, "hilite");}
if (cal.activeYear) { Calendar.removeClass(cal.activeYear, "active");}
cal.activeYear = null; var Y = cal.date.getFullYear() + (fwd ? 1 : -1); var yr = yc.firstChild; var show = false; for (var i = 12; i > 0; --i) { if (Y >= cal.minYear && Y <= cal.maxYear) { yr.firstChild.data = Y; yr.year = Y; yr.style.display = "block"; show = true;} else { yr.style.display = "none";}
yr = yr.nextSibling; Y += fwd ? 2 : -2;}
if (show) { yc.style.left = cd.offsetLeft + "px"; yc.style.top = (cd.offsetTop + cd.offsetHeight) + "px"; yc.style.display = "block";}
}; Calendar.tableMouseUp = function(ev) { var cal = Calendar._C; if (!cal) { return false;}
if (cal.timeout) { clearTimeout(cal.timeout);}
var el = cal.activeDiv; if (!el) { return false;}
var target = Calendar.getTargetElement(ev); Calendar.removeClass(el, "active"); if (target == el || target.parentNode == el) { Calendar.cellClick(el);}
var mon = Calendar.findMonth(target); var date = null; if (mon) { date = new Date(cal.date); if (mon.month != date.getMonth()) { date.setMonth(mon.month); cal.setDate(date);}
} else { var year = Calendar.findYear(target); if (year) { date = new Date(cal.date); if (year.year != date.getFullYear()) { date.setFullYear(year.year); cal.setDate(date);}
}
}
with (Calendar) { removeEvent(document, "mouseup", tableMouseUp); removeEvent(document, "mouseover", tableMouseOver); removeEvent(document, "mousemove", tableMouseOver); cal._hideCombos(); stopEvent(ev); _C = null;}
}; Calendar.tableMouseOver = function (ev) { var cal = Calendar._C; if (!cal) { return;}
var el = cal.activeDiv; var target = Calendar.getTargetElement(ev); if (target == el || target.parentNode == el) { Calendar.addClass(el, "hilite active"); Calendar.addClass(el.parentNode, "rowhilite");} else { Calendar.removeClass(el, "active"); Calendar.removeClass(el, "hilite"); Calendar.removeClass(el.parentNode, "rowhilite");}
var mon = Calendar.findMonth(target); if (mon) { if (mon.month != cal.date.getMonth()) { if (cal.hilitedMonth) { Calendar.removeClass(cal.hilitedMonth, "hilite");}
Calendar.addClass(mon, "hilite"); cal.hilitedMonth = mon;} else if (cal.hilitedMonth) { Calendar.removeClass(cal.hilitedMonth, "hilite");}
} else { var year = Calendar.findYear(target); if (year) { if (year.year != cal.date.getFullYear()) { if (cal.hilitedYear) { Calendar.removeClass(cal.hilitedYear, "hilite");}
Calendar.addClass(year, "hilite"); cal.hilitedYear = year;} else if (cal.hilitedYear) { Calendar.removeClass(cal.hilitedYear, "hilite");}
}
}
Calendar.stopEvent(ev);}; Calendar.tableMouseDown = function (ev) { if (Calendar.getTargetElement(ev) == Calendar.getElement(ev)) { Calendar.stopEvent(ev);}
}; Calendar.calDragIt = function (ev) { var cal = Calendar._C; if (!(cal && cal.dragging)) { return false;}
var posX; var posY; if (Calendar.is_ie) { posY = window.event.clientY + document.body.scrollTop; posX = window.event.clientX + document.body.scrollLeft;} else { posX = ev.pageX; posY = ev.pageY;}
cal.hideShowCovered(); var st = cal.element.style; st.left = (posX - cal.xOffs) + "px"; st.top = (posY - cal.yOffs) + "px"; Calendar.stopEvent(ev);}; Calendar.calDragEnd = function (ev) { var cal = Calendar._C; if (!cal) { return false;}
cal.dragging = false; with (Calendar) { removeEvent(document, "mousemove", calDragIt); removeEvent(document, "mouseover", stopEvent); removeEvent(document, "mouseup", calDragEnd); tableMouseUp(ev);}
cal.hideShowCovered();}; Calendar.dayMouseDown = function(ev) { var el = Calendar.getElement(ev); if (el.disabled) { return false;}
var cal = el.calendar; cal.activeDiv = el; Calendar._C = cal; if (el.navtype != 300) with (Calendar) { addClass(el, "hilite active"); addEvent(document, "mouseover", tableMouseOver); addEvent(document, "mousemove", tableMouseOver); addEvent(document, "mouseup", tableMouseUp);} else if (cal.isPopup) { cal._dragStart(ev);}
Calendar.stopEvent(ev); if (el.navtype == -1 || el.navtype == 1) { cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250);} else if (el.navtype == -2 || el.navtype == 2) { cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)" : "Calendar.showYearsCombo(false)", 250);} else { cal.timeout = null;}
}; Calendar.dayMouseDblClick = function(ev) { Calendar.cellClick(Calendar.getElement(ev)); if (Calendar.is_ie) { document.selection.empty();}
}; Calendar.dayMouseOver = function(ev) { var el = Calendar.getElement(ev); if (Calendar.isRelated(el, ev) || Calendar._C || el.disabled) { return false;}
if (el.ttip) { if (el.ttip.substr(0, 1) == "_") { var date = null; with (el.calendar.date) { date = new Date(getFullYear(), getMonth(), el.caldate);}
el.ttip = date.print(el.calendar.ttDateFormat) + el.ttip.substr(1);}
el.calendar.tooltips.firstChild.data = el.ttip;}
if (el.navtype != 300) { Calendar.addClass(el, "hilite"); if (el.caldate) { Calendar.addClass(el.parentNode, "rowhilite");}
}
Calendar.stopEvent(ev);}; Calendar.dayMouseOut = function(ev) { with (Calendar) { var el = getElement(ev); if (isRelated(el, ev) || _C || el.disabled) { return false;}
removeClass(el, "hilite"); if (el.caldate) { removeClass(el.parentNode, "rowhilite");}
el.calendar.tooltips.firstChild.data = _TT["SEL_DATE"]; stopEvent(ev);}
}; Calendar.cellClick = function(el) { var cal = el.calendar; var closing = false; var newdate = false; var date = null; if (typeof el.navtype == "undefined") { Calendar.removeClass(cal.currentDateEl, "selected"); Calendar.addClass(el, "selected"); closing = (cal.currentDateEl == el); if (!closing) { cal.currentDateEl = el;}
cal.date.setDate(el.caldate); date = cal.date; newdate = true;} else { if (el.navtype == 200) { Calendar.removeClass(el, "hilite"); cal.callCloseHandler(); return;}
date = (el.navtype == 0) ? new Date() : new Date(cal.date); var year = date.getFullYear(); var mon = date.getMonth(); function setMonth(m) { var day = date.getDate(); var max = date.getMonthDays(m); if (day > max) { date.setDate(max);}
date.setMonth(m);}; switch (el.navtype) { case -2:
if (year > cal.minYear) { date.setFullYear(year - 1);}
break; case -1:
if (mon > 0) { setMonth(mon - 1);} else if (year-- > cal.minYear) { date.setFullYear(year); setMonth(11);}
break; case 1:
if (mon < 11) { setMonth(mon + 1);} else if (year < cal.maxYear) { date.setFullYear(year + 1); setMonth(0);}
break; case 2:
if (year < cal.maxYear) { date.setFullYear(year + 1);}
break; case 100:
cal.setMondayFirst(!cal.mondayFirst); return;}
if (!date.equalsTo(cal.date)) { cal.setDate(date); newdate = el.navtype == 0;}
}
if (newdate) { cal.callHandler();}
if (closing) { Calendar.removeClass(el, "hilite"); cal.callCloseHandler();}
}; Calendar.prototype.create = function (_par) { var parent = null; if (! _par) { parent = document.getElementsByTagName("body")[0]; this.isPopup = true;} else { parent = _par; this.isPopup = false;}
this.date = this.dateStr ? new Date(this.dateStr) : new Date(); var table = Calendar.createElement("table"); this.table = table; table.cellSpacing = 0; table.cellPadding = 0; table.calendar = this; Calendar.addEvent(table, "mousedown", Calendar.tableMouseDown); var div = Calendar.createElement("div"); this.element = div; div.className = "calendar"; if (this.isPopup) { div.style.position = "absolute"; div.style.display = "none";}
div.appendChild(table); var thead = Calendar.createElement("thead", table); var cell = null; var row = null; var cal = this; var hh = function (text, cs, navtype) { cell = Calendar.createElement("td", row); cell.colSpan = cs; cell.className = "button"; Calendar._add_evs(cell); cell.calendar = cal; cell.navtype = navtype; if (text.substr(0, 1) != "&") { cell.appendChild(document.createTextNode(text));}
else { cell.innerHTML = text;}
return cell;}; row = Calendar.createElement("tr", thead); var title_length = 6; (this.isPopup) && --title_length; (this.weekNumbers) && ++title_length; hh("-", 1, 100).ttip = Calendar._TT["TOGGLE"]; this.title = hh("", title_length, 300); this.title.className = "title"; if (this.isPopup) { this.title.ttip = Calendar._TT["DRAG_TO_MOVE"]; this.title.style.cursor = "move"; hh("&#x00d7;", 1, 200).ttip = Calendar._TT["CLOSE"];}
row = Calendar.createElement("tr", thead); row.className = "headrow"; this._nav_py = hh("&#x00ab;", 1, -2); this._nav_py.ttip = Calendar._TT["PREV_YEAR"]; this._nav_pm = hh("&#x2039;", 1, -1); this._nav_pm.ttip = Calendar._TT["PREV_MONTH"]; this._nav_now = hh(Calendar._TT["TODAY"], this.weekNumbers ? 4 : 3, 0); this._nav_now.ttip = Calendar._TT["GO_TODAY"]; this._nav_nm = hh("&#x203a;", 1, 1); this._nav_nm.ttip = Calendar._TT["NEXT_MONTH"]; this._nav_ny = hh("&#x00bb;", 1, 2); this._nav_ny.ttip = Calendar._TT["NEXT_YEAR"]
row = Calendar.createElement("tr", thead); row.className = "daynames"; if (this.weekNumbers) { cell = Calendar.createElement("td", row); cell.className = "name wn"; cell.appendChild(document.createTextNode(Calendar._TT["WK"]));}
for (var i = 7; i > 0; --i) { cell = Calendar.createElement("td", row); cell.appendChild(document.createTextNode("")); if (!i) { cell.navtype = 100; cell.calendar = this; Calendar._add_evs(cell);}
}
this.firstdayname = (this.weekNumbers) ? row.firstChild.nextSibling : row.firstChild; this._displayWeekdays(); var tbody = Calendar.createElement("tbody", table); this.tbody = tbody; for (i = 6; i > 0; --i) { row = Calendar.createElement("tr", tbody); if (this.weekNumbers) { cell = Calendar.createElement("td", row); cell.appendChild(document.createTextNode(""));}
for (var j = 7; j > 0; --j) { cell = Calendar.createElement("td", row); cell.appendChild(document.createTextNode("")); cell.calendar = this; Calendar._add_evs(cell);}
}
var tfoot = Calendar.createElement("tfoot", table); row = Calendar.createElement("tr", tfoot); row.className = "footrow"; cell = hh(Calendar._TT["SEL_DATE"], this.weekNumbers ? 8 : 7, 300); cell.className = "ttip"; if (this.isPopup) { cell.ttip = Calendar._TT["DRAG_TO_MOVE"]; cell.style.cursor = "move";}
this.tooltips = cell; div = Calendar.createElement("div", this.element); this.monthsCombo = div; div.className = "combo"; for (i = 0; i < Calendar._MN.length; ++i) { var mn = Calendar.createElement("div"); mn.className = "label"; mn.month = i; mn.appendChild(document.createTextNode(Calendar._MN3[i])); div.appendChild(mn);}
div = Calendar.createElement("div", this.element); this.yearsCombo = div; div.className = "combo"; for (i = 12; i > 0; --i) { var yr = Calendar.createElement("div"); yr.className = "label"; yr.appendChild(document.createTextNode("")); div.appendChild(yr);}
this._init(this.mondayFirst, this.date); parent.appendChild(this.element);}; Calendar._keyEvent = function(ev) { if (!window.calendar) { return false;}
(Calendar.is_ie) && (ev = window.event); var cal = window.calendar; var act = (Calendar.is_ie || ev.type == "keypress"); if (ev.ctrlKey) { switch (ev.keyCode) { case 37:
act && Calendar.cellClick(cal._nav_pm); break; case 38:
act && Calendar.cellClick(cal._nav_py); break; case 39:
act && Calendar.cellClick(cal._nav_nm); break; case 40:
act && Calendar.cellClick(cal._nav_ny); break; default:
return false;}
} else switch (ev.keyCode) { case 32:
Calendar.cellClick(cal._nav_now); break; case 27:
act && cal.hide(); break; case 37:
case 38:
case 39:
case 40:
if (act) { var date = cal.date.getDate() - 1; var el = cal.currentDateEl; var ne = null; var prev = (ev.keyCode == 37) || (ev.keyCode == 38); switch (ev.keyCode) { case 37:
(--date >= 0) && (ne = cal.ar_days[date]); break; case 38:
date -= 7; (date >= 0) && (ne = cal.ar_days[date]); break; case 39:
(++date < cal.ar_days.length) && (ne = cal.ar_days[date]); break; case 40:
date += 7; (date < cal.ar_days.length) && (ne = cal.ar_days[date]); break;}
if (!ne) { if (prev) { Calendar.cellClick(cal._nav_pm);} else { Calendar.cellClick(cal._nav_nm);}
date = (prev) ? cal.date.getMonthDays() : 1; el = cal.currentDateEl; ne = cal.ar_days[date - 1];}
Calendar.removeClass(el, "selected"); Calendar.addClass(ne, "selected"); cal.date.setDate(ne.caldate); cal.currentDateEl = ne;}
break; case 13:
if (act) { cal.callHandler(); cal.hide();}
break; default:
return false;}
Calendar.stopEvent(ev);}; Calendar.prototype._init = function (mondayFirst, date) { var today = new Date(); var year = date.getFullYear(); if (year < this.minYear) { year = this.minYear; date.setFullYear(year);} else if (year > this.maxYear) { year = this.maxYear; date.setFullYear(year);}
this.mondayFirst = mondayFirst; this.date = new Date(date); var month = date.getMonth(); var mday = date.getDate(); var no_days = date.getMonthDays(); date.setDate(1); var wday = date.getDay(); var MON = mondayFirst ? 1 : 0; var SAT = mondayFirst ? 5 : 6; var SUN = mondayFirst ? 6 : 0; if (mondayFirst) { wday = (wday > 0) ? (wday - 1) : 6;}
var iday = 1; var row = this.tbody.firstChild; var MN = Calendar._MN3[month]; var hasToday = ((today.getFullYear() == year) && (today.getMonth() == month)); var todayDate = today.getDate(); var week_number = date.getWeekNumber(); var ar_days = new Array(); for (var i = 0; i < 6; ++i) { if (iday > no_days) { row.className = "emptyrow"; row = row.nextSibling; continue;}
var cell = row.firstChild; if (this.weekNumbers) { cell.className = "day wn"; cell.firstChild.data = week_number; cell = cell.nextSibling;} ++week_number; row.className = "daysrow"; for (var j = 0; j < 7; ++j) { cell.className = "day"; if ((!i && j < wday) || iday > no_days) { cell.innerHTML = "&nbsp;"; cell.disabled = true; cell = cell.nextSibling; continue;}
cell.disabled = false; cell.firstChild.data = iday; if (typeof this.checkDisabled == "function") { date.setDate(iday); if (this.checkDisabled(date)) { cell.className += " disabled"; cell.disabled = true;}
}
if (!cell.disabled) { ar_days[ar_days.length] = cell; cell.caldate = iday; cell.ttip = "_"; if (iday == mday) { cell.className += " selected"; this.currentDateEl = cell;}
if (hasToday && (iday == todayDate)) { cell.className += " today"; cell.ttip += Calendar._TT["PART_TODAY"];}
if (wday == SAT || wday == SUN) { cell.className += " weekend";}
} ++iday; ((++wday) ^ 7) || (wday = 0); cell = cell.nextSibling;}
row = row.nextSibling;}
this.ar_days = ar_days; this.title.firstChild.data = Calendar._MN[month] + ", " + year;}; Calendar.prototype.setDate = function (date) { if (!date.equalsTo(this.date)) { this._init(this.mondayFirst, date);}
}; Calendar.prototype.setMondayFirst = function (mondayFirst) { this._init(mondayFirst, this.date); this._displayWeekdays();}; Calendar.prototype.setDisabledHandler = function (unaryFunction) { this.checkDisabled = unaryFunction;}; Calendar.prototype.setRange = function (a, z) { this.minYear = a; this.maxYear = z;}; Calendar.prototype.callHandler = function () { if (this.onSelected) { this.onSelected(this, this.date.print(this.dateFormat));}
}; Calendar.prototype.callCloseHandler = function () { if (this.onClose) { this.onClose(this);}
this.hideShowCovered();}; Calendar.prototype.destroy = function () { var el = this.element.parentNode; el.removeChild(this.element); Calendar._C = null; delete el;}; Calendar.prototype.reparent = function (new_parent) { var el = this.element; el.parentNode.removeChild(el); new_parent.appendChild(el);}; Calendar._checkCalendar = function(ev) { if (!window.calendar) { return false;}
var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev); for (; el != null && el != calendar.element; el = el.parentNode); if (el == null) { window.calendar.callCloseHandler(); Calendar.stopEvent(ev);}
}; Calendar.prototype.show = function () { var rows = this.table.getElementsByTagName("tr"); for (var i = rows.length; i > 0;) { var row = rows[--i]; Calendar.removeClass(row, "rowhilite"); var cells = row.getElementsByTagName("td"); for (var j = cells.length; j > 0;) { var cell = cells[--j]; Calendar.removeClass(cell, "hilite"); Calendar.removeClass(cell, "active");}
}
this.element.style.display = "block"; this.hidden = false; if (this.isPopup) { window.calendar = this; Calendar.addEvent(document, "keydown", Calendar._keyEvent); Calendar.addEvent(document, "keypress", Calendar._keyEvent); Calendar.addEvent(document, "mousedown", Calendar._checkCalendar);}
this.hideShowCovered();}; Calendar.prototype.hide = function () { if (this.isPopup) { Calendar.removeEvent(document, "keydown", Calendar._keyEvent); Calendar.removeEvent(document, "keypress", Calendar._keyEvent); Calendar.removeEvent(document, "mousedown", Calendar._checkCalendar);}
this.element.style.display = "none"; this.hidden = true; this.hideShowCovered();}; Calendar.prototype.showAt = function (x, y) { var s = this.element.style; s.left = x + "px"; s.top = y + "px"; this.show();}; Calendar.prototype.showAtElement = function (el) { var p = Calendar.getAbsolutePos(el); this.showAt(p.x, p.y + el.offsetHeight);}; Calendar.prototype.setDateFormat = function (str) { this.dateFormat = str;}; Calendar.prototype.setTtDateFormat = function (str) { this.ttDateFormat = str;}; Calendar.prototype.parseDate = function (str, fmt) { var y = 0; var m = -1; var d = 0; var a = str.split(/\W+/); if (!fmt) { fmt = this.dateFormat;}
var b = fmt.split(/\W+/); var i = 0, j = 0; for (i = 0; i < a.length; ++i) { if (b[i] == "D" || b[i] == "DD") { continue;}
if (b[i] == "d" || b[i] == "dd") { d = parseInt(a[i], 10);}
if (b[i] == "m" || b[i] == "mm") { m = parseInt(a[i], 10) - 1;}
if (b[i] == "y") { y = parseInt(a[i], 10);}
if (b[i] == "yy") { y = parseInt(a[i], 10) + 1900;}
if (b[i] == "M" || b[i] == "MM") { for (j = 0; j < 12; ++j) { if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break;}
}
}
}
if (y != 0 && m != -1 && d != 0) { this.setDate(new Date(y, m, d)); return;}
y = 0; m = -1; d = 0; for (i = 0; i < a.length; ++i) { if (a[i].search(/[a-zA-Z]+/) != -1) { var t = -1; for (j = 0; j < 12; ++j) { if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break;}
}
if (t != -1) { if (m != -1) { d = m+1;}
m = t;}
} else if (parseInt(a[i], 10) <= 12 && m == -1) { m = a[i]-1;} else if (parseInt(a[i], 10) > 31 && y == 0) { y = a[i];} else if (d == 0) { d = a[i];}
}
if (y == 0) { var today = new Date(); y = today.getFullYear();}
if (m != -1 && d != 0) { this.setDate(new Date(y, m, d));}
}; Calendar.prototype.hideShowCovered = function () { var tags = new Array("applet", "iframe", "select"); var el = this.element; var p = Calendar.getAbsolutePos(el); var EX1 = p.x; var EX2 = el.offsetWidth + EX1; var EY1 = p.y; var EY2 = el.offsetHeight + EY1; for (var k = tags.length; k > 0; ) { var ar = document.getElementsByTagName(tags[--k]); var cc = null; for (var i = ar.length; i > 0;) { cc = ar[--i]; p = Calendar.getAbsolutePos(cc); var CX1 = p.x; var CX2 = cc.offsetWidth + CX1; var CY1 = p.y; var CY2 = cc.offsetHeight + CY1; if (this.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) { cc.style.visibility = "visible";} else { cc.style.visibility = "hidden";}
}
}
}; Calendar.prototype._displayWeekdays = function () { var MON = this.mondayFirst ? 0 : 1; var SUN = this.mondayFirst ? 6 : 0; var SAT = this.mondayFirst ? 5 : 6; var cell = this.firstdayname; for (var i = 0; i < 7; ++i) { cell.className = "day name"; if (!i) { cell.ttip = this.mondayFirst ? Calendar._TT["SUN_FIRST"] : Calendar._TT["MON_FIRST"]; cell.navtype = 100; cell.calendar = this; Calendar._add_evs(cell);}
if (i == SUN || i == SAT) { Calendar.addClass(cell, "weekend");}
cell.firstChild.data = Calendar._DN3[i + 1 - MON]; cell = cell.nextSibling;}
}; Calendar.prototype._hideCombos = function () { this.monthsCombo.style.display = "none"; this.yearsCombo.style.display = "none";}; Calendar.prototype._dragStart = function (ev) { if (this.dragging) { return;}
this.dragging = true; var posX; var posY; if (Calendar.is_ie) { posY = window.event.clientY + document.body.scrollTop; posX = window.event.clientX + document.body.scrollLeft;} else { posY = ev.clientY + window.scrollY; posX = ev.clientX + window.scrollX;}
var st = this.element.style; this.xOffs = posX - parseInt(st.left); this.yOffs = posY - parseInt(st.top); with (Calendar) { addEvent(document, "mousemove", calDragIt); addEvent(document, "mouseover", stopEvent); addEvent(document, "mouseup", calDragEnd);}
}; Date._MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31); Date.SECOND = 1000 ; Date.MINUTE = 60 * Date.SECOND; Date.HOUR = 60 * Date.MINUTE; Date.DAY = 24 * Date.HOUR; Date.WEEK = 7 * Date.DAY; Date.prototype.getMonthDays = function(month) { var year = this.getFullYear(); if (typeof month == "undefined") { month = this.getMonth();}
if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) { return 29;} else { return Date._MD[month];}
}; Date.prototype.getWeekNumber = function() { var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0); var then = new Date(this.getFullYear(), 0, 1, 0, 0, 0); var time = now - then; var day = then.getDay(); (day > 3) && (day -= 4) || (day += 3); return Math.round(((time / Date.DAY) + day) / 7);}; Date.prototype.equalsTo = function(date) { return ((this.getFullYear() == date.getFullYear()) &&
(this.getMonth() == date.getMonth()) &&
(this.getDate() == date.getDate()));}; Date.prototype.print = function (frm) { var str = new String(frm); var m = this.getMonth(); var d = this.getDate(); var y = this.getFullYear(); var wn = this.getWeekNumber(); var w = this.getDay(); var s = new Array(); s["d"] = d; s["dd"] = (d < 10) ? ("0" + d) : d; s["m"] = 1+m; s["mm"] = (m < 9) ? ("0" + (1+m)) : (1+m); s["y"] = y; s["yy"] = new String(y).substr(2, 2); s["w"] = wn; s["ww"] = (wn < 10) ? ("0" + wn) : wn; with (Calendar) { s["D"] = _DN3[w]; s["DD"] = _DN[w]; s["M"] = _MN3[m]; s["MM"] = _MN[m];}
var re = /(.*)(\W|^)(d|dd|m|mm|y|yy|MM|M|DD|D|w|ww)(\W|$)(.*)/; while (re.exec(str) != null) { str = RegExp.$1 + RegExp.$2 + s[RegExp.$3] + RegExp.$4 + RegExp.$5;}
return str;}; window.calendar = null; 