/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow selected tab to be remained after save or page reload
 * keeping state in localstorage
 */

jQuery(function($) {
    var loadTabs = function() {
        function saveActiveTab(href, $session) {
            // Remove the old entry if exists, key is always dependant on the url
            // This should be removed in the future
            if (localStorage.getItem('active-tab')) {
                localStorage.removeItem('active-tab');
            }

            // Reset the array
            activeTabsHrefs = [];

            // Save clicked tab href to the array
            activeTabsHrefs.push(href);

            // Store the selected tabs hrefs in localStorage
            localStorage.setItem(window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').replace(/&[a-zA-Z-_]+=[0-9]+/, '') + '&tabSession=' + $session, JSON.stringify(activeTabsHrefs));
        }

        function activateTab(href) {
            $('a[data-toggle="tab"][href="' + href + '"]').tab('show');
        }

        function hasTab(href) {
            return $('a[data-toggle="tab"][href="' + href + '"]').length;
        }

       // Do a clean up
        function cleanUp($session) {
            for(var i=0, len=localStorage.length; i<len; i++) {
                var key = localStorage.key(i),
                    reg = "&tabSession=" + $session;
                console.log(!/reg/.test(key))
                if (/&tabSession=/.test(key) && !/reg/.test(key)) {
                   localStorage.removeItem(key);
                }
            }
        }

         // Is there a session data attribute?
        var tabsUl = document.querySelector('ul.nav-tabs'), $session = '';
        if (tabsUl) {
           $session = document.querySelector('ul.nav-tabs').getAttribute('data-session');
        }

        // Array with active tabs hrefs
        var activeTabsHrefs = JSON.parse(localStorage.getItem(window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').replace(/&[a-zA-Z-_]+=[0-9]+/, '') + '&tabSession=' + $session));

        // jQuery object with all tabs links
        var $tabs = $('a[data-toggle="tab"]');

        $tabs.on('click', function(e) {
            saveActiveTab($(e.target).attr('href'), $session);
        });

        if (activeTabsHrefs !== null) {
            // Clean default tabs
            $tabs.parent('.active').removeClass('active');

            // When moving from tab area to a different view
            $.each(activeTabsHrefs, function(index, tabHref) {
                if (!hasTab(tabHref)) {
                    localStorage.removeItem(window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').replace(/&[a-zA-Z-_]+=[0-9]+/, '') + '&tabSession=' + $session);

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
                cleanUp($session);
            });
        } else {
            $tabs.parents('ul').each(function(index, ul) {
                // If no tabs is saved, activate first tab from each tab set and save it
                var href = $(ul).find('a').first().tab('show').attr('href');
                saveActiveTab(href, $session);
            });
           cleanUp($session);
        }
    };

    setTimeout(loadTabs, 100);
});
