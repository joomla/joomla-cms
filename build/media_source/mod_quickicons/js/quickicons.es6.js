/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document, Joomla) {
  'use strict';

  var init = function init() {
    // Cleanup
    document.removeEventListener('DOMContentLoaded', init); // Get the elements

    var elements = [].slice.call(document.querySelectorAll('.quickicon-counter'));

    if (elements.length) {
      elements.forEach(function (element) {
        var iconurl = element.getAttribute('data-url');

        if (iconurl && Joomla && Joomla.request && typeof Joomla.request === 'function') {
          Joomla.request({
            url: iconurl,
            method: 'GET',
            onSuccess: function onSuccess(resp) {
              var response;

              try {
                response = JSON.parse(resp);
              } catch (error) {
                throw new Error('Failed to parse JSON');
              }

              if (response.data) {
                var elem = document.createElement('span');
                elem.textContent = response.data;
                element.parentNode.replaceChild(elem, element);
            }
            }
          });
        }
      });
    }
  };

  document.addEventListener('DOMContentLoaded', init);
})(document, Joomla);