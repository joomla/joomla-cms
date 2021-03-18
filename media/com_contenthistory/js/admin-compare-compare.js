/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict'; // This method is used to decode HTML entities

  var decodeHtml = function decodeHtml(html) {
    var textarea = document.createElement('textarea');
    textarea.innerHTML = html;
    return textarea.value;
  };

  var compare = function compare(original, changed) {
    var display = changed.nextElementSibling;
    var diff = window.Diff.diffWords(original.innerHTML, changed.innerHTML);
    var fragment = document.createDocumentFragment();
    diff.forEach(function (part) {
      var color = '';

      if (part.added) {
        color = '#a6f3a6';
      }

      if (part.removed) {
        color = '#f8cbcb';
      } // @todo use the tag MARK here not SPAN


      var span = document.createElement('span');
      span.style.backgroundColor = color;
      span.style.borderRadius = '.2rem';
      span.appendChild(document.createTextNode(decodeHtml(part.value)));
      fragment.appendChild(span);
    });
    display.appendChild(fragment);
  };

  var onBoot = function onBoot() {
    var diffs = [].slice.call(document.querySelectorAll('.original'));
    diffs.forEach(function (fragment) {
      compare(fragment, fragment.nextElementSibling);
    }); // Cleanup

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})();