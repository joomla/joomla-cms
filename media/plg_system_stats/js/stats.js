/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
 */

Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';

  var data = {
    option: 'com_ajax',
    group: 'system',
    plugin: 'renderStatsMessage',
    format: 'raw'
  };

  var initStatsEvents = function initStatsEvents() {
    var messageContainer = document.getElementById('system-message-container');
    var joomlaAlert = messageContainer.querySelector('.js-pstats-alert');
    var detailsContainer = messageContainer.querySelector('.js-pstats-data-details');

    // Show details about the information being sent
    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-details')) {
        event.preventDefault();
        detailsContainer.classList.toggle('d-none');
      }
    });

    // Always allow
    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-allow-always')) {
        event.preventDefault();

        // Remove message
        joomlaAlert.close();

        // Set data
        data.plugin = 'sendAlways';

        Joomla.getJson(data);
      }
    });

    // Allow once
    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-allow-once')) {
        event.preventDefault();

        // Remove message
        joomlaAlert.close();

        // Set data
        data.plugin = 'sendOnce';

        Joomla.getJson(data);
      }
    });

    // Never allow
    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-allow-never')) {
        event.preventDefault();

        // Remove message
        joomlaAlert.close();

        // Set data
        data.plugin = 'sendNever';

        Joomla.getJson(data);
      }
    });
  };

  var getJson = function getJson(options) {
    var messageContainer = document.getElementById('system-message-container');
    Joomla.request({
      url: 'index.php?option=' + options.option + '&group=' + options.group + '&plugin=' + options.plugin + '&format=' + options.format,
      headers: {
        'Content-Type': 'application/json'
      },
      onSuccess: function onSuccess(response) {
        try {
          var json = JSON.parse(response);
          if (json && json.html) {
            messageContainer.innerHTML = response.html;
            messageContainer.querySelector('.js-pstats-alert').style.display = 'block';

            initStatsEvents();
          }
        } catch (e) {
          throw new Error(e);
        }
      },
      onError: function onError(xhr) {
        Joomla.renderMessages({
          error: [xhr.response]
        });
      }
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    data.plugin = 'sendStats';
    getJson(data);
  });
})(Joomla, document);
