/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
  // Get the elements
    const elements = document.querySelectorAll('.system-counter');

    for (let i = 0, l = elements.length; l > i; i += 1) {
      const badgeurl = elements[i].getAttribute('data-url');

    Joomla.request({
      url: badgeurl,
      method: 'POST',
      onSuccess: (resp) => {
        let response;
        try {
          response = JSON.parse(resp);
        } catch (error) {
          throw new Error('Failed to parse JSON');
        }

        if (response.error || !response.success) {
          element.classList.remove('fa-spin');
          element.classList.remove('fa-spinner');
          element.classList.add('text-danger');
          element.classList.add('fa-remove');
        } else if (response.data) {
          const elem = document.createElement('span');

          elem.classList.add('pull-right');
          elem.classList.add('badge');
          elem.classList.add('badge-pill');
          elem.classList.add('badge-warning');
          elem.innerHTML = response.data;

          element.parentNode.replaceChild(elem, element);
        } else {
          element.classList.remove('fa-spin');
          element.classList.remove('fa-spinner');
          element.classList.add('fa-check');
          element.classList.add('text-success');
        }
      },
      onError: () => {
        element.classList.remove('fa-spin');
        element.classList.remove('fa-spinner');
        element.classList.add('text-danger');
        element.classList.add('fa-remove');
      },
    });
    }
  });
})();
