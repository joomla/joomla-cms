/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.twoFactorMethodChange = () => {
      const selectedPane = `com_users_twofactor_${document.getElementById('jform_twofactor_method').value}`;

      [].slice.call(document.querySelectorAll('#com_users_twofactor_forms_container>div')).forEach((el) => {
        if (el.id !== selectedPane) {
          document.getElementById(el.id).style.display = 'none';
          return;
        }

        document.getElementById(el.id).style.display = 'block';
      });
    };
  });
})(Joomla);
