/*
	JSCookMenu v1.4.3.  (c) Copyright 2002-2005 by Heng Yuan

	Permission is hereby granted, free of charge, to any person obtaining a
	copy of this software and associated documentation files (the "Software"),
	to deal in the Software without restriction, including without limitation
	the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the
	Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included
	in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
	OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	ITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
	DEALINGS IN THE SOFTWARE.
*/

var _cmIDCount = 0; var _cmIDName = 'cmSubMenuID'; var _cmTimeOut = null; var _cmCurrentItem = null; var _cmNoAction = new Object (); var _cmNoClick = new Object (); var _cmSplit = new Object (); var _cmItemList = new Array (); var _cmNodeProperties = { mainFolderLeft: '', mainFolderRight: '', mainItemLeft: '', mainItemRight: '', folderLeft: '', folderRight: '', itemLeft: '', itemRight: '', mainSpacing: 0, subSpacing: 0, delay: 500, clickOpen: 1
}; function cmNewID ()
{ return _cmIDName + (++_cmIDCount);}
function cmActionItem (item, prefix, isMain, idSub, orient, nodeProperties)
{ var clickOpen = _cmNodeProperties.clickOpen; if (nodeProperties.clickOpen)
clickOpen = nodeProperties.clickOpen; _cmItemList[_cmItemList.length] = item; var index = _cmItemList.length - 1; idSub = (!idSub) ? 'null' : ('\'' + idSub + '\''); orient = '\'' + orient + '\''; prefix = '\'' + prefix + '\''; var onClick = (clickOpen == 3) || (clickOpen == 2 && isMain); var returnStr; if (onClick)
returnStr = ' onmouseover="cmItemMouseOver (this,' + prefix + ',' + isMain + ',' + idSub + ',' + index + ')" onmousedown="cmItemMouseDownOpenSub (this,' + index + ',' + prefix + ',' + orient + ',' + idSub + ')"'; else
returnStr = ' onmouseover="cmItemMouseOverOpenSub (this,' + prefix + ',' + isMain + ',' + idSub + ',' + orient + ',' + index + ')" onmousedown="cmItemMouseDown (this,' + index + ')"'; return returnStr + ' onmouseout="cmItemMouseOut (this,' + nodeProperties.delay + ')" onmouseup="cmItemMouseUp (this,' + index + ')"';}
function cmNoClickItem (item, prefix, isMain, idSub, orient, nodeProperties)
{ _cmItemList[_cmItemList.length] = item; var index = _cmItemList.length - 1; idSub = (!idSub) ? 'null' : ('\'' + idSub + '\''); orient = '\'' + orient + '\''; prefix = '\'' + prefix + '\''; return ' onmouseover="cmItemMouseOver (this,' + prefix + ',' + isMain + ',' + idSub + ',' + index + ')" onmouseout="cmItemMouseOut (this,' + nodeProperties.delay + ')"';}
function cmNoActionItem (item, prefix)
{ return item[1];}
function cmSplitItem (prefix, isMain, vertical)
{ var classStr = 'cm' + prefix; if (isMain)
{ classStr += 'Main'; if (vertical)
classStr += 'HSplit'; else
classStr += 'VSplit';}
else
classStr += 'HSplit'; return eval (classStr);}
function cmDrawSubMenu (subMenu, prefix, id, orient, nodeProperties)
{ var str = '<div class="' + prefix + 'SubMenu" id="' + id + '"><table summary="sub menu" cellspacing="' + nodeProperties.subSpacing + '" class="' + prefix + 'SubMenuTable">'; var strSub = ''; var item; var idSub; var hasChild; var i; var classStr; for (i = 5; i < subMenu.length; ++i)
{ item = subMenu[i]; if (!item)
continue; hasChild = (item.length > 5); idSub = hasChild ? cmNewID () : null; if (item == _cmSplit)
item = cmSplitItem (prefix, 0, true); str += '<tr class="' + prefix + 'MenuItem"'; if (item[0] != _cmNoClick)
str += cmActionItem (item, prefix, 0, idSub, orient, nodeProperties); else
str += cmNoClickItem (item, prefix, 0, idSub, orient, nodeProperties); str += '>'
if (item[0] == _cmNoAction || item[0] == _cmNoClick)
{ str += cmNoActionItem (item, prefix); str += '</tr>'; continue;}
classStr = prefix + 'Menu'; classStr += hasChild ? 'Folder' : 'Item'; str += '<td class="' + classStr + 'Left">'; if (item[0] != null)
str += item[0]; else
str += hasChild ? nodeProperties.folderLeft : nodeProperties.itemLeft; str += '</td><td class="' + classStr + 'Text">' + item[1]; str += '</td><td class="' + classStr + 'Right">'; if (hasChild)
{ str += nodeProperties.folderRight; strSub += cmDrawSubMenu (item, prefix, idSub, orient, nodeProperties);}
else
str += nodeProperties.itemRight; str += '</td></tr>';}
str += '</table></div>' + strSub; return str;}
function cmDraw (id, menu, orient, nodeProperties, prefix)
{ var obj = cmGetObject (id); if (!nodeProperties)
nodeProperties = _cmNodeProperties; if (!prefix)
prefix = ''; var str = '<table summary="main menu" class="' + prefix + 'Menu" cellspacing="' + nodeProperties.mainSpacing + '">'; var strSub = ''; if (!orient)
orient = 'hbr'; var orientStr = String (orient); var orientSub; var vertical; if (orientStr.charAt (0) == 'h')
{ orientSub = 'v' + orientStr.substr (1, 2); str += '<tr>'; vertical = false;}
else
{ orientSub = 'v' + orientStr.substr (1, 2); vertical = true;}
var i; var item; var idSub; var hasChild; var classStr; for (i = 0; i < menu.length; ++i)
{ item = menu[i]; if (!item)
continue; str += vertical ? '<tr' : '<td'; str += ' class="' + prefix + 'MainItem"'; hasChild = (item.length > 5); idSub = hasChild ? cmNewID () : null; str += cmActionItem (item, prefix, 1, idSub, orient, nodeProperties) + '>'; if (item == _cmSplit)
item = cmSplitItem (prefix, 1, vertical); if (item[0] == _cmNoAction || item[0] == _cmNoClick)
{ str += cmNoActionItem (item, prefix); str += vertical? '</tr>' : '</td>'; continue;}
classStr = prefix + 'Main' + (hasChild ? 'Folder' : 'Item'); str += vertical ? '<td' : '<span'; str += ' class="' + classStr + 'Left">'; str += (item[0] == null) ? (hasChild ? nodeProperties.mainFolderLeft : nodeProperties.mainItemLeft)
: item[0]; str += vertical ? '</td>' : '</span>'; str += vertical ? '<td' : '<span'; str += ' class="' + classStr + 'Text">'; str += item[1]; str += vertical ? '</td>' : '</span>'; str += vertical ? '<td' : '<span'; str += ' class="' + classStr + 'Right">'; str += hasChild ? nodeProperties.mainFolderRight : nodeProperties.mainItemRight; str += vertical ? '</td>' : '</span>'; str += vertical ? '</tr>' : '</td>'; if (hasChild)
strSub += cmDrawSubMenu (item, prefix, idSub, orientSub, nodeProperties);}
if (!vertical)
str += '</tr>'; str += '</table>' + strSub; obj.innerHTML = str;}
function cmDrawFromText (id, orient, nodeProperties, prefix)
{ var domMenu = cmGetObject (id); var menu = null; for (var currentDomItem = domMenu.firstChild; currentDomItem; currentDomItem = currentDomItem.nextSibling)
{ if (!currentDomItem.tagName || currentDomItem.tagName.toLowerCase () != 'ul')
continue; menu = cmDrawFromTextSubMenu (currentDomItem); break;}
if (menu)
cmDraw (id, menu, orient, nodeProperties, prefix);}
function cmDrawFromTextSubMenu (domMenu)
{ var items = new Array (); for (var currentDomItem = domMenu.firstChild; currentDomItem; currentDomItem = currentDomItem.nextSibling)
{ if (!currentDomItem.tagName || currentDomItem.tagName.toLowerCase () != 'li')
continue; if (currentDomItem.firstChild == null)
{ items[items.length] = _cmSplit; continue;}
var item = new Array (); var currentItem = currentDomItem.firstChild; for (; currentItem; currentItem = currentItem.nextSibling)
{ if (!currentItem.tagName || currentItem.tagName.toLowerCase () != 'span')
continue; if (!currentItem.firstChild)
item[0] = null; else
item[0] = currentItem.innerHTML; break;}
if (!currentItem)
continue; for (; currentItem; currentItem = currentItem.nextSibling)
{ if (!currentItem.tagName || currentItem.tagName.toLowerCase () != 'a')
continue; item[1] = currentItem.innerHTML; item[2] = currentItem.href; item[3] = currentItem.target; item[4] = currentItem.title; if (item[4] == '')
item[4] = null; break;}
for (; currentItem; currentItem = currentItem.nextSibling)
{ if (!currentItem.tagName || currentItem.tagName.toLowerCase () != 'ul')
continue; var subMenuItems = cmDrawFromTextSubMenu (currentItem); for (i = 0; i < subMenuItems.length; ++i)
item[i + 5] = subMenuItems[i]; break;}
items[items.length] = item;}
return items;}
function cmItemMouseOver (obj, prefix, isMain, idSub, index)
{ clearTimeout (_cmTimeOut); if (!obj.cmPrefix)
{ obj.cmPrefix = prefix; obj.cmIsMain = isMain;}
var thisMenu = cmGetThisMenu (obj, prefix); if (!thisMenu.cmItems)
thisMenu.cmItems = new Array (); var i; for (i = 0; i < thisMenu.cmItems.length; ++i)
{ if (thisMenu.cmItems[i] == obj)
break;}
if (i == thisMenu.cmItems.length)
{ thisMenu.cmItems[i] = obj;}
if (_cmCurrentItem)
{ if (_cmCurrentItem == obj || _cmCurrentItem == thisMenu)
{ var item = _cmItemList[index]; cmSetStatus (item); return;}
var thatPrefix = _cmCurrentItem.cmPrefix; var thatMenu = cmGetThisMenu (_cmCurrentItem, thatPrefix); if (thatMenu != thisMenu.cmParentMenu)
{ if (_cmCurrentItem.cmIsMain)
_cmCurrentItem.className = thatPrefix + 'MainItem'; else
_cmCurrentItem.className = thatPrefix + 'MenuItem'; if (thatMenu.id != idSub)
cmHideMenu (thatMenu, thisMenu, thatPrefix);}
}
_cmCurrentItem = obj; cmResetMenu (thisMenu, prefix); var item = _cmItemList[index]; var isDefaultItem = cmIsDefaultItem (item); if (isDefaultItem)
{ if (isMain)
obj.className = prefix + 'MainItemHover'; else
obj.className = prefix + 'MenuItemHover';}
cmSetStatus (item);}
function cmItemMouseOverOpenSub (obj, prefix, isMain, idSub, orient, index)
{ cmItemMouseOver (obj, prefix, isMain, idSub, index); if (idSub)
{ var subMenu = cmGetObject (idSub); cmShowSubMenu (obj, prefix, subMenu, orient);}
}
function cmItemMouseOut (obj, delayTime)
{ if (!delayTime)
delayTime = _cmNodeProperties.delay; _cmTimeOut = window.setTimeout ('cmHideMenuTime ()', delayTime); window.defaultStatus = '';}
function cmItemMouseDown (obj, index)
{ if (cmIsDefaultItem (_cmItemList[index]))
{ if (obj.cmIsMain)
obj.className = obj.cmPrefix + 'MainItemActive'; else
obj.className = obj.cmPrefix + 'MenuItemActive';}
}
function cmItemMouseDownOpenSub (obj, index, prefix, orient, idSub)
{ cmItemMouseDown (obj, index); if (idSub)
{ var subMenu = cmGetObject (idSub); cmShowSubMenu (obj, prefix, subMenu, orient);}
}
function cmItemMouseUp (obj, index)
{ var item = _cmItemList[index]; var link = null, target = '_self'; if (item.length > 2)
link = item[2]; if (item.length > 3 && item[3])
target = item[3]; if (link != null)
{ window.open (link, target);}
var prefix = obj.cmPrefix; var thisMenu = cmGetThisMenu (obj, prefix); var hasChild = (item.length > 5); if (!hasChild)
{ if (cmIsDefaultItem (item))
{ if (obj.cmIsMain)
obj.className = prefix + 'MainItem'; else
obj.className = prefix + 'MenuItem';}
cmHideMenu (thisMenu, null, prefix);}
else
{ if (cmIsDefaultItem (item))
{ if (obj.cmIsMain)
obj.className = prefix + 'MainItemHover'; else
obj.className = prefix + 'MenuItemHover';}
}
}
function cmMoveSubMenu (obj, subMenu, orient)
{ var mode = String (orient); var p = subMenu.offsetParent; var subMenuWidth = cmGetWidth (subMenu); var horiz = cmGetHorizontalAlign (obj, mode, p, subMenuWidth); if (mode.charAt (0) == 'h')
{ if (mode.charAt (1) == 'b')
subMenu.style.top = (cmGetYAt (obj, p) + cmGetHeight (obj)) + 'px'; else
subMenu.style.top = (cmGetYAt (obj, p) - cmGetHeight (subMenu)) + 'px'; if (horiz == 'r')
subMenu.style.left = (cmGetXAt (obj, p)) + 'px'; else
subMenu.style.left = (cmGetXAt (obj, p) + cmGetWidth (obj) - subMenuWidth) + 'px';}
else
{ if (horiz == 'r')
subMenu.style.left = (cmGetXAt (obj, p) + cmGetWidth (obj)) + 'px'; else
subMenu.style.left = (cmGetXAt (obj, p) - subMenuWidth) + 'px'; if (mode.charAt (1) == 'b')
subMenu.style.top = (cmGetYAt (obj, p)) + 'px'; else
subMenu.style.top = (cmGetYAt (obj, p) + cmGetHeight (obj) - cmGetHeight (subMenu)) + 'px';}
}
function cmGetHorizontalAlign (obj, mode, p, subMenuWidth)
{ var horiz = mode.charAt (2); if (!(document.body))
return horiz; var body = document.body; var browserLeft; var browserRight; if (window.innerWidth)
{ browserLeft = window.pageXOffset; browserRight = window.innerWidth + browserLeft;}
else if (body.clientWidth)
{ browserLeft = body.clientLeft; browserRight = body.clientWidth + browserLeft;}
else
return horiz; if (mode.charAt (0) == 'h')
{ if (horiz == 'r' && (cmGetXAt (obj) + subMenuWidth) > browserRight)
horiz = 'l'; if (horiz == 'l' && (cmGetXAt (obj) + cmGetWidth (obj) - subMenuWidth) < browserLeft)
horiz = 'r'; return horiz;}
else
{ if (horiz == 'r' && (cmGetXAt (obj, p) + cmGetWidth (obj) + subMenuWidth) > browserRight)
horiz = 'l'; if (horiz == 'l' && (cmGetXAt (obj, p) - subMenuWidth) < browserLeft)
horiz = 'r'; return horiz;}
}
function cmShowSubMenu (obj, prefix, subMenu, orient)
{ if (!subMenu.cmParentMenu)
{ var thisMenu = cmGetThisMenu (obj, prefix); subMenu.cmParentMenu = thisMenu; if (!thisMenu.cmSubMenu)
thisMenu.cmSubMenu = new Array (); thisMenu.cmSubMenu[thisMenu.cmSubMenu.length] = subMenu;}
cmMoveSubMenu (obj, subMenu, orient); subMenu.style.visibility = 'visible'; if (document.all)
{ if (!subMenu.cmOverlap)
subMenu.cmOverlap = new Array (); cmHideControl ("IFRAME", subMenu); cmHideControl ("SELECT", subMenu); cmHideControl ("OBJECT", subMenu);}
}
function cmResetMenu (thisMenu, prefix)
{ if (thisMenu.cmItems)
{ var i; var str; var items = thisMenu.cmItems; for (i = 0; i < items.length; ++i)
{ if (items[i].cmIsMain)
str = prefix + 'MainItem'; else
str = prefix + 'MenuItem'; if (items[i].className != str)
items[i].className = str;}
}
}
function cmHideMenuTime ()
{ if (_cmCurrentItem)
{ var prefix = _cmCurrentItem.cmPrefix; cmHideMenu (cmGetThisMenu (_cmCurrentItem, prefix), null, prefix); _cmCurrentItem = null;}
}
function cmHideMenu (thisMenu, currentMenu, prefix)
{ var str = prefix + 'SubMenu'; if (thisMenu.cmSubMenu)
{ var i; for (i = 0; i < thisMenu.cmSubMenu.length; ++i)
{ cmHideSubMenu (thisMenu.cmSubMenu[i], prefix);}
}
while (thisMenu && thisMenu != currentMenu)
{ cmResetMenu (thisMenu, prefix); if (thisMenu.className == str)
{ thisMenu.style.visibility = 'hidden'; cmShowControl (thisMenu);}
else
break; thisMenu = cmGetThisMenu (thisMenu.cmParentMenu, prefix);}
}
function cmHideSubMenu (thisMenu, prefix)
{ if (thisMenu.style.visibility == 'hidden')
return; if (thisMenu.cmSubMenu)
{ var i; for (i = 0; i < thisMenu.cmSubMenu.length; ++i)
{ cmHideSubMenu (thisMenu.cmSubMenu[i], prefix);}
}
cmResetMenu (thisMenu, prefix); thisMenu.style.visibility = 'hidden'; cmShowControl (thisMenu);}
function cmHideControl (tagName, subMenu)
{ var x = cmGetX (subMenu); var y = cmGetY (subMenu); var w = subMenu.offsetWidth; var h = subMenu.offsetHeight; var i; for (i = 0; i < document.all.tags(tagName).length; ++i)
{ var obj = document.all.tags(tagName)[i]; if (!obj || !obj.offsetParent)
continue; var ox = cmGetX (obj); var oy = cmGetY (obj); var ow = obj.offsetWidth; var oh = obj.offsetHeight; if (ox > (x + w) || (ox + ow) < x)
continue; if (oy > (y + h) || (oy + oh) < y)
continue; if(obj.style.visibility == "hidden")
continue; subMenu.cmOverlap[subMenu.cmOverlap.length] = obj; obj.style.visibility = "hidden";}
}
function cmShowControl (subMenu)
{ if (subMenu.cmOverlap)
{ var i; for (i = 0; i < subMenu.cmOverlap.length; ++i)
subMenu.cmOverlap[i].style.visibility = "";}
subMenu.cmOverlap = null;}
function cmGetThisMenu (obj, prefix)
{ var str1 = prefix + 'SubMenu'; var str2 = prefix + 'Menu'; while (obj)
{ if (obj.className == str1 || obj.className == str2)
return obj; obj = obj.parentNode;}
return null;}
function cmIsDefaultItem (item)
{ if (item == _cmSplit || item[0] == _cmNoAction || item[0] == _cmNoClick)
return false; return true;}
function cmGetObject (id)
{ if (document.all)
return document.all[id]; return document.getElementById (id);}
function cmGetWidth (obj)
{ var width = obj.offsetWidth; if (width > 0 || !cmIsTRNode (obj))
return width; if (!obj.firstChild)
return 0; return obj.lastChild.offsetLeft - obj.firstChild.offsetLeft + cmGetWidth (obj.lastChild);}
function cmGetHeight (obj)
{ var height = obj.offsetHeight; if (height > 0 || !cmIsTRNode (obj))
return height; if (!obj.firstChild)
return 0; return obj.firstChild.offsetHeight;}
function cmGetX (obj)
{ var x = 0; do
{ x += obj.offsetLeft; obj = obj.offsetParent;}
while (obj); return x;}
function cmGetXAt (obj, elm)
{ var x = 0; while (obj && obj != elm)
{ x += obj.offsetLeft; obj = obj.offsetParent;}
if (obj == elm)
return x; return x - cmGetX (elm);}
function cmGetY (obj)
{ var y = 0; do
{ y += obj.offsetTop; obj = obj.offsetParent;}
while (obj); return y;}
function cmIsTRNode (obj)
{ var tagName = obj.tagName; return tagName == "TR" || tagName == "tr" || tagName == "Tr" || tagName == "tR";}
function cmGetYAt (obj, elm)
{ var y = 0; if (!obj.offsetHeight && cmIsTRNode (obj))
{ var firstTR = obj.parentNode.firstChild; obj = obj.firstChild; y -= firstTR.firstChild.offsetTop;}
while (obj && obj != elm)
{ y += obj.offsetTop; obj = obj.offsetParent;}
if (obj == elm)
return y; return y - cmGetY (elm);}
function cmSetStatus (item)
{ var descript = ''; if (item.length > 4)
descript = (item[4] != null) ? item[4] : (item[2] ? item[2] : descript); else if (item.length > 2)
descript = (item[2] ? item[2] : descript); window.defaultStatus = descript;}
function cmGetProperties (obj)
{ if (obj == undefined)
return 'undefined'; if (obj == null)
return 'null'; var msg = obj + ':\n'; var i; for (i in obj)
msg += i + ' = ' + obj[i] + '; '; return msg;}