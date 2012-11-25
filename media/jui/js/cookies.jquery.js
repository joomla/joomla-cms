/*jshint eqnull:true */
/*!
 * jQuery Cookie Plugin v1.2
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */ (function ($, document, undefined) {
    var pluses = /\+/g;

    function raw(s) {
        return s;
    }

    function decoded(s) {
        return decodeURIComponent(s.replace(pluses, ' '));
    }
    var config = $.cookie = function (key, value, options) {
        // write
        if (value !== undefined) {
            options = $.extend({}, config.defaults, options);
            if (value === null) {
                options.expires = -1;
            }
            if (typeof options.expires === 'number') {
                var days = options.expires,
                    t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }
            value = config.json ? JSON.stringify(value) : String(value);
            return (document.cookie = [
                encodeURIComponent(key), '=', config.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
                ].join(''));
        }
        // read
        var decode = config.raw ? raw : decoded;
        var cookies = document.cookie.split('; ');
        for (var i = 0, parts;
        (parts = cookies[i] && cookies[i].split('=')); i++) {
            if (decode(parts.shift()) === key) {
                var cookie = decode(parts.join('='));
                return config.json ? JSON.parse(cookie) : cookie;
            }
        }
        return null;
    };
    config.defaults = {};
    $.removeCookie = function (key, options) {
        if ($.cookie(key) !== null) {
            $.cookie(key, null, options);
            return true;
        }
        return false;
    };
})(jQuery, document);
/*!
  @Persistent tabs and accordions for Joomla 3.0 admin
  @Based on http://stackoverflow.com/a/10524697
  @By: Youjoomla LLC
  @License:GNU/GPL v2.
*/
(function ($) {
    $(document).ready(function () {
        function getParameters() {
            var searchString = window.location.search.substring(1),
                params = searchString.split("&"),
                hash = {};
            for (var i = 0; i < params.length; i++) {
                var val = params[i].split("=");
                hash[unescape(val[0])] = unescape(val[1]);
            }
            return hash;
        }
        if (getParameters().layout == 'edit') {
            if (!getParameters().id) {
                uniQueid = getParameters().extension_id;
            } else {
                uniQueid = getParameters().id;
            }
            var uniqueName = getParameters().view + uniQueid;
        } else {
            uniqueName = '';
        }
        if (uniqueName) {
            $('a[data-toggle="tab"]').on('shown', function (e) {
                //save the latest tab using a cookie:
                $.cookie('last_tab' + uniqueName, $(e.target).attr('href'));
            });
            //activate latest tab, if it exists:
            var lastTab = $.cookie('last_tab' + uniqueName);
            // if user did not open any new tabs get first tab
            var findFirst = $('.nav-tabs').find('a').attr('href');
            if (!lastTab) {
                lastTab = findFirst;
            }
            if (lastTab) {
                $('ul.nav-tabs').children().removeClass('active');
                $('a[href=' + lastTab + ']').parents('li:first').addClass('active');
                $('div.tab-content').children().removeClass('active');
                $(lastTab).addClass('active');
            }
            /* last accordion */
            $('.accordion-body').on('shown', function (e) {
                var getlink = this.get('id');
                /* save last accordion */
                $.cookie('last_accordion' + uniqueName, getlink);
            });
            var lastAcc = $.cookie('last_accordion' + uniqueName);
            if (lastAcc) {
                $('a[data-toggle="collapse"]').addClass('collapsed');
                $('.accordion-body').removeClass('in').height('0px');
                $('a[href="#' + lastAcc + '"]').removeClass('collapsed');
                $('#' + lastAcc).addClass('in').height('auto');
            }
        }
    });
})(jQuery)