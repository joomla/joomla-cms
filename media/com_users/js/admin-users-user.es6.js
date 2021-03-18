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
      const method = document.getElementById('jform_twofactor_method');

      if (method) {
        const selectedPane = `com_users_twofactor_${method.value}`;
        const twoFactorForms = [].slice.call(document.querySelectorAll('#com_users_twofactor_forms_container > div'));
        twoFactorForms.forEach(value => {
          const {
            id
          } = value;

          if (id !== selectedPane) {
            document.getElementById(id).classList.add('hidden');
          } else {
            document.getElementById(id).classList.remove('hidden');
          }
        });
      }
    };
  });
})(Joomla);