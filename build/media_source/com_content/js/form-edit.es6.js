/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, submitForm) => {
  'use strict';

  // Selectors used by this script
  const buttonDataSelector = 'data-submit-task';
  const formId = 'item-form';
  const autosaveStatusId = 'com-content-autosave-status';
  let autosaveTimer = null;
  let manualSubmitInProgress = false;
  let autosaveInProgress = false;
  let currentSignature = '';
  let dirty = false;

  const getForm = () => document.getElementById(formId) || document.forms.adminForm;

  /**
   * Submit the task
   * @param task
   */
  const submitTask = (task) => {
    const form = getForm();

    if (!form) {
      return;
    }

    if (task !== 'article.apply') {
      manualSubmitInProgress = true;
    }

    if (task === 'article.cancel' || document.formvalidator.isValid(form)) {
      submitForm(task, form);
    }
  };

  const getAutosaveOptions = () => {
    const options = Joomla.getOptions('com_content.autosave', {});

    return {
      enabled: options.enabled === true,
      interval: Number.isInteger(options.interval) ? options.interval : 30,
    };
  };

  const getFormSignature = (form) => {
    const payload = new URLSearchParams(new FormData(form));
    payload.delete('task');

    return payload.toString();
  };

  const ensureStatusNode = () => {
    let statusNode = document.getElementById(autosaveStatusId);

    if (statusNode) {
      return statusNode;
    }

    const form = getForm();

    if (!form) {
      return null;
    }

    statusNode = document.createElement('p');
    statusNode.id = autosaveStatusId;
    statusNode.className = 'small text-muted mt-2 mb-0';
    statusNode.setAttribute('role', 'status');
    statusNode.setAttribute('aria-live', 'polite');
    statusNode.textContent = Joomla.Text._('COM_CONTENT_AUTOSAVE_STATUS_READY', 'Autosave ready');

    form.parentNode.insertBefore(statusNode, form);

    return statusNode;
  };

  const updateStatus = (messageKey, fallbackText) => {
    const statusNode = ensureStatusNode();

    if (!statusNode) {
      return;
    }

    statusNode.textContent = Joomla.Text._(messageKey, fallbackText);
  };

  const runAutosaveCycle = () => {
    const form = getForm();

    if (!form || manualSubmitInProgress || autosaveInProgress || !dirty) {
      return Promise.resolve({ mode: 'skipped' });
    }

    if (!document.formvalidator.isValid(form)) {
      updateStatus('COM_CONTENT_AUTOSAVE_STATUS_SKIPPED_INVALID', 'Autosave skipped (validation failed)');

      return Promise.resolve({ mode: 'invalid' });
    }

    autosaveInProgress = true;
    updateStatus('COM_CONTENT_AUTOSAVE_STATUS_SAVING', 'Autosaving...');

    const payload = new URLSearchParams(new FormData(form));
    payload.set('task', 'article.autosave');

    return Joomla.asyncAdminRequest({
      url: form.getAttribute('action') || window.location.href,
      method: 'POST',
      data: payload.toString(),
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      featureFlagKey: 'com_content.autosave',
      fallbackOnError: false,
      onSuccess: (responsePayload) => {
        dirty = false;

        if (responsePayload?.meta?.skipped === true) {
          if (responsePayload.meta.reason === 'throttled') {
            updateStatus('COM_CONTENT_AUTOSAVE_STATUS_SKIPPED_THROTTLED', 'Autosave skipped (too frequent)');

            return;
          }

          updateStatus('COM_CONTENT_AUTOSAVE_STATUS_SKIPPED_UNCHANGED', 'Autosave skipped (no changes)');

          return;
        }

        if (responsePayload?.meta?.autosaveAt) {
          updateStatus('COM_CONTENT_AUTOSAVE_STATUS_SAVED', `Autosaved (${responsePayload.meta.autosaveAt})`);

          return;
        }

        updateStatus('COM_CONTENT_AUTOSAVE_STATUS_SAVED', 'Autosaved');
      },
      onError: () => {
        updateStatus('COM_CONTENT_AUTOSAVE_STATUS_FAILED', 'Autosave failed');
      },
      onFallback: () => {
        updateStatus('COM_CONTENT_AUTOSAVE_STATUS_FAILED', 'Autosave failed');
      },
    }).finally(() => {
      autosaveInProgress = false;
    });
  };

  const installDirtyTracking = () => {
    const form = getForm();

    if (!form) {
      return;
    }

    currentSignature = getFormSignature(form);

    form.addEventListener('input', () => {
      const nextSignature = getFormSignature(form);

      if (nextSignature !== currentSignature) {
        currentSignature = nextSignature;
        dirty = true;
        updateStatus('COM_CONTENT_AUTOSAVE_STATUS_PENDING', 'Autosave pending changes');
      }
    });

    form.addEventListener('change', () => {
      const nextSignature = getFormSignature(form);

      if (nextSignature !== currentSignature) {
        currentSignature = nextSignature;
        dirty = true;
        updateStatus('COM_CONTENT_AUTOSAVE_STATUS_PENDING', 'Autosave pending changes');
      }
    });

    form.addEventListener('submit', () => {
      manualSubmitInProgress = true;
    });
  };

  const installAutosaveScheduler = () => {
    const options = getAutosaveOptions();

    if (!options.enabled) {
      return;
    }

    const safeInterval = Math.max(5, options.interval);

    installDirtyTracking();
    ensureStatusNode();

    if (autosaveTimer) {
      window.clearInterval(autosaveTimer);
    }

    autosaveTimer = window.setInterval(runAutosaveCycle, safeInterval * 1000);
  };

  // Register events
  document.addEventListener('DOMContentLoaded', () => {
    Joomla.contentAutosave = {
      run: runAutosaveCycle,
      setup: installAutosaveScheduler,
    };

    installAutosaveScheduler();

    document.querySelectorAll(`[${buttonDataSelector}]`).forEach((button) => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const task = button.getAttribute(buttonDataSelector);
        submitTask(task);
      });
    });
  });
})(document, Joomla.submitform);
