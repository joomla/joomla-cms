/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable no-alert
(function (Joomla, document) {
  'use strict';

  if (!Joomla) {
    throw new Error('core.js was not properly initialised');
  }

  Joomla.finderIndexer = function () {
    var getRequest;
    var totalItems = null;
    var offset = null;
    var progress = null;
    var optimized = false;
    var path = 'index.php?option=com_finder&tmpl=component&format=json';
    var token = "&".concat(document.getElementById('finder-indexer-token').getAttribute('name'), "=1");

    var removeElement = function removeElement(id) {
      var element = document.getElementById(id);

      if (element) {
        return element.parentNode.removeChild(element);
      }

      return null;
    };

    var updateProgress = function updateProgress(header, message) {
      progress = offset / totalItems * 100;
      var progressBar = document.getElementById('progress-bar');
      var progressHeader = document.getElementById('finder-progress-header');
      var progressMessage = document.getElementById('finder-progress-message');

      if (progressHeader) {
        progressHeader.innerText = header;
      }

      if (progressMessage) {
        progressMessage.innerHTML = message;
      }

      if (progressBar) {
        if (progress < 100) {
          progressBar.style.width = "".concat(progress, "%");
          progressBar.setAttribute('aria-valuenow', progress);
        } else {
          progressBar.classList.remove('bar-success');
          progressBar.classList.add('bar-warning');
          progressBar.setAttribute('aria-valuemin', 100);
          progressBar.setAttribute('aria-valuemax', 200);
          progressBar.style.width = "".concat(progress, "%");
          progressBar.setAttribute('aria-valuenow', progress);
        } // Auto close the window


        if (message === Joomla.Text._('COM_FINDER_INDEXER_MESSAGE_COMPLETE')) {
          removeElement('progress');
          window.parent.Joomla.Modal.getCurrent().close();
        }
      }
    };

    var handleResponse = function handleResponse(json, resp) {
      var progressHeader = document.getElementById('finder-progress-header');
      var progressMessage = document.getElementById('finder-progress-message');

      try {
        if (json === null) {
          throw new Error(resp);
        }

        if (json.error) {
          throw new Error(json);
        }

        if (json.start) {
          // eslint-disable-next-line prefer-destructuring
          totalItems = json.totalItems;

          if (document.getElementById('finder-debug-data')) {
            var debuglist = document.getElementById('finder-debug-data');
            Object.entries(json.pluginState).forEach(function (context) {
              var item = "<dt class=\"col-sm-3\">".concat(context[0], "</dt>");
              item += "<dd id=\"finder-".concat(context[0].replace(/\s+/g, '-').toLowerCase(), "\" class=\"col-sm-9\"></dd>");
              debuglist.insertAdjacentHTML('beforeend', item);
            });
          }
        }

        offset += json.batchOffset;
        updateProgress(json.header, json.message);

        if (document.getElementById('finder-debug-data')) {
          Object.entries(json.pluginState).forEach(function (context) {
            document.getElementById("finder-".concat(context[0].replace(/\s+/g, '-').toLowerCase())).innerHTML = "".concat(json.pluginState[context[0]].offset, " of ").concat(json.pluginState[context[0]].total);
          });
        }

        if (offset < totalItems) {
          getRequest('indexer.batch');
        } else if (!optimized) {
          optimized = true;
          getRequest('indexer.optimize');
        }
      } catch (error) {
        removeElement('progress');

        try {
          if (json.error) {
            if (progressHeader) {
              progressHeader.innerText = json.header;
              progressHeader.classList.add('finder-error');
            }

            if (progressMessage) {
              progressMessage.innerHTML = json.message;
              progressMessage.classList.add('finder-error');
            }
          }
        } catch (ignore) {
          if (error === '') {
            // eslint-disable-next-line no-ex-assign
            error = Joomla.JText._('COM_FINDER_NO_ERROR_RETURNED');
          }

          if (progressHeader) {
            progressHeader.innerText = Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED');
            progressHeader.classList.add('finder-error');
          }

          if (progressMessage) {
            progressMessage.innerHTML = error;
            progressMessage.classList.add('finder-error');
          }
        }
      }

      return true;
    };

    var handleFailure = function handleFailure(xhr) {
      var progressHeader = document.getElementById('finder-progress-header');
      var progressMessage = document.getElementById('finder-progress-message');
      var data = _typeof(xhr) === 'object' && xhr.responseText ? xhr.responseText : null;
      data = data ? JSON.parse(data) : null;
      removeElement('progress');

      if (data) {
        data = data.responseText !== null ? data.evaluate(data.responseText, true) : data;
      }

      var header = data ? data.header : Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED');
      var message = data ? data.message : "".concat(Joomla.JText._('COM_FINDER_MESSAGE_RETURNED'), "<br>").concat(data);

      if (progressHeader) {
        progressHeader.innerText = header;
        progressHeader.classList.add('finder-error');
      }

      if (progressMessage) {
        progressMessage.innerHTML = message;
        progressMessage.classList.add('finder-error');
      }
    };

    getRequest = function getRequest(task) {
      Joomla.request({
        url: "".concat(path, "&task=").concat(task).concat(token),
        method: 'GET',
        data: '',
        perform: true,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        onSuccess: function onSuccess(response) {
          handleResponse(JSON.parse(response));
        },
        onError: function onError(xhr) {
          handleFailure(xhr);
        }
      });
    };

    var initialize = function initialize() {
      offset = 0;
      progress = 0;
      getRequest('indexer.start');
    };

    initialize();
  };
})(Joomla, document); // @todo use directly the Joomla.finderIndexer() instead of the Indexer()!!!


document.addEventListener('DOMContentLoaded', function () {
  window.Indexer = Joomla.finderIndexer();
});