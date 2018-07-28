/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Keepalive javascript behavior
 *
 * Used for keeping the session alive
 *
 * @package  Joomla
 * @since    3.7.0
 */
!(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const keepaliveOptions = Joomla.getOptions('system.keepalive');


    let keepaliveUri = keepaliveOptions && keepaliveOptions.uri ? keepaliveOptions.uri.replace(/&amp;/g, '&') : '';


    const keepaliveInterval = keepaliveOptions && keepaliveOptions.interval ? keepaliveOptions.interval : 45 * 1000;

    // Fallback in case no keepalive uri was found.
    if (keepaliveUri === '') {
      const systemPaths = Joomla.getOptions('system.paths');

      keepaliveUri = `${systemPaths ? `${systemPaths.root}/index.php` : window.location.pathname}?option=com_ajax&format=json`;
    }

    window.setInterval(() => {
      Joomla.request({
        url: keepaliveUri,
        onSuccess(response, xhr) {
          // Do nothing
        },
        onError(xhr) {
          // Do nothing
        },
      });
    }, keepaliveInterval);
  });
}(window, document, Joomla));
