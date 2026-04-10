/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};
(Joomla => {

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbutton = task => {
      if (task === 'groups.delete') {
        const cids = document.getElementsByName('cid[]');
        for (let i = 0; i < cids.length; i += 1) {
          if (cids[i].checked && cids[i].parentNode.getAttribute('data-usercount') !== '0') {
            // @todo replace with joomla-alert
            if (window.confirm(Joomla.Text._('COM_USERS_GROUPS_CONFIRM_DELETE'))) {
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
