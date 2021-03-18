/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Ajax call to get the override status.
(() => {
  'use strict'; // Add a listener on content loaded to initiate the check.

  document.addEventListener('DOMContentLoaded', () => {
    if (Joomla.getOptions('js-override-check')) {
      const options = Joomla.getOptions('js-override-check');

      const update = (type, text, linkHref) => {
        const link = document.getElementById('plg_quickicon_overridecheck');
        const linkSpans = link.querySelectorAll('span.j-links-link');

        if (link) {
          link.classList.add(type);

          if (linkHref) {
            link.setAttribute('href', linkHref);
          }
        }

        if (linkSpans.length) {
          linkSpans.forEach(span => {
            span.innerHTML = text;
          });
        }
      };

      Joomla.request({
        url: options.ajaxUrl,
        method: 'GET',
        data: '',
        perform: true,
        onSuccess: response => {
          const updateInfoList = JSON.parse(response);

          if (updateInfoList.installerOverride !== 'disabled') {
            if (Array.isArray(updateInfoList)) {
              if (updateInfoList.length === 0) {
                // No overrides found
                update('success', Joomla.Text._('PLG_QUICKICON_OVERRIDECHECK_UPTODATE'), '');
              } else {
                // Scroll to page top
                window.scrollTo(0, 0);
                update('danger', Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_OVERRIDEFOUND').replace('%s', `<span class="badge badge-light">${updateInfoList.length}</span>`), '');
              }
            } else {
              // An error occurred
              update('danger', Joomla.Text._('PLG_QUICKICON_OVERRIDECHECK_ERROR'), '');
            }
          } else {
            update('danger', Joomla.Text._('PLG_QUICKICON_OVERRIDECHECK_ERROR_ENABLE'), `index.php?option=com_plugins&task=plugin.edit&extension_id=${options.pluginId}`);
          }
        },
        onError: () => {
          // An error occurred
          update('danger', Joomla.Text._('PLG_QUICKICON_OVERRIDECHECK_ERROR'), '');
        }
      });
    }
  });
})();