/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-login-submit');
    const form = document.getElementById('form-login');
    const sysMessage = document.getElementById('system-message-container');

    if (!form) {
      throw new Error('The form element form#form-login is required to proceed')
    }

    if (btn) {
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        if (form && document.formvalidator.isValid(form)) {
          Joomla.submitbutton('login');
        }
      });
    }

    document.getElementById('mod-login-username').focus();

    form.addEventListener('submit', (event) => {
      const segments = [];

      event.preventDefault();
      segments.push('format=json');

      for (let eIndex = 0; eIndex < form.elements.length; eIndex += 1) {
        const element = form.elements[eIndex];

        if (element.hasAttribute('name') && element.nodeName === 'INPUT') {
          segments.push(`${encodeURIComponent(element.name)}=${encodeURIComponent(element.value)}`);
        } else if (element.hasAttribute('name') && element.nodeName === 'SELECT' && element.value.length > 0) {
          segments.push(`${encodeURIComponent(element.name)}=${encodeURIComponent(element.value)}`);
        }
      }

      Joomla.request({
        url: 'index.php',
        method: 'POST',
        data: segments.join('&').replace(/%20/g, '+'),
        perform: true,
        onSuccess: (responseStr) => {
          let response, respError;

          // Reset the form
          form.reset()

          // Try to parse response
          try {
            response = JSON.parse(responseStr);
          } catch (e) {
            respError = e;
          }

          // In case of a broken response: show a message
          if (respError) {
            Joomla.renderMessages({'warning': [
              'The response is corrupted, please try to reload the page.',
              respError.message
            ]});
            sysMessage && sysMessage.scrollIntoView({behavior: "smooth"});
          } else if (response.success) {
            Joomla.Event.dispatch(form, 'joomla:login');
            window.location.href = response.data.return;
          } else if (typeof response.messages === 'object' && response.messages !== null) {
            Joomla.renderMessages(response.messages);
            sysMessage && sysMessage.scrollIntoView({behavior: "smooth"});
          }
        },
        onError: (xhr) => {
          Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
        },
      });
    });

  });
})(window.Joomla, document);
