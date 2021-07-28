/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const Banner = new bootstrap.Modal(document.getElementById('cookieBanner'));
    Banner.show();

    document.querySelectorAll('[data-cookiecategory="necessary"]').forEach((item) => {
      item.setAttribute('checked', true);
      item.setAttribute('disabled', true);
    });
  });

  document.querySelectorAll('a[data-bs-toggle="collapse"]').forEach((item) => {
    item.addEventListener('click', () => {
      if (item.innerText === Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT')) {
        item.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_LESS_BUTTON_TEXT');
      } else {
        item.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT');
      }
    });
  });
})(document);
