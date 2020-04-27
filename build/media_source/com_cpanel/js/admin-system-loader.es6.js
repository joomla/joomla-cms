/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, Joomla) => {
  'use strict';

  const init = () => {
    // Cleanup
    document.removeEventListener('DOMContentLoaded', init);

    // Get the elements
    const elements = [].slice.call(document.querySelectorAll('.system-counter'));

    if (elements.length) {
      elements.forEach((element) => {
        const badgeurl = element.getAttribute('data-url');

        if (badgeurl && Joomla && Joomla.request && typeof Joomla.request === 'function') {
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

                elem.classList.add('float-right');
                elem.classList.add('badge');
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
    }
  };

  document.addEventListener('DOMContentLoaded', init);
})(document, Joomla);
