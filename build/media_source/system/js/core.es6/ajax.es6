/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Ajax related functions
 *
 * @since  4.0.0
 */
((window, Joomla) => {
  'use strict';

  /**
   * Method to perform AJAX request
   *
   * @param {Object} options   Request options:
   * {
   *    url:     'index.php', Request URL
   *    method:  'GET',       Request method GET (default), POST
   *    data:    null,        Data to be sent, see
   *                https://developer.mozilla.org/docs/Web/API/XMLHttpRequest/send
   *    perform: true,        Perform the request immediately
   *              or return XMLHttpRequest instance and perform it later
   *    headers: null,        Object of custom headers, eg {'X-Foo': 'Bar', 'X-Bar': 'Foo'}
   *
   *    onBefore:  (xhr) => {}            // Callback on before the request
   *    onSuccess: (response, xhr) => {}, // Callback on the request success
   *    onError:   (xhr) => {},           // Callback on the request error
   * }
   *
   * @return XMLHttpRequest|Boolean
   *
   * @example
   *
   *   Joomla.request({
   *    url: 'index.php?option=com_example&view=example',
   *    onSuccess: (response, xhr) => {
   *     JSON.parse(response);
   *    }
   *   })
   *
   * @see    https://developer.mozilla.org/docs/Web/API/XMLHttpRequest
   */
  Joomla.request = (options) => {
    let xhr;
    // Prepare the options
    const newOptions = Joomla.extend({
      url: '',
      method: 'GET',
      data: null,
      perform: true,
    }, options);

    // Set up XMLHttpRequest instance
    try {
      xhr = new XMLHttpRequest();

      xhr.open(newOptions.method, newOptions.url, true);

      // Set the headers
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('X-Ajax-Engine', 'Joomla!');

      if (newOptions.method !== 'GET') {
        const token = Joomla.getOptions('csrf.token', '');

        if (token) {
          xhr.setRequestHeader('X-CSRF-Token', token);
        }

        if (!newOptions.headers || !newOptions.headers['Content-Type']) {
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
      }

      // Custom headers
      if (newOptions.headers) {
        [].slice.call(Object.keys(newOptions.headers)).forEach((key) => {
          // Allow request without Content-Type
          // eslint-disable-next-line no-empty
          if (key === 'Content-Type' && newOptions.headers['Content-Type'] === 'false') {

          } else {
            xhr.setRequestHeader(key, newOptions.headers[key]);
          }
        });
      }

      xhr.onreadystatechange = () => {
        // Request not finished
        if (xhr.readyState !== 4) {
          return;
        }

        // Request finished and response is ready
        if (xhr.status === 200) {
          if (newOptions.onSuccess) {
            newOptions.onSuccess.call(window, xhr.responseText, xhr);
          }
        } else if (newOptions.onError) {
          newOptions.onError.call(window, xhr);
        }
      };

      // Do request
      if (newOptions.perform) {
        if (newOptions.onBefore && newOptions.onBefore.call(window, xhr) === false) {
          // Request interrupted
          return xhr;
        }

        xhr.send(newOptions.data);
      }
    } catch (error) {
      // eslint-disable-next-line no-unused-expressions,no-console
      window.console ? console.log(error) : null;
      return false;
    }

    return xhr;
  };
})(window, Joomla);
