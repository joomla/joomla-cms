/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var elements = [].slice.call(document.querySelectorAll('#moduleEditModal .modal-footer .btn'));

    if (elements.length) {
      elements.forEach(function (element) {
        element.addEventListener('click', function (event) {
          var target = event.target.getAttribute('data-target');

          if (target) {
            var iframe = document.querySelector('#moduleEditModal iframe');
            iframe.contents().querySelector(target).click();
          }
        });
      });
    }
  });
})();
