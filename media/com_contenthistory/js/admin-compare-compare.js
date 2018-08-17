/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  var compare = function compare(original, changed) {
    var display = changed.nextElementSibling;
    var color = '';
    // @todo use the tag MARK here not SPAN
    var span = null;
    var diff = window.JsDiff.diffWords(original.innerHTML, changed.innerHTML);
    var fragment = document.createDocumentFragment();

    diff.forEach(function (part) {
      if (part.added) {
        color = '#a6f3a6';
      }

      if (part.removed) {
        color = '#f8cbcb';
      }

      span = document.createElement('span');
      span.style.backgroundColor = color;
      span.style.borderRadius = '.2rem';
      span.appendChild(document.createTextNode(part.value));
      fragment.appendChild(span);
    });

    display.appendChild(fragment);
  };

  var onBoot = function onBoot() {
    var diffs = [].slice.call(document.querySelectorAll('.original'));
    diffs.forEach(function (fragment) {
      compare(fragment, fragment.nextElementSibling);
    });

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})();
