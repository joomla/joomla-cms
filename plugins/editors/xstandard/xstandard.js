/**
* @version $Id: xstandard-liet.js,v 1.0 $
* @copyright (C) 2005 Mambosolutions.com
* @license http://creativecommons.org/licenses/by-nc-nd/2.0/ Creative Commons Attribution-NonCommercial-NoDerivs 2.0
* @author http://www.mambosolutions.com
* XStandard Lite is Free Software
*/

// -- Events handling ---------------------------

var mosEvents = { 
	//cross-browser event handling by Scott Andrew
	addEvent : function (elm, evType, fn, useCapture) {
		if (elm.addEventListener)   { 	//EOMB
    		elm.addEventListener(evType, fn, useCapture);
			return true;
		} else if (elm.attachEvent) { 	// IE
	  		var r = elm.attachEvent('on' + evType, fn);
		} else { 								// IE 5 Mac and some others
	   	 elm['on' + evType] = fn;
		}
	},
	removeEvent : function (elm, evType, fn, useCapture) {
		if (elm.removeEventListener)   { //EOMB
    		elm.removeEventListener(evType, fn, useCapture);
		} else if (elm.detachEvent)    { // IE
	  		elm.detachEvent('on' + evType, fn);
		} else { 								// IE 5 Mac and some others
	   	 target['on' + evType] = undefined;
		}
	}
}

// -- XStandard Lite prototype -------------------

function XStandardLite ()
{
	this.instances = null;	
}

XStandardLite.prototype.init = function()
{
	 this.instances = this.getInstances();
	
	 var self = this;
	 document.adminForm.onsubmit = function() {
	 		self.save();
	 }
}

XStandardLite.prototype.getInstances = function() {
	var objects = new Array();
	var elements = document.getElementsByTagName('OBJECT');
	for (var i = 0; i < elements.length; i++) {
    if (elements[i].type == 'application/x-xstandard' ) {
		objects.push(elements[i]);
    }
  }
  
  return objects;
}

XStandardLite.prototype.save = function() {
	for(var instance in this.instances) {
		var object = this.instances[instance];
		object.EscapeUnicode = true;
		document.getElementById(object.className).value = object.value;
	}
}

// -- Loader-----------------------------------

function onLoad() {
	xstandard_lite = new XStandardLite();
	xstandard_lite.init();
} 

mosEvents.addEvent(window, 'load', onLoad, true);


