/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const elements = [].slice.call(document.querySelectorAll('#moduleEditModal .modal-footer .btn'));

    if (elements.length) {
      elements.forEach((element) => {
        element.addEventListener('click', (event) => {
          const target = event.target.getAttribute('data-target');

          if (target) {
            const iframe = document.querySelector('#moduleEditModal iframe');
            iframe.contents().querySelector(target).click();
          }
        });
      });
    }
  });
})();
