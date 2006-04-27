/**
* @version $Id$
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JMenu javascript behavior
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
  
/* -------------------------------------------- */
/* -- JMenu prototype ------------------------- */
/* -------------------------------------------- */

//constructor
var JMenu = function() { this.constructor.apply(this, arguments);}
JMenu.prototype = {
	constructor: function(element) 
	{	
		var elements = element.getElementsByTagName("LI");
		var nested = null
		for (var i=0; i<elements.length; i++) 
		{
			var element = elements[i];
			
			this.registerEvent(element, 'mouseover');
			this.registerEvent(element, 'mouseout');
			
			//find nested UL
			for (j=0; j < element.childNodes.length; j++) {
				if (element.childNodes[j].nodeName == "UL")  {	
					nested = element.childNodes[j]
				}
			}
			
			if(nested == null) 
				return;
			
			//declare width
			var offsetWidth  = 0;
			
			//find longest child
			for (k=0; k < nested.childNodes.length; k++) {
				var node  = nested.childNodes[k]
				if (node.nodeName == "LI") 
					offsetWidth = (offsetWidth >= node.offsetWidth) ? offsetWidth :  node.offsetWidth;
			}
			
			//match longest child
			for (l=0; l < nested.childNodes.length; l++) {
				var node = nested.childNodes[l]
				if (node.nodeName == "LI") {
					node.style.width = offsetWidth+'px';
				}
			}
			
			nested.style.width = offsetWidth+'px';
		}
	},
	
	onmouseover: function(event, args)  {
		this.addClassName(event.element, 'hover');
	},

	onmouseout: function(event, args)  {
		this.removeClassName(event.element, 'hover');
	},
	
	registerEvent: function(target,type,args) 
	{
		//use a closure to keep scope
		var self = this;
			
		if (target.addEventListener)   { 
    		target.addEventListener(type,onEvent,true);
		} else if (target.attachEvent) { 
	  		target.attachEvent('on'+type,onEvent);
		} 
		
		function onEvent(e)	{
			e = e||window.event;
			e.element = target;
			return self["on"+type](e, args);
		}
	},
	
	addClassName: function(element, className) {
		this.removeClassName(element, className); 
		element.className+=(element.className.length>0?' ':'')+className; 
	},
  
	removeClassName: function(element, className) {
		element.className=element.className.replace(new RegExp("^"+className+"\\b\\s*|\\s*\\b"+className+"\\b",'g'),''); 
	}
}