/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbutton = (pressbutton) => {
      // @todo replace with joomla-alert
      if (pressbutton === 'index.purge' && !window.confirm(Joomla.Text._('COM_FINDER_INDEX_CONFIRM_PURGE_PROMPT'))) {
        return false;
      }
      // @todo replace with joomla-alert
      if (pressbutton === 'index.delete' && !window.confirm(Joomla.Text._('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT'))) {
        return false;
      }
      Joomla.submitform(pressbutton);
      return true;
    };
  });
})();
