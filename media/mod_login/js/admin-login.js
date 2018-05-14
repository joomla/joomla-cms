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

  var btn = document.getElementById('btn-login-submit');
  if (btn) {
    btn.addEventListener('click', function (event) {
      event.preventDefault();
      var form = document.getElementById('form-login');
      if (form && document.formvalidator.isValid(form)) {
        Joomla.submitbutton('login');
      }
    });
  }

  var formTmp = document.querySelector('.login-initial');
  if (formTmp) {
    formTmp.style.display = 'block';
    if (!document.querySelector('joomla-alert')) {
      document.getElementById('mod-login-username').focus();
    }
  }
})();
