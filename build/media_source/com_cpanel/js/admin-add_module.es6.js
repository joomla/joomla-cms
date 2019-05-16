/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    window.jSelectModuleType = () => {
      const elements = [].slice.call(document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn.hidden'));

      if (elements.length) {
        elements.forEach((button) => {
          button.classList.remove('hidden');
        });
      }
    };

    const buttons = [].slice.call(document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn'));
    const hideButtons = [];

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

            const iframe = document.querySelector('#moduleDashboardAddModal iframe');
            const content = iframe.contentDocument || iframe.contentWindow.document;

            content.querySelector(clickTarget).click();
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
  });
})(document);
