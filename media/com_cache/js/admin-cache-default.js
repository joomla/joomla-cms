/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (document, Joomla) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    [].slice.call(document.querySelectorAll('.cache-entry')).forEach(function (el) {
      el.addEventListener('click', function (_ref) {
        var currentTarget = _ref.currentTarget;
        Joomla.isChecked(currentTarget.checked);
      });
    });
  });
})(document, Joomla);