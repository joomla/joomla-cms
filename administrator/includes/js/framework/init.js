// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: init.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

// -- observers --------------------------------

// create the observer
var onPropertyChange = function (oEvent) {
  if (oEvent.propertyName == 'selected') {
		items = document.list.getSelectedItems().length
		document.toolbar.enable(items);
   } 
}

// create an "observer" object with a single  "observe" method.
var observer = { observe: onPropertyChange };

// -- controllers ------------------------------

document.controllers.appendController(submit);
document.controllers.appendController(popup);

// -- loader ----------------------------------

document.toolbar = null;
document.list   = null;
document.addLoadEvent(function() {  
 	document.toolbar = loadToolbar();
	 document.list    = loadList();
});

function loadToolbar() {
  element = document.getElementById('mostoolbar')
  if(element) {
  	 var toolbar = new mosToolbar(element, document)
  	 toolbar.create();
	 return toolbar; 
  }
  return null;
}

function loadList() {
	element = document.getElementById('moslist')
  if(element) {
		var list = new mosList(element, document)
	 	list.addObserver(observer);
  	 	list.create();
		return list;
  }
  return null;
}