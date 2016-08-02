/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow selected tab to be remained after save or page reload
 * keeping state in localstorage
 */

jQuery(function($) {
    var loadTabs = function() {
        function saveActiveTab(href) {
            if (activeTabsHrefs === null) {
                activeTabsHrefs = [];
            }

            // Save clicked tab href to the array
            activeTabsHrefs.push(href);

            // Store the selected tabs hrefs in localstorage
            localStorage.setItem('active-tabs', JSON.stringify(activeTabsHrefs));
        }

        function activateTab(href) {
            $('a[data-toggle="tab"][href=' + href + ']').tab('show');
        }

        function hasTab(href) {
            return $('a[data-toggle="tab"][href=' + href + ']').length;
        }

        // Array with active tabs hrefs
        var activeTabsHrefs = JSON.parse(localStorage.getItem('active-tabs'));

        // jQuery object with all tabs links
        var $tabs = $('a[data-toggle="tab"]');

        $tabs.on('click', function(e) {
            saveActiveTab($(e.target).attr('href'));
        });

        if (activeTabsHrefs !== null) {
            // Clean default tabs
            $tabs.parent('.active').removeClass('active');

            // When moving from tab area to a different view
            $.each(activeTabsHrefs, function(index, tabHref) {
                if (!hasTab(tabHref)) {
                    localStorage.removeItem('active-tabs');

                    return true;
                }

                // Add active attribute for selected tab indicated by url
                activateTab(tabHref);

                // Check whether internal tab is selected (in format <tabname>-<id>)
                var seperatorIndex = tabHref.indexOf('-');

                if (seperatorIndex !== -1) {
                    var singular = tabHref.substring(0, seperatorIndex);
                    var plural = singular + "s";
                    activateTab(plural);
                }
            });
        } else {
            $tabs.parents('ul').each(function(index, ul) {
                // If no tabs is saved, activate first tab from each tab set and save it
                var href = $(ul).find('a').first().tab('show').attr('href');
                saveActiveTab(href);
            });
        }
    };

    setTimeout(loadTabs, 100);
});
