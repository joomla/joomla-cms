/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  'use strict';

  var updateBannerFields = function updateBannerFields(value) {
    var imgWrapper = document.getElementById('image');
    var custom = document.getElementById('custom');

    switch (value) {
      case '0':
        // Image
        imgWrapper.classList.remove('hidden');
        custom.classList.add('hidden');
        break;

      case '1':
        // Custom
        imgWrapper.classList.add('hidden');
        custom.classList.remove('hidden');
        break;

      default: // Do nothing

    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    var jformType = document.getElementById('jform_type');

    if (jformType) {
      // Hide/show parameters initially
      updateBannerFields(jformType.value); // Hide/show parameters when the type has been selected

      jformType.addEventListener('change', function (_ref) {
        var target = _ref.target;
        updateBannerFields(target.value);
      });
    }
  });
})(document);