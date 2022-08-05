/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
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
      let elm = '';
      let color = '';
      let indicator = '';
      let pre = null;
      const diff = Diff.diffLines(original.innerHTML, changed.innerHTML);
      const fragment = document.createDocumentFragment();

      /* eslint-enable */

      diff.forEach((part) => {
        if (part.added) {
          elm = 'ins';
          color = '#a6f3a6';
          indicator = '+';
        } else if (part.removed) {
          elm = 'del';
          color = '#f8cbcb';
          indicator = '-';
        } else {
          elm = 'span';
          color = '';
          indicator = '';
        }

        pre = document.createElement(elm);
        pre.style.backgroundColor = color;
        pre.className = 'diffview';
        pre.appendChild(document.createTextNode(decodeHtmlspecialChars(part.value)));
        fragment.appendChild(pre);
        pre.insertAdjacentHTML('afterbegin', `<span class="indicator">${indicator}</span>`);
      });
      display.appendChild(fragment);

      display.appendChild(fragment);
    };

    const diffs = [].slice.call(document.querySelectorAll('#original'));
    for (let i = 0, l = diffs.length; i < l; i += 1) {
      compare(diffs[i], diffs[i].nextElementSibling);
    }
  });
})();
