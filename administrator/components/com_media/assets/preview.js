/**
* @version		$Id: popup-imagemanager.js 3604 2006-05-24 00:23:00Z Jinx $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JMediaPreview behavior for media component
 *
 * Inspired in spirit and design by <http://www.huddletogether.com/projects/lightbox/>  This is in some ways an object rewrite of
 * the original lightbox JavaScript behavior.
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Extensions
 * @subpackage	Media
 * @since		1.5
 */
JMediaPreview = function() { this.constructor.apply(this, arguments);}
JMediaPreview.prototype = {
	constructor: function()
	{
		// Get all anchor tags where rel="preview" and attach the preview behavior to it
		var anchors = document.getElementsByTagName("a");
		for (var i=0; i<anchors.length; i++){
			if (anchors[i].getAttribute("href") && (anchors[i].getAttribute("rel") == "preview")){
				anchors[i].onclick = function () {  document.mediapreview.show(this); return false;}
			}
		}
		this.createPreview();
	},

	/**
	 * Pause code execution for a specified amount of time.
	 *  - Reference: <http://www.faqts.com/knowledge_base/view.phtml/aid/1602>
	 */
	pause: function(time)
	{
		var now = new Date();
		var exitTime = now.getTime() + time;
		while (true) {
			now = new Date();
			if (now.getTime() > exitTime)
				return;
		}
	},

	/**
	 * Key press handler.
	 * - hide preview on 'x'
	 */
	processkey: function(e)
	{
		if (e == null) { // ie
			keycode = event.keyCode;
		} else { // mozilla
			keycode = e.which;
		}
		key = String.fromCharCode(keycode).toLowerCase();

		if(key == 'x'){ document.mediapreview.hide(); }
	},

	/**
	 * Set the document onkeypress event handler
	 */
	listen: function()
	{
		document.onkeypress = document.mediapreview.processkey;
	},

	/**
	 * Show the image preview after preloading the image and then centering the image and then display.
	 */
	show: function(link)
	{
		// Prepare Objects
		var overlay			= window.top.document.getElementById('overlay');
		var preview			= window.top.document.getElementById('preview');
		var caption			= window.top.document.getElementById('previewCaption');
		var image			= window.top.document.getElementById('previewImage');
		var loadingImage	= window.top.document.getElementById('loadingImage');
		var container		= window.top.document.getElementById('previewContainer');

		// Get screen information
		var arrayPageSize	= this.getPageSize();
		var arrayPageScroll	= this.getPageScroll();

		// Center the loading image if it exists
		if (loadingImage) {
			loadingImage.style.top		= (arrayPageScroll[1] + ((arrayPageSize[3] - 35 - loadingImage.height) / 2) + 'px');
			loadingImage.style.left		= (((arrayPageSize[0] - 20 - loadingImage.width) / 2) + 'px');
			loadingImage.style.display	= 'block';
		}

		// set height of Overlay to take up whole page and show
		overlay.style.height = (arrayPageSize[1] + 'px');
		overlay.style.display = 'block';

		// preload image
		preload = new Image();
		preload.onload=function()
		{
			image.src = this.src;

			// center lightbox and make sure that the top and left values are not negative
			// and the image placed outside the viewport
			var previewTop	= arrayPageScroll[1] + ((arrayPageSize[3] - 35 - preload.height) / 2);
			var previewLeft	= ((arrayPageSize[0] - 20 - preload.width) / 2);

			preview.style.top = (previewTop < 0) ? "0px" : previewTop + "px";
			preview.style.left = (previewLeft < 0) ? "0px" : previewLeft + "px";

			container.style.width = preload.width + 'px';

			// Set the caption if it exists based upon the link's title attribute
			if(link.getAttribute('title')){
				caption.style.display = 'block';
				caption.innerHTML = link.getAttribute('title');
			} else {
				caption.style.display = 'none';
			}

			// A small pause between the image loading and displaying is required with IE,
			// this prevents the previous image displaying for a short burst causing flicker.
			if (navigator.appVersion.indexOf("MSIE")!=-1){
				document.mediapreview.pause(250);
			}

			if (loadingImage) {	loadingImage.style.display = 'none'; }
			preview.style.display = 'block';
			document.mediapreview.toggleElements('hidden');

			// After image is loaded, update the overlay height as the new image might have
			// increased the overall page height.
			arrayPageSize = document.mediapreview.getPageSize();
			overlay.style.width = '100%';
			overlay.style.height = (arrayPageSize[1] + 'px');

			// Check for 'x' keypress
			document.mediapreview.listen();
			return false;
		}
		image.src	= '';
		preload.src = link.href;
	},

	/*
	 * Hide the image preview and set listeners back to normal.
	 */
	hide: function()
	{
		// get objects
		overlay = window.top.document.getElementById('overlay');
		preview = window.top.document.getElementById('preview');

		// hide preview and overlay
		overlay.style.display = 'none';
		preview.style.display = 'none';

		this.toggleElements('visible');

		// disable keypress listener
		document.onkeypress = '';
	},

	/**
	 * <div id="overlay">
	 *		<a href="#" onclick="document.mediapreview.hide(); return false;"><img id="loadingImage" /></a>
	 *	</div>
	 * <div id="preview">
	 *		<a href="#" onclick="document.mediapreview.hide(); return false;" title="Click anywhere to close image">
	 *			<img id="previewCloseButton" />
	 *			<img id="previewImage" />
	 *		</a>
	 *		<div id="previewContainer">
	 *			<div id="previewCaption"></div>
	 *			<div id="previewMsg"></div>
	 *		</div>
	 * </div>
	 */
	createPreview: function()
	{
		var body = window.top.document.getElementsByTagName("body")[0];

		// create overlay div and hardcode some functional styles (aesthetic styles are in CSS file)
		var overlay = window.top.document.createElement("DIV");
		overlay.setAttribute('id','overlay');
		overlay.onclick = function () { document.mediapreview.hide(); return false; }
		overlay.style.display = 'none';
		overlay.style.position = 'absolute';
		overlay.style.top = '0';
		overlay.style.left = '0';
		overlay.style.zIndex = '900';
	 	overlay.style.width = '100%';
		body.insertBefore(overlay, body.firstChild);

		var arrayPageSize	= this.getPageSize();
		var arrayPageScroll	= this.getPageScroll();

		// preload and create loader image
		var preloader = new Image();

		// if loader image found, create link to hide preview and create loadingimage
		preloader.onload=function()
		{
			var loadingImageLink = window.top.document.createElement("a");
			loadingImageLink.setAttribute('href','#');
			loadingImageLink.onclick = function () { document.mediapreview.hide(); return false; }
			overlay.appendChild(loadingImageLink);

			var loadingImage = window.top.document.createElement("img");
			loadingImage.src = '';
			loadingImage.setAttribute('id','loadingImage');
			loadingImage.style.position = 'absolute';
			loadingImage.style.zIndex = '1500';
			loadingImageLink.appendChild(loadingImage);

			// IE does wierd things with animated gif files.
			preloader.onload=function() {};
			return false;
		}
		preloader.src = '';

		// create preview div, same note about styles as above
		var preview = window.top.document.createElement("div");
		preview.setAttribute('id','preview');
		preview.style.display = 'none';
		preview.style.position = 'absolute';
		preview.style.zIndex = '1000';
		body.insertBefore(preview, overlay.nextSibling);

		// create link
		var link = window.top.document.createElement("a");
		link.setAttribute('href','#');
		link.setAttribute('title','Click to close');
		link.onclick = function () { document.mediapreview.hide(); return false; }
		preview.appendChild(link);

		// preload and create close button image
		var preloadCloseButton = new Image();

		// if close button image found,
		preloadCloseButton.onload=function()
		{
			var closeButton = window.top.document.createElement("img");
			closeButton.src = '';
			closeButton.setAttribute('id','previewCloseButton');
			closeButton.style.position = 'absolute';
			closeButton.style.zIndex = '2000';
			link.appendChild(closeButton);

			return false;
		}
		preloadCloseButton.src = '';

		// create image
		var image = window.top.document.createElement("img");
		image.setAttribute('id','previewImage');
		link.appendChild(image);

		// create details div, a container for the caption and keyboard message
		var container = window.top.document.createElement("div");
		container.setAttribute('id','previewContainer');
		preview.appendChild(container);

		// create caption
		var caption = window.top.document.createElement("div");
		caption.setAttribute('id','previewCaption');
		caption.style.display = 'none';
		container.appendChild(caption);

		// create keyboard message
		var message = window.top.document.createElement("div");
		message.setAttribute('id','previewMsg');
		message.innerHTML = 'press <kbd>x</kbd> to close';
		container.appendChild(message);
	},

	/*
	 * Hide/Show the document elements that bleed through image overlay in certain situations.  Thanks IE
	 */
	toggleElements: function(state)
	{
	    // Select elements
	    selects = window.top.document.getElementsByTagName('select');
	    for(i = 0; i < selects.length; i++) {
	        selects[i].style.visibility = state;
	    }
	    // IFrame elements
	    iframes = window.top.document.getElementsByTagName('iframe');
	    for(i=0;i<iframes.length;i++) {
			if (state == 'hidden') {
			   	iframes[i].scrolling = 'no';
			} else {
			   	iframes[i].scrolling = 'auto';
			}
	    }
	},

// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
	getPageScroll: function()
	{
		var yScroll;

		if (self.pageYOffset) {
			yScroll = self.pageYOffset;
		} else if (window.top.document.documentElement && window.top.document.documentElement.scrollTop){	 // Explorer 6 Strict
			yScroll = window.top.document.documentElement.scrollTop;
		} else if (window.top.document.body) {// all other Explorers
			yScroll = window.top.document.body.scrollTop;
		}

		arrayPageScroll = new Array('',yScroll)
		return arrayPageScroll;
	},

// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
	getPageSize: function()
	{
		var xScroll, yScroll;

		if (window.innerHeight && window.scrollMaxY) {
			xScroll = window.top.document.body.scrollWidth;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (window.top.document.body.scrollHeight > window.top.document.body.offsetHeight){ // all but Explorer Mac
			xScroll = window.top.document.body.scrollWidth;
			yScroll = window.top.document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = window.top.document.body.offsetWidth;
			yScroll = window.top.document.body.offsetHeight;
		}

		var windowWidth, windowHeight;
		if (window.top.innerHeight) {	// all except Explorer
			windowWidth = window.top.innerWidth;
			windowHeight = window.top.innerHeight;
		} else if (window.top.document.documentElement && window.top.document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = window.top.document.documentElement.clientWidth;
			windowHeight = window.top.document.documentElement.clientHeight;
		} else if (window.top.document.body) { // other Explorers
			windowWidth = window.top.document.body.clientWidth;
			windowHeight = window.top.document.body.clientHeight;
		}

		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else {
			pageHeight = yScroll;
		}

		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){
			pageWidth = windowWidth;
		} else {
			pageWidth = xScroll;
		}

		arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
		return arrayPageSize;
	}
}

document.mediapreview = null;
Window.onDomReady(function(){
	document.mediapreview = new JMediaPreview();
});