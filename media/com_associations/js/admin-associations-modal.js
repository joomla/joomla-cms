/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    if (Joomla.getOptions('modal-associations')) {
      var fnName = Joomla.getOptions('modal-associations').func;
      var links = [].slice.call(document.querySelectorAll('.select-link'));

      links.forEach(function (item) {
        item.addEventListener('click', function (event) {
          // eslint-disable-next-line no-restricted-globals
          if (self !== top) {
            // Run function on parent window.
            window.parent[fnName](event.target.getAttribute('data-id'));
          }
        });
      });
    }
  });
})(Joomla, document);
