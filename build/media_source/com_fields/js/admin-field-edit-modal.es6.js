/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  'use strict';

  Joomla.submitbutton = (task) => {
    if (task === 'field.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
      if (window.opener && (task === 'field.save' || task === 'field.cancel')) {
        window.opener.document.closeEditWindow = window.self;
        window.opener.setTimeout('window.document.closeEditWindow.close()', 1000);
      }

      Joomla.submitform(task, document.getElementById('item-form'));
    }
  };
})(Joomla);
