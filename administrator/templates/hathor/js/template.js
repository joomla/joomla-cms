/**
 * @version		$Id$
 * @package		Hathor
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Functions
 */

/**
 * Set focus to username on the login screen
 */
function setFocus() {
	if (document.getElementById("login-page")) {
		document.login.username.select();
		document.login.username.focus();
	}
}

/**
 * Change the skip nav target to work with webkit browsers (Safari/Chrome) and
 * Opera
 */
function setSkip() {
	var is_webkit = navigator.userAgent.toLowerCase().indexOf('webkit') > -1;
	var is_opera = navigator.userAgent.toLowerCase().indexOf('opera') > -1;
	if (is_webkit || is_opera) {
		var target = document.getElementById('skiptarget');
		target.href = "#skiptarget";
		target.innerText = "Start of main content";
		target.setAttribute("tabindex", "0");
		document.getElementById('skiplink').setAttribute("onclick",
				"document.getElementById('skiptarget').focus();");
	}
}

/**
 * Set the Aria Role based on the id
 *
 * @param id
 * @param rolevalue
 * @return
 */
function setRoleAttribute(id, rolevalue) {
	if (document.getElementById(id)) {
		document.getElementById(id).setAttribute("role", rolevalue);
	}
}

/**
 * Set the WAI-ARIA Roles Specify the html id then aria role
 *
 * @return
 */
function setAriaRoleElementsById() {
	setRoleAttribute("header", "banner");
	setRoleAttribute("element-box", "main");
	setRoleAttribute("footer", "contentinfo");
	setRoleAttribute("nav", "navigation");
	setRoleAttribute("submenu", "navigation");
	setRoleAttribute("system-message", "alert");
}

/**
 * This sets the given Aria Property state to true for the given element
 *
 * @param el
 *            The element (tag.class)
 * @param prop
 *            The property to set to true
 * @return
 */
function setPropertyAttribute(el, prop) {
	if (document.getElements(el)) {
		document.getElements(el).set(prop, "true");
	}
}

/**
 * Set the WAI-ARIA Properties Specify the tag.class then the aria property to
 * set to true If classes are changed on the fly (i.e. aria-invalid) they need
 * to be changed there instead of here.
 *
 * @return
 */
function setAriaProperties() {
	setPropertyAttribute("input.required", "aria-required");
	setPropertyAttribute("textarea.required", "aria-required");
	setPropertyAttribute("input.readonly", "aria-readonly");
	setPropertyAttribute("input.invalid", "aria-invalid");
	setPropertyAttribute("textarea.invalid", "aria-invalid");
}


/**
 * Process file
 */

window.addEvent('domready', function() {
	setFocus();
	setSkip();
	setAriaRoleElementsById();
	setAriaProperties();
});



/**
 * For IE6 - Background flicker fix
 */
try {
	document.execCommand('BackgroundImageCache', false, true);
} catch (e) {
}
