/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (Joomla) {
  'use strict';

  Joomla.submitbutton = function (task, type) {
    if (task === 'item.setType' || task === 'item.setMenuType') {
      if (task === 'item.setType') {
        var list = [].slice.call(document.querySelectorAll('#item-form input[name="jform[type]"]'));
        list.forEach(function (item) {
          item.value = type;
        });
        document.getElementById('fieldtype').value = 'type';
      } else {
        var _list = [].slice.call(document.querySelectorAll('#item-form input[name="jform[menutype]"]'));

        _list.forEach(function (item) {
          item.value = type;
        });
      }

      Joomla.submitform('item.setType', document.getElementById('item-form'));
    } else if (task === 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
      Joomla.submitform(task, document.getElementById('item-form'));
    } else {
      // special case for modal popups validation response
      var _list2 = [].slice.call(document.querySelectorAll('#item-form .modal-value.invalid'));

      _list2.forEach(function (field) {
        var idReversed = field.getAttribute('id').split('').reverse().join('');
        var separatorLocation = idReversed.indexOf('_');
        var nameId = "".concat(idReversed.substr(separatorLocation).split('').reverse().join(''), "name");
        document.getElementById(nameId).classList.add('invalid');
      });
    }
  };

  var onChange = function onChange(_ref) {
    var target = _ref.target;
    var menuType = target.value;
    Joomla.request({
      url: "index.php?option=com_menus&task=item.getParentItem&menutype=".concat(menuType),
      headers: {
        'Content-Type': 'application/json'
      },
      onSuccess: function onSuccess(response) {
        var data = JSON.parse(response);
        var list = [].slice.call(document.querySelectorAll('#jform_parent_id option'));
        list.forEach(function (item) {
          if (item.value !== '1') {
            item.parentNode.removeChild(item);
          }
        });
        data.forEach(function (value) {
          var option = document.createElement('option');
          option.innerText = value.title;
          option.id = value.id;
          document.getElementById('jform_parent_id').appendChild(option);
        });
        var newEvent = document.createEvent('HTMLEvents');
        newEvent.initEvent('change', true, false);
        document.getElementById('jform_parent_id').dispatchEvent(newEvent);
      },
      onError: function onError(xhr) {
        Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
      }
    });
  };

  if (!Joomla || typeof Joomla.request !== 'function') {
    throw new Error('core.js was not properly initialised');
  }

  var element = document.getElementById('jform_menutype');

  if (element) {
    element.addEventListener('change', onChange);
  } // Menu type Login Form specific


  document.getElementById('item-form').addEventListener('submit', function () {
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