/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(function (document) {
  'use strict';

  // Selectors used by this script
  var debugSectionTogglerSelector = '.dbg-header';
  var toggleTargetAttribute = 'data-debug-toggle';

  var toggle = function (id) {
    var element = document.getElementById(id);
    if (element) {
      element.style.display = (element.style.display === 'block') ? 'none' : 'block';
    }
  };

  var registerEvents = function () {
    var sectionTogglers = [].slice.call(document.querySelectorAll(debugSectionTogglerSelector));
    sectionTogglers.forEach(function (toggler) {
      toggler.addEventListener('click', function (event) {
        event.preventDefault();
        toggle(toggler.getAttribute(toggleTargetAttribute));
      });
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    registerEvents();

    var sidebarWrapper = document.getElementById('sidebar-wrapper');
    var debugWrapper = document.getElementById('system-debug');

    if (sidebarWrapper && debugWrapper) {
      debugWrapper.style.marginLeft = '60px';
    }

  });

}(document));
