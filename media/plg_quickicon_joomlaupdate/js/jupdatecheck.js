/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * Ajax call to get the update status of Joomla
 */
(function (window, document, Joomla) {
  'use strict';

  var checkForJoomlaUpdates = function checkForJoomlaUpdates() {
    var options = Joomla.getOptions('js-joomla-update');
    var link = document.getElementById('plg_quickicon_joomlaupdate');
    var linkSpans = [].slice.call(link.querySelectorAll('span.j-links-link'));

    Joomla.request({
      url: options.ajaxUrl + '&eid=700&cache_timeout=3600',
      method: 'GET',
      data: '',
      perform: true,
      onSuccess: function onSuccess(response) {
        var updateInfoList = JSON.parse(response);

        if (updateInfoList instanceof Array) {
          if (updateInfoList.length === 0) {
            // No updates
            link.classList.add('success');
            linkSpans.forEach(function (span) {
              span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE');
            });
          } else {
            var updateInfo = updateInfoList.shift();

            if (updateInfo.version !== options.version) {
              var messages = {
                message: ['Joomla.JText._(\'PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_MESSAGE\').replace("%s", \'<span class="badge badge-light">' + updateInfoList.length + '</span>\')' + ('<button class="btn btn-primary" onclick="document.location=\'' + options.url + '\'">') + (Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_BUTOON') + '</button>')],
                error: ['info']
              };

              // Render the message
              Joomla.renderMessages(messages);

              // Scroll to page top
              window.scrollTo(0, 0);

              link.classList.add('danger');
              linkSpans.forEach(function (span) {
                span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND').replace('%s', '<span class="badge badge-light">}' + updateInfoList.length + '</span>');
              });
            } else {
              linkSpans.forEach(function (span) {
                span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE');
              });
            }
          }
        } else {
          // An error occurred
          link.classList.add('danger');
          linkSpans.forEach(function (span) {
            span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_ERROR');
          });
        }
      },
      onError: function onError() {
        // An error occurred
        link.classList.add('danger');
        linkSpans.forEach(function (span) {
          span.innerHTML = Joomla.JText._('PLG_QUICKICON_JOOMLAUPDATE_ERROR');
        });
      }
    });
  };

  var onBoot = function onBoot() {
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
