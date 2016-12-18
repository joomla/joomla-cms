/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
var JCaption = function(_selector) {
	'use strict';

    var $, selector,

    initialize = function(_selector) {
        $ = jQuery.noConflict();
        selector = _selector;
        $(selector).each(function(index, el) {
            createCaption(el);
        })
    },

    createCaption = function(element) {
        var $el = $(element),
        caption = $el.attr('title'),
        width = $el.attr("width") || element.width,
        align = $el.attr("align") || $el.css("float") || element.style.styleFloat || "none",
        $p = $('<p/>', {
            "text" : caption,
            "class" : selector.replace('.', '_')
        }),
        $container = $('<div/>', {
            "class" : selector.replace('.', '_') + " " + align,
            "css" : {
                "float" : align,
                "width" : width
            }
        });
        $el.before($container);
        $container.append($el);
        if (caption !== "") {
            $container.append($p);
        }
    }
    initialize(_selector);
};
