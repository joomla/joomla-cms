/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @deprecated  4.3
 *              This file is deprecated and will be removed with Joomla 5.0
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
      }

      // do field validation
      if (form.jform_subject.value === '') {
        html.innerText = Joomla.Text._('COM_USERS_MAIL_PLEASE_FILL_IN_THE_SUBJECT');
        form.insertAdjacentElement('afterbegin', html);
      } else if (form.jform_group.value < 0) {
        html.innerText = Joomla.Text._('COM_USERS_MAIL_PLEASE_SELECT_A_GROUP');
        form.insertAdjacentElement('afterbegin', html);
      } else if (form.jform_message.value === '') {
        html.innerText = Joomla.Text._('COM_USERS_MAIL_PLEASE_FILL_IN_THE_MESSAGE');
        form.insertAdjacentElement('afterbegin', html);
      } else {
        Joomla.submitform(pressbutton);
      }
    };
  });
})();
