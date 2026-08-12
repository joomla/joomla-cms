/**
 * @package     Joomla.Administrator
 * @subpackage  mod_healthcheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * CSP-safe onclick handler for healthcheck icons.
 *
 * Instead of inline onclick="..." attributes, icons use data-onclick="handlerName".
 * Handlers are registered via Joomla.registerHealthCheckAction(name, fn) and called
 * when the user clicks an element carrying that data-onclick value.
 *
 * Usage (in a plugin's JS):
 *   Joomla.registerHealthCheckAction('myHandler', function (event) { ... });
 *
 * Usage (in a plugin's PHP):
 *   'onclick' => 'myHandler'
 */
((Joomla, document) => {
  'use strict';

  const registry = {};

  Joomla.registerHealthCheckAction = (name, fn) => {
    registry[name] = fn;
  };

  document.addEventListener('click', (event) => {
    const el = event.target.closest('[data-onclick]');

    if (!el) {
      return;
    }

    const name = el.getAttribute('data-onclick');
    const fn = registry[name];

    if (typeof fn === 'function') {
      event.preventDefault();
      fn.call(el, event);
    }
  });
})(window.Joomla, document);