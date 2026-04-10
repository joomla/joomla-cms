/**
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, window) => {

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.com-users-methods-list-method-record-delete').forEach(el => {
      el.addEventListener('click', event => {
        if (!window.confirm(Joomla.Text._('JGLOBAL_CONFIRM_DELETE'))) {
          event.preventDefault();
        }
      });
    });
  });
})(Joomla, window);
