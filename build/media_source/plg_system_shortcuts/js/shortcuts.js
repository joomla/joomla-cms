/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.onkeyup = function(e) {

	/* 
	 * Form Controls
	 *
	 * these shortcuts are available for any Joomla admin form
	 * that uses the standard Joomla toolbar.
	*/
	
	// Save - Alt/Opt S
	if (e.altKey && e.which == 83) {
		var toolbar = document.getElementById("toolbar-apply");
		toolbar.getElementsByClassName("button-apply")[0].click(); 
	};
	// Save & Close - Shift + Alt/Opt S
	if (e.shiftKey && e.altKey && e.which == 83) {
		var toolbar = document.getElementById("toolbar-save");
		toolbar.getElementsByClassName("button-save")[0].click();
	};
	// Save & New - Shift + Alt/Opt N
	if (e.shiftKey && e.altKey && e.which == 78) {
		var toolbar = document.getElementById("save-group-children-save-new");
		toolbar.getElementsByClassName("button-save-new")[0].click();
	};
	// Save as Copy - Shift + Alt/Opt C
	if (e.shiftKey && e.altKey && e.which == 67) {
		var toolbar = document.getElementById("save-group-children-save-copy");
		toolbar.getElementsByClassName("button-save-copy")[0].click();
	};
	// Cancel/Close - Alt/Opt X
	if (e.altKey && e.which == 88) {
		var toolbar = document.getElementById("toolbar-cancel");
		toolbar.getElementsByClassName("button-cancel")[0].click();
	};
	
	/* 
	 * Item Controls
	 *
	 * these shortcuts are available for any Joomla admin view
	 * that uses the standard Joomla toolbar for letting users
	 * create new items.
	*/
	
	// New - Alt/Opt N
	if (e.altKey && e.which == 78) {
		var toolbar = document.getElementById("toolbar-new");
		toolbar.getElementsByClassName("button-new")[0].click();
	};
	
	// Help - Alt/Opt H
	if (e.altKey && e.which == 72) {
		var toolbar = document.getElementById("toolbar-help");
		toolbar.getElementsByClassName("button-help")[0].click();
	};
	
	// Options - Alt/Opt O
	if (e.altKey && e.which == 79) {
		var toolbar = document.getElementById("toolbar-options");
		toolbar.getElementsByClassName("button-options")[0].click();
	};
	
	}
	
	/* 
	 * Admin Shortcuts
	 *
	 * these shortcuts are available in the Joomla administrator
	 * application on any page.
	*/
	
	
	