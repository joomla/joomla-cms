/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbutton = (pressbutton) => {
      if (pressbutton === 'mail.cancel') {
        Joomla.submitform(pressbutton);
        return;
      }
      
      if (pressbutton === 'mail.send') {
        Joomla.submitform(pressbutton);
      }
    };
  });
})();
