/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, Joomla) => {
  'use strict';
  /**
   * Every quickicon with an ajax request url loads data and set them into the counter element
   * Also the data name is set as singular or plural.
   * A SR-only text ist added
   * The class pulse gets 'warning', 'success' or 'error', depending on the retrieved data.
   */

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.quickicon').forEach(quickicon => {
      const pulse = quickicon.querySelector('.pulse');
      const counter = quickicon.querySelector('.quickicon-amount');

      if (!counter) {
        return;
      }

      if (counter.dataset.url) {
        Joomla.request({
          url: counter.dataset.url,
          method: 'GET',
          onSuccess: resp => {
            const response = JSON.parse(resp);

            if (Object.prototype.hasOwnProperty.call(response, 'data')) {
              const name = quickicon.querySelector('.quickicon-name');
              const nameSpan = document.createElement('span');

              if (pulse) {
                const className = response.data > 0 ? 'warning' : 'success';
                pulse.classList.add(className);
              } // Set name in singular or plural


              if (response.data.name && name) {
                nameSpan.textContent = response.data.name;
                name.replaceChild(nameSpan, name.firstChild);
              } // Set amount of number into counter span


              counter.textContent = `\u200E${response.data.amount}`; // Insert screenreader text

              const sronly = quickicon.querySelector('.quickicon-sr-desc');

              if (response.data.sronly && sronly) {
                sronly.textContent = response.data.sronly;
              }
            } else if (pulse) {
              pulse.classList.add('error');
            }
          },
          onError: () => {
            if (pulse) {
              pulse.classList.add('error');
            }
          }
        });
      }
    });
  });
})(document, Joomla);