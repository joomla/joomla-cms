/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JCaption javascript behavior
 *
 * Used for displaying image captions
 *
 * @package     Joomla
 * @since       1.5
 * @version  1.0
 */
var JCaption = function(selector) {
	var insertBefore = function(el, referenceNode) {
		    referenceNode.parentNode.insertBefore(el, referenceNode);
	    },

	    initialize = function(selector) {
		    var elements = document.querySelectorAll(selector);
		    for (var i = 0, count = elements.length; i < count; i++) {
			    createCaption(elements[i], selector);
		    }
	    },

	    createCaption = function(element, selector) {
		    var container, caption = element.getAttribute('title'),
		        width = element.getAttribute("width") || element.style.width,
		        align = element.getAttribute("align") || element.style.cssFloat || "none",
		        pEl = document.createElement('p'),
		        clearClass = selector.replace('.', '_').replace('#', '').replace(',', '').split(' ');
		    pEl.innerHTML = caption;
		    container = document.createElement('div');
		    clearClass.forEach(function(className) {
			    pEl.classList.add(className);
			    container.classList.add(className);
		    });
		    container.classList.add(align);
		    insertBefore(container, element);

		    if (caption !== "") {
			    container.appendChild(pEl);
		    }

		    container.style.cssFloat = align;
		    container.style.width = /px/.test(width) ? width : width + 'px';

		    container.appendChild(element);
	    };

	initialize(selector);
};
