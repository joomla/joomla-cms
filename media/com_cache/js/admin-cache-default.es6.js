/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((document, Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    [].slice.call(document.querySelectorAll('.cache-entry')).forEach(el => {
      el.addEventListener('click', ({
        currentTarget
      }) => {
        Joomla.isChecked(currentTarget.checked);
      });
    });
  });
})(document, Joomla);