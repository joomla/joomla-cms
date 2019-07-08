/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {
  'use strict';

  /**
   * Every quickicon with an ajax request url loads data and set them into the counter element
   * Also the data name is set as singular or plural.
   * The class pulse gets 'warning', 'success' or 'error', depending on the retrieved data.
   */
  document.addEventListener('DOMContentLoaded', () => {
    Array.prototype.forEach.call(document.querySelectorAll('.quickicon'), (quickicon) => {
      const pulse = quickicon.querySelector('.pulse');
      const counter = quickicon.querySelector('.quickicon-amount');
      if (!counter) {
        return;
      }

      if (counter.dataset.url) {
        Joomla.request({
          url: counter.dataset.url,
          method: 'GET',
          onSuccess: ((resp) => {
            const response = JSON.parse(resp);

            if (Object.prototype.hasOwnProperty.call(response, 'data')) {
              const name = quickicon.querySelector('.quickicon-name');
              const nameSpan = document.createElement('span');

              if (pulse) {
                const className = response.data > 0 ? 'warning' : 'success';
                pulse.classList.add(className);
              }

              // Set name in singular or plural
              if (name && name.dataset.nameSingular && name.dataset.namePlural) {
                if (response.data <= 1) {
                  nameSpan.textContent = name.dataset.nameSingular;
                } else {
                  nameSpan.textContent = name.dataset.namePlural;
                }

                name.replaceChild(nameSpan, name.firstChild);
              }

              // Set amount of number into counter span
              counter.textContent = response.data;

              // Insert screenreader text
              const srElement = quickicon.querySelector('.quickicon-sr-desc');
              if (srElement) {
                if (response.data === 0) {
                  srElement.textContent = srElement.dataset.sronlyZero;
                } else if (response.data === 1) {
                  srElement.textContent = srElement.dataset.sronlyOne;
                } else {
                  srElement.textContent = srElement.dataset.sronlyN;
                }
              }
            } else if (pulse) {
              pulse.classList.add('error');
            }
          }),
          onError: (() => {
            if (pulse) {
              pulse.classList.add('error');
            }
          }),
        });
      }
    });
  });
})(document, Joomla);
