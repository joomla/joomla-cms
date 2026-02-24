/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document) => {
  'use strict';

  const asyncTasks = new Set([
    'articles.publish',
    'articles.unpublish',
    'articles.archive',
    'articles.trash',
    'articles.featured',
    'articles.unfeatured',
  ]);

  const refreshListContainer = (url) => Joomla.request({
    url,
    method: 'GET',
    promise: true,
  }).then((xhr) => {
    const responseText = xhr.responseText || '';

    if (!responseText.length) {
      return;
    }

    const parser = new DOMParser();
    const parsedDocument = parser.parseFromString(responseText, 'text/html');
    const nextContainer = parsedDocument.querySelector('#j-main-container');
    const currentContainer = document.querySelector('#j-main-container');

    if (!nextContainer || !currentContainer) {
      return;
    }

    currentContainer.innerHTML = nextContainer.innerHTML;
    document.dispatchEvent(new CustomEvent('joomla:updated'));
  });

  const installAsyncSubmitHandlers = () => {
    const originalSubmitbutton = Joomla.submitbutton;
    const originalListItemTask = Joomla.listItemTask;

    Joomla.submitbutton = (task, formSelector, validate) => {
      if (!asyncTasks.has(task)) {
        originalSubmitbutton(task, formSelector, validate);

        return;
      }

      let form = document.querySelector(formSelector || 'form.form-validate');

      if (typeof formSelector === 'string' && form === null) {
        form = document.querySelector(`#${formSelector}`);
      }

      if (!form) {
        originalSubmitbutton(task, formSelector, validate);

        return;
      }

      const payload = new URLSearchParams(new FormData(form));
      payload.set('task', task);

      Joomla.asyncAdminRequest({
        url: form.action || window.location.href,
        method: 'POST',
        data: payload.toString(),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        featureFlagKey: 'com_content.async_admin',
        fallbackTask: task,
        formSelector,
        validate,
        fallbackOnError: true,
        onSuccess: (responsePayload) => {
          const refreshUrl = responsePayload.redirect || form.action || window.location.href;

          refreshListContainer(refreshUrl).then(() => {
            if (responsePayload.messages) {
              Joomla.renderMessages(responsePayload.messages);
            }
          });
        },
        onFallback: () => {
          originalSubmitbutton(task, formSelector, validate);
        },
      });
    };

    Joomla.listItemTask = (id, task, form = null) => {
      if (!asyncTasks.has(task)) {
        return originalListItemTask(id, task, form);
      }

      let newForm = form;

      if (form !== null) {
        newForm = document.getElementById(form);
      } else {
        newForm = document.adminForm;
      }

      const cb = newForm[id];
      let i = 0;
      let cbx;

      if (!cb) {
        return false;
      }

      while (true) {
        cbx = newForm[`cb${i}`];

        if (!cbx) {
          break;
        }

        cbx.checked = false;

        i += 1;
      }

      cb.checked = true;
      newForm.boxchecked.value = 1;

      Joomla.submitbutton(task, `#${newForm.id}`, false);

      return false;
    };
  };

  const onClick = () => {
    const form = document.getElementById('adminForm');
    document.getElementById('filter-search').value = '';
    form.submit();
  };

  const onBoot = () => {
    const form = document.getElementById('adminForm');
    const element = form.querySelector('button[type="reset"]');

    installAsyncSubmitHandlers();

    if (element) {
      element.addEventListener('click', onClick);
    }

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(document);
