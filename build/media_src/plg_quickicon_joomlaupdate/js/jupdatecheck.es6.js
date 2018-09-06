/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * Ajax call to get the update status of Joomla
 */
((window, document, Joomla) => {
  'use strict';

  const checkForJoomlaUpdates = () => {
    const options = Joomla.getOptions('js-joomla-update');
    const link = document.getElementById('plg_quickicon_joomlaupdate');
    const linkSpans = [].slice.call(link.querySelectorAll('span.j-links-link'));

    Joomla.request({
      url: `${options.ajaxUrl}&eid=700&cache_timeout=3600`,
      method: 'GET',
      data: '',
      perform: true,
      onSuccess: (response) => {
        const updateInfoList = JSON.parse(response);

        if (updateInfoList instanceof Array) {
          if (updateInfoList.length === 0) {
            // No updates
            link.classList.add('success');
            linkSpans.forEach((span) => {
              span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE');
            });
          } else {
            const updateInfo = updateInfoList.shift();

            if (updateInfo.version !== options.version) {
              const messages = {
                message: [
                  `${Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_MESSAGE').replace('%s', `<span class="badge badge-danger">${updateInfoList.length}</span>`)}`
                  + `<button class="btn btn-primary" onclick="document.location='${options.url}'">`
                  + `${Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_BUTTON')}</button>`,
                ],
                error: ['info'],
              };

              // Render the message
              Joomla.renderMessages(messages);

              // Scroll to page top
              window.scrollTo(0, 0);

              link.classList.add('danger');
              linkSpans.forEach((span) => {
                span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND').replace('%s', `<span class="badge badge-light">${updateInfoList.length}</span>`);
              });
            } else {
              linkSpans.forEach((span) => {
                span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE');
              });
            }
          }
        } else {
          // An error occurred
          link.classList.add('danger');
          linkSpans.forEach((span) => {
            span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_ERROR');
          });
        }
      },
      onError: () => {
        // An error occurred
        link.classList.add('danger');
        linkSpans.forEach((span) => {
          span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_ERROR');
        });
      },
    });
  };

  const onBoot = () => {
    if (!Joomla || typeof Joomla.getOptions !== 'function' || !Joomla.getOptions('js-joomla-update')) {
      throw new Error('Script is not properly initialised');
    }

    setTimeout(checkForJoomlaUpdates, 2000);

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  // Initialize
  document.addEventListener('DOMContentLoaded', onBoot);
})(window, document, Joomla);
