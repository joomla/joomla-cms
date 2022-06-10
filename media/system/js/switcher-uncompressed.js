/**
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Switcher behavior
 *
 * @package     Joomla
 * @since       1.5
 */
var JSwitcher = function(toggler, element, _options) {
    var $, $togglers, $elements, current, options = {
        onShow : function() {
        },
        onHide : function() {
        },
        cookieName : 'switcher',
        togglerSelector : 'a',
        elementSelector : 'div.tab',
        elementPrefix : 'page-'
    },

    initialize = function(toggler, element, _options) {
        $ = jQuery.noConflict();
        $.extend(options, _options);

        $togglers = $(toggler).find(options.togglerSelector);
        $elements = $(element).find(options.elementSelector);

        if (($togglers.length === 0) || ($togglers.length !== $elements.length)) {
            return;
        }

        hideAll();

        $togglers.each(function() {
            $(this).on('click', function() {
                display($(this).attr('id'));
            });
        })

        var first = document.location.hash.substring(1);
        if (first) {
            display(first);
        } else if ($togglers.length) {
            display($togglers.first().attr('id'));
        }
    },

    display = function(togglerId) {
        var $toggler = $('#' + togglerId), $element = $('#' + options.elementPrefix + togglerId);

        if ($toggler.length === 0 || $element.length === 0 || togglerId === current) {
            return this;
        }

        if (current) {
            hide($('#' + options.elementPrefix + current));
            $('#' + current).removeClass('active');
        }

        show($element);
        $toggler.addClass('active');
        current = togglerId;
        document.location.hash = current;
        $(window).scrollTop(0);
    },

    hide = function(element) {
        options.onShow(element);
        $(element).hide();
    },

    hideAll = function() {
        $elements.hide();
        $togglers.removeClass('active');
    },

    show = function(element) {
        options.onHide(element);
        $(element).show();
    };

    initialize(toggler, element, _options);

    return{
        display: display,
        hide: hide,
        hideAll: hideAll,
        show: show
    };
};
