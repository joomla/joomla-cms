/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow selected tab to be remained after save or page reload
 * keeping state in sessionStorage with better handling of multiple tab widgets per page
 * and not saving the state if there is no id in the url (like on the CREATE page of content)
 */

jQuery(function ($) {

    $.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results) {
            return results[1];
        } else {
            return null;
        }
    }

    function xpath(el) {
        if (typeof el == "string") return document.evaluate(el, document, null, 0, null)
        if (!el || el.nodeType != 1) return ''
        if (el.id) return "//*[@id='" + el.id + "']"
        var sames = [].filter.call(el.parentNode.children, function (x) {
            return x.tagName == el.tagName
        })
        return xpath(el.parentNode) + '/' + el.tagName.toLowerCase() + (sames.length > 1 ? '[' + ([].indexOf.call(sames, el) + 1) + ']' : '')
    }

    (function ($) {
        $.xpath = function (exp, ctxt) {
            var item, coll = [],
                result = document.evaluate(exp, ctxt || document, null, 5, null);

            while (item = result.iterateNext())
                coll.push(item);

            return $(coll);
        }
    })(jQuery);

    var loadTabs = function () {

        function remove_item(activeTabsHrefs, tabCollection) {
            var b = '';
            for (b in activeTabsHrefs) {
                if (activeTabsHrefs[b].indexOf(tabCollection) > -1) {
                    activeTabsHrefs.splice(b, 1);
                }
            }
            return activeTabsHrefs;
        }

        function getStorageKey() {
            return window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').replace(/#[a-zA-Z0-9%]+/, '');
        }

        function saveActiveTab(event) {

            if (null == $.urlParam('id')) return;

            var href = $(event.target).attr('href');
            var tabCollection = xpath($(event.target).closest('.nav-tabs').first().get(0));

            if (!tabCollection || typeof href == 'undefined') return;

            var storageValue = tabCollection + '|' + href;

            var activeTabsHrefs = JSON.parse(sessionStorage.getItem(getStorageKey()));

            if (!activeTabsHrefs) {
                var activeTabsHrefs = [];
            }

            // Reset the array
            remove_item(activeTabsHrefs, tabCollection);

            // Save clicked tab href to the array
            activeTabsHrefs.push(storageValue);

            // Store the selected tabs hrefs in sessionStorage
            sessionStorage.setItem(getStorageKey(), JSON.stringify(activeTabsHrefs));
        }

        function activateTab(tabFakexPath) {
            var parts = tabFakexPath.split('|');
            jQuery.xpath(parts[0]).find('a[data-toggle="tab"][href="' + parts[1] + '"]').tab('show');
        }

        function hasTab(href) {
            return $('a[data-toggle="tab"][href="' + href + '"]').length;
        }

        // Array with active tabs hrefs
        var activeTabsHrefs = JSON.parse(sessionStorage.getItem(getStorageKey()));

        // jQuery object with all tabs links
        var $tabs = $('a[data-toggle="tab"]');

        $tabs.on('click', function (e) {
            saveActiveTab(e);
        });

        if (activeTabsHrefs !== null) {

            // Clean default tabs
            $tabs.parent('.active').removeClass('active');

            // When moving from tab area to a different view
            $.each(activeTabsHrefs, function (index, tabFakexPath) {

                // Add active attribute for selected tab indicated by url
                activateTab(tabFakexPath);

            });
        } else {
            $tabs.parents('ul').each(function (index, ul) {
                // If no tabs is saved, activate first tab from each tab set and save it
                var href = $(ul).find('a').first().tab('show').attr('href');
                saveActiveTab(href);
            });
        }
    };

    setTimeout(loadTabs, 100);
});
