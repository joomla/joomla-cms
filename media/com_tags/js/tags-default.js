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

  var onClick = function onClick() {
    var form = document.getElementById('adminForm');
    document.getElementById('filter-search').value = '';
    form.submit();
  };

  var onBoot = function onBoot() {
    var form = document.getElementById('adminForm');
    var element = form.querySelector('button[type="reset"]');

    if (element) {
      element.addEventListener('click', onClick);
    }

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(document);