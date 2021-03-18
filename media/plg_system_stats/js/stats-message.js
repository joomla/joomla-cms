/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
 */
Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';

  var initStatsEvents = function initStatsEvents(callback) {
    var messageContainer = document.getElementById('system-message-container');
    var joomlaAlert = messageContainer.querySelector('.js-pstats-alert');
    var detailsContainer = messageContainer.querySelector('#js-pstats-data-details'); // Show details about the information being sent

    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-details')) {
        event.preventDefault();
        detailsContainer.classList.toggle('d-none');
      }
    }); // Always allow

    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-allow-always')) {
        event.preventDefault(); // Remove message

        joomlaAlert.close();
        callback({
          plugin: 'sendAlways'
        });
      }
    }); // Allow once

    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-allow-once')) {
        event.preventDefault(); // Remove message

        joomlaAlert.close();
        callback({
          plugin: 'sendOnce'
        });
      }
    }); // Never allow

    document.addEventListener('click', function (event) {
      if (event.target.classList.contains('js-pstats-btn-allow-never')) {
        event.preventDefault(); // Remove message

        joomlaAlert.close();
        callback({
          plugin: 'sendNever'
        });
      }
    });
  };

  var getJson = function getJson() {
    var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
        _ref$plugin = _ref.plugin,
        plugin = _ref$plugin === void 0 ? 'sendStats' : _ref$plugin;

    var url = "index.php?option=com_ajax&group=system&plugin=".concat(plugin, "&format=raw");
    var messageContainer = document.getElementById('system-message-container');
    Joomla.request({
      url: url,
      headers: {
        'Content-Type': 'application/json'
      },
      onSuccess: function onSuccess(response) {
        try {
          var json = JSON.parse(response);

          if (json && json.html) {
            messageContainer.innerHTML = json.html;
            messageContainer.querySelector('.js-pstats-alert').classList.remove('hidden');
            initStatsEvents(getJson);
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
    getJson();
  });
})(Joomla, document);