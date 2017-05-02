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

    // jQuery extension to allow getting of url params
    $.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results) {
            return results[1];
        } else {
            return null;
        }
    }

    // jQuery extension to get the XPATH of a DOM element
    $.getXpath = function (el) {
        if (typeof el == "string") return document.evaluate(el, document, null, 0, null)
        if (!el || el.nodeType != 1) return ''
        if (el.id) return "//*[@id='" + el.id + "']"
        var sames = [].filter.call(el.parentNode.children, function (x) {
            return x.tagName == el.tagName
        })
        return $.getXpath(el.parentNode) + '/' + el.tagName.toLowerCase() + (sames.length > 1 ? '[' + ([].indexOf.call(sames, el) + 1) + ']' : '')
    }

    // jQuery extension to get the DOM element from an XPATH
    $.findXpath = function (exp, ctxt) {
        var item, coll = [],
            result = document.evaluate(exp, ctxt || document, null, 5, null);

        while (item = result.iterateNext())
            coll.push(item);

        return $(coll);
    }

    var loadTabs = function () {

        /**
         * Remove an item from an array
         */
        function remove_item(activeTabsHrefs, tabCollection) {
            var b = '';
            for (b in activeTabsHrefs) {
                if (activeTabsHrefs[b].indexOf(tabCollection) > -1) {
                    activeTabsHrefs.splice(b, 1);
                }
            }
            return activeTabsHrefs;
        }

        /**
         * Generate the sessionStorage key we will use
         */
        function getStorageKey() {
            return window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').replace(/#[a-zA-Z0-9%]+/, '');
        }

        /**
         * Save this tab to the storage in the form of a pseudo keyed array
         */
        function saveActiveTab(event) {

            /**
             * Don't store state if there is no id in the url, allows for not storing on create screens
             * Allow storing of state on Global Config pages
             * Allow storing of state on frontend edit (when a_id is in url)
             */
            if ((null == $.urlParam('id') && $.urlParam('option') != 'com_config') && null == $.urlParam('a_id')) {
                return;
            }

            // get this tabs own href
            var href = $(event.target).attr('href');

            // find the collection of tabs this tab belongs to, and calcuate the unique xpath to it
            var tabCollection = $.getXpath($(event.target).closest('.nav-tabs').first().get(0));

            // error handling
            if (!tabCollection || typeof href == 'undefined') return;

            // Create a dummy keyed array as js doesnt allow keyed arrays
            var storageValue = tabCollection + '|' + href;

            // Get the current array from the storage
            var activeTabsHrefs = JSON.parse(sessionStorage.getItem(getStorageKey()));

            // If none start a new array
            if (!activeTabsHrefs) {
                var activeTabsHrefs = [];
            }

            // Avoid Duplicates in the storage
            remove_item(activeTabsHrefs, tabCollection);

            // Save clicked tab, with relationship to tabCollection to the array
            activeTabsHrefs.push(storageValue);

            // Store the selected tabs as an array in sessionStorage
            sessionStorage.setItem(getStorageKey(), JSON.stringify(activeTabsHrefs));
        }

        // Array with active tabs hrefs
        var activeTabsHrefs = JSON.parse(sessionStorage.getItem(getStorageKey()));

        // jQuery object with all tabs links
        var alltabs = $('a[data-toggle="tab"]');

        // When a tab is clicked, save its state!
        alltabs.on('click', function (e) {
            saveActiveTab(e);
        });

        if (activeTabsHrefs !== null) {

            // Clean default tabs
            alltabs.parent('.active').removeClass('active');

            // When moving from tab area to a different view
            $.each(activeTabsHrefs, function (index, tabFakexPath) {

                // Click the tab
                var parts = tabFakexPath.split('|');
                $.findXpath(parts[0]).find('a[data-toggle="tab"][href="' + parts[1] + '"]').click();

            });

        } else {

            alltabs.parents('ul').each(function (index, ul) {
                // If no tabs is saved, activate first tab from each tab set and save it
                $(ul).find('a').first().click();
            });

        }
    };

    setTimeout(loadTabs, 100);
});
