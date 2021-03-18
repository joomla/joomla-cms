/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document, Joomla) {
  'use strict';
  /**
   * Every quickicon with an ajax request url loads data and set them into the counter element
   * Also the data name is set as singular or plural.
   * A SR-only text ist added
   * The class pulse gets 'warning', 'success' or 'error', depending on the retrieved data.
   */

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quickicon').forEach(function (quickicon) {
      var pulse = quickicon.querySelector('.pulse');
      var counter = quickicon.querySelector('.quickicon-amount');

      if (!counter) {
        return;
      }

      if (counter.dataset.url) {
        Joomla.request({
          url: counter.dataset.url,
          method: 'GET',
          onSuccess: function onSuccess(resp) {
            var response = JSON.parse(resp);

            if (Object.prototype.hasOwnProperty.call(response, 'data')) {
              var name = quickicon.querySelector('.quickicon-name');
              var nameSpan = document.createElement('span');

              if (pulse) {
                var className = response.data > 0 ? 'warning' : 'success';
                pulse.classList.add(className);
              } // Set name in singular or plural


              if (response.data.name && name) {
                nameSpan.textContent = response.data.name;
                name.replaceChild(nameSpan, name.firstChild);
              } // Set amount of number into counter span


              counter.textContent = "\u200E".concat(response.data.amount); // Insert screenreader text

              var sronly = quickicon.querySelector('.quickicon-sr-desc');

              if (response.data.sronly && sronly) {
                sronly.textContent = response.data.sronly;
              }
            } else if (pulse) {
              pulse.classList.add('error');
            }
          },
          onError: function onError() {
            if (pulse) {
              pulse.classList.add('error');
            }
          }
        });
      }
    });
  });
})(document, Joomla);