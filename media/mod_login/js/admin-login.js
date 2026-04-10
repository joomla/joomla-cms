/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {

  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-login-submit');
    if (btn) {
      btn.addEventListener('click', event => {
        event.preventDefault();
        if (document.formvalidator.isValid(btn.form)) {
          Joomla.submitbutton('login');
        }
      });
    }
  });
})(window.Joomla, document);
