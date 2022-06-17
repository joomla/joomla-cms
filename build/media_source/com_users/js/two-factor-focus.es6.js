/**
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const elCodeField = document.getElementById('users-mfa-code');
    const elValidateButton = document.getElementById('users-mfa-captive-button-submit');
    const elToolbarButton = document.getElementById('toolbar-user-mfa-submit').querySelector('button');

    // Focus the code field. If the code field is hidden, focus the submit button (useful e.g. for WebAuthn)
    if (
      elCodeField && elCodeField.style.display !== 'none'
      && !elCodeField.classList.contains('visually-hidden') && elCodeField.type !== 'hidden'
    ) {
      elCodeField.focus();
    } else {
      if (elValidateButton) {
        elValidateButton.focus();
      }
      if (elToolbarButton) {
        elToolbarButton.focus();
      }
    }

    // Capture the admin toolbar buttons, make them click the inline buttons
    document.querySelectorAll('.button-user-mfa-submit').forEach((elButton) => {
      elButton.addEventListener('click', (e) => {
        e.preventDefault();

        elValidateButton.click();
      });
    });

    document.querySelectorAll('.button-user-mfa-logout').forEach((elButton) => {
      elButton.addEventListener('click', (e) => {
        e.preventDefault();

        const elLogout = document.getElementById('users-mfa-captive-button-logout');

        if (elLogout) {
          elLogout.click();
        }
      });
    });

    document.querySelectorAll('.button-user-mfa-choose-another').forEach((elButton) => {
      elButton.addEventListener('click', (e) => {
        e.preventDefault();

        const elChooseAnother = document.getElementById('users-mfa-captive-form-choose-another');

        if (elChooseAnother) {
          elChooseAnother.click();
        }
      });
    });
  });
})();
