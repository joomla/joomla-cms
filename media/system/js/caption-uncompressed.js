/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JCaption javascript behavior
 *
 * Used for displaying image captions
 *
 * @package  Joomla
 * @since    1.5
 * @version  1.1
 */

jQuery.fn.JCaption = function () {
	'use strict';

	var $ = jQuery;
	var selector = this.selector.replace('.', '_');

    return this.each(function () {
        var $el = $(this);
        var caption = $el.prop('title');
        var width = $el.prop('width') || this.width;
        var align = $el.prop('align') || $el.css('float') || this.style.float || 'none';

        var $container = $('<div/>', {
            'class' : selector + ' ' + align,
            'css' : {
                'float' : align,
                'width' : width
            }
        }).insertBefore($el).append($el);

        if (caption !== '') {
            $('<p/>', {
                'text' : caption,
                'class' : selector
            }).appendTo($container);
        }
    });
};

function JCaption (selector) { jQuery(selector).JCaption(); }
