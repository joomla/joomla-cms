/**
 * @copyright Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// Ajax call to get the override status.
(() => {
  'use strict';

  // Add a listener on content loaded to initiate the check.
  document.addEventListener('DOMContentLoaded', () => {
    if (Joomla.getOptions('js-privacy-check')) {
      const options = Joomla.getOptions('js-privacy-check');
      const update = (type, text, linkHref) => {
        const link = document.getElementById('plg_quickicon_privacycheck');
        const linkSpans = link.querySelectorAll('span.j-links-link');
        if (link) {
          link.classList.add(type);

          if (linkHref) {
            link.setAttribute('href', linkHref);
          }
        }

        if (linkSpans.length) {
          linkSpans.forEach((span) => {
            span.innerHTML = text;
          });
        }
      };

      const languageStrings = options.plg_quickicon_privacycheck_text;

      Joomla.request({
        url: options.plg_quickicon_privacycheck_ajax_url,
        method: 'GET',
        data: '',
        perform: true,
        onSuccess: (response) => {
          const privacyRequestsList = JSON.parse(response);

          if (privacyRequestsList.data.number_urgent_requests === 0) {
            // No requests
            update('success', languageStrings.NOREQUEST, '');
          } else {
            // Requests
            update(
              'danger',
              `${languageStrings.REQUESTFOUND}&nbsp;<span class="badge badge-light">${privacyRequestsList.data.number_urgent_requests}</span>`,
              options.plg_quickicon_privacycheck_url,
            );
          }
        },
        onError: () => {
          // An error occurred
          update('danger', languageStrings.ERROR, '');
        },
      });
    }
  });
})();
