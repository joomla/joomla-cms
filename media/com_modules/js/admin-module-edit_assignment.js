/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  var onChange = function onChange(value) {
    if (value === '-' || parseInt(value, 10) === 0) {
      document.getElementById('menuselect-group').style.display = 'none';
    } else {
      document.getElementById('menuselect-group').style.display = 'block';
    }
  };

  var onBoot = function onBoot() {
    var element = document.getElementById('jform_assignment');

    if (element) {
      // Initialise the state
      onChange(element.value);

      // Check for changes in the state
      element.addEventListener('change', function (event) {
        onChange(event.target.value);
      });
    }

    document.removeEventListener('DOMContentLoaded', onBoot);
  };
  document.addEventListener('DOMContentLoaded', onBoot);
})();
