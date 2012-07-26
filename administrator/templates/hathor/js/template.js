/**
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
	if (document.id("login-page")) {
		document.id("form-login").username.select();
		document.id("form-login").username.focus();
	}
}

/**
 * Change the skip nav target to work with webkit browsers (Safari/Chrome) and
 * Opera
 */
function setSkip() {
	if (Browser.Engine.webkit || Browser.opera) {
		var target = document.id('skiptarget');
		target.href = "#skiptarget";
		target.innerText = "Start of main content";
		target.setAttribute("tabindex", "0");
		document.id('skiplink').setAttribute("onclick",
				"document.id('skiptarget').focus();");
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
	if (document.id(id)) {
		document.id(id).setAttribute("role", rolevalue);
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

/** from accessible suckerfish menu by Matt Carroll,
 * mootooled by Bill Tomczak
 */

window.addEvent('domready', function(){
	  var menu = document.id('menu');
	  if (menu && !menu.hasClass('disabled')) {
	    menu.getElements('li').each(function(cel){
	      cel.addEvent('mouseenter', function(){
	        this.addClass('sfhover');
	      });
	      cel.addEvent('mouseleave', function() {
					this.removeClass('sfhover');
				});
	    });

	  	menu.getElements('a').each(function(ael) {
				ael.addEvent('focus', function() {
					this.addClass('sffocus');
					this.getParents('li').addClass('sfhover');
				});
				ael.addEvent('blur', function() {
					this.removeClass('sffocus');
					this.getParents('li').removeClass('sfhover');
				});
			});
		}
	});

window.addEvent('domready', function() {
	setFocus();
	setSkip();
	setAriaRoleElementsById();
	setAriaProperties();
});
