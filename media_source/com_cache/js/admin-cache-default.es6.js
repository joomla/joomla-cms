/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((document, Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.cache-entry').forEach((el) => el.addEventListener('click', ({ currentTarget }) => Joomla.isChecked(currentTarget.checked)));
  });
})(document, Joomla);
