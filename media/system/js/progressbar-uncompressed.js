/*
 name: Fx.ProgressBar

 description: Creates a progressbar with WAI-ARIA and optional HTML5 support.

 license: MIT-style

 authors:
 - Harald Kirschner <mail [at] digitarald [dot] de>
 - Rouven Weßling <me [at] rouvenwessling [dot] de>

 requires: [Core/Fx, Core/Class, Core/Element]

 provides: Fx.ProgressBar
 */

Fx.ProgressBar = function(_element, _options) {
	'use strict';

    var $, useHtml5, now, $element, indeterminate, options = {
        onComplete : function() {
        },
        text : null,
        html5 : true
    },

    initialize = function(_element, _options) {
        $ = jQuery.noConflict();
        $.extend(options, _options);

        var element, classes = $(_element).attr('class'), id = $(_element).attr('id'), progress;

        element = $(_element).get(0);
        useHtml5 = options.html5 && supportsHtml5();
        if (useHtml5) {
            progress = $('<progress></progress>', {
                'value' : 10,
                'max' : 100,
                'class' : classes,
                'id' : id
            });
            $(element).replaceWith(progress);
            element = progress;
        } else {
            progress = $('<div>', {
                'id' : id,
                'class' : classes,
                'class' : 'progress progress-striped',
                'role' : 'progressbar',
                'aria-valuenow' : '0', // WAI-ARIA
                'aria-valuemin' : '0',
                'aria-valuemax' : '100'
            }).html($('<div>', {
                'class' : 'bar'
            })).get(0);
            $(element).replaceWith(progress);
            element = progress;
        }

        $element = $(element);
        set(0);
    },

    supportsHtml5 = function() {
        return 'value' in document.createElement('progress');
    },

    setIndeterminate = function() {
        indeterminate = true;

        if (useHtml5) {
            $element.removeAttr('value');
        } else {
            $element.find('.bar').css('width', '100%').addClass('active');
            $element.removeAttr('aria-valuenow').attr('title', '');
        }
    },

    set = function(to) {
        var $text = $(options.text);

        if (to >= 100) {
            to = 100;
        }
        now = to;

        if (useHtml5) {
            $element.val(to);
        } else {
            $element.find('.bar').css('width', to + '%');
            $element.removeAttr('aria-valuenow').attr('title', Math.round(to) + '%');
        }

        if ($text.length) {
            $text.text(Math.round(to) + '%');
        }
        if (to >= 100) {
            options.onComplete('complete');
        }

        return this;
    };

    initialize(_element, _options);

    return {
        set : set,
        setIndeterminate : setIndeterminate,
        element : $element.get(0)
    };
};
