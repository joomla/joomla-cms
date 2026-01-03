/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-login');
    const btn = document.getElementById('btn-login-submit');
    const usernameInput = document.getElementById('mod-login-username');
    const passwordInput = document.getElementById('mod-login-password');
    const passwordSection = form ? form.querySelector('.login-password') : null;
    const submitSection = form ? form.querySelector('.login-submit') : null;
    const methodsSection = form ? form.querySelector('.login-methods') : null;
    const continuePasswordBtn = document.getElementById('btn-login-password');
    const passkeyBtn = form ? form.querySelector('[data-is-passkey="true"]') : null;

    // Track if password flow is active (for Pattern A)
    let passwordFlowActive = !methodsSection;

    /**
     * Show the password section (Pattern A flow)
     */
    const showPasswordSection = () => {
      if (!passwordSection || !submitSection || !passwordInput) {
        return;
      }

      // Hide the methods section
      if (methodsSection) {
        methodsSection.style.display = 'none';
      }

      // Show password and submit sections
      passwordSection.style.display = '';
      submitSection.style.display = '';

      // Update ARIA state on continue button
      if (continuePasswordBtn) {
        continuePasswordBtn.setAttribute('aria-expanded', 'true');
      }

      // Set password as required and mark flow as active
      passwordInput.required = true;
      passwordFlowActive = true;

      // Focus password field
      passwordInput.focus();
    };

    // Pattern A: Only apply if methods section exists (passkey available)
    if (methodsSection && passwordSection && submitSection && continuePasswordBtn && passwordInput) {
      // Mark JS-enabled mode
      document.documentElement.classList.add('js-enabled');

      // Hide password and submit sections initially
      passwordSection.style.display = 'none';
      submitSection.style.display = 'none';

      // Remove required from hidden password field to prevent browser validation blocking
      passwordInput.required = false;

      // Update ARIA state on continue button
      continuePasswordBtn.setAttribute('aria-expanded', 'false');

      // "Continue with password" button handler
      continuePasswordBtn.addEventListener('click', showPasswordSection);

      // Passkey button handling - intercept if username is empty
      if (passkeyBtn && usernameInput) {
        // Clear aria-invalid when username is filled
        usernameInput.addEventListener('input', () => {
          if (usernameInput.value.trim() !== '') {
            usernameInput.removeAttribute('aria-invalid');
          }
        });

        // Use capturing phase to check username before original onclick runs
        passkeyBtn.addEventListener('click', (e) => {
          if (usernameInput.value.trim() === '') {
            // Prevent the original onclick and any other handlers
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            // Set aria-invalid on username field
            usernameInput.setAttribute('aria-invalid', 'true');

            // Ensure message container has aria-live for screen readers
            const messageContainer = document.getElementById('system-message-container');
            if (messageContainer && !messageContainer.hasAttribute('aria-live')) {
              messageContainer.setAttribute('aria-live', 'polite');
              messageContainer.setAttribute('role', 'status');
            }

            // Show warning using Joomla's message system
            Joomla.renderMessages({
              warning: [Joomla.Text._('MOD_LOGIN_USERNAME_REQUIRED_FOR_PASSKEY')],
            });

            // Focus username
            usernameInput.focus();
          }
          // If username is present, let the event continue to the original onclick handler
        }, true); // Capturing phase
      }
    }

    // Form submit handler
    if (btn) {
      btn.addEventListener('click', (event) => {
        event.preventDefault();

        // If Pattern A is active and password flow not started, show password section
        if (methodsSection && !passwordFlowActive) {
          showPasswordSection();
          return;
        }

        // Normal form validation and submit
        if (document.formvalidator.isValid(btn.form)) {
          Joomla.submitbutton('login');
        }
      });
    }

    // Intercept other submit paths when Pattern A is active
    if (form && methodsSection) {
      form.addEventListener('submit', (event) => {
        if (!passwordFlowActive) {
          event.preventDefault();
          showPasswordSection();
        }
      });
    }

    // Intercept Enter key in username field when Pattern A is active
    if (usernameInput && methodsSection) {
      usernameInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !passwordFlowActive) {
          e.preventDefault();
          showPasswordSection();
        }
      });
    }
  });
})(window.Joomla, document);
