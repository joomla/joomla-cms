/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  const menuHide = (value) => {
    if (value === 0 || value === '-') {
      document.getElementById('menuselect-group').style.display = 'none';
    } else {
      document.getElementById('menuselect-group').style.display = 'block';
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    menuHide(document.getElementById('jform_assignment').value);

    document.getElementById('jform_assignment').addEventListener('change', (event) => {
      menuHide(event.target.value);
    });
  });
})();
