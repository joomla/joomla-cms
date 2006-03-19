/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	ADxMenu script
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	(c) 2004 - Aleksandar Vacic, www.aplus.co.yu
	Some rights reserved, http://creativecommons.org/licenses/by-sa/2.0/
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	Using:
	X v3.15.2, Cross-Browser.com DHTML Library
	Copyright (c) 2004 Michael Foster, Licensed LGPL (gnu.org)
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/

/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	central object: keeps track of viewport size, initializes menus
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
var ADXM_Constructor = function() {
	//	deny all non-DOM browsers and all IE
	if ((document.all && !window.opera) || !document.getElementById && !document.documentElement) return;

	var self = this;

	//	deny non-supporting browsers
	var _ua = navigator.userAgent.toLowerCase();
	var _nv = (navigator.vendor) ? navigator.vendor.toLowerCase() : "";
	var _np = (navigator.product) ? navigator.product.toLowerCase() : "";

	/*
		allow:
		- Gecko rv:1.5+
		watch for khtml (webkit) and Opera, for future updates
	*/

	if (
		( window.opera )
		|| ( _np == "gecko" && parseFloat(_ua.indexOf("rv:") + 3) < 1.5 )
		|| ( _ua.indexOf("webkit") != -1 )
	) return;

	//	keeps the menu IDs (of the main UL)
	var _aMenuIDs = new Array();
	//	keeps menu layouts
	var _aLayouts = new Array();

	//	return array of 1st level child elements with specified tag name
	function _GetChildsByTagName(oNode, sNodeName) {
		var a = new Array();
		if (oNode && oNode.childNodes && oNode.childNodes.length) {
			for (var i=0;i<oNode.childNodes.length;i++) {
				if (oNode.childNodes[i].nodeName == sNodeName) {
					a[a.length] = oNode.childNodes[i];
				}
			}
		}
		return a;
	};

	//	find menus on the page - look for class named "adxm"
	function _FindMenus() {
		var aTmp = xGetElementsByClassName("adxm", document, "ul", function(oM) {
			var sLayout = "H";
			if ( oM.className.indexOf("adxmV") != -1 )
				sLayout = "V";
			_Add(oM.id, sLayout);
		} );
	};

	//	add the menu to the array
	function _Add(sMenuID, sLayout) {
		if ( typeof(sMenuID) == "undefined") return;
		_aMenuIDs[_aMenuIDs.length] = sMenuID;
		_aLayouts[_aLayouts.length] = sLayout;
	};

	//	process all menus
	function _Process() {
		var nMenus = _aMenuIDs.length, oMenu;
		for (var i=0;i<nMenus;i++) {
			oMenu = document.getElementById(_aMenuIDs[i]);
			if (oMenu) {
				_Processmenu( oMenu, _aLayouts[i], oMenu );
			}
		}
	};

	//	sets properties, handlers and other stuff for menus
	function _Processmenu(oMenu, sLayout, oMainMenu) {
		var aMenuLIs = _GetChildsByTagName(oMenu, "LI");
		var oMenuLI, oUL, aUL;
		for (var i=0;i<aMenuLIs.length;i++) {
			oMenuLI = aMenuLIs[i];
			aUL = oMenuLI.getElementsByTagName("UL");	//	UL elements in sub(main) item
			if (aUL.length)	{	//	has submenus
				//	save properties for positioning
				oMenuLI.submenu = aUL[0];
				oMenuLI.bIsH = (sLayout == "H");

				oMenuLI.onmouseover = function() {
					ADXM.Repos(this);
				};

				//	process the submenu in the same manner. all submenus have vertical layout
				_Processmenu( aUL[0], "V", oMainMenu );
			}

		}// for aMenuLIs
	};

	//	reposition menu to fit in the viewport
	this.Repos = function(oItem) {
		var nTmp;
		//	get the submenu pointer
		var oMenu = oItem.submenu;
		var bIsH = oItem.bIsH;

		//	full submenu size
		var nW = oMenu.offsetWidth;
		var nH = oMenu.offsetHeight;

		//	full parent item size, width + padding + border
		var nIW = oItem.offsetWidth;
		var nIH = oItem.offsetHeight;

		//	inner submenu size, width + padding, no border
		if (nTmp = xGetComputedStyle(oItem, "width")) nIW = nTmp;
		if (nTmp = xGetComputedStyle(oItem, "height")) nIH = nTmp;

		//	where should menu be (top-left point) in parent item coordinates?
		var nLeft = (bIsH) ? 0 : nIW;
		var nTop = (bIsH) ? nIH : 0;

		//	where is that top-left point menu in page coordinates?
		var nPageX = xPageX(oItem) + nLeft;
		var nPageY = xPageY(oItem) + nTop;

		//	get available client dims (with scrolling included)
		_Viewport();
		var nClientW = _nCW;
		var nClientH = _nCH;

		if ( nClientW != 0 && nClientH != 0 ) {
			//	Horizontal placement
			var nDiff = 0;
			if (nPageX < 0) {
				nDiff += -nPageX;
				if (!bIsH) nDiff += nIW;
			} else {
				nTmp = nClientW - (nPageX + nW);
				if (nTmp < 0) {
					nDiff = nTmp;
					if (!bIsH) nDiff = -nW-nIW;
				}
			}
			if ( nDiff != 0 ) {
				oMenu.style.right = "auto";
				nLeft += nDiff;
				xLeft(oMenu, nLeft);
			}

			//	Vertical placement
			var nDiff = 0;
			if (nPageY < 0) {
				nDiff += -nPageY;
				if (bIsH) nDiff += nIH;
			} else {
				nTmp = nClientH - (nPageY + nH);
				if (nTmp < 0) {
					nDiff = nTmp;
					if (bIsH) nDiff = -nH-nIH;
				}
			}
			if ( nDiff != 0 ) {
				oMenu.style.bottom = "auto";
				nTop += nDiff;
				xTop(oMenu, nTop);
			}
		}
	};

	//	fetch client width, including scrolled part
	var _nCW = 0;
	function _FetchCW() {
		var nTmp = xClientWidth();
		if (nTmp > 0) nTmp += xScrollLeft();
		_nCW = nTmp;
	};

	//	fetch client height, including scrolled part
	var _nCH = 0;
	function _FetchCH() {
		var nTmp = xClientHeight();
		if (nTmp > 0) nTmp += xScrollTop();
		_nCH = nTmp;
	};

	//	call this one for window.onresize
	function _Viewport() {
		_FetchCH();
		_FetchCW();
	};

	//	call this one for window.onload
	this.Init = function() {
		_Viewport();
		_FindMenus();
		_Process();
	};

};
var ADXM = new ADXM_Constructor();

/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	set event handlers
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
if (window.addEventListener) {
	window.addEventListener("load", ADXM.Init, false);
} else if (window.attachEvent) {
	window.attachEvent("onload", ADXM.Init);
}

/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	these functions are taken from Mike Foster's X library, and simplified where possible.
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
function xClientWidth() {
	var w = 0;
	if (!window.opera && document.documentElement && document.documentElement.clientWidth)
		w = document.documentElement.clientWidth;
	else if ( xDef(window.innerWidth, window.innerHeight, document.height) ) {
		w = window.innerWidth;
		if (document.height > window.innerHeight) w -= 16;
	}
	return w;
}

function xClientHeight() {
	var h = 0;
	if (!window.opera && document.documentElement && document.documentElement.clientHeight)
		h = document.documentElement.clientHeight;
	else if ( xDef(window.innerHeight, window.innerWidth, document.width) ) {
		h = window.innerHeight;
		if (document.width > window.innerWidth) h -= 16;
	}
	return h;
}

function xScrollLeft() {
	var offset = 0;
	if ( xDef(window.pageXOffset) )
		offset = window.pageXOffset;
	else if ( document.documentElement && document.documentElement.scrollLeft )
		offset = document.documentElement.scrollLeft;
	return offset;
}

function xScrollTop() {
	var offset = 0;
	if ( xDef(window.pageYOffset) )
		offset = window.pageYOffset;
	else if ( document.documentElement && document.documentElement.scrollTop )
		offset = document.documentElement.scrollTop;
	return offset;
}

function xLeft(e, iX) {
	if ( xDef(iX) )
		e.style.left = iX + "px";
	else {
    	if ( xDef(e.offsetLeft) )
			iX = e.offsetLeft;
		else
			iX = parseInt(e.style.left);
    	if (isNaN(iX))
			iX = 0;
	}
	return iX;
}

function xTop(e, iY) {
	if ( xDef(iY) )
		e.style.top = iY + "px";
	else {
		if ( xDef(e.offsetTop) )
			iY = e.offsetTop;
		else
			iY = parseInt(e.style.top);
		if (isNaN(iY))
			iY = 0;
	}
	return iY;
}

function xPageX(e) {
	var x = 0;
	while (e) {
		if ( xDef(e.offsetLeft) ) x += e.offsetLeft;
		else break;
		e = e.offsetParent;
	}
	return x;
}

function xPageY(e) {
	var y = 0;
	while (e) {
		if ( xDef(e.offsetTop) ) y += e.offsetTop;
		else break;
		e = e.offsetParent;
	}
	return y;
}

function xDef() {
	for (var i=0; i<arguments.length; ++i) {
		if ( typeof(arguments[i]) == "undefined" )
			return false;
	}
	return true;
}

function xGetElementsByClassName(clsName, parentEle, tagName, fn) {
	var found = new Array();
	var re = new RegExp('\\b'+clsName+'\\b', 'i');
	var list = parentEle.getElementsByTagName(tagName);
	for (var i = 0; i < list.length; ++i) {
		if (list[i].className.search(re) != -1) {
			found[found.length] = list[i];
			if (fn) fn(list[i]);
		}
	}
	return found;
}

function xGetComputedStyle(oEle, sProp) {
	var p = null;
	if(document.defaultView && document.defaultView.getComputedStyle){
		p = document.defaultView.getComputedStyle(oEle,'').getPropertyValue(sProp);
		p = parseInt(p);
	} else if(oEle.currentStyle) {
		p = oEle.currentStyle[sProp];
		p = parseInt(p);
	}
	return p;
}
