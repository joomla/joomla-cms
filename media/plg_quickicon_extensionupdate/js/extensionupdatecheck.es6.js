/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Ajax call to get the update status of the installed extensions
(() => {
  'use strict';

  // Add a listener on content loaded to initiate the check
  document.addEventListener('DOMContentLoaded', () => {
    if (Joomla.getOptions('js-extensions-update')) {
      const options = Joomla.getOptions('js-extensions-update');
      Joomla.request({
        url: `${options.ajaxUrl}&eid=0&skip=700`,
        method: 'GET',
        data: '',
        perform: true,
        onSuccess: (response) => {
          const link = document.getElementById('plg_quickicon_extensionupdate');
          const linkSpan = link.querySelectorAll('span.j-links-link');
          const updateInfoList = JSON.parse(response);

          if (updateInfoList instanceof Array) {
            if (updateInfoList.length === 0) {
            // No updates
              link.classList.add('success');
              for (let i = 0, len = linkSpan.length; i < len; i += 1) {
                linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPTODATE');
              }
            } else {
              const messages = {
                message: [
                  `${Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_MESSAGE').replace('%s', `<span class="badge badge-light">${updateInfoList.length}</span>`)}<button class="btn btn-primary" onclick="document.location='${options.url}'">${Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_BUTTON')}</button>`,
                ],
                error: ['info'],
              };

              // Render the message
              Joomla.renderMessages(messages);

              // Scroll to page top
              window.scrollTo(0, 0);

              link.classList.add('danger');
              for (let i = 0, len = linkSpan.length; i < len; i += 1) {
                linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND').replace('%s', `<span class="badge badge-light">${updateInfoList.length}</span>`);
              }
            }
          } else {
            // An error occurred
            link.classList.add('danger');
            for (let i = 0, len = linkSpan.length; i < len; i += 1) {
              linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR');
            }
          }
        },
        onError: () => {
        // An error occurred
          const link = document.getElementById('plg_quickicon_extensionupdate');
          const linkSpan = link.querySelectorAll('span.j-links-link');
          link.classList.add('danger');
          for (let i = 0, len = linkSpan.length; i < len; i += 1) {
            linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR');
          }
        },
      });
    }
  });
})();
