// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: element.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/* -------------------------------------------- */
/* -- mosElement prototype -------------------- */
/* -------------------------------------------- */

function mosElement(element, parent)
{
	if(element) 
	{
		this.element = element;
		this.parent  = parent;
	}
}

mosElement.prototype.create = function()
{
	
}

mosElement.prototype.inherit = function (oSuper) {
  	for (sProperty in oSuper) {
     	this[sProperty] = oSuper[sProperty];
  	}
}

mosElement.prototype.addProperty = function (sType, sName, vValue) {
       
	if (typeof vValue != sType) {
    	throw('Property ' + sName + ' must be of type ' + sType + '.');
      return;
  	}
     
  	this[sName] = vValue;
       
   var sFuncName = sName.charAt(0).toUpperCase() + sName.substring(1, sName.length);
       
  	this["get" + sFuncName] = function () { return this[sName] };
  	this["set" + sFuncName] = function (vNewValue) {

  		if (typeof vNewValue != sType) {
     		throw('Property ' + sName + ' must be of type ' + sType + '.');
      	return;
  		}

 	 	var vOldValue = this['get' + sFuncName]();
  		var oEvent = {  
      	propertyName: sName,  
        	propertyOldValue: vOldValue,  
         propertyNewValue: vNewValue,  
        	returnValue: true  
 		};
  		this.beforePropertyChange(oEvent);
   	if (oEvent.returnValue) {
   		this[sName] = oEvent.propertyNewValue;
			this.afterPropertyChange(oEvent);
  		}
	}
}

//default beforePropertyChange() method – does nothing
mosElement.prototype.beforePropertyChange = function (oEvent) {
     
}

//default afterPropertyChange() method – does nothing
mosElement.prototype.afterPropertyChange = function(oEvent)
{

}

//Events handling
mosElement.prototype.addEvent = function(elm, evType, args) 
{	
	//use a closure to keep scope
	var self = this;
	mosEvents.addEvent(elm, evType, onEvent, false);
	
	function onEvent(e)	{
		var event = new mosEvent(e); 
		return self["on"+evType](event, args);
	}
};


mosElement.prototype.addClass = function(cName) { 
	this.killClass(cName); 
	return this.element && (this.element.className+=(this.element.className.length>0?' ':'')+cName); 
}

mosElement.prototype.killClass = function(cName)	{ 
	return this.element && (this.element.className=this.element.className.replace(new RegExp("^"+cName+"\\b\\s*|\\s*\\b"+cName+"\\b",'g'),'')); 
}

mosElement.prototype.hasClass = function(cName)		{ 
	return (!this.element || !this.element.className)?false:(new RegExp("\\b"+cName+"\\b")).test(this.element.className) 
}





