/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbutton = (pressbutton) => {
      const form = document.adminForm;
      if (pressbutton === 'mail.cancel') {
        Joomla.submitform(pressbutton);
        return;
      }

      // do field validation
      if (form.jform_subject.value === '') {
        // TODO replace with joomla-alert
        alert(Joomla.JText._('COM_USERS_MAIL_PLEASE_FILL_IN_THE_SUBJECT'));
      } else if (Joomla.getSelectedValue('adminForm', 'jform[group]') < 0) {
        // TODO replace with joomla-alert
        alert(Joomla.JText._('COM_USERS_MAIL_PLEASE_SELECT_A_GROUP'));
      } else if (form.jform_message.value === '') {
        // TODO replace with joomla-alert
        alert(Joomla.JText._('COM_USERS_MAIL_PLEASE_FILL_IN_THE_MESSAGE'));
      } else {
        Joomla.submitform(pressbutton);
      }
    };
  });
})();
