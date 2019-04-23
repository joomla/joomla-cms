/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    window.jSelectModuleType = (elem) => {
      const elements = [].slice.call(document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn'));

      if (elements.length) {
        elements.forEach((button) => {
          button.classList.remove('hidden');
        });
      }
    };

    var buttons = [].slice.call(document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn'));

    if (buttons.length) {
      buttons.forEach(function (button) {
        button.addEventListener('click', function (event) {
          let target = event.currentTarget;

          // There is some bug with events in iframe where currentTarget is "null" => prevent this here by bubble up
          if (!target)
          {
            target = event.target;
          }

          const clickTarget = target.getAttribute('data-target');

          if (target) {
            const iframe = document.querySelector('#moduleDashboardAddModal iframe');
            const content = iframe.contentDocument || iframe.contentWindow.document;

            content.querySelector(clickTarget).click();
          }
        });
      });
    }
  });
})();
