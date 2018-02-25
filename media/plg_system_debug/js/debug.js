/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(function (document) {
  'use strict';

  var toggle = function (id) {
    var element = document.getElementById(id);
    if(element) {
      element.style.display = (element.style.display == 'none') ? 'block' : 'none';
    }
  };

  document.addEventListener('DOMContentLoaded', function () {

    Joomla.toggleContainer = toggle;

    var sidebarWrapper = document.getElementById('sidebar-wrapper');
    var debugWrapper = document.getElementById('system-debug');

    if (sidebarWrapper && debugWrapper) {
      debugWrapper.style.marginLeft = '60px';
    }
  });

}(document));
