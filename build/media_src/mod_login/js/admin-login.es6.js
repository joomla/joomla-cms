/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  const btn = document.getElementById('btn-login-submit');
  const form = document.getElementById('form-login');
  const formTmp = document.querySelector('.login-initial');

  if (btn) {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      if (form && document.formvalidator.isValid(form)) {
        Joomla.submitbutton('login');
      }
    });
  }

  if (formTmp) {
    formTmp.style.display = 'block';
    if (!document.querySelector('joomla-alert')) {
      document.getElementById('mod-login-username').focus();
    }
  }

  if (form) {
    form.addEventListener('submit', (event) => {
      const segments = [];

      event.preventDefault();
      segments.push('format=json');

      for (let elementIndex = 0; elementIndex < form.elements.length; elementIndex++) {
        const element = form.elements[elementIndex];

        if (element.hasAttribute('name') && element.nodeName === 'INPUT') {
          segments.push(encodeURIComponent(element.name) + '=' + encodeURIComponent(element.value));
        }
      }

      Joomla.request({
        url: 'index.php',
        method: 'POST',
        data: segments.join('&').replace(/%20/g, '+'),
        perform: true,
        onSuccess: (json) => {
          const response = JSON.parse(json);

          if (typeof response.messages === 'object' && response.messages !== null) {
            Joomla.renderMessages(response.messages);
          }

          if (response.success) {
            letsFade('out', 'narrow');
            window.location.href = response.data.return;
          }
        },
        onError: (xhr) => {
          Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
        },
      });
    });
  }

  letsFade('in');

  function letsFade(fadeAction, transitAction) {
    const sideBar = document.querySelector('.sidebar-wrapper');
    const sidebarChildren = sideBar.children;
    const sideChildrenLength = sidebarChildren.length;
    const contentChildren = document.querySelector('.container-main').children;
    const contChildrenLength = contentChildren.length;

    for (let i = 0; i < sideChildrenLength; i++) {
      sidebarChildren[i].classList.add('load-fade' + fadeAction);
    }

    for (let i = 0; i < contChildrenLength; i++) {
      contentChildren[i].classList.add('load-fade' + fadeAction);
    }

    if (transitAction) {
      sideBar.classList.add('transit-' + transitAction);
    }
  }
})();
