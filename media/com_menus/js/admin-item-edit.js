/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
        var nameId = idReversed.substr(separatorLocation).split('').reverse().join('') + 'name';
        document.getElementById(nameId).classList.add('invalid');
      });
    }
  };

  var onChange = function onChange(event) {
    var menuType = event.target.value;

    Joomla.request({
      url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menuType,
      headers: { 'Content-Type': 'application/json' },

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

  var onBoot = function onBoot() {
    if (!Joomla || typeof Joomla.request !== 'function') {
      throw new Error('core.js was not properly initialised');
    }

    var element = document.getElementById('jform_menutype');

    if (element) {
      element.addEventListener('change', onChange);
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(Joomla);
