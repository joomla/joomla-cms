/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Ajax call to get the override status.
(function () {
  'use strict';

  // Add a listener on content loaded to initiate the check.

  document.addEventListener('DOMContentLoaded', function () {
    if (Joomla.getOptions('js-override-check')) {
      var options = Joomla.getOptions('js-override-check');
      Joomla.request({
        url: options.ajaxUrl,
        method: 'GET',
        data: '',
        perform: true,
        onSuccess: function onSuccess(response) {
          var link = document.getElementById('plg_quickicon_overridecheck');
          var linkSpan = link.querySelectorAll('span.j-links-link');
          var updateInfoList = JSON.parse(response);

          if (updateInfoList.installerOverride !== 'disabled') {
            if (updateInfoList instanceof Array) {
              if (updateInfoList.length === 0) {
                // No overrides found
                link.classList.add('success');
                for (var i = 0, len = linkSpan.length; i < len; i += 1) {
                  linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_UPTODATE');
                }
              } else {
                // Scroll to page top
                window.scrollTo(0, 0);

                link.classList.add('danger');
                for (var _i = 0, _len = linkSpan.length; _i < _len; _i += 1) {
                  linkSpan[_i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_OVERRIDEFOUND').replace('%s', '<span class="badge badge-light">' + updateInfoList.length + '</span>');
                }
              }
            } else {
              // An error occurred
              link.classList.add('danger');
              for (var _i2 = 0, _len2 = linkSpan.length; _i2 < _len2; _i2 += 1) {
                linkSpan[_i2].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_ERROR');
              }
            }
          } else {
            link.classList.add('danger');
            link.setAttribute('href', 'index.php?option=com_plugins&task=plugin.edit&extension_id=491');
            for (var _i3 = 0, _len3 = linkSpan.length; _i3 < _len3; _i3 += 1) {
              linkSpan[_i3].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_ERROR_ENABLE');
            }
          }
        },
        onError: function onError() {
          // An error occurred
          var link = document.getElementById('plg_quickicon_overridecheck');
          var linkSpan = link.querySelectorAll('span.j-links-link');
          link.classList.add('danger');
          for (var i = 0, len = linkSpan.length; i < len; i += 1) {
            linkSpan[i].innerHTML = Joomla.JText._('PLG_QUICKICON_OVERRIDECHECK_ERROR');
          }
        }
      });
    }
  });
})();
