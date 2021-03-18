/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    window.jSelectModuleType = function () {
      var elements = document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn.hidden');

      if (elements.length) {
        setTimeout(function () {
          elements.forEach(function (button) {
            button.classList.remove('hidden');
          });
        }, 1000);
      }
    };

    var buttons = document.querySelectorAll('#moduleDashboardAddModal .modal-footer .btn');
    var hideButtons = [];
    var isSaving = false;

    if (buttons.length) {
      buttons.forEach(function (button) {
        if (button.classList.contains('hidden')) {
          hideButtons.push(button);
        }

        button.addEventListener('click', function (event) {
          var elem = event.currentTarget; // There is some bug with events in iframe where currentTarget is "null"
          // => prevent this here by bubble up

          if (!elem) {
            elem = event.target;
          }

          if (elem) {
            var clickTarget = elem.getAttribute('data-target'); // We remember to be in the saving process

            isSaving = clickTarget === '#saveBtn'; // Reset saving process, if e.g. the validation of the form fails

            setTimeout(function () {
              isSaving = false;
            }, 1500);
            var iframe = document.querySelector('#moduleDashboardAddModal iframe');
            var content = iframe.contentDocument || iframe.contentWindow.document;
            content.querySelector(clickTarget).click();
          }
        });
      });
    } // @TODO remove jQuery dependency, when the modal is not bootstrap anymore

    /* global jQuery */


    jQuery('#moduleDashboardAddModal').on('hide.bs.modal', function () {
      hideButtons.forEach(function (button) {
        button.classList.add('hidden');
      });
    });
    jQuery('#moduleDashboardAddModal').on('hidden.bs.modal', function () {
      if (isSaving) {
        setTimeout(function () {
          window.parent.location.reload();
        }, 1000);
      }
    });
  });
})(document);