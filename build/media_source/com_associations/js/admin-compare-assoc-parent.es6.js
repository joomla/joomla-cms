/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  const compare = (original, changed) => {
    const display = changed.nextElementSibling;
    const tagName = 'mark';
    let className = 'same';
    let tagElement = null;

    const diff = window.JsDiff.diffWords(original.textContent, changed.textContent);
    const fragment = document.createDocumentFragment();

    diff.forEach((part) => {
      if (part.added) {
        className = 'added';
      }

      if (part.removed) {
        className = 'removed';
      }

      tagElement = document.createElement(tagName);
      tagElement.setAttribute('class', className);
      tagElement.appendChild(document.createTextNode(part.value));
      fragment.appendChild(tagElement);
    });

    display.appendChild(fragment);
  };

  const onBoot = () => {
    const diffs = [].slice.call(document.querySelectorAll('.original'));
    diffs.forEach((fragment) => {
      compare(fragment, fragment.nextElementSibling);
    });

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})();
