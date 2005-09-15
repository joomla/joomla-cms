// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: mosSwitcher.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/* -------------------------------------------- */
/* -- mosSwitcher prototype ------------------- */
/* -------------------------------------------- */

mosSwitcher.prototype = new mosElement;
mosSwitcher.prototype.base = mosElement.prototype;

function mosSwitcher(element, parent)
{
	this.base = mosElement.prototype;
	
	if(element) {
		this.element = element;
		this.parent  = parent;
		this.active  = null;
		return this;
	}
	
	return;
}

mosSwitcher.prototype.create = function()
{
	//hide all
	elements = this.element.childNodes;
	for (i=0; i < elements.length; i++) {
		if (elements[i].nodeName == "DIV") {
			this.hide(elements[i])
		}
	}
}

mosSwitcher.prototype.switchTo = function(id)
{
	element = document.getElementById(id);
		
	if(element) {
		//hide old element
		if(this.active) {
			this.hide(this.active);
		}
		
		//show new element
		this.show(element);
			
		this.active = element;
	}
}

mosSwitcher.prototype.hide = function(element) {
	this.setVisibility(element, false);
}

mosSwitcher.prototype.show = function(element) {
	this.setVisibility(element, true);
}

mosSwitcher.prototype.setVisibility = function(element, bShow) { 
	element.style.display = bShow ? "block" : "none"
}