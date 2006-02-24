/**
 * @version $Id: tinymce.php 1820 2006-01-14 20:29:16Z stingrey $
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


// -- Events handling ---------------------------

var JEvents = { 
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

XStandardLite.prototype.getInstances = function() 
{
	var objects = new Array();
	var elements = document.getElementsByTagName('OBJECT');
	for (var i = 0; i < elements.length; i++) {
    if (elements[i].type == 'application/x-xstandard' ) {
		objects.push(elements[i]);
    }
  }
  
  return objects;
}

XStandardLite.prototype.save = function() 
{
	for(var instance in this.instances) {
		var object = this.instances[instance];
		object.EscapeUnicode = true;
		
		var contents = object.value;
		
		contents = contents.replace(/<joomla:image\s*.*?\/>/gi, '{image}');
		contents = contents.replace(/<joomla:pagebreak\s*.*?\/>/gi, '{pagebreak}');
		contents = contents.replace(/<joomla:readmore\s*.*?\/>/gi, '{readmore}');
		
		document.getElementById(object.className).value = contents;
	}
}

// -- Loader-----------------------------------

function onLoad() {
	xstandard_lite = new XStandardLite();
	xstandard_lite.init();
} 

JEvents.addEvent(window, 'load', onLoad, true);


