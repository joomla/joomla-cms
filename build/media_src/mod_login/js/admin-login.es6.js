/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  const sideBar = document.querySelector('.sidebar-wrapper');
  const sidebarChildren = sideBar.children;
  const sideChildrenLength = sidebarChildren.length;
  const contentChildren = document.querySelector('.container-main').children;
  const contChildrenLength = contentChildren.length;
  const logCookie = document.cookie.replace(/(?:(?:^|.*;\s*)log\s*=\s*([^;]*).*$)|^.*$/, "$1");

  const form = document.getElementById('form-login');
  const btn = document.getElementById('btn-login-submit');
  if (btn) {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      if (form && document.formvalidator.isValid(form)) {
        Joomla.submitbutton('login');
      }
    });
  }

  const formTmp = document.querySelector('.login-initial');
  if (formTmp) {
    formTmp.style.display = 'block';
    if (!document.querySelector('joomla-alert')) {
      document.getElementById('mod-login-username')
        .focus();
    }
  }

  if (form) {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(event.target);
      formData.append('format', 'json');
      const urlEncodedDataPairs = [];

      for(const pair of formData.entries()) {
        urlEncodedDataPairs.push(encodeURIComponent(pair[0]) + '=' + encodeURIComponent(pair[1]));
      }

      Joomla.request({
        url: 'index.php',
        method: 'POST',
        data: urlEncodedDataPairs.join('&').replace(/%20/g, '+'),
        perform: true,
        onSuccess: (response, xhr) => {
          response = JSON.parse(response);

          if (typeof response.messages === 'object' && response.messages !== null) {
            Joomla.renderMessages(response.messages);
          }

          if (response.success) {
            document.cookie = 'log=logged-in';
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

  if ((logCookie) && (logCookie === 'logout')) {
    letsFade('in');
    document.cookie = 'log= ';
  }

  function letsFade(fadeAction, transitAction) {
    const fadeAct = fadeAction;
    const transAct = transitAction;

    for (let i = 0; i < sideChildrenLength; i++) {
      sidebarChildren[i].classList.add('load-fade' + fadeAct);
    }

    for (let i = 0; i < contChildrenLength; i++) {
      contentChildren[i].classList.add('load-fade' + fadeAct);
    }

    if (transAct) {
      sideBar.classList.add('transit-' + transAct);
    }
  }
})();
