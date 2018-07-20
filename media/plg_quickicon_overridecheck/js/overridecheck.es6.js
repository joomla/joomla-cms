/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Ajax call to get the override status.
(() => {
  'use strict';

  // Add a listener on content loaded to initiate the check.

  document.addEventListener('DOMContentLoaded', () => {
    if (Joomla.getOptions('js-override-check')) {
      const options = Joomla.getOptions('js-override-check');
      Joomla.request({
        url: options.ajaxUrl,
        method: 'GET',
        data: '',
        perform: true,
        onSuccess: (response) => {
          const link = document.getElementById('plg_quickicon_overridecheck');
          const linkSpan = link.querySelectorAll('span.j-links-link');
          const updateInfoList = JSON.parse(response);

          if (updateInfoList.installerOverride !== 'disabled') {
            if (updateInfoList instanceof Array) {
              if (updateInfoList.length === 0) {
                // No overrides found
                link.classList.add('success');
                for (let i = 0, len = linkSpan.length; i < len; i += 1) {
                  linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_UPTODATE');
                }
              } else {
                // Scroll to page top
                window.scrollTo(0, 0);

                link.classList.add('danger');
                for (let i = 0, len = linkSpan.length; i < len; i += 1) {
                  linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_OVERRIDEFOUND').replace('%s', `<span class="badge badge-light">${updateInfoList.length}</span>`);
                }
              }
            } else {
              // An error occurred
              link.classList.add('danger');
              for (let i = 0, len = linkSpan.length; i < len; i += 1) {
                linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_ERROR');
              }
            }
          } else {
            link.classList.add('danger');
            link.setAttribute('href', 'index.php?option=com_plugins&task=plugin.edit&extension_id=491');
            for (let i = 0, len = linkSpan.length; i < len; i += 1) {
              linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_ERROR_ENABLE');
            }
          }
        },
        onError: () => {
          // An error occurred
          const link = document.getElementById('plg_quickicon_overridecheck');
          const linkSpan = link.querySelectorAll('span.j-links-link');
          link.classList.add('danger');
          for (let i = 0, len = linkSpan.length; i < len; i += 1) {
            linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_ERROR');
          }
        },
      });
    }
  });
})();
