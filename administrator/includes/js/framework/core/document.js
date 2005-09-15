// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: document.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

// -- Document extension functions --------------

document.controllers = {
	_controllers : new Array(),
	appendController : function(controller) {
		this._controllers.push(controller);
	},
	getControllers : function() {
		return this._controllers;
	}
}

document.taskDispatcher = {
	getControllerForTask : function(task) {
		controllers = document.controllers.getControllers();
		for(controller in controllers)	{
			if(controllers[controller].supportsTask(task)) {
				return controllers[controller];
				break;
			}
		}
		return null;
	}
}

document.addLoadEvent = function(func) { 
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
		oldonload();
      func(); 
	}
  }
}

// based on : xGetElementsByClassName, Copyright 2001-2005 Michael Foster (Cross-Browser.com)
// Part of X, a Cross-Browser Javascript Library, Distributed under the terms of the GNU LGPL

document.getElementsByClassName =  function(sClassName, oElement, sTagName, fn)
{
  var found = new Array();
  var re = new RegExp('\\b'+sClassName+'\\b', 'i');
  var list = oElement.getElementsByTagName(sTagName);
  for (var i = 0; i < list.length; ++i) {
    if (list[i].className && list[i].className.search(re) != -1) {
      found[found.length] = list[i];
      if (fn) fn(list[i]);
    }
  }
  return found;
}


/*document.getElementsByClassName = function(className) {
  var children = document.getElementsByTagName('*') || document.all;
  var elements = new Array();
  
  for (var i = 0; i < children.length; i++) {
    var child = children[i];
    var classNames = child.className.split(' ');
    for (var j = 0; j < classNames.length; j++) {
      if (classNames[j] == className) {
        elements.push(child);
        break;
      }
    }
  }
  
  return elements;
}*/

