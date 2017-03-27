/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function($) {
    $("a.js-permission").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();

        var activeTab = [];
        activeTab.push("#" + e.target.href.split("#")[1]);
        var path = window.location.pathname;
        localStorage.removeItem(e.target.href.replace(/&return=[a-zA-Z0-9%]+/, "").replace(/&[a-zA-Z-_]+=[0-9]+/, ""));
        localStorage.setItem(path + e.target.href.split("index.php")[1].split("#")[0], JSON.stringify(activeTab));
        return window.location.href = e.target.href.split("#")[0];
    });
});