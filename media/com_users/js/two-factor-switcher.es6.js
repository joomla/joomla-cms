/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(Joomla => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.twoFactorMethodChange = () => {
      const selectedPane = `com_users_twofactor_${document.getElementById('jform_twofactor_method').value}`;
      [].slice.call(document.querySelectorAll('#com_users_twofactor_forms_container>div')).forEach(el => {
        if (el.id !== selectedPane) {
          document.getElementById(el.id).classList.add('hidden');
          return;
        }

        document.getElementById(el.id).classList.remove('hidden');
      });
    };
  });
})(Joomla);