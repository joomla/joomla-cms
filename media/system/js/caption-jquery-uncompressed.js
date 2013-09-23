/**
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
    var $, selector;
    var initialize = function(selector) {
        $ = jQuery.noConflict();
        selector = selector;
        $(selector).each(function(index, el) {
            createCaption(el);
        })
    }
    var createCaption = function(element) {
        var $el = $(element);
        var caption = $el.attr('title');
        var width = $el.attr("width") || element.width;
        var align = $el.attr("align") || $el.css("float") || element.style.styleFloat || "none";
        var $p = $('<p/>', {
            "text" : caption,
            "class" : selector.replace('.', '_')
        });
        var $container = $('<div/>', {
            "class" : selector.replace('.', '_') + " " + align,
            "css" : {
                "float" : align,
                "width" : width
            }
        });
        $el.parent().before($container, $el);
        $container.append($el);
        if (caption !== "") {
            $container.append($p);
        }
    }
    initialize(selector);
}

 
