/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const renderResult = (resultEl, message, type) => {
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${type} mt-3`;
      alertDiv.textContent = message;
      resultEl.replaceChildren(alertDiv);
    };

    // AJAX responses may include a new token to prevent JINVALID_TOKEN errors on subsequent submissions.
    // The form is updated with the new token, and the old one is removed to keep CSRF protection.
    const updateToken = (form, newToken) => {
      if (!newToken) {
        return;
      }

      // Joomla CSRF token input: name is a 32-char hex hash, value is always "1"
      const allHidden = form.querySelectorAll('input[type="hidden"][value="1"]');

      for (const el of allHidden) {
        if (/^[a-f0-9]{32}$/.test(el.name)) {
          el.name = newToken;
          break;
        }
      }
    };

    const setSubmitting = (form, isSubmitting) => {
      const btn = form.querySelector('.mod-contact-form__btn-submit');

      if (!btn) {
        return;
      }

      if (isSubmitting) {
        btn.disabled = true;
        btn.textContent = Joomla.Text._('MOD_CONTACT_FORM_SENDING');
      } else {
        btn.disabled = false;
        btn.textContent = btn.dataset.submitLabel || Joomla.Text._('MOD_CONTACT_FORM_SEND');
      }
    };

    const handleSubmit = async (event) => {
      const form = event.target;

      if (!form.matches('form[data-mod-contact-form]')) {
        return;
      }

      event.preventDefault();

      const instanceId = form.dataset.instanceId !== undefined
        ? Number(form.dataset.instanceId)
        : null;
      const resultEl    = document.getElementById(form.dataset.resultTarget);
      const ajaxUrl     = form.dataset.ajaxUrl;
      const redirectUrl = form.dataset.redirect;
      const successMsg  = form.dataset.successMessage;

      if (document.formvalidator && typeof document.formvalidator.isValid === 'function' && !document.formvalidator.isValid(form)) {
        const firstInvalidField = form.querySelector('[aria-invalid="true"], .invalid, .form-control-danger');

        if (firstInvalidField && typeof firstInvalidField.focus === 'function') {
          firstInvalidField.focus();
        }

        return;
      }

      if (resultEl) {
        resultEl.replaceChildren();
      }

      setSubmitting(form, true);

      let responseData;

      try {
        const response = await fetch(ajaxUrl, {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
        });

        if (!response.ok) {
          const key = response.status >= 500
            ? 'MOD_CONTACT_FORM_MESSAGE_SERVER_ERROR'
            : 'MOD_CONTACT_FORM_MESSAGE_NETWORK_ERROR';
          throw new Error(Joomla.Text._(key));
        }

        const json = await response.json();

        // com_ajax wraps responses: { success, message, messages, data }
        responseData = json.data ?? json;
      } catch (err) {
        setSubmitting(form, false);

        if (resultEl) {
          const message = err && err.message
            ? err.message
            : Joomla.Text._('MOD_CONTACT_FORM_MESSAGE_NETWORK_ERROR');
          renderResult(resultEl, message, 'danger');
        }

        return;
      }

      updateToken(form, responseData.token);

      if (
        instanceId !== null
        && responseData.instanceId !== undefined
        && responseData.instanceId !== instanceId
      ) {
        setSubmitting(form, false);

        if (resultEl) {
          renderResult(resultEl, Joomla.Text._('MOD_CONTACT_FORM_MESSAGE_NETWORK_ERROR'), 'danger');
        }

        return;
      }

      if (responseData.ok) {
        if (redirectUrl) {
          window.location.assign(redirectUrl);
          return;
        }

        setSubmitting(form, false);

        if (resultEl) {
          renderResult(resultEl, responseData.message || successMsg, 'success');
        }

        form.reset();
      } else {
        setSubmitting(form, false);

        const errors = responseData.errors || {};

        if (resultEl) {
          const message = errors.general
            ? errors.general
            : Joomla.Text._('MOD_CONTACT_FORM_MESSAGE_VALIDATION_FAILED');
          renderResult(resultEl, message, 'danger');
        }
      }
    };

    document.addEventListener('submit', handleSubmit);
  });
})(window.Joomla, document);
