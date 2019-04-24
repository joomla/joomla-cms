/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    window.jSelectModuleType = () => {
      const elements = [].slice.call(document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn'));

      if (elements.length) {
        elements.forEach((button) => {
          button.classList.remove('hidden');
        });
      }
    };

    const buttons = [].slice.call(document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn'));

    if (buttons.length) {
      buttons.forEach((button) => {
        button.addEventListener('click', (event) => {
          let elem = event.currentTarget;

          // There is some bug with events in iframe where currentTarget is "null"
          // => prevent this here by bubble up
          if (!elem) {
            elem = event.target;
          }

          const clicktarget = elem.getAttribute('data-target');

          if (clicktarget) {
            const iframe = document.querySelector('#moduleDashboardAddModal iframe');
            const content = iframe.contentDocument || iframe.contentWindow.document;
            content.querySelector(clicktarget).click();
          }
        });
      });
    }
  });
})(document);
