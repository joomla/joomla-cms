/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow selected tab to be remained after save or page reload
 * keeping state in localstorage
 */

jQuery(function() {

    var loadTab = function() {
        var $ = jQuery.noConflict();

        jQuery(document).find('a[data-toggle="tab"]').on('click', function(e) {
            // Store the selected tab href in localstorage
            window.localStorage.setItem('tab-href', $(e.target).attr('href'));
        });

        var activateTab = function(href) {
            var $el = $('a[data-toggle="tab"]a[href*=' + href + ']');
            $el.tab('show');
        };

        var hasTab = function(href){
            return $('a[data-toggle="tab"]a[href*=' + href + ']').length;
        };

        if (localStorage.getItem('tab-href')) {
            // When moving from tab area to a different view
            if(!hasTab(localStorage.getItem('tab-href'))){
                localStorage.removeItem('tab-href');
                return true;
            }
            // Clean default tabs
            $('a[data-toggle="tab"]').parent().removeClass('active');
            var tabhref = localStorage.getItem('tab-href');
            // Add active attribute for selected tab indicated by url
            activateTab(tabhref);
            // Check whether internal tab is selected (in format <tabname>-<id>)
            var seperatorIndex = tabhref.indexOf('-');
            if (seperatorIndex !== -1) {
                var singular = tabhref.substring(0, seperatorIndex);
                var plural = singular + "s";
                activateTab(plural);
            }
        }
    };
    setTimeout(loadTab, 100);

});
