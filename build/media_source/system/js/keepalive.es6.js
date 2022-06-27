/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
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
if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised');
}

const keepAliveOptions = Joomla.getOptions('system.keepalive');
const keepAliveInterval = keepAliveOptions && keepAliveOptions.interval ? parseInt(keepAliveOptions.interval, 10) : 45 * 1000;
let keepAliveUri = keepAliveOptions && keepAliveOptions.uri ? keepAliveOptions.uri.replace(/&amp;/g, '&') : '';

// Fallback in case no keepalive uri was found.
if (keepAliveUri === '') {
  const systemPaths = Joomla.getOptions('system.paths');

  keepAliveUri = `${(systemPaths ? `${systemPaths.root}/index.php` : window.location.pathname)}?option=com_ajax&format=json`;
}

setInterval(() => navigator.sendBeacon(keepAliveUri), keepAliveInterval);
