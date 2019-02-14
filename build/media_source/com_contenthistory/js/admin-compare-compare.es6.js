/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  const decodeHtmlspecialChars = (text) => {
    const map = {
      '&amp;': '&',
      '&#038;': '&',
      '&lt;': '<',
      '&gt;': '>',
      '&quot;': '"',
      '&#039;': "'",
      '&#8217;': '’',
      '&#8216;': '‘',
      '&#8211;': '–',
      '&#8212;': '—',
      '&#8230;': '…',
      '&#8221;': '”',
    };

    /* eslint-disable */
    return text.replace(/\&[\w\d\#]{2,5}\;/g, (m) => { const n = map[m]; return n; });
  };

  const compare = (original, changed) => {
    const display = changed.nextElementSibling;
    let color = '';
    // @todo use the tag MARK here not SPAN
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
      span.appendChild(document.createTextNode(decodeHtmlspecialChars(part.value)));
      fragment.appendChild(span);
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
