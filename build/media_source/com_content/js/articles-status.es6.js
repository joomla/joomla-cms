/**
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const elements = [].slice.call(document.querySelectorAll('.article-status'));

    elements.forEach((element) => {
      element.addEventListener('click', (event) => {
        event.stopPropagation();
      });
    });
  });
})();
