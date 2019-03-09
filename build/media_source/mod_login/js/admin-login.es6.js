/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  const btn = document.getElementById('btn-login-submit');
  if (btn) {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      const form = document.getElementById('form-login');
      if (form && document.formvalidator.isValid(form)) {
        Joomla.submitbutton('login');
      }
    });
  }

  const formTmp = document.querySelector('.login-initial');
  if (formTmp) {
    formTmp.style.display = 'block';
    if (!document.querySelector('joomla-alert')) {
      document.getElementById('mod-login-username').focus();
    }
  }
})();
