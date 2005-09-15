// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: mosList.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/* -------------------------------------------- */
/* -- mosList prototype ----------------------- */
/* -------------------------------------------- */

mosList.prototype = new mosElement;
mosList.prototype.base = mosElement.prototype;

function mosList(element, parent)
{
	this.base = mosElement.prototype;
	// inherit from Observable to make this object an Observable
	this.inherit(new Observable());
	
	if(element) 
	{
		this.element = element;
		this.parent  = parent;
		
		this.selector = null;
		this.items    = new Array();
		
		return this;
	}
	
	return;
}

mosList.prototype.create = function()
{
	//get selector
	var head = this.element.getElementsByTagName('THEAD')[0];
	if(head) {
		var selector = head.getElementsByTagName('INPUT')[0];
		if(selector) {
			this.selector = selector;
			this.addEvent(this.selector, 'click', null)
		}	
	}
	
	//get items
	var body = this.element.getElementsByTagName('TBODY')[0];
	if(body) {
		var items = body.getElementsByTagName('TR');
		for (var i = 0; i < items.length; i++) {
				this.addItem(items[i])
		}
	}

}

mosList.prototype.onclick = function(event, args)     
{
	//reverse change setSelected will handle this
	for(var item in this.items) {
		this.items[item].setSelected(this.selector.checked);
	}
}

mosList.prototype.addItem = function(node) 
{
	var item = new mosListItem(node, this);
	item.create();
	
	this.items.push(item)
}

mosList.prototype.getSelectedItems = function()
{
	if(!this.items.length)
		return null;
	
	var selected = new Array();
	for(var item in this.items) {
		if(this.items[item].isSelected()) {
			selected.push(this.items[item]);
		}
	}
	return selected;
}

mosList.prototype.selectAllItems = function()
{
	for(var item in this.items) {
		this.items[item].setSelected(true);
	}
}

/* -------------------------------------------- */
/* -- mosListRow prototype -------------------- */
/* -------------------------------------------- */

mosListItem.prototype = new mosElement;
mosListItem.prototype.base = mosElement.prototype;

function mosListItem(element, parent)
{	
	this.base = mosElement.prototype;
	
	if(element)
	{
		this.element = element;
		this.parent  = parent;
	
		this.selector = null;
		
		this.addProperty('boolean', 'selected', false); 
	}
}

mosListItem.prototype.create = function()
{
	//get selector
	var selector = this.element.getElementsByTagName('INPUT')[0];
	if(selector) {
		this.selector = selector;
		this.addEvent(this.selector, 'click', null)	
	}
}

mosListItem.prototype.onclick = function(event, args)     
{
	//reverse change setSelected will handle this
	if(this.selector) {
		this.setSelected(this.selector.checked);
	}
}

mosListItem.prototype.isSelected = function() {
	if(!this.selector) 
		return false;

	return this.selector.checked;
}

mosListItem.prototype.getId = function() {
	if(!this.selector)
		return null;

	return this.selector.value;

}

mosListItem.prototype.afterPropertyChange = function(oEvent)
{
	if (oEvent.propertyName == 'selected') {
			if(this.selector) {
       this.selector.checked = oEvent.propertyNewValue;
			}
 } 

	this.parent.notify(oEvent);
}