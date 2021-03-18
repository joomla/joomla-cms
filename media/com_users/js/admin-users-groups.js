/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (Joomla) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    Joomla.submitbutton = function (task) {
      if (task === 'groups.delete') {
        var cids = document.getElementsByName('cid[]');

        for (var i = 0; i < cids.length; i += 1) {
          if (cids[i].checked && cids[i].parentNode.getAttribute('data-usercount') !== '0') {
            // TODO replace with joomla-alert
            if (window.confirm(Joomla.JText._('COM_USERS_GROUPS_CONFIRM_DELETE'))) {
              Joomla.submitform(task);
            }

            return false;
          }
        }
      }

      Joomla.submitform(task);
      return false;
    };
  });
})(Joomla);