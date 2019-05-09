/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {
  'use strict';

  /**
   * Every quickicon with an ajax request url loads data and set them into the counter element
   * Also the data name is set as singular or plural.
   */
  document.addEventListener('DOMContentLoaded', () => {
    Array.prototype.forEach.call(document.querySelectorAll('.quickicon'), (quickicon) => {
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

            if (response.data) {
              const name = quickicon.querySelector('.quickicon-name');
              const nameSpan = document.createElement('span');

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
            }
          }),
        });
      }
    });
  });
})(document, Joomla);
