// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: events.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

// -- Event handling ----------------------------

function mosEvent(e) // object prototype
{
  	var e = e || window.event;
  	if(!e) return;
	
	this.event = e;
	
  	if(e.type) 	          this.type = e.type;
  	if(e.target)          this.target = e.target;
  	else if(e.srcElement) this.target = e.srcElement;
 }
 
 mosEvent.prototype = {
 	stopPropagation : function() {
		if(window.event && window.event.cancelBubble) {
			this.event.returnValue = false;
		}
		if(this.event && this.event.stopPropagation) {
			this.event.stopPropagation();
		}
	}
 }
 
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