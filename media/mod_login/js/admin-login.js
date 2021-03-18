/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (Joomla, document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btn-login-submit');
    var form = document.getElementById('form-login');
    var formTmp = document.querySelector('.login-initial');

    if (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();

        if (form && document.formvalidator.isValid(form)) {
          Joomla.submitbutton('login');
        }
      });
    }

    if (formTmp) {
      formTmp.classList.remove('hidden');

      if (!document.querySelector('joomla-alert')) {
        document.getElementById('mod-login-username').focus();
      }
    }

    if (form) {
      form.addEventListener('submit', function (event) {
        var segments = [];
        event.preventDefault();
        segments.push('format=json');

        for (var eIndex = 0; eIndex < form.elements.length; eIndex += 1) {
          var element = form.elements[eIndex];

          if (element.hasAttribute('name') && element.nodeName === 'INPUT') {
            segments.push("".concat(encodeURIComponent(element.name), "=").concat(encodeURIComponent(element.value)));
          } else if (element.hasAttribute('name') && element.nodeName === 'SELECT' && element.value.length > 0) {
            segments.push("".concat(encodeURIComponent(element.name), "=").concat(encodeURIComponent(element.value)));
          }
        }

        Joomla.request({
          url: 'index.php',
          method: 'POST',
          data: segments.join('&').replace(/%20/g, '+'),
          perform: true,
          onSuccess: function onSuccess(xhr) {
            var response = JSON.parse(xhr);

            if (response.success) {
              Joomla.Event.dispatch(form, 'joomla:login');
              window.location.href = response.data.return;
            } else if (_typeof(response.messages) === 'object' && response.messages !== null) {
              Joomla.renderMessages(response.messages);
            }
          },
          onError: function onError(xhr) {
            Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
          }
        });
      });
    }
  });
})(window.Joomla, document);