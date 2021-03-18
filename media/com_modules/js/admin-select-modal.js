/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var elems = document.querySelectorAll('#new-modules-list a.select-link');
    elems.forEach(function (elem) {
      elem.addEventListener('click', function (_ref) {
        var currentTarget = _ref.currentTarget,
            target = _ref.target;
        var targetElem = currentTarget; // There is some bug with events in iframe where currentTarget is "null"
        // => prevent this here by bubble up

        if (!targetElem) {
          targetElem = target;

          if (targetElem && !targetElem.classList.contains('select-link')) {
            targetElem = targetElem.parentNode;
          }
        }

        var functionName = targetElem.getAttribute('data-function');

        if (functionName && typeof window.parent[functionName] === 'function') {
          window.parent[functionName](targetElem);
        }
      });
    });
  });
})(document);