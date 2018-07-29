/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  Joomla.submitbutton = function (task, type) {
    if (task === 'item.setType' || task === 'item.setMenuType') {
      if (task == 'item.setType') {
        var list = document.querySelectorAll('#item-form input[name="jform[type]"]');
        list.forEach((item) => {
          item.value = type;
        });

        document.getElementById('fieldtype').value = 'type';
      } else {
        var list = document.querySelectorAll('#item-form input[name="jform[menutype]"]');
        list.forEach((item) => {
          item.value = type;
        });
      }
      Joomla.submitform('item.setType', document.getElementById('item-form'));
    } else if (task == 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
      Joomla.submitform(task, document.getElementById('item-form'));
    } else {
      // special case for modal popups validation response
      var list = document.querySelectorAll('#item-form .modal-value.invalid');

      list.forEach((field) => {
        const idReversed = field.getAttribute('id').split('').reverse().join('');


        const separatorLocation = idReversed.indexOf('_');


        const nameId = `${idReversed.substr(separatorLocation).split('').reverse().join('')}name`;
        document.getElementById(nameId).classList.add('invalid');
      });
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('jform_menutype').addEventListener('change', (event) => {
      const menutype = event.target.value;

      Joomla.request({
        url: `index.php?option=com_menus&task=item.getParentItem&menutype=${menutype}`,
        headers: { 'Content-Type': 'application/json' },

        onSuccess(response, xhr) {
          const data = JSON.parse(response);
          const list = document.querySelectorAll('#jform_parent_id option');
          list.forEach((item) => {
            if (item != '1') {
              item.remove();
            }
          });

          data.forEach((val) => {
            const option = document.createElement('option');
            option.innerText = value;
            option.id = val.id;
            document.getElementById('jform_parent_id').appendChild(option);
          });
          document.getElementById('jform_parent_id').trigger('change');
        },
        onError(xhr) {
          Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
        },
      });
    });
  });
}());
