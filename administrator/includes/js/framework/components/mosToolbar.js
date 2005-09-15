// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: mosToolbar.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/* -------------------------------------------- */
/* -- mosToolbar prototype -------------------- */
/* -------------------------------------------- */

mosToolbar.prototype = new mosElement;
mosToolbar.prototype.base = mosElement.prototype;

function mosToolbar(element, parent)
{
	this.base = mosElement.prototype;
	
	if(element) {
		this.element = element;
		this.parent  = parent;
		this.buttons = new Array();
		return this;
	}
	
	return;
}

mosToolbar.prototype.create = function()
{
	//add buttons
	elements = this.element.getElementsByTagName('DIV');
	for (i=0; i < elements.length; i++) {
		if (elements[i].nodeName == "DIV" && elements[i].className == "button" ) {
			this.addButton(elements[i], i)
		}
	}
}

mosToolbar.prototype.enable = function(enable) 
{
	for (i=0; i < this.buttons.length; i++) {
		this.buttons[i].enable(enable);
	}
}

mosToolbar.prototype.addButton = function(node) 
{
	var button = new mosToolbarButton(node, this);
	button.create();
	
	this.buttons.push(button)
}

mosToolbar.prototype.getButtons = function() { 
	return this.buttons 
}

/* -------------------------------------------- */
/* -- mosToolbarButton prototype -------------- */
/* -------------------------------------------- */

mosToolbarButton.prototype = new mosElement;
mosToolbarButton.prototype.base = mosElement.prototype;

function mosToolbarButton(element, parent)
{	
	this.base = mosElement.prototype;
	
	if(element)
	{
		this.element = element;
		this.parent  = parent;
		
		//attributes
		this.enabled = false
		this.task    = null
		
		//nodes
		this.link  = null
		this.image = null
		
		//actions
		this.doToggle   = false;
	}
}

mosToolbarButton.prototype.create = function()
{
	//set link element
	var element = this.element.getElementsByTagName('A')[0];
	this.link = new mosElement(element, this);
	
	//set image element
	element    = this.element.getElementsByTagName('IMG')[0];
	this.image = new mosElement(element, this);
	this.imageSRC = this.image.element.src.slice(0, -4)
	
	//set actions
	this._parseType(this.link);
	
	//set task
	this._parseTask(this.link);
	
	//set enabled
	this.enable(false);
	
	this.addEvent(this.element, 'mouseover', null)
	this.addEvent(this.element, 'mouseout' , null) 
}

mosToolbarButton.prototype.enable = function(enable)
{
	if(!this.doEnable) { //always enabled
		this._enable(true);
		return;
	}
	
	this._enable(enable);
}

mosToolbarButton.prototype.onmouseover = function(event, args) 
{ 
	if(!this.enabled) {
		return;
	}
	
	this.addClass('button-hover');
}

mosToolbarButton.prototype.onmouseout = function(event, args)  
{ 
	if(!this.enabled) {
		return;
	}
	
	this.killClass('button-hover');
}

//Utility functions
mosToolbarButton.prototype._parseType = function(obj)
{
	el = obj.element;
	
	if (!(el && el.type)) {
		return;
	}
	
	types = el.type.split(" ");
	for (var i = types.length; i > 0;) 
	{
		type = types[--i]; //get type
		if(type == "toggle")
			this.doEnable = true
	}
}

mosToolbarButton.prototype._parseTask = function(obj)
{
	el = obj.element;
	
	this.task = el.getAttribute('onclick');
	el.setAttribute('onclick', 'return true');
}

mosToolbarButton.prototype._enable = function(enable)
{
	if(enable) {			//switch state
		this.image.element.src = this.imageSRC + '_f2.png';
		this.link.addClass('enabled');
		this.link.element.setAttribute('href', '#');
		this.link.element.setAttribute('onclick', this.task);
	} else {
		this.image.element.src = this.imageSRC + '.png';
		this.link.killClass('enabled');
		this.link.element.removeAttribute('href');
		this.link.element.setAttribute('onclick', 'return true');
	}
	
	this.enabled = enable;
}