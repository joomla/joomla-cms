/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* 
 * Form Controls
 *
 * these shortcuts are available for any Joomla admin form
 * that uses the standard Joomla toolbar.
*/

// Save
if(document.getElementById('toolbar-apply')!= undefined) {
	Mousetrap.bind('mod+shift+2', () => {
		var toolbar = document.getElementById("toolbar-apply");
		toolbar.getElementsByClassName("button-apply")[0].click();
	});
}

// Save & Close 
if(document.getElementById('save-group-children-save')!= undefined) {
	Mousetrap.bind('mod+shift+3', () => {
		var toolbar = document.getElementById("save-group-children-save");
		toolbar.getElementsByClassName("button-save")[0].click();
	});
}

// Save & New 
if(document.getElementById('save-group-children-save-new')!= undefined) {
	Mousetrap.bind('mod+shift+4', () => {
		var toolbar = document.getElementById("save-group-children-save-new");
		toolbar.getElementsByClassName("button-save-new")[0].click();
	});
}

// Save as copy 
if(document.getElementById('save-group-children-save-copy')!= undefined) {
	Mousetrap.bind('mod+shift+5', () => {
		var toolbar = document.getElementById("save-group-children-save-copy");
		toolbar.getElementsByClassName("button-save-copy")[0].click();
	});
}

// Cancel/Close 
if(document.getElementById('toolbar-cancel')!= undefined) {
	Mousetrap.bind('mod+shift+6', () => {
		var toolbar = document.getElementById("toolbar-cancel");
		toolbar.getElementsByClassName("button-cancel")[0].click();
	});
}	

/* 
 * Item Controls
 *
 * these shortcuts are available for any Joomla admin view
 * that uses the standard Joomla toolbar for letting users
 * create new items.
*/

// New
if(document.getElementById('toolbar-new')!= undefined) {
	Mousetrap.bind('mod+shift+7', () => {
		var toolbar = document.getElementById("toolbar-new");
		toolbar.getElementsByClassName("button-new")[0].click();
	});
}

/* 
 * Admin Shortcuts
 *
 * these shortcuts are available in the Joomla administrator
 * application on any page.
*/

// Control Panel
Mousetrap.bind('mod+shift+1', () => {
    window.open("index.php","_self");
});

// Help
if(document.getElementById('toolbar-help')!= undefined) {
	Mousetrap.bind('j h', () => {
		var toolbar = document.getElementById("toolbar-help");
		toolbar.getElementsByClassName("button-help")[0].click();
	});
}


