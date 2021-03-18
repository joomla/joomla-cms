/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const decodeHtmlspecialChars = text => {
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
        '&#8221;': '”'
      };
      /* eslint-disable */

      return text.replace(/\&[\w\d\#]{2,5}\;/g, m => {
        const n = map[m];
        return n;
      });
    };

    const compare = (original, changed) => {
      const display = changed.nextElementSibling;
      let color = '';
      let pre = null;
      const diff = Diff.diffLines(original.innerHTML, changed.innerHTML);
      const fragment = document.createDocumentFragment();
      /* eslint-enable */

      diff.forEach(part => {
        if (part.added) {
          color = '#a6f3a6';
        } else if (part.removed) {
          color = '#f8cbcb';
        } else {
          color = '';
        }

        pre = document.createElement('pre');
        pre.style.backgroundColor = color;
        pre.className = 'diffview';
        pre.appendChild(document.createTextNode(decodeHtmlspecialChars(part.value)));
        fragment.appendChild(pre);
      });
      display.appendChild(fragment);
    };

    const diffs = [].slice.call(document.querySelectorAll('#original'));

    for (let i = 0, l = diffs.length; i < l; i += 1) {
      compare(diffs[i], diffs[i].nextElementSibling);
    }
  });
})();