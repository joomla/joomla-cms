/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Ajax call to get the update status of the installed extensions
(function () {
  'use strict';

  // Add a listener on content loaded to initiate the check

  document.addEventListener('DOMContentLoaded', function () {
    if (Joomla.getOptions('js-extensions-update')) {
      var options = Joomla.getOptions('js-extensions-update');
      Joomla.request({
        url: options.ajaxUrl + '&eid=0&skip=700',
        method: 'GET',
        data: '',
        perform: true,
        onSuccess: function onSuccess(response) {
          var link = document.getElementById('plg_quickicon_extensionupdate');
          var linkSpan = link.querySelectorAll('span.j-links-link');
          var updateInfoList = JSON.parse(response);

          if (updateInfoList instanceof Array) {
            if (updateInfoList.length === 0) {
              // No updates
              link.classList.add('success');
              for (var i = 0, len = linkSpan.length; i < len; i += 1) {
                linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPTODATE');
              }
            } else {
              var messages = {
                message: [Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_MESSAGE').replace('%s', '<span class="badge badge-light">' + updateInfoList.length + '</span>') + '<button class="btn btn-primary" onclick="document.location=\'' + options.url + '\'">' + Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_BUTTON') + '</button>'],
                error: ['info']
              };

              // Render the message
              Joomla.renderMessages(messages);

              // Scroll to page top
              window.scrollTo(0, 0);

              link.classList.add('danger');
              for (var _i = 0, _len = linkSpan.length; _i < _len; _i += 1) {
                linkSpan[_i].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND').replace('%s', '<span class="badge badge-light">' + updateInfoList.length + '</span>');
              }
            }
          } else {
            // An error occurred
            link.classList.add('danger');
            for (var _i2 = 0, _len2 = linkSpan.length; _i2 < _len2; _i2 += 1) {
              linkSpan[_i2].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR');
            }
          }
        },
        onError: function onError() {
          // An error occurred
          var link = document.getElementById('plg_quickicon_extensionupdate');
          var linkSpan = link.querySelectorAll('span.j-links-link');
          link.classList.add('danger');
          for (var i = 0, len = linkSpan.length; i < len; i += 1) {
            linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR');
          }
        }
      });
    }
  });
})();
