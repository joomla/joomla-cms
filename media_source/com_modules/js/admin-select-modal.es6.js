/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#new-modules-list a.select-link').forEach((elem) => {
      elem.addEventListener('click', ({ currentTarget, target }) => {
        let targetElem = currentTarget;

        // There is some bug with events in iframe where currentTarget is "null"
        // => prevent this here by bubble up
        if (!targetElem) {
          targetElem = target;

          if (targetElem && !targetElem.classList.contains('select-link')) {
            targetElem = targetElem.parentNode;
          }
        }

        const functionName = targetElem.getAttribute('data-function');

        if (functionName && typeof window.parent[functionName] === 'function') {
          window.parent[functionName](targetElem);
        }
      });
    });
  });
})(document);
