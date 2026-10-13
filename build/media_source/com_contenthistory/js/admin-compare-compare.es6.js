/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  // This method is used to decode HTML entities
  const decodeHtml = (html) => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = Joomla.sanitizeHtml(html);
    return textarea.value;
  };

  const compare = (original, changed) => {
    const display = changed.nextElementSibling;
    const diff = window.Diff.diffWords(original.innerHTML, changed.innerHTML);
    const fragment = document.createDocumentFragment();

    diff.forEach((part) => {
          const tag = part.added ? 'ins' : part.removed ? 'del' : 'span';
          const element = document.createElement(tag);

          if (part.added) {
            element.style.backgroundColor = '#2ced2c';
          }

          if (part.removed) {
            element.style.backgroundColor = '#e70d0d';
          }

          element.style.borderRadius = '.2rem';
          element.style.padding = '2px 4px';

          element.appendChild(document.createTextNode(decodeHtml(part.value)));
          fragment.appendChild(element);
    });

    display.appendChild(fragment);
  };

  const onBoot = () => {
    document.querySelectorAll('.original').forEach((fragment) => compare(fragment, fragment.nextElementSibling));

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})();
