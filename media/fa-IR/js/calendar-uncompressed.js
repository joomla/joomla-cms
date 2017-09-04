Calendar = function(D, C, F, A) {
    this.activeDiv = null;
    this.currentDateEl = null;
    this.getDateStatus = null;
    this.getDateToolTip = null;
    this.getDateText = null;
    this.timeout = null;
    this.onSelected = F || null;
    this.onClose = A || null;
    this.dragging = false;
    this.hidden = false;
    this.minYear = 1000;
    this.maxYear = 3000;
    this.langNumbers = false;
    this.dateType = "gregorian";
    this.dateFormat = Calendar._TT.DEF_DATE_FORMAT;
    this.ttDateFormat = Calendar._TT.TT_DATE_FORMAT;
    this.isPopup = true;
    this.weekNumbers = true;
    this.firstDayOfWeek = typeof D == "number" ? D : Calendar._FD;
    this.showsOtherMonths = false;
    this.dateStr = C;
    this.ar_days = null;
    this.showsTime = false;
    this.time24 = true;
    this.yearStep = 2;
    this.hiliteToday = true;
    this.multiple = null;
    this.table = null;
    this.element = null;
    this.tbody = null;
    this.firstdayname = null;
    this.monthsCombo = null;
    this.yearsCombo = null;
    this.hilitedMonth = null;
    this.activeMonth = null;
    this.hilitedYear = null;
    this.activeYear = null;
    this.dateClicked = false;
    if (typeof Calendar._SDN == "undefined") {
        if (typeof Calendar._SDN_len == "undefined") {
            Calendar._SDN_len = 3
        }
        var B = new Array();
        for (var E = 8; E > 0;) {
            B[--E] = Calendar._DN[E].substr(0, Calendar._SDN_len)
        }
        Calendar._SDN = B;
        if (typeof Calendar._SMN_len == "undefined") {
            Calendar._SMN_len = 3
        }
        if (typeof Calendar._JSMN_len == "undefined") {
            Calendar._JSMN_len = 3
        }
        B = new Array();
        for (var E = 12; E > 0;) {
            B[--E] = Calendar._MN[E].substr(0, Calendar._SMN_len)
        }
        Calendar._SMN = B;
        B = new Array();
        for (var E = 12; E > 0;) {
            B[--E] = Calendar._JMN[E].substr(0, Calendar._JSMN_len)
        }
        Calendar._JSMN = B
    }
};
Calendar._C = null;
Calendar.is_ie = (/msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent));
Calendar.is_ie5 = (Calendar.is_ie && /msie 5\.0/i.test(navigator.userAgent));
Calendar.is_opera = /opera/i.test(navigator.userAgent);
Calendar.is_khtml = /Konqueror|Safari|KHTML/i.test(navigator.userAgent);
Calendar.getAbsolutePos = function(E) {
    var A = 0,
        D = 0;
    var C = /^div$/i.test(E.tagName);
    if (C && E.scrollLeft) {
        A = E.scrollLeft
    }
    if (C && E.scrollTop) {
        D = E.scrollTop
    }
    var F = {
        x: E.offsetLeft - A,
        y: E.offsetTop - D
    };
    if (E.offsetParent) {
        var B = this.getAbsolutePos(E.offsetParent);
        F.x += B.x;
        F.y += B.y
    }
    return F
};
Calendar.isRelated = function(C, A) {
    var D = A.relatedTarget;
    if (!D) {
        var B = A.type;
        if (B == "mouseover") {
            D = A.fromElement
        } else {
            if (B == "mouseout") {
                D = A.toElement
            }
        }
    }
    while (D) {
        if (D == C) {
            return true
        }
        D = D.parentNode
    }
    return false
};
Calendar.removeClass = function(E, D) {
    if (!(E && E.className)) {
        return
    }
    var A = E.className.split(" ");
    var B = new Array();
    for (var C = A.length; C > 0;) {
        if (A[--C] != D) {
            B[B.length] = A[C]
        }
    }
    E.className = B.join(" ")
};
Calendar.addClass = function(B, A) {
    Calendar.removeClass(B, A);
    B.className += " " + A
};
Calendar.getElement = function(A) {
    var B = Calendar.is_ie ? window.event.srcElement : A.currentTarget;
    while (B.nodeType != 1 || /^div$/i.test(B.tagName)) {
        B = B.parentNode
    }
    return B
};
Calendar.getTargetElement = function(A) {
    var B = Calendar.is_ie ? window.event.srcElement : A.target;
    while (B.nodeType != 1) {
        B = B.parentNode
    }
    return B
};
Calendar.stopEvent = function(A) {
    A || (A = window.event);
    if (Calendar.is_ie) {
        A.cancelBubble = true;
        A.returnValue = false
    } else {
        A.preventDefault();
        A.stopPropagation()
    }
    return false
};
Calendar.addEvent = function(A, C, B) {
    if (A.attachEvent) {
        A.attachEvent("on" + C, B)
    } else {
        if (A.addEventListener) {
            A.addEventListener(C, B, true)
        } else {
            A["on" + C] = B
        }
    }
};
Calendar.removeEvent = function(A, C, B) {
    if (A.detachEvent) {
        A.detachEvent("on" + C, B)
    } else {
        if (A.removeEventListener) {
            A.removeEventListener(C, B, true)
        } else {
            A["on" + C] = null
        }
    }
};
Calendar.createElement = function(C, B) {
    var A = null;
    if (document.createElementNS) {
        A = document.createElementNS("http://www.w3.org/1999/xhtml", C)
    } else {
        A = document.createElement(C)
    }
    if (typeof B != "undefined") {
        B.appendChild(A)
    }
    return A
};
Calendar.prototype.convertNumbers = function(A) {
    A = A.toString();
    if (this.langNumbers) {
        A = A.convertNumbers()
    }
    return A
};
String.prototype.toEnglish = function() {
    str = this.toString();
    if (Calendar._NUMBERS) {
        for (var A = 0; A < Calendar._NUMBERS.length; A++) {
            str = str.replace(new RegExp(Calendar._NUMBERS[A], "g"), A)
        }
    }
    return str
};
String.prototype.convertNumbers = function() {
    str = this.toString();
    if (Calendar._NUMBERS) {
        for (var A = 0; A < Calendar._NUMBERS.length; A++) {
            str = str.replace(new RegExp(A, "g"), Calendar._NUMBERS[A])
        }
    }
    return str
};
Calendar._add_evs = function(el) {
    with(Calendar) {
        addEvent(el, "mouseover", dayMouseOver);
        addEvent(el, "mousedown", dayMouseDown);
        addEvent(el, "mouseout", dayMouseOut);
        if (is_ie) {
            addEvent(el, "dblclick", dayMouseDblClick);
            el.setAttribute("unselectable", true)
        }
    }
};
Calendar.findMonth = function(A) {
    if (typeof A.month != "undefined") {
        return A
    } else {
        if (typeof A.parentNode.month != "undefined") {
            return A.parentNode
        }
    }
    return null
};
Calendar.findYear = function(A) {
    if (typeof A.year != "undefined") {
        return A
    } else {
        if (typeof A.parentNode.year != "undefined") {
            return A.parentNode
        }
    }
    return null
};
Calendar.showMonthsCombo = function() {
    var E = Calendar._C;
    if (!E) {
        return false
    }
    var E = E;
    var F = E.activeDiv;
    var D = E.monthsCombo;
    if (E.hilitedMonth) {
        Calendar.removeClass(E.hilitedMonth, "hilite")
    }
    if (E.activeMonth) {
        Calendar.removeClass(E.activeMonth, "active")
    }
    var C = E.monthsCombo.getElementsByTagName("div")[E.date.getLocalMonth(true, E.dateType)];
    Calendar.addClass(C, "active");
    E.activeMonth = C;
    var B = D.style;
    B.display = "block";
    if (F.navtype < 0) {
        B.left = F.offsetLeft + "px"
    } else {
        var A = D.offsetWidth;
        if (typeof A == "undefined") {
            A = 50
        }
        B.left = (F.offsetLeft + F.offsetWidth - A) + "px"
    }
    B.top = (F.offsetTop + F.offsetHeight) + "px"
};
Calendar.showYearsCombo = function(D) {
    var A = Calendar._C;
    if (!A) {
        return false
    }
    var A = A;
    var C = A.activeDiv;
    var F = A.yearsCombo;
    if (A.hilitedYear) {
        Calendar.removeClass(A.hilitedYear, "hilite")
    }
    if (A.activeYear) {
        Calendar.removeClass(A.activeYear, "active")
    }
    A.activeYear = null;
    var B = A.date.getLocalFullYear(true, A.dateType) + (D ? 1 : -1);
    var I = F.firstChild;
    var H = false;
    for (var E = 12; E > 0; --E) {
        if (B >= A.minYear && B <= A.maxYear) {
            I.innerHTML = A.convertNumbers(B);
            I.year = B;
            I.style.display = "block";
            H = true
        } else {
            I.style.display = "none"
        }
        I = I.nextSibling;
        B += D ? A.yearStep : -A.yearStep
    }
    if (H) {
        var J = F.style;
        J.display = "block";
        if (C.navtype < 0) {
            J.left = C.offsetLeft + "px"
        } else {
            var G = F.offsetWidth;
            if (typeof G == "undefined") {
                G = 50
            }
            J.left = (C.offsetLeft + C.offsetWidth - G) + "px"
        }
        J.top = (C.offsetTop + C.offsetHeight) + "px"
    }
};
Calendar.tableMouseUp = function(ev) {
    var cal = Calendar._C;
    if (!cal) {
        return false
    }
    if (cal.timeout) {
        clearTimeout(cal.timeout)
    }
    var el = cal.activeDiv;
    if (!el) {
        return false
    }
    var target = Calendar.getTargetElement(ev);
    ev || (ev = window.event);
    Calendar.removeClass(el, "active");
    if (target == el || target.parentNode == el) {
        Calendar.cellClick(el, ev)
    }
    var mon = Calendar.findMonth(target);
    var date = null;
    if (mon) {
        date = new Date(cal.date);
        if (mon.month != date.getLocalMonth(true, cal.dateType)) {
            date.setLocalMonth(true, cal.dateType, mon.month);
            cal.setDate(date);
            cal.dateClicked = false;
            cal.callHandler()
        }
    } else {
        var year = Calendar.findYear(target);
        if (year) {
            date = new Date(cal.date);
            if (year.year != date.getLocalFullYear(true, cal.dateType)) {
                date._calSetLocalFullYear(cal.dateType, year.year);
                cal.setDate(date);
                cal.dateClicked = false;
                cal.callHandler()
            }
        }
    }
    with(Calendar) {
        removeEvent(document, "mouseup", tableMouseUp);
        removeEvent(document, "mouseover", tableMouseOver);
        removeEvent(document, "mousemove", tableMouseOver);
        cal._hideCombos();
        _C = null;
        return stopEvent(ev)
    }
};
Calendar.tableMouseOver = function(M) {
    var A = Calendar._C;
    if (!A) {
        return
    }
    var C = A.activeDiv;
    var I = Calendar.getTargetElement(M);
    if (I == C || I.parentNode == C) {
        Calendar.addClass(C, "hilite active");
        Calendar.addClass(C.parentNode, "rowhilite")
    } else {
        if (typeof C.navtype == "undefined" || (C.navtype != 50 && (C.navtype == 0 || Math.abs(C.navtype) > 2))) {
            Calendar.removeClass(C, "active")
        }
        Calendar.removeClass(C, "hilite");
        Calendar.removeClass(C.parentNode, "rowhilite")
    }
    M || (M = window.event);
    if (C.navtype == 50 && I != C) {
        var L = Calendar.getAbsolutePos(C);
        var O = C.offsetWidth;
        var N = M.clientX;
        var P;
        var K = true;
        if (N > L.x + O) {
            P = N - L.x - O;
            K = false
        } else {
            P = L.x - N
        }
        if (P < 0) {
            P = 0
        }
        var F = C._range;
        var H = C._current;
        var G = Math.floor(P / 10) % F.length;
        for (var E = F.length; --E >= 0;) {
            if (F[E] == H) {
                break
            }
        }
        while (G-- > 0) {
            if (K) {
                if (--E < 0) {
                    E = F.length - 1
                }
            } else {
                if (++E >= F.length) {
                    E = 0
                }
            }
        }
        var B = F[E];
        C.innerHTML = A.convertNumbers(B);
        A.onUpdateTime()
    }
    var D = Calendar.findMonth(I);
    if (D) {
        if (D.month != A.date.getLocalMonth(true, A.dateType)) {
            if (A.hilitedMonth) {
                Calendar.removeClass(A.hilitedMonth, "hilite")
            }
            Calendar.addClass(D, "hilite");
            A.hilitedMonth = D
        } else {
            if (A.hilitedMonth) {
                Calendar.removeClass(A.hilitedMonth, "hilite")
            }
        }
    } else {
        if (A.hilitedMonth) {
            Calendar.removeClass(A.hilitedMonth, "hilite")
        }
        var J = Calendar.findYear(I);
        if (J) {
            if (J.year != A.date.getLocalFullYear(true, A.dateType)) {
                if (A.hilitedYear) {
                    Calendar.removeClass(A.hilitedYear, "hilite")
                }
                Calendar.addClass(J, "hilite");
                A.hilitedYear = J
            } else {
                if (A.hilitedYear) {
                    Calendar.removeClass(A.hilitedYear, "hilite")
                }
            }
        } else {
            if (A.hilitedYear) {
                Calendar.removeClass(A.hilitedYear, "hilite")
            }
        }
    }
    return Calendar.stopEvent(M)
};
Calendar.tableMouseDown = function(A) {
    if (Calendar.getTargetElement(A) == Calendar.getElement(A)) {
        return Calendar.stopEvent(A)
    }
};
Calendar.calDragIt = function(B) {
    var C = Calendar._C;
    if (!(C && C.dragging)) {
        return false
    }
    var E;
    var D;
    if (Calendar.is_ie) {
        D = window.event.clientY + document.body.scrollTop;
        E = window.event.clientX + document.body.scrollLeft
    } else {
        E = B.pageX;
        D = B.pageY
    }
    C.hideShowCovered();
    var A = C.element.style;
    A.left = (E - C.xOffs) + "px";
    A.top = (D - C.yOffs) + "px";
    return Calendar.stopEvent(B)
};
Calendar.calDragEnd = function(ev) {
    var cal = Calendar._C;
    if (!cal) {
        return false
    }
    cal.dragging = false;
    with(Calendar) {
        removeEvent(document, "mousemove", calDragIt);
        removeEvent(document, "mouseup", calDragEnd);
        tableMouseUp(ev)
    }
    cal.hideShowCovered()
};
Calendar.dayMouseDown = function(ev) {
    var el = Calendar.getElement(ev);
    if (el.disabled) {
        return false
    }
    var cal = el.calendar;
    cal.activeDiv = el;
    Calendar._C = cal;
    if (el.navtype != 300) {
        with(Calendar) {
            if (el.navtype == 50) {
                el._current = el.innerHTML.toEnglish();
                addEvent(document, "mousemove", tableMouseOver)
            } else {
                addEvent(document, Calendar.is_ie5 ? "mousemove" : "mouseover", tableMouseOver)
            }
            addClass(el, "hilite active");
            addEvent(document, "mouseup", tableMouseUp)
        }
    } else {
        if (cal.isPopup) {
            cal._dragStart(ev)
        }
    }
    if (el.navtype == -1 || el.navtype == 1) {
        if (cal.timeout) {
            clearTimeout(cal.timeout)
        }
        cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250)
    } else {
        if (el.navtype == -2 || el.navtype == 2) {
            if (cal.timeout) {
                clearTimeout(cal.timeout)
            }
            cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)" : "Calendar.showYearsCombo(false)", 250)
        } else {
            cal.timeout = null
        }
    }
    return Calendar.stopEvent(ev)
};
Calendar.dayMouseDblClick = function(A) {
    Calendar.cellClick(Calendar.getElement(A), A || window.event);
    if (Calendar.is_ie) {
        document.selection.empty()
    }
};
Calendar.dayMouseOver = function(B) {
    var A = Calendar.getElement(B);
    if (Calendar.isRelated(A, B) || Calendar._C || A.disabled) {
        return false
    }
    if (A.ttip) {
        if (A.ttip.substr(0, 1) == "_") {
            A.ttip = A.caldate.print(A.calendar.ttDateFormat, A.calendar.dateType, A.calendar.langNumbers) + A.ttip.substr(1)
        }
        A.calendar.tooltips.innerHTML = A.ttip
    }
    if (A.navtype != 300) {
        Calendar.addClass(A, "hilite");
        if (A.caldate) {
            Calendar.addClass(A.parentNode, "rowhilite")
        }
    }
    return Calendar.stopEvent(B)
};
Calendar.dayMouseOut = function(ev) {
    with(Calendar) {
        var el = getElement(ev);
        if (isRelated(el, ev) || _C || el.disabled) {
            return false
        }
        removeClass(el, "hilite");
        if (el.caldate) {
            removeClass(el.parentNode, "rowhilite")
        }
        if (el.calendar) {
            el.calendar.tooltips.innerHTML = _TT.SEL_DATE
        }
        return stopEvent(ev)
    }
};
Calendar.cellClick = function(E, N) {
    var C = E.calendar;
    var H = false;
    var K = false;
    var F = null;
    if (typeof E.navtype == "undefined") {
        if (C.currentDateEl) {
            Calendar.removeClass(C.currentDateEl, "selected");
            Calendar.addClass(E, "selected");
            H = (C.currentDateEl == E);
            if (!H) {
                C.currentDateEl = E
            }
        }
        C.date.setUTCDateOnly(E.caldate);
        F = C.date;
        var B = !(C.dateClicked = !E.otherMonth);
        if (!B && !C.currentDateEl) {
            C._toggleMultipleDate(new Date(F))
        } else {
            K = !E.disabled
        }
        if (B) {
            C._init(C.firstDayOfWeek, F)
        }
    } else {
        if (E.navtype == 200) {
            Calendar.removeClass(E, "hilite");
            C.callCloseHandler();
            return
        }
        F = new Date(C.date);
        if (E.navtype == 0) {
            F.setUTCDateOnly(new Date())
        }
        C.dateClicked = false;
        var M = F.getLocalFullYear(true, C.dateType);
        var G = F.getLocalMonth(true, C.dateType);

        function A(Q) {
            var R = F.getLocalDate(true, C.dateType);
            var P = F.getLocalMonthDays(C.dateType, Q);
            if (R > P) {
                F.setLocalDate(true, C.dateType, P)
            }
            F.setLocalMonth(true, C.dateType, Q)
        }
        switch (E.navtype) {
            case 400:
                Calendar.removeClass(E, "hilite");
                var O = Calendar._TT.ABOUT;
                if (typeof O != "undefined") {
                    O += C.showsTime ? Calendar._TT.ABOUT_TIME : ""
                } else {
                    O = 'Help and about box text is not translated into this language.\nIf you know this language and you feel generous please update\nthe corresponding file in "lang" subdir to match calendar-en.js\nand send it back to <mihai_bazon@yahoo.com> to get it into the distribution  ;-)\n\nThank you!\nhttp://dynarch.com/mishoo/calendar.epl\n'
                }
                alert(O);
                return;
            case -2:
                if (M > C.minYear) {
                    F._calSetLocalFullYear(C.dateType, M - 1)
                }
                break;
            case -1:
                if (G > 0) {
                    A(G - 1)
                } else {
                    if (M-- > C.minYear) {
                        F._calSetLocalFullYear(C.dateType, M);
                        A(11)
                    }
                }
                break;
            case 1:
                if (G < 11) {
                    A(G + 1)
                } else {
                    if (M < C.maxYear) {
                        A(0);
                        F._calSetLocalFullYear(C.dateType, M + 1)
                    }
                }
                break;
            case 2:
                if (M < C.maxYear) {
                    F._calSetLocalFullYear(C.dateType, M + 1)
                }
                break;
            case 100:
                C.setFirstDayOfWeek(E.fdow);
                return;
            case 50:
                var J = E._range;
                var L = E.innerHTML.toEnglish();
                for (var I = J.length; --I >= 0;) {
                    if (J[I] == L) {
                        break
                    }
                }
                if (N && N.shiftKey) {
                    if (--I < 0) {
                        I = J.length - 1
                    }
                } else {
                    if (++I >= J.length) {
                        I = 0
                    }
                }
                var D = J[I];
                E.innerHTML = C.convertNumbers(D);
                C.onUpdateTime();
                return;
            case 0:
                if ((typeof C.getDateStatus == "function") && C.getDateStatus(F, F.getLocalFullYear(true, C.dateType), F.getLocalMonth(true, C.dateType), F.getLocalDate(true, C.dateType))) {
                    return false
                }
                break
        }
        if (!F.equalsTo(C.date)) {
            C.setDate(F);
            K = true
        } else {
            if (E.navtype == 0) {
                K = H = true
            }
        }
    }
    if (K) {
        N && C.callHandler()
    }
    if (H) {
        Calendar.removeClass(E, "hilite");
        N && C.callCloseHandler()
    }
};
Calendar.prototype.create = function(L) {
    var K = null;
    if (!L) {
        K = document.getElementsByTagName("body")[0];
        this.isPopup = true
    } else {
        K = L;
        this.isPopup = false
    }
    if (!this.date) {
        this.date = this.dateStr ? new Date(this.dateStr) : new Date()
    }
    var O = Calendar.createElement("table");
    this.table = O;
    O.cellSpacing = 0;
    O.cellPadding = 0;
    O.calendar = this;
    Calendar.addEvent(O, "mousedown", Calendar.tableMouseDown);
    var A = Calendar.createElement("div");
    this.element = A;
    if (Calendar._DIR) {
        this.element.style.direction = Calendar._DIR
    }
    A.className = "calendar";
    if (this.isPopup) {
        A.style.position = "absolute";
        A.style.display = "none"
    }
    A.appendChild(O);
    var I = Calendar.createElement("thead", O);
    var M = null;
    var P = null;
    var B = this;
    var E = function(S, R, Q) {
        M = Calendar.createElement("td", P);
        M.colSpan = R;
        M.className = "button";
        if (Q != 0 && Math.abs(Q) <= 2) {
            M.className += " nav"
        }
        Calendar._add_evs(M);
        M.calendar = B;
        M.navtype = Q;
        M.innerHTML = "<div unselectable='on'>" + S + "</div>";
        return M
    };
    P = Calendar.createElement("tr", I);
    var C = 6;
    (this.isPopup) && --C;
    (this.weekNumbers) && ++C;
    E("?", 1, 400).ttip = Calendar._TT.INFO;
    this.title = E("", C, 300);
    this.title.className = "title";
    if (this.isPopup) {
        this.title.ttip = Calendar._TT.DRAG_TO_MOVE;
        this.title.style.cursor = "move";
        E("&#x00d7;", 1, 200).ttip = Calendar._TT.CLOSE
    }
    P = Calendar.createElement("tr", I);
    P.className = "headrow";
    this._nav_py = E("&#x00ab;", 1, -2);
    this._nav_py.ttip = Calendar._TT.PREV_YEAR;
    this._nav_pm = E("&#x2039;", 1, -1);
    this._nav_pm.ttip = Calendar._TT.PREV_MONTH;
    this._nav_now = E(Calendar._TT.TODAY, this.weekNumbers ? 4 : 3, 0);
    this._nav_now.ttip = Calendar._TT.GO_TODAY;
    this._nav_nm = E("&#x203a;", 1, 1);
    this._nav_nm.ttip = Calendar._TT.NEXT_MONTH;
    this._nav_ny = E("&#x00bb;", 1, 2);
    this._nav_ny.ttip = Calendar._TT.NEXT_YEAR;
    P = Calendar.createElement("tr", I);
    P.className = "daynames";
    if (this.weekNumbers) {
        M = Calendar.createElement("td", P);
        M.className = "name wn";
        M.innerHTML = Calendar._TT.WK
    }
    for (var H = 7; H > 0; --H) {
        M = Calendar.createElement("td", P);
        if (!H) {
            M.navtype = 100;
            M.calendar = this;
            Calendar._add_evs(M)
        }
    }
    this.firstdayname = (this.weekNumbers) ? P.firstChild.nextSibling : P.firstChild;
    this._displayWeekdays();
    var G = Calendar.createElement("tbody", O);
    this.tbody = G;
    for (H = 6; H > 0; --H) {
        P = Calendar.createElement("tr", G);
        if (this.weekNumbers) {
            M = Calendar.createElement("td", P)
        }
        for (var F = 7; F > 0; --F) {
            M = Calendar.createElement("td", P);
            M.calendar = this;
            Calendar._add_evs(M)
        }
    }
    if (this.showsTime) {
        P = Calendar.createElement("tr", G);
        P.className = "time";
        M = Calendar.createElement("td", P);
        M.className = "time";
        M.colSpan = 2;
        M.innerHTML = Calendar._TT.TIME || "&nbsp;";
        M = Calendar.createElement("td", P);
        M.className = "time";
        M.colSpan = this.weekNumbers ? 4 : 3;
        (function() {
            function T(c, e, d, f) {
                var a = Calendar.createElement("span", M);
                a.className = c;
                a.innerHTML = B.convertNumbers(e);
                a.calendar = B;
                a.ttip = Calendar._TT.TIME_PART;
                a.navtype = 50;
                a._range = [];
                if (typeof d != "number") {
                    a._range = d
                } else {
                    for (var b = d; b <= f; ++b) {
                        var Z;
                        if (b < 10 && f >= 10) {
                            Z = "0" + b
                        } else {
                            Z = "" + b
                        }
                        a._range[a._range.length] = Z
                    }
                }
                Calendar._add_evs(a);
                return a
            }
            var X = B.date.getUTCHours();
            var Q = B.date.getUTCMinutes();
            var Y = !B.time24;
            var R = (X > 12);
            if (Y && R) {
                X -= 12
            }
            var V = T("hour", X, Y ? 1 : 0, Y ? 12 : 23);
            var U = Calendar.createElement("span", M);
            U.innerHTML = ":";
            U.className = "colon";
            var S = T("minute", Q, 0, 59);
            var W = null;
            M = Calendar.createElement("td", P);
            M.className = "time";
            M.colSpan = 2;
            if (Y) {
                W = T("ampm", R ? Calendar._TT.LPM : Calendar._TT.LAM, [Calendar._TT.LAM, Calendar._TT.LPM])
            } else {
                M.innerHTML = "&nbsp;"
            }
            B.onSetTime = function() {
                var a, Z = this.date.getUTCHours(),
                    b = this.date.getUTCMinutes();
                if (Y) {
                    a = (Z >= 12);
                    if (a) {
                        Z -= 12
                    }
                    if (Z == 0) {
                        Z = 12
                    }
                    W.innerHTML = a ? Calendar._TT.LPM : Calendar._TT.LAM
                }
                Z = (Z < 10) ? ("0" + Z) : Z;
                b = (b < 10) ? ("0" + b) : b;
                V.innerHTML = B.convertNumbers(Z);
                S.innerHTML = B.convertNumbers(b)
            };
            B.onUpdateTime = function() {
                var a = this.date;
                var b = parseInt(V.innerHTML.toEnglish(), 10);
                if (Y) {
                    if ((W.innerHTML == Calendar._TT.LPM || W.innerHTML == Calendar._TT.PM) && b < 12) {
                        b += 12
                    } else {
                        if ((W.innerHTML == Calendar._TT.LAM || W.innerHTML == Calendar._TT.AM) && b == 12) {
                            b = 0
                        }
                    }
                }
                var c = a.getLocalDate(true, this.dateType);
                var Z = a.getLocalMonth(true, this.dateType);
                var e = a.getLocalFullYear(true, this.dateType);
                a.setUTCHours(b);
                a.setUTCMinutes(parseInt(S.innerHTML.toEnglish(), 10));
                a._calSetLocalFullYear(this.dateType, e);
                a.setLocalMonth(true, this.dateType, Z);
                a.setLocalDate(true, this.dateType, c);
                this.dateClicked = false;
                this.callHandler()
            }
        })()
    } else {
        this.onSetTime = this.onUpdateTime = function() {}
    }
    var J = Calendar.createElement("tfoot", O);
    P = Calendar.createElement("tr", J);
    P.className = "footrow";
    M = E(Calendar._TT.SEL_DATE, this.weekNumbers ? 8 : 7, 300);
    M.className = "ttip";
    if (this.isPopup) {
        M.ttip = Calendar._TT.DRAG_TO_MOVE;
        M.style.cursor = "move"
    }
    this.tooltips = M;
    A = Calendar.createElement("div", this.element);
    this.monthsCombo = A;
    A.className = "combo";
    for (H = 0; H < Calendar._MN.length; ++H) {
        var D = Calendar.createElement("div");
        D.className = Calendar.is_ie ? "label-IEfix" : "label";
        D.month = H;
        D.innerHTML = (this.dateType == "jalali" ? Calendar._JSMN[H] : Calendar._SMN[H]);
        A.appendChild(D)
    }
    A = Calendar.createElement("div", this.element);
    this.yearsCombo = A;
    A.className = "combo";
    for (H = 12; H > 0; --H) {
        var N = Calendar.createElement("div");
        N.className = Calendar.is_ie ? "label-IEfix" : "label";
        A.appendChild(N)
    }
    this._init(this.firstDayOfWeek, this.date);
    K.appendChild(this.element)
};
Calendar.prototype.recreate = function() {
    if (this.element) {
        var A = this.element.parentNode;
        A.removeChild(this.element);
        if (A == document.body) {
            this.create()
        } else {
            this.create(A);
            this.show()
        }
    } else {
        this.create()
    }
};
Calendar.prototype.setWeekNumbers = function(A) {
    this.weekNumbers = A;
    this.recreate()
};
Calendar.prototype.setOtherMonths = function(A) {
    this.showsOtherMonths = A;
    this.refresh()
};
Calendar.prototype.setLangNumbers = function(A) {
    this.langNumbers = A;
    this.refresh()
};
Calendar.prototype.setDateType = function(A) {
    this.dateType = A;
    this.recreate()
};
Calendar.prototype.setShowsTime = function(A) {
    this.showsTime = A;
    this.recreate()
};
Calendar.prototype.setTime24 = function(A) {
    this.time24 = A;
    this.recreate()
};
Calendar._keyEvent = function(L) {
    var A = window._dynarch_popupCalendar;
    if (!A || A.multiple) {
        return false
    }(Calendar.is_ie) && (L = window.event);
    var I = (Calendar.is_ie || L.type == "keypress"),
        M = L.keyCode;
    if (Calendar._DIR == "rtl") {
        if (M == 37) {
            M = 39
        } else {
            if (M == 39) {
                M = 37
            }
        }
    }
    if (L.ctrlKey) {
        switch (M) {
            case 37:
                I && Calendar.cellClick(A._nav_pm);
                break;
            case 38:
                I && Calendar.cellClick(A._nav_py);
                break;
            case 39:
                I && Calendar.cellClick(A._nav_nm);
                break;
            case 40:
                I && Calendar.cellClick(A._nav_ny);
                break;
            default:
                return false
        }
    } else {
        switch (M) {
            case 32:
                Calendar.cellClick(A._nav_now);
                break;
            case 27:
                I && A.callCloseHandler();
                break;
            case 37:
            case 38:
            case 39:
            case 40:
                if (I) {
                    var E, N, J, G, C, D;
                    E = M == 37 || M == 38;
                    D = (M == 37 || M == 39) ? 1 : 7;

                    function B() {
                        C = A.currentDateEl;
                        var K = C.pos;
                        N = K & 15;
                        J = K >> 4;
                        G = A.ar_days[J][N]
                    }
                    B();

                    function F() {
                        var K = new Date(A.date);
                        K.setLocalDate(true, A.dateType, K.getLocalDate(true, A.dateType) - D);
                        A.setDate(K)
                    }

                    function H() {
                        var K = new Date(A.date);
                        K.setLocalDate(true, A.dateType, K.getLocalDate(true, A.dateType) + D);
                        A.setDate(K)
                    }
                    while (1) {
                        switch (M) {
                            case 37:
                                if (--N >= 0) {
                                    G = A.ar_days[J][N]
                                } else {
                                    N = 6;
                                    M = 38;
                                    continue
                                }
                                break;
                            case 38:
                                if (--J >= 0) {
                                    G = A.ar_days[J][N]
                                } else {
                                    F();
                                    B()
                                }
                                break;
                            case 39:
                                if (++N < 7) {
                                    G = A.ar_days[J][N]
                                } else {
                                    N = 0;
                                    M = 40;
                                    continue
                                }
                                break;
                            case 40:
                                if (++J < A.ar_days.length) {
                                    G = A.ar_days[J][N]
                                } else {
                                    H();
                                    B()
                                }
                                break
                        }
                        break
                    }
                    if (G) {
                        if (!G.disabled) {
                            Calendar.cellClick(G)
                        } else {
                            if (E) {
                                F()
                            } else {
                                H()
                            }
                        }
                    }
                }
                break;
            case 13:
                if (I) {
                    Calendar.cellClick(A.currentDateEl, L)
                }
                break;
            default:
                return false
        }
    }
    return Calendar.stopEvent(L)
};
Calendar.prototype._init = function(L, V) {
    var U = new Date(),
        P = U.getLocalFullYear(false, this.dateType),
        X = U.getLocalMonth(false, this.dateType),
        B = U.getLocalDate(false, this.dateType);
    this.table.style.visibility = "hidden";
    var H = V.getLocalFullYear(true, this.dateType);
    if (H < this.minYear) {
        H = this.minYear;
        V._calSetLocalFullYear(this.dateType, H)
    } else {
        if (H > this.maxYear) {
            H = this.maxYear;
            V._calSetLocalFullYear(this.dateType, H)
        }
    }
    this.firstDayOfWeek = L;
    this.date = new Date(V);
    var W = V.getLocalMonth(true, this.dateType);
    var Z = V.getLocalDate(true, this.dateType);
    var Y = V.getLocalMonthDays(this.dateType);
    V.setLocalDate(true, this.dateType, 1);
    var Q = (V.getUTCDay() - this.firstDayOfWeek) % 7;
    if (Q < 0) {
        Q += 7
    }
    V.setLocalDate(true, this.dateType, -Q);
    V.setLocalDate(true, this.dateType, V.getLocalDate(true, this.dateType) + 1);
    var E = this.tbody.firstChild;
    var J = (this.dateType == "jalali" ? Calendar._JSMN[W] : Calendar._SMN[W]);
    var N = this.ar_days = new Array();
    var M = Calendar._TT.WEEKEND;
    var D = this.multiple ? (this.datesCells = {}) : null;
    for (var S = 0; S < 6; ++S, E = E.nextSibling) {
        var A = E.firstChild;
        if (this.weekNumbers) {
            A.className = "day wn";
            A.innerHTML = this.convertNumbers(V.getLocalWeekNumber(this.dateType));
            A = A.nextSibling
        }
        E.className = "daysrow";
        var T = false,
            F, C = N[S] = [];
        for (var R = 0; R < 7; ++R, A = A.nextSibling, V.setLocalDate(true, this.dateType, F + 1)) {
            F = V.getLocalDate(true, this.dateType);
            var G = V.getUTCDay();
            A.className = "day";
            A.pos = S << 4 | R;
            C[R] = A;
            var K = (V.getLocalMonth(true, this.dateType) == W);
            if (!K) {
                if (this.showsOtherMonths) {
                    A.className += " othermonth";
                    A.otherMonth = true
                } else {
                    A.className = "emptycell";
                    A.innerHTML = "&nbsp;";
                    A.disabled = true;
                    continue
                }
            } else {
                A.otherMonth = false;
                T = true
            }
            A.disabled = false;
            A.innerHTML = this.getDateText ? this.getDateText(V, F) : this.convertNumbers(F);
            if (D) {
                D[V.print("%Y%m%d", this.dateType, this.langNumbers)] = A
            }
            if (this.getDateStatus) {
                var O = this.getDateStatus(V, H, W, F);
                if (this.getDateToolTip) {
                    var I = this.getDateToolTip(V, H, W, F);
                    if (I) {
                        A.title = I
                    }
                }
                if (O === true) {
                    A.className += " disabled";
                    A.disabled = true
                } else {
                    if (/disabled/i.test(O)) {
                        A.disabled = true
                    }
                    A.className += " " + O
                }
            }
            if (!A.disabled) {
                A.caldate = new Date(V);
                A.ttip = "_";
                if (!this.multiple && K && F == Z && this.hiliteToday) {
                    A.className += " selected";
                    this.currentDateEl = A
                }
                if (V.getLocalFullYear(true, this.dateType) == P && V.getLocalMonth(true, this.dateType) == X && F == B) {
                    A.className += " today";
                    A.ttip += Calendar._TT.PART_TODAY
                }
                if (M.indexOf(G.toString()) != -1) {
                    A.className += A.otherMonth ? " oweekend" : " weekend"
                }
            }
        }
        if (!(T || this.showsOtherMonths)) {
            E.className = "emptyrow"
        }
    }
    this.title.innerHTML = (this.dateType == "jalali" ? Calendar._JMN[W] : Calendar._MN[W]) + ", " + this.convertNumbers(H);
    this.onSetTime();
    this.table.style.visibility = "visible";
    this._initMultipleDates()
};
Calendar.prototype._initMultipleDates = function() {
    if (this.multiple) {
        for (var B in this.multiple) {
            if (this.multiple[B] instanceof Date) {
                var A = this.datesCells[B];
                var C = this.multiple[B];
                if (A) {
                    A.className += " selected"
                }
            }
        }
    }
};
Calendar.prototype._toggleMultipleDate = function(B) {
    if (this.multiple) {
        var C = B.print("%Y%m%d", this.dateType, this.langNumbers);
        var A = this.datesCells[C];
        if (A) {
            var D = this.multiple[C];
            if (!D) {
                Calendar.addClass(A, "selected");
                this.multiple[C] = B
            } else {
                Calendar.removeClass(A, "selected");
                delete this.multiple[C]
            }
        }
    }
};
Calendar.prototype.setDateToolTipHandler = function(A) {
    this.getDateToolTip = A
};
Calendar.prototype.setDate = function(A) {
    if (!A.equalsTo(this.date)) {
        this.date = A;
        this.refresh()
    }
};
Calendar.prototype.refresh = function() {
    if (this.element) {
        this._init(this.firstDayOfWeek, this.date)
    } else {
        this.create()
    }
};
Calendar.prototype.setFirstDayOfWeek = function(A) {
    this._init(A, this.date);
    this._displayWeekdays()
};
Calendar.prototype.setDateStatusHandler = Calendar.prototype.setDisabledHandler = function(A) {
    this.getDateStatus = A
};
Calendar.prototype.setRange = function(A, B) {
    this.minYear = A;
    this.maxYear = B
};
Calendar.prototype.callHandler = function() {
    if (this.onSelected) {
        this.onSelected(this, this.date.print(this.dateFormat, this.dateType, this.langNumbers))
    }
};
Calendar.prototype.callCloseHandler = function() {
    if (this.onClose) {
        this.onClose(this)
    }
    this.hideShowCovered()
};
Calendar.prototype.destroy = function() {
    var A = this.element.parentNode;
    A.removeChild(this.element);
    Calendar._C = null;
    window._dynarch_popupCalendar = null
};
Calendar.prototype.reparent = function(B) {
    var A = this.element;
    A.parentNode.removeChild(A);
    B.appendChild(A)
};
Calendar._checkCalendar = function(B) {
    var C = window._dynarch_popupCalendar;
    if (!C) {
        return false
    }
    var A = Calendar.is_ie ? Calendar.getElement(B) : Calendar.getTargetElement(B);
    for (; A != null && A != C.element; A = A.parentNode) {}
    if (A == null) {
        window._dynarch_popupCalendar.callCloseHandler();
        return Calendar.stopEvent(B)
    }
};
Calendar.prototype.show = function() {
    if (this.isPopup) {
        this.element.parentNode.appendChild(this.element)
    }
    var E = this.table.getElementsByTagName("tr");
    for (var D = E.length; D > 0;) {
        var F = E[--D];
        Calendar.removeClass(F, "rowhilite");
        var C = F.getElementsByTagName("td");
        for (var B = C.length; B > 0;) {
            var A = C[--B];
            Calendar.removeClass(A, "hilite");
            Calendar.removeClass(A, "active")
        }
    }
    this.element.style.display = "block";
    this.hidden = false;
    if (this.isPopup) {
        window._dynarch_popupCalendar = this;
        Calendar.addEvent(document, "keydown", Calendar._keyEvent);
        Calendar.addEvent(document, "keypress", Calendar._keyEvent);
        Calendar.addEvent(document, "mousedown", Calendar._checkCalendar)
    }
    this.hideShowCovered()
};
Calendar.prototype.hide = function() {
    if (this.isPopup) {
        Calendar.removeEvent(document, "keydown", Calendar._keyEvent);
        Calendar.removeEvent(document, "keypress", Calendar._keyEvent);
        Calendar.removeEvent(document, "mousedown", Calendar._checkCalendar)
    }
    this.element.style.display = "none";
    this.hidden = true;
    this.hideShowCovered()
};
Calendar.prototype.showAt = function(A, C) {
    var B = this.element.style;
    B.left = A + "px";
    B.top = C + "px";
    this.show()
};
Calendar.prototype.showAtElement = function(C, D) {
    var A = this;
    var E = Calendar.getAbsolutePos(C);
    if (!D || typeof D != "string") {
        this.showAt(E.x, E.y + C.offsetHeight);
        return true
    }

    function B(I) {
        if (I.x < 0) {
            I.x = 0
        }
        if (I.y < 0) {
            I.y = 0
        }
        var J = document.createElement("div");
        var H = J.style;
        H.position = "absolute";
        H.right = H.bottom = H.width = H.height = "0px";
        document.body.appendChild(J);
        var G = Calendar.getAbsolutePos(J);
        document.body.removeChild(J);
        if (Calendar.is_ie) {
            G.y += document.body.scrollTop;
            G.x += document.body.scrollLeft;
            G.y += (document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop;
			G.x += (document.documentElement && document.documentElement.scrollLeft) || document.body.scrollLeft;
        } else {
            G.y += window.scrollY;
            G.x += window.scrollX
        }
        var F = I.x + I.width - G.x;
        if (F > 0) {
            I.x -= F
        }
        F = I.y + I.height - G.y;
        if (F > 0) {
            I.y -= F
        }
    }
    this.element.style.display = "block";
    Calendar.continuation_for_the_fucking_khtml_browser = function() {
        var F = A.element.offsetWidth;
        var H = A.element.offsetHeight;
        A.element.style.display = "none";
        var G = D.substr(0, 1);
        var I = "l";
        if (D.length > 1) {
            I = D.substr(1, 1)
        }
        switch (G) {
            case "T":
                E.y -= H;
                break;
            case "B":
                E.y += C.offsetHeight;
                break;
            case "C":
                E.y += (C.offsetHeight - H) / 2;
                break;
            case "t":
                E.y += C.offsetHeight - H;
                break;
            case "b":
                break
        }
        switch (I) {
            case "L":
                E.x -= F;
                break;
            case "R":
                E.x += C.offsetWidth;
                break;
            case "C":
                E.x += (C.offsetWidth - F) / 2;
                break;
            case "l":
                E.x += C.offsetWidth - F;
                break;
            case "r":
                break
        }
        E.width = F;
        E.height = H + 40;
        A.monthsCombo.style.display = "none";
        B(E);
        A.showAt(E.x, E.y)
    };
    if (Calendar.is_khtml) {
        setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()", 10)
    } else {
        Calendar.continuation_for_the_fucking_khtml_browser()
    }
};
Calendar.prototype.setDateFormat = function(A) {
    this.dateFormat = A
};
Calendar.prototype.setTtDateFormat = function(A) {
    this.ttDateFormat = A
};
Calendar.prototype.parseDate = function(C, A, B) {
    if (!A) {
        A = this.dateFormat
    }
    if (!B) {
        B = this.dateType
    }
    this.setDate(Date.parseDate(C, A, B))
};
Calendar.prototype.hideShowCovered = function() {
    if (!Calendar.is_ie && !Calendar.is_opera) {
        return
    }

    function B(R) {
        var Q = R.style.visibility;
        if (!Q) {
            if (document.defaultView && typeof(document.defaultView.getComputedStyle) == "function") {
                if (!Calendar.is_khtml) {
                    Q = document.defaultView.getComputedStyle(R, "").getPropertyValue("visibility")
                } else {
                    Q = ""
                }
            } else {
                if (R.currentStyle) {
                    Q = R.currentStyle.visibility
                } else {
                    Q = ""
                }
            }
        }
        return Q
    }
    var P = new Array("applet", "iframe", "select");
    var C = this.element;
    var A = Calendar.getAbsolutePos(C);
    var F = A.x;
    var D = C.offsetWidth + F;
    var O = A.y;
    var N = C.offsetHeight + O;
    for (var H = P.length; H > 0;) {
        var G = document.getElementsByTagName(P[--H]);
        var E = null;
        for (var J = G.length; J > 0;) {
            E = G[--J];
            A = Calendar.getAbsolutePos(E);
            var M = A.x;
            var L = E.offsetWidth + M;
            var K = A.y;
            var I = E.offsetHeight + K;
            if (this.hidden || (M > D) || (L < F) || (K > N) || (I < O)) {
                if (!E.__msh_save_visibility) {
                    E.__msh_save_visibility = B(E)
                }
                E.style.visibility = E.__msh_save_visibility
            } else {
                if (!E.__msh_save_visibility) {
                    E.__msh_save_visibility = B(E)
                }
                E.style.visibility = "hidden"
            }
        }
    }
};
Calendar.prototype._displayWeekdays = function() {
    var B = this.firstDayOfWeek;
    var A = this.firstdayname;
    var D = Calendar._TT.WEEKEND;
    for (var C = 0; C < 7; ++C) {
        A.className = "day name";
        var E = (C + B) % 7;
        if (C) {
            A.ttip = Calendar._TT.DAY_FIRST.replace("%s", Calendar._DN[E]);
            A.navtype = 100;
            A.calendar = this;
            A.fdow = E;
            Calendar._add_evs(A)
        }
        if (D.indexOf(E.toString()) != -1) {
            Calendar.addClass(A, "weekend")
        }
        A.innerHTML = Calendar._SDN[(C + B) % 7];
        A = A.nextSibling
    }
};
Calendar.prototype._hideCombos = function() {
    this.monthsCombo.style.display = "none";
    this.yearsCombo.style.display = "none"
};
Calendar.prototype._dragStart = function(ev) {
    if (this.dragging) {
        return
    }
    this.dragging = true;
    var posX;
    var posY;
    if (Calendar.is_ie) {
        posY = window.event.clientY + document.body.scrollTop;
        posX = window.event.clientX + document.body.scrollLeft
    } else {
        posY = ev.clientY + window.scrollY;
        posX = ev.clientX + window.scrollX
    }
    var st = this.element.style;
    this.xOffs = posX - parseInt(st.left);
    this.yOffs = posY - parseInt(st.top);
    with(Calendar) {
        addEvent(document, "mousemove", calDragIt);
        addEvent(document, "mouseup", calDragEnd)
    }
};
Date._MD = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
Date._JMD = new Array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
Date.SECOND = 1000;
Date.MINUTE = 60 * Date.SECOND;
Date.HOUR = 60 * Date.MINUTE;
Date.DAY = 24 * Date.HOUR;
Date.WEEK = 7 * Date.DAY;
Date.parseDate = function(I, M, C) {
    I = I.toEnglish();
    var N = new Date();
    var G = new Date();
    var E = null;
    var J = null;
    var O = null;
    var R = 0;
    var K = 0;
    var P = 0;
    var A = 0;
    var Q = M.match(/%.|[^%]+/g);
    for (var L = 0; L < Q.length; L++) {
        if (Q[L].charAt(0) == "%") {
            switch (Q[L]) {
                case "%%":
                case "%t":
                case "%n":
                case "%u":
                case "%w":
                    I = I.substr(1);
                    break;
                    I = I.substr(1);
                    break;
                case "%U":
                case "%W":
                case "%V":
                    var B;
                    if (B = I.match(/^[0-5]?\d/)) {
                        I = I.substr(B[0].length)
                    }
                    break;
                case "%C":
                    var H;
                    if (H = I.match(/^\d{1,2}/)) {
                        I = I.substr(H[0].length)
                    }
                    break;
                case "%A":
                case "%a":
                    var S = (Q[L] == "%a") ? Calendar._SDN : Calendar._DN;
                    for (j = 0; j < 7; ++j) {
                        if (I.substr(0, S[j].length).toLowerCase() == S[j].toLowerCase()) {
                            I = I.substr(S[j].length);
                            break
                        }
                    }
                    break;
                case "%d":
                case "%e":
                    if (O = I.match(/^[0-3]?\d/)) {
                        I = I.substr(O[0].length);
                        O = parseInt(O[0], 10)
                    }
                    break;
                case "%m":
                    if (J = I.match(/^[01]?\d/)) {
                        I = I.substr(J[0].length);
                        J = parseInt(J[0], 10) - 1
                    }
                    break;
                case "%Y":
                case "%y":
                    if (E = I.match(/^\d{2,4}/)) {
                        I = I.substr(E[0].length);
                        E = parseInt(E[0], 10);
                        if (E < 100) {
                            if (C == "jalali") {
                                E += (E > 29) ? 1300 : 1400
                            } else {
                                E += (E > 29) ? 1900 : 2000
                            }
                        }
                    }
                    break;
                case "%b":
                case "%B":
                    if (C == "jalali") {
                        var F = (Q[L] == "%b") ? Calendar._JSMN : Calendar._JMN
                    } else {
                        var F = (Q[L] == "%b") ? Calendar._SMN : Calendar._MN
                    }
                    for (j = 0; j < 12; ++j) {
                        if (I.substr(0, F[j].length).toLowerCase() == F[j].toLowerCase()) {
                            I = I.substr(F[j].length);
                            J = j;
                            break
                        }
                    }
                    break;
                case "%H":
                case "%I":
                case "%k":
                case "%l":
                    if (R = I.match(/^[0-2]?\d/)) {
                        I = I.substr(R[0].length);
                        R = parseInt(R[0], 10)
                    }
                    break;
                case "%P":
                case "%p":
                    if (I.substr(0, Calendar._TT.LPM.length) == Calendar._TT.LPM) {
                        I = I.substr(Calendar._TT.LPM.length);
                        if (R < 12) {
                            R += 12
                        }
                    }
                    if (I.substr(0, Calendar._TT.PM.length) == Calendar._TT.PM) {
                        I = I.substr(Calendar._TT.PM.length);
                        if (R < 12) {
                            R += 12
                        }
                    }
                    if (I.substr(0, Calendar._TT.LAM.length) == Calendar._TT.LAM) {
                        I = I.substr(Calendar._TT.LAM.length);
                        if (R >= 12) {
                            R -= 12
                        }
                    }
                    if (I.substr(0, Calendar._TT.AM.length) == Calendar._TT.AM) {
                        I = I.substr(Calendar._TT.AM.length);
                        if (R >= 12) {
                            R -= 12
                        }
                    }
                    break;
                case "%M":
                    if (K = I.match(/^[0-5]?\d/)) {
                        I = I.substr(K[0].length);
                        K = parseInt(K[0], 10)
                    }
                    break;
                case "%S":
                    if (P = I.match(/^[0-5]?\d/)) {
                        I = I.substr(P[0].length);
                        P = parseInt(P[0], 10)
                    }
                    break;
                case "%s":
                    var D;
                    if (D = I.match(/^-?\d+/)) {
                        return new Date(parseInt(D[0], 10) * 1000)
                    }
                    break;
                default:
                    I = I.substr(2);
                    break
            }
        } else {
            I = I.substr(Q[L].length)
        }
    }
    if (E == null || isNaN(E)) {
        E = N.getLocalFullYear(false, C)
    }
    if (J == null || isNaN(J)) {
        J = N.getLocalMonth(false, C)
    }
    if (O == null || isNaN(O)) {
        O = N.getLocalDate(false, C)
    }
    if (R == null || isNaN(R)) {
        R = N.getHours()
    }
    if (K == null || isNaN(K)) {
        K = N.getMinutes()
    }
    if (P == null || isNaN(P)) {
        P = N.getSeconds()
    }
    G.setLocalFullYear(true, C, E, J, O);
    G.setUTCHours(R, K, P, A);
    return G
};
Date.prototype.getUTCMonthDays = function(B) {
    var A = this.getUTCFullYear();
    if (typeof B == "undefined") {
        B = this.getUTCMonth()
    }
    if (((0 == (A % 4)) && ((0 != (A % 100)) || (0 == (A % 400)))) && B == 1) {
        return 29
    } else {
        return Date._MD[B]
    }
};
Date.prototype.getJalaliUTCMonthDays = function(B) {
    var A = this.getJalaliUTCFullYear();
    if (typeof B == "undefined") {
        B = this.getJalaliUTCMonth()
    }
    if (B == 11 && JalaliDate.checkDate(A, B + 1, 30)) {
        return 30
    } else {
        return Date._JMD[B]
    }
};
Date.prototype.getLocalMonthDays = function(A, B) {
    if (A == "jalali") {
        return this.getJalaliUTCMonthDays(B)
    } else {
        return this.getUTCMonthDays(B)
    }
};
Date.prototype.getUTCDayOfYear = function() {
    var A = new Date(Date.UTC(this.getUTCFullYear(), this.getUTCMonth(), this.getUTCDate(), 0, 0, 0));
    var C = new Date(Date.UTC(this.getUTCFullYear(), 0, 0, 0, 0, 0));
    var B = A - C;
    return Math.floor(B / Date.DAY)
};
Date.prototype.getJalaliUTCDayOfYear = function() {
    var B = new Date(Date.UTC(this.getUTCFullYear(), this.getUTCMonth(), this.getUTCDate(), 0, 0, 0));
    var A = JalaliDate.jalaliToGregorian(this.getJalaliUTCFullYear(), 1, 0);
    var D = new Date(Date.UTC(A[0], A[1] - 1, A[2], 0, 0, 0));
    var C = B - D;
    return Math.floor(C / Date.DAY)
};
Date.prototype.getLocalDayOfYear = function(A) {
    if (A == "jalali") {
        return this.getJalaliUTCDayOfYear()
    } else {
        return this.getUTCDayOfYear()
    }
};
Date.prototype.getUTCWeekNumber = function() {
    var C = new Date(Date.UTC(this.getUTCFullYear(), this.getUTCMonth(), this.getUTCDate(), 0, 0, 0));
    var B = C.getUTCDay();
    C.setUTCDate(C.getUTCDate() - (B + 6) % 7 + 3);
    var A = C.valueOf();
    C.setUTCMonth(0);
    C.setUTCDate(4);
    return Math.round((A - C.valueOf()) / (7 * 86400000)) + 1
};
Date.prototype.getJalaliUTCWeekNumber = function() {
    var A = JalaliDate.jalaliToGregorian(this.getJalaliUTCFullYear(), 1, 1);
    var B = new Date(Date.UTC(A[0], A[1] - 1, A[2], 0, 0, 0));
    var C = this.getJalaliUTCDayOfYear() - ((7 - B.getJalaliUTCDay()) % 7) - 1;
    if (C < 0) {
        return new Date(this - this.getJalaliUTCDay() * Date.DAY).getJalaliUTCWeekNumber()
    }
    return Math.floor(C / 7) + 1
};
Date.prototype.getLocalWeekNumber = function(A) {
    if (A == "jalali") {
        return this.getJalaliUTCWeekNumber()
    } else {
        return this.getUTCWeekNumber()
    }
};
Date.prototype.equalsTo = function(A) {
    return (A && (this.getUTCFullYear() == A.getUTCFullYear()) && (this.getUTCMonth() == A.getUTCMonth()) && (this.getUTCDate() == A.getUTCDate()) && (this.getUTCHours() == A.getUTCHours()) && (this.getUTCMinutes() == A.getUTCMinutes()))
};
Date.prototype.setUTCDateOnly = function(A) {
    var B = new Date(A);
    this.setUTCDate(1);
    this._calSetFullYear(B.getUTCFullYear());
    this.setUTCMonth(B.getUTCMonth());
    this.setUTCDate(B.getUTCDate())
};
Date.prototype.print = function(I, B, N) {
    var J = this.getLocalMonth(true, B);
    var O = this.getLocalDate(true, B);
    var D = this.getLocalFullYear(true, B);
    var A = this.getLocalWeekNumber(true, B);
    var F = this.getUTCDay();
    var G = {};
    var S = this.getUTCHours();
    var M = (S >= 12);
    var C = (M) ? (S - 12) : S;
    var E = this.getLocalDayOfYear(B);
    if (C == 0) {
        C = 12
    }
    var K = this.getUTCMinutes();
    var Q = this.getUTCSeconds();
    G["%a"] = Calendar._SDN[F];
    G["%A"] = Calendar._DN[F];
    G["%b"] = (B == "jalali" ? Calendar._JSMN[J] : Calendar._SMN[J]);
    G["%B"] = (B == "jalali" ? Calendar._JMN[J] : Calendar._MN[J]);
    G["%C"] = 1 + Math.floor(D / 100);
    G["%d"] = (O < 10) ? ("0" + O) : O;
    G["%e"] = O;
    G["%H"] = (S < 10) ? ("0" + S) : S;
    G["%I"] = (C < 10) ? ("0" + C) : C;
    G["%j"] = (E < 100) ? ((E < 10) ? ("00" + E) : ("0" + E)) : E;
    G["%k"] = S;
    G["%l"] = C;
    G["%m"] = (J < 9) ? ("0" + (1 + J)) : (1 + J);
    G["%M"] = (K < 10) ? ("0" + K) : K;
    G["%n"] = "\n";
    G["%p"] = M ? Calendar._TT.PM : Calendar._TT.AM;
    G["%P"] = M ? Calendar._TT.LPM : Calendar._TT.LAM;
    G["%s"] = Math.floor(this.getTime() / 1000);
    G["%S"] = (Q < 10) ? ("0" + Q) : Q;
    G["%t"] = "\t";
    G["%U"] = G["%W"] = G["%V"] = (A < 10) ? ("0" + A) : A;
    G["%u"] = this.getLocalDay(true, B) + 1;
    G["%w"] = this.getLocalDay(true, B);
    G["%y"] = ("" + D).substr(2, 2);
    G["%Y"] = D;
    G["%%"] = "%";
    var H = /%./g;
    if (!Calendar.is_ie5 && !Calendar.is_khtml) {
        I = I.replace(H, function(T) {
            return G[T] || T
        })
    } else {
        var R = I.match(H);
        for (var L = 0; L < R.length; L++) {
            var P = G[R[L]];
            if (P) {
                H = new RegExp(R[L], "g");
                I = I.replace(H, P)
            }
        }
    }
    if (N) {
        I = I.convertNumbers()
    }
    return I
};
Date.prototype._calSetFullYear = function(B) {
    var A = new Date(this);
    A.setUTCFullYear(B);
    if (A.getUTCMonth() != this.getUTCMonth()) {
        this.setUTCDate(28)
    }
    return this.setUTCFullYear(B)
};
Date.prototype._calSetJalaliFullYear = function(B) {
    var A = new Date(this);
    A.setJalaliUTCFullYear(B);
    if (A.getJalaliUTCMonth() != this.getJalaliUTCMonth()) {
        this.setJalaliUTCDate(29)
    }
    return this.setJalaliUTCFullYear(B)
};
Date.prototype._calSetLocalFullYear = function(A, B) {
    if (A == "jalali") {
        return this._calSetJalaliFullYear(B)
    } else {
        return this._calSetFullYear(B)
    }
};
Date.prototype.setLocalFullYear = function(B, C, E, A, D) {
    if (C == "jalali") {
        if (A == undefined) {
            A = B ? this.getJalaliUTCMonth() : this.getJalaliMonth()
        }
        if (D == undefined) {
            D = B ? this.getJalaliUTCDate() : this.getJalaliDate()
        }
        return B ? this.setJalaliUTCFullYear(E, A, D) : this.setJalaliFullYear(E, A, D)
    } else {
        if (A == undefined) {
            A = B ? this.getUTCMonth() : this.getMonth()
        }
        if (D == undefined) {
            D = B ? this.getUTCDate() : this.getDate()
        }
        return B ? this.setUTCFullYear(E, A, D) : this.setFullYear(E, A, D)
    }
};
Date.prototype.setLocalMonth = function(B, C, A, D) {
    if (C == "jalali") {
        if (D == undefined) {
            D = B ? this.getJalaliUTCDate() : this.getJalaliDate()
        }
        return B ? this.setJalaliUTCMonth(A, D) : this.setJalaliMonth(A, D)
    } else {
        if (D == undefined) {
            D = B ? this.getUTCDate() : this.getDate()
        }
        return B ? this.setUTCMonth(A, D) : this.setMonth(A, D)
    }
};
Date.prototype.setLocalDate = function(A, B, C) {
    if (B == "jalali") {
        return A ? this.setJalaliUTCDate(C) : this.setJalaliDate(C)
    } else {
        return A ? this.setUTCDate(C) : this.setDate(C)
    }
};
Date.prototype.getLocalFullYear = function(A, B) {
    if (B == "jalali") {
        return A ? this.getJalaliUTCFullYear() : this.getJalaliFullYear()
    } else {
        return A ? this.getUTCFullYear() : this.getFullYear()
    }
};
Date.prototype.getLocalMonth = function(A, B) {
    if (B == "jalali") {
        return A ? this.getJalaliUTCMonth() : this.getJalaliMonth()
    } else {
        return A ? this.getUTCMonth() : this.getMonth()
    }
};
Date.prototype.getLocalDate = function(A, B) {
    if (B == "jalali") {
        return A ? this.getJalaliUTCDate() : this.getJalaliDate()
    } else {
        return A ? this.getUTCDate() : this.getDate()
    }
};
Date.prototype.getLocalDay = function(A, B) {
    if (B == "jalali") {
        return A ? this.getJalaliUTCDay() : this.getJalaliDay()
    } else {
        return A ? this.getUTCDay() : this.getDay()
    }
};
window._dynarch_popupCalendar = null;