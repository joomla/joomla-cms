/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Keepalive javascript behavior
 *
 * Used for keeping the session alive
 *
 * @package  Joomla.JavaScript
 * @since    3.7.0
 */
((document, Joomla) => {
  'use strict';

  const keepAlive = keepAliveUri => {
    Joomla.request({
      url: keepAliveUri
    });
  };

  const onBoot = () => {
    if (!Joomla || typeof Joomla.getOptions !== 'function' && typeof Joomla.request !== 'function') {
      throw new Error('core.js was not properly initialised');
    }

    const keepAliveOptions = Joomla.getOptions('system.keepalive');
    const keepAliveInterval = keepAliveOptions && keepAliveOptions.interval ? parseInt(keepAliveOptions.interval, 10) : 45 * 1000;
    let keepAliveUri = keepAliveOptions && keepAliveOptions.uri ? keepAliveOptions.uri.replace(/&amp;/g, '&') : ''; // Fallback in case no keepalive uri was found.

    if (keepAliveUri === '') {
      const systemPaths = Joomla.getOptions('system.paths');
      keepAliveUri = `${systemPaths ? `${systemPaths.root}/index.php` : window.location.pathname}?option=com_ajax&format=json`;
    }

    setInterval(keepAlive, keepAliveInterval, keepAliveUri); // Cleanup

    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot, true);
})(document, Joomla);