/**
* @version		$Id: $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Unobtrusive Javascript Tree Manager library
 *
 * Inspired by: Alf Magne Kalleland <www.dhtmlgoodies.com>
 * 
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Menu Manager
 * @since		1.5
 */
JTreeManager = function() { this.constructor.apply(this, arguments);}
JTreeManager.prototype =
{
	constructor: function() 
	{	
		var self = this;
		
		this.trees = document.getElementsByClassName('jtree');
		this.cookie = new JCookie();
		this.ajaxObjectArray = new Array();
		this.nodeId = 1;
		this.guid = 1;

		// Path to images
		this.imageFolder	= 'components/com_menus/assets/images/';

		// Image files
		this.folderImage	= 'folder.gif';
		this.plusImage		= 'plus.gif';
		this.minusImage		= 'minus.gif';

		// Cookie - initially expanded nodes
		this.initExpandedNodes = this.cookie.get('jtree_expandedNodes');

		// AJAX Specific
		this.useAjaxToLoadNodesDynamically = false;

		// Build the tree
		$c(this.trees).each(function(tree){
			//self.initTree(tree);
		});
		if(this.initExpandedNodes){
			var nodes = this.initExpandedNodes.split(',');
			for(var no=0;no<nodes.length;no++){
				if(nodes[no]) this.toggleNode(false,nodes[no]);	
			}			
		}	
	},
	
	addEvent: function(element, type, handler) {
	    // assign each event handler a unique ID
	    if (!handler.$$guid) handler.$$guid = this.guid++;
	    // create a hash table of event types for the element
	    if (!element.events) element.events = {};
	    // create a hash table of event handlers for each element/event pair
	    var handlers = element.events[type];
	    if (!handlers) {
	        handlers = element.events[type] = {};
	        // store the existing event handler (if there is one)
	        if (element["on" + type]) {
	            handlers[0] = element["on" + type];
	        }
	    }
	    // store the event handler in the hash table
	    handlers[handler.$$guid] = handler;
	    // assign a global event handler to do all the work
	    element["on" + type] = this.handleEvent;
	},

	handleEvent: function(event) {
	    // grab the event object (IE uses a global event object)
	    event = event || window.event;
	    // get a reference to the hash table of event handlers
	    var handlers = this.events[event.type];
	    // execute each event handler
	    for (var i in handlers) {
	        this.$$handleEvent = handlers[i];
	        this.$$handleEvent(event);
	    }
	},

	expandAll: function(treeId)
	{
		var nodes = document.getElementById(treeId).getElementsByTagName('LI');
		for(var no=0;no<nodes.length;no++){
			var subTrees = nodes[no].getElementsByTagName('UL');
			if(subTrees.length>0 && subTrees[0].style.display!='block'){
				this.toggleNode(false,nodes[no].id.replace(/[^0-9]/g,''));
			}			
		}
	},

	collapseAll: function(treeId)
	{
		var nodes = document.getElementById(treeId).getElementsByTagName('LI');
		for(var no=0;no<nodes.length;no++){
			var subTrees = nodes[no].getElementsByTagName('UL');
			if(subTrees.length>0 && subTrees[0].style.display=='block'){
				this.toggleNode(false,nodes[no].id.replace(/[^0-9]/g,''));
			}			
		}		
	},

	toggleNode: function(e,inputId)
	{
		if(inputId){
			if(!document.getElementById('node'+inputId))return;
			thisNode = document.getElementById('node'+inputId).getElementsByTagName('IMG')[0]; 
		} else {
			thisNode = e;
			if(e.tagName=='A')thisNode = e.parentNode.getElementsByTagName('IMG')[0];	
		}
		if(thisNode.style.visibility=='hidden')return;
		var parentNode = thisNode.parentNode;
		inputId = parentNode.id.replace(/[^0-9]/g,'');
		if(thisNode.src.indexOf(this.plusImage)>=0){
			thisNode.src = thisNode.src.replace(this.plusImage,this.minusImage);
			var ul = parentNode.getElementsByTagName('UL')[0];
			ul.style.display='block';
			if(!this.initExpandedNodes)this.initExpandedNodes = ',';
			if(this.initExpandedNodes.indexOf(',' + inputId + ',')<0) this.initExpandedNodes = this.initExpandedNodes + inputId + ',';
			
			if(this.useAjaxToLoadNodesDynamically){	// Using AJAX/XMLHTTP to get data from the server
				var firstLi = ul.getElementsByTagName('LI')[0];
				var parentId = firstLi.getAttribute('parentId');
				if(!parentId)parentId = firstLi.parentId;
				if(parentId){
					ajaxObjectArray[ajaxObjectArray.length] = new sack();
					var ajaxIndex = ajaxObjectArray.length-1;
					ajaxObjectArray[ajaxIndex].requestFile = ajaxRequestFile + '?parentId=' + parentId;					
					ajaxObjectArray[ajaxIndex].onCompletion = function() { getNodeDataFromServer(ajaxIndex,ul.id,parentId); };	// Specify function that will be executed after file has been found					
					ajaxObjectArray[ajaxIndex].runAJAX();		// Execute AJAX function
				}			
			}
		}else{
			thisNode.src = thisNode.src.replace(this.minusImage,this.plusImage);
			parentNode.getElementsByTagName('UL')[0].style.display='none';
			this.initExpandedNodes = this.initExpandedNodes.replace(',' + inputId,'');
		}	
		this.cookie.set('jtree_expandedNodes',this.initExpandedNodes);
		return false;
	},

	addTreeHTML: function(parent, html)
	{
		// Create a new div tag as a placeholder
		var tmp = document.createElement('UL');
		tmp.id = 'tree'+Math.round(Math.random()*1000000);
		tmp.innerHTML = html;
		parent.appendChild(tmp);
//		this.initTree(tmp);
	},

	addChildNode: function(e,title,url,click)
	{
		var self = this;

		// Get child list if it exists or create if it doesn't
		uls = e.getElementsByTagName('UL');
		if(uls.length==0){
			var ul = document.createElement('UL');
			e.appendChild(ul);
		}else{
			ul = uls[0];
			ul.style.display='block';
		}
		// Set plus image visible
		var img = e.getElementsByTagName('IMG');
		img[0].style.visibility='visible';
		// Create child element
		var li = document.createElement('LI');
		li.className='leaf';
		li.id = 'node' + self.nodeId++;
		// Create child anchor tag
		var a = document.createElement('A');
		a.path = url;
		a.href = '#';
		a.innerHTML = title;
		if (click) {
			self.addEvent(a,'click', function(){eval(click);return false;});
		}
		// Create the new img tag
		var i = document.createElement('IMG');
		i.src = self.imageFolder + self.plusImage;
		i.style.visibility = 'hidden';
		i.onclick = function(){return document.treemanager.toggleNode(this);}
		li.appendChild(i);
		li.appendChild(a);
		ul.id = 'newNode' + Math.round(Math.random()*1000000);
		ul.appendChild(li);
		as = e.getElementsByTagName('A');
		as[0].onclick = img[0].onclick;
		self.toggleNode(as[0]);
	},

	initTree: function(tree)
	{
		var self = this;

		// Get an array of all tree nodes
		var nodes = tree.getElementsByTagName('LI');
		this.subcounter	= 0;
		for(var no=0;no<nodes.length;no++){					
			self.nodeId++;
			var subTrees = nodes[no].getElementsByTagName('UL');
			var img = document.createElement('IMG');
			img.src = self.imageFolder + self.plusImage;
			img.onclick = function(){return document.treemanager.toggleNode(this);}
			if(subTrees.length==0) {
				nodes[no].className = 'leaf';
				img.style.visibility='hidden';
			} else {
				nodes[no].className = 'node';
				subTrees[0].id = 'subtree_'+this.subcounter;
				this.subcounter++;
			}
			var aTag = nodes[no].getElementsByTagName('A')[0];
			aTag.path = aTag.href;
			if (this.useAjaxToLoadNodesDynamically) {
				aTag.href = '#';
			}
			self.addEvent(aTag,'click',function(){return document.treemanager.toggleNode(this);});
			nodes[no].insertBefore(aTag);
			if(!nodes[no].id) nodes[no].id = 'node' + self.nodeId;
		}	
	}
}

Window.onDomReady(function(){
	document.treemanager = new JTreeManager();
});