/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    window.jSelectModuleType = () => {
      const elements = document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn.hidden');

      if (elements.length) {
        setTimeout(() => {
          elements.forEach((button) => {
            button.classList.remove('hidden');
          });
        }, 1000);
      }
    };

    const buttons = document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn');
    const hideButtons = [];
    let isSaving = false;

    if (buttons.length) {
      buttons.forEach((button) => {
        if (button.classList.contains('hidden')) {
          hideButtons.push(button);
        }

        button.addEventListener('click', (event) => {
          let elem = event.currentTarget;

          // There is some bug with events in iframe where currentTarget is "null"
          // => prevent this here by bubble up
          if (!elem) {
            elem = event.target;
          }

          if (elem) {
            const clickTarget = elem.getAttribute('data-target');

            // We remember to be in the saving process
            isSaving = clickTarget === '#saveBtn';

            // Reset saving process, if e.g. the validation of the form fails
            setTimeout(() => { isSaving = false; }, 1500);

            const iframe = document.querySelector('#moduleDashboardAddModal iframe');
            const content = iframe.contentDocument || iframe.contentWindow.document;
            const targetBtn = content.querySelector(clickTarget);

            if (targetBtn) {
              targetBtn.click();
            }
          }
        });
      });
    }

    // @TODO remove jQuery dependency, when the modal is not bootstrap anymore
    /* global jQuery */
    jQuery('#moduleDashboardAddModal').on('hide.bs.modal', () => {
      hideButtons.forEach((button) => {
        button.classList.add('hidden');
      });
    });

    jQuery('#moduleDashboardAddModal').on('hidden.bs.modal', () => {
      if (isSaving) {
        setTimeout(() => { window.parent.location.reload(); }, 1000);
      }
    });
  });
})(document);
