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
 * JPopup javascript behavior
 *
 * Used for displaying DHTML only popups instead of using buggy modal windows.
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
 
/* -------------------------------------------- */
/* -- JPopup prototype ------------------------ */
/* -------------------------------------------- */

//constructor
var JPopup = function() { this.constructor.apply(this, arguments);}
JPopup.prototype = {

	constructor: function() 
	{	
		//Initialise parameters
		this.visible     = false;
		this.hideSelects = false;
		
		this.returnFunc = null;
		this.URL        = null;
		this.baseURL    = null;
		
		this.mask       = null;
		this.frame      = null;
		this.container  = null;
		
		this.tabIndexes   = new Array();
		this.tabbableTags = new Array("A","BUTTON","TEXTAREA","INPUT","IFRAME");	
		
		
		//Setup events
		this.registerEvent(window, 'resize');
		this.registerEvent(window, 'scroll');
		
		if (!document.all) {
			// If using Mozilla or Firefox, use Tab-key trap.
			this.registerEvent(document, 'keypress');
		}
		
		//find library base url
		var base     = document.getElementsByTagName('BASE')[0].getAttribute('href');
		this.baseURL = base.replace(/administrator\//g, "");
		
		// Add the HTML to the body
		body = document.getElementsByTagName('body')[0];
		popmask = document.createElement('div');
		popmask.id = 'popup-overlay';
		
		popcont = document.createElement('div');
		popcont.id = 'popup-container';
		popcont.innerHTML = '' +
			'<div id="popup-inner">' +
				'<div id="popup-titlebar">' +
					'<div id="popup-title"></div>' +
					'<div id="popup-controls">' +
						'<img src="'+this.baseURL+'includes/js/joomla/popup-close.gif" onclick="document.popup.hide();" />' +
					'</div>' +
				'</div>' +
				'<iframe style="width:100%;height:100%;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popup-frame" name="popup-frame" width="100%" height="100%"  onload="document.popup.onload();"></iframe>' +
			'</div>';
		body.appendChild(popmask);
		body.appendChild(popcont);
		
		this.mask      = document.getElementById("popup-overlay");
		this.container = document.getElementById("popup-container");
		this.frame     = document.getElementById("popup-frame");	
	
		// check to see if this is IE version 6 or lower. hide select boxes if so
		var brsVersion = parseInt(window.navigator.appVersion.charAt(0), 10);
		if (brsVersion <= 6 && window.navigator.userAgent.indexOf("MSIE") > -1) {
			this.hideSelects = true;
		}
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
	
	onresize: function(event, args)  {
		this.center();
	},

	onscroll: function(event, args)  {
		this.center();
	},
	
	onload: function(event, args)  {
		if(!this.visible) 
			return;
			
		this.frame.style.display = 'block';
		this.setTitle();
	},
	
	onkeypress: function(event, args)  {
		/*
	 	 * Tab key trap. iff popup is shown and key was [TAB], suppress it.
	 	 * @argument e - event - keyboard event that caused this function to be called.
	 	 */
		if (this.visible && event.keyCode == 9)  return false;
	},
	
	/**
	* @argument width - int in pixels
	* @argument height - int in pixels
	* @argument url - url to display
	* @argument returnFunc - function to call when returning true from the window.
	*/
	show: function(url, width, height, returnFunc) 
	{
		this.visible = true;
		
		this.disableTabIndexes();
		
		this.mask.style.display      = "block";
		this.container.style.display = "block";
			
		// calculate where to place the window on screen
		this.center(width, height);
	
		var titleBarHeight = parseInt(document.getElementById("popup-titlebar").offsetHeight, 10);
	
		this.container.style.width = width + "px";
		this.container.style.height = (height+titleBarHeight) + "px";
		
		// need to set the width of the iframe to the title bar width because of the dropshadow
		// some oddness was occuring and causing the frame to poke outside the border in IE6
		this.frame.style.width = parseInt(document.getElementById("popup-titlebar").offsetWidth, 10) + "px";
		this.frame.style.height = (height) + "px";
	
		// load the url
		if(this.URL != url) {
			this.URL = url;
			this.frame.style.display = 'none';
			this.frame.src = url;
		}
	
		this.returnFunc = returnFunc;
		
		// for IE
		if (this.hideSelects == true) {
			this.hideSelectBoxes();
		}
	},

	center: function(width, height) 
	{
		if (this.visible == true) {
			if (width == null || isNaN(width)) {
				width = this.container.offsetWidth;
			}
			if (height == null) {
				height = this.container.offsetHeight;
			}
		
			var fullHeight = this.getViewportHeight();
			var fullWidth  = this.getViewportWidth();
		
			var theBody = document.documentElement;
		
			var scTop = Browser.is_ie ? parseInt(theBody.scrollTop,10) : 0;
			var scLeft = Browser.is_ie ? parseInt(theBody.scrollLeft,10) : 0;
			
			var titleBarHeight = parseInt(document.getElementById("popup-titlebar").offsetHeight, 10);
		
			this.container.style.top =  scTop + ((fullHeight - (height+titleBarHeight)) / 2) + "px";
			this.container.style.left = scLeft + ((fullWidth - width) / 2) + "px";
			
			this.mask.style.height = fullWidth + "px";
			this.mask.style.width = fullWidth + "px";
			//alert(fullWidth + " " + width + " " + this.container.style.left);
		}
	},
	
	/**
 	 * @argument callReturnFunc - bool - determines if we call the return function specified
 	 * @argument returnVal - anything - return value 
 	 */
	hide: function(callReturnFunc) 
	{
		this.visible = false;
		
		this.restoreTabIndexes();
		if (this.mask == null) {
			return;
		}
		this.mask.style.display      = "none";
		this.container.style.display = "none";
		
		if (callReturnFunc == true && this.returnFunc != null) {
			this.returnFunc(window.frames["popup-frame"].returnVal);
		}
		//this.frame.src = this.baseURL+'includes/js/joomla/popup-loading.html';
		
		// display all select boxes
		if (this.hideSelects == true) {
			this.displaySelectBoxes();
		}
	},
	
	/**
	 * Increase the height of the popup
	 */
	increaseHeight: function(height)
	{
		var effect = new fx.Height(this.container, {opacity: false, duration: 200});
		effect.custom(this.container.offsetHeight, this.container.offsetHeight + height - 9);
		
		var effect = new fx.Height(this.frame, {opacity: false, duration: 200});
		effect.custom(this.frame.offsetHeight, this.frame.offsetHeight + height);
	},
	
	/**
	 * Increase the height of the popup
	 */
	decreaseHeight: function(height)
	{
		var effect = new fx.Height(this.container, {opacity: false, duration: 200});
		effect.custom(this.container.offsetHeight, this.container.offsetHeight - height - 6);
		
		var effect = new fx.Height(this.frame, {opacity: false, duration: 200});
		effect.custom(this.frame.offsetHeight, this.frame.offsetHeight - height);
	},

	/**
 	 * Sets the popup title based on the title of the html document it contains.
 	 * Uses a timeout to keep checking until the title is valid.
 	 */
	setTitle: function() 
	{
		document.getElementById("popup-title").innerHTML = window.frames["popup-frame"].document.title;
	},


	/*
	 * For IE.  Go through predefined tags and disable tabbing into them.
	 */
	disableTabIndexes: function() 
	{
		if (document.all) {
			var i = 0;
			for (var j = 0; j < this.tabbableTags.length; j++) {
				var tagElements = document.getElementsByTagName(this.tabbableTags[j]);
				for (var k = 0 ; k < tagElements.length; k++) {
					this.tabIndexes[i] = tagElements[k].tabIndex;
					tagElements[k].tabIndex="-1";
					i++;
				}
			}
		}
	},

	/* 
	 * For IE. Restore tab-indexes.
	 */
	restoreTabIndexes: function() 
	{
		if (document.all) {
			var i = 0;
			for (var j = 0; j < this.tabbableTags.length; j++) {
				var tagElements = document.getElementsByTagName(this.tabbableTags[j]);
				for (var k = 0 ; k < tagElements.length; k++) {
					tagElements[k].tabIndex = this.tabIndexes[i];
					tagElements[k].tabEnabled = true;
					i++;
				}
			}
		}
	},

	/**
	 * Hides all drop down form select boxes on the screen so they do not appear above the mask layer.
	 * IE has a problem with wanted select form tags to always be the topmost z-index or layer
     */
	hideSelectBoxes: function() 
	{
		for(var i = 0; i < document.forms.length; i++) {
			for(var e = 0; e < document.forms[i].length; e++){
				if(document.forms[i].elements[e].tagName == "SELECT") {
					document.forms[i].elements[e].style.visibility="hidden";
				}
			}
		}
	},

	/**
	 * Makes all drop down form select boxes on the screen visible so they do not reappear after the dialog is closed.
	 * IE has a problem with wanted select form tags to always be the topmost z-index or layer
	 */
	displaySelectBoxes: function() 
	{
		for(var i = 0; i < document.forms.length; i++) {
			for(var e = 0; e < document.forms[i].length; e++){
				if(document.forms[i].elements[e].tagName == "SELECT") {
				document.forms[i].elements[e].style.visibility="visible";
				}
			}
		}
	},
	
	getViewportHeight:function() {
		if (window.innerHeight!=window.undefined) return window.innerHeight;
		if (document.compatMode=='CSS1Compat') return document.documentElement.clientHeight;
		if (document.body) return document.body.clientHeight; 
		return window.undefined; 
	},
	
	getViewportWidth:function() {
		if (window.innerWidth!=window.undefined) return window.innerWidth; 
		if (document.compatMode=='CSS1Compat') return document.documentElement.clientWidth; 
		if (document.body) return document.body.clientWidth; 
		return window.undefined; 
	}
}

document.popup = null
document.addLoadEvent(function() {
  var popup = new JPopup()
  document.popup = popup
});