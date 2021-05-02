/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbutton = (pressbutton) => {
      const form = document.adminForm;
      const html = document.createElement('joomla-alert');

      if (pressbutton === 'mail.cancel') {
        Joomla.submitform(pressbutton);
        return;
      } else {
        Joomla.submitform(pressbutton);
      }
    };
  });
})();
