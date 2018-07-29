/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const compare = (original, changed) => {
      const display = changed.nextElementSibling;
      let color = '';
      let span = null;
      const diff = window.JsDiff.diffWords(original.innerHTML, changed.innerHTML);
      const fragment = document.createDocumentFragment();

      diff.forEach((part) => {
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

    const diffs = [].slice.call(document.querySelectorAll('.original'));
    diffs.forEach((fragment) => {
      compare(fragment, fragment.nextElementSibling);
    });
  });
})();
