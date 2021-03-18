/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  'use strict'; // Selectors used by this script

  var debugSectionTogglerSelector = '.dbg-header';
  var toggleTargetAttribute = 'data-debug-toggle';
  /**
   * Toggle an element by id
   * @param id
   */

  var toggle = function toggle(id) {
    document.getElementById(id).classList.toggle('hidden');
  };
  /**
   * Register events
   */


  var registerEvents = function registerEvents() {
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
  });
})(document);