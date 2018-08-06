/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((document, Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    [].slice.call(document.querySelectorAll('.cache-entry')).forEach((el) => {
      el.addEventListener('click', (event) => {
        Joomla.isChecked(event.currentTarget.checked);
      });
    });
  });
})(document, Joomla);
