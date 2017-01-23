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
    var initialize = function(selector) {
        var elements = document.querySelectorAll(selector);
        for (var i = 0, count = elements.length; i < count; i++) {
            createCaption(elements[i]);
        }
    },

    createCaption = function(element) {
        var container, caption = element.getAttribute('title'),
        width = element.getAttribute("width") || element.width,
        align = element.getAttribute("align") || element.style.styleFloat || "none",
        pEl = createElement('<p/>');
        pEl.text = caption;
        pEl.class = selector.replace('.', '_');
        container = createElement('<div/>');
        container.class = selector.replace('.', '_') + " " + align;
        container.style.styleFloat = align;
        container.style.width = width;
        container.parentNode.insertBefore(element, container);
        container.innerHTML = element;
        if (caption !== "") {
            container.innerHTML = pEl;
        }
    };

    initialize(selector);
};
