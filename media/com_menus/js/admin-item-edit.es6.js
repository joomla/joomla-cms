/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(Joomla => {
  'use strict';

  Joomla.submitbutton = (task, type) => {
    if (task === 'item.setType' || task === 'item.setMenuType') {
      if (task === 'item.setType') {
        const list = [].slice.call(document.querySelectorAll('#item-form input[name="jform[type]"]'));
        list.forEach(item => {
          item.value = type;
        });
        document.getElementById('fieldtype').value = 'type';
      } else {
        const list = [].slice.call(document.querySelectorAll('#item-form input[name="jform[menutype]"]'));
        list.forEach(item => {
          item.value = type;
        });
      }

      Joomla.submitform('item.setType', document.getElementById('item-form'));
    } else if (task === 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
      Joomla.submitform(task, document.getElementById('item-form'));
    } else {
      // special case for modal popups validation response
      const list = [].slice.call(document.querySelectorAll('#item-form .modal-value.invalid'));
      list.forEach(field => {
        const idReversed = field.getAttribute('id').split('').reverse().join('');
        const separatorLocation = idReversed.indexOf('_');
        const nameId = `${idReversed.substr(separatorLocation).split('').reverse().join('')}name`;
        document.getElementById(nameId).classList.add('invalid');
      });
    }
  };

  const onChange = ({
    target
  }) => {
    const menuType = target.value;
    Joomla.request({
      url: `index.php?option=com_menus&task=item.getParentItem&menutype=${menuType}`,
      headers: {
        'Content-Type': 'application/json'
      },

      onSuccess(response) {
        const data = JSON.parse(response);
        const list = [].slice.call(document.querySelectorAll('#jform_parent_id option'));
        list.forEach(item => {
          if (item.value !== '1') {
            item.parentNode.removeChild(item);
          }
        });
        data.forEach(value => {
          const option = document.createElement('option');
          option.innerText = value.title;
          option.id = value.id;
          document.getElementById('jform_parent_id').appendChild(option);
        });
        const newEvent = document.createEvent('HTMLEvents');
        newEvent.initEvent('change', true, false);
        document.getElementById('jform_parent_id').dispatchEvent(newEvent);
      },

      onError: xhr => {
        Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
      }
    });
  };

  if (!Joomla || typeof Joomla.request !== 'function') {
    throw new Error('core.js was not properly initialised');
  }

  const element = document.getElementById('jform_menutype');

  if (element) {
    element.addEventListener('change', onChange);
  } // Menu type Login Form specific


  document.getElementById('item-form').addEventListener('submit', () => {
    if (document.getElementById('jform_params_login_redirect_url') && document.getElementById('jform_params_logout_redirect_url')) {
      // Login
      if (!document.getElementById('jform_params_login_redirect_url').closest('.control-group').classList.contains('hidden')) {
        document.getElementById('jform_params_login_redirect_menuitem_id').value = '';
      }

      if (!document.getElementById('jform_params_login_redirect_menuitem_name').closest('.control-group').classList.contains('hidden')) {
        document.getElementById('jform_params_login_redirect_url').value = '';
      } // Logout


      if (!document.getElementById('jform_params_logout_redirect_url').closest('.control-group').classList.contains('hidden')) {
        document.getElementById('jform_params_logout_redirect_menuitem_id').value = '';
      }

      if (!document.getElementById('jform_params_logout_redirect_menuitem_id').closest('.control-group').classList.contains('hidden')) {
        document.getElementById('jform_params_logout_redirect_url').value = '';
      }
    }
  });
})(Joomla);