/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Ajax call to get the update status of Joomla
 */
(function (document, Joomla) {
  'use strict';

  var checkForJoomlaUpdates = function checkForJoomlaUpdates() {
    if (Joomla.getOptions('js-extensions-update')) {
      var options = Joomla.getOptions('js-joomla-update');

      var update = function update(type, text) {
        var link = document.getElementById('plg_quickicon_joomlaupdate');
        var linkSpans = [].slice.call(link.querySelectorAll('span.j-links-link'));

        if (link) {
          link.classList.add(type);
        }

        if (linkSpans.length) {
          linkSpans.forEach(function (span) {
            span.innerHTML = text;
          });
        }
      };

      Joomla.request({
        url: options.ajaxUrl,
        method: 'GET',
        data: '',
        perform: true,
        onSuccess: function onSuccess(response) {
          var updateInfoList = JSON.parse(response);

          if (Array.isArray(updateInfoList)) {
            if (updateInfoList.length === 0) {
              // No updates
              update('success', Joomla.Text._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE'));
            } else {
              var updateInfo = updateInfoList.shift();

              if (updateInfo.version !== options.version) {
                update('danger', Joomla.Text._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND').replace('%s', "<span class=\"badge badge-light\"> \u200E ".concat(updateInfo.version, "</span>")));
              } else {
                update('success', Joomla.Text._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE'));
              }
            }
          } else {
            // An error occurred
            update('danger', Joomla.Text._('PLG_QUICKICON_JOOMLAUPDATE_ERROR'));
          }
        },
        onError: function onError() {
          // An error occurred
          update('danger', Joomla.Text._('PLG_QUICKICON_JOOMLAUPDATE_ERROR'));
        }
      });
    }
  };

  var onBoot = function onBoot() {
    if (!Joomla || typeof Joomla.getOptions !== 'function' || !Joomla.getOptions('js-joomla-update')) {
      throw new Error('Script is not properly initialised');
    }

    setTimeout(checkForJoomlaUpdates, 2000); // Cleanup

    document.removeEventListener('DOMContentLoaded', onBoot);
  }; // Initialize


  document.addEventListener('DOMContentLoaded', onBoot);
})(document, Joomla);