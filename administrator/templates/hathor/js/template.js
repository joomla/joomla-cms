/**
 * @package		Hathor
 * @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Functions
 */

/**
 * Change the skip nav target to work with webkit browsers (Safari/Chrome) and
 * Opera
 */
function setSkip() {
	var $ = jQuery.noConflict();
	var browser = $.browser;
	if (browser.chrome || browser.safari || browser.opera) {
		var $target = $('#skiptarget');
		$target.attr('href',"#skiptarget");
		$target.text("Start of main content");
		$target.attr("tabindex", "0");
		$('#skiplink').on("click", function(){
			$('#skiptarget').focus();
		});
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
	if (jQuery('#' + id).length) {
		jQuery('#'+ id).attr("role", rolevalue);
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
	if (jQuery(el).length) {
		jQuery(el).attr(prop, "true");
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

jQuery(function($){
	var $menu = $('#menu');
	if ($menu.length && !$menu.hasClass('disabled')) {
		$menu.find('li').each(function(){
			$(this).on('mouseenter', function(){
				$(this).addClass('sfhover');
			});
			$(this).on('mouseleave', function() {
				$(this).removeClass('sfhover');
			});
		});

		$menu.find('a').each(function() {
			$(this).on('focus', function() {
				$(this).addClass('sffocus');
				$(this).closest('li').addClass('sfhover');
			});
			$(this).on('blur', function() {
				$(this).removeClass('sffocus');
				$(this).closest('li').removeClass('sfhover');
			});
		});
	}
});

jQuery(function() {
	setSkip();
	setAriaRoleElementsById();
	setAriaProperties();
});