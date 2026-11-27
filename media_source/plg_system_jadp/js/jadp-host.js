/**
 * @package     Joomla.Plugin
 * @subpackage  System.jadp
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Create the JADP toolbar host from options supplied by Joomla.
 *
 * The host is created in JavaScript rather than injected into the rendered
 * HTML response by the system plugin.
 */
(function () {
  'use strict';

  if (!window.Joomla || typeof Joomla.getOptions !== 'function') {
    return;
  }

  const options = Joomla.getOptions('plg_system_jadp');

  if (!options || !options.css || !options.labels) {
    return;
  }

  const host = document.createElement('div');

  host.id = 'jadp-root';
  host.dataset.jadpCss = options.css;
  host.dataset.triggerPosition = options.triggerPosition || 'bottom-left';

  const triggerIcon = options.triggerIcon || {};
  host.dataset.triggerIconType = triggerIcon.type || 'default';
  host.dataset.triggerIconClass = triggerIcon.class || '';
  host.dataset.triggerIconCss = triggerIcon.css || '';
  host.dataset.triggerIconImage = triggerIcon.image || '';

  Object.entries(options.labels).forEach(([name, value]) => {
    host.dataset[name] = value;
  });

  document.documentElement.appendChild(host);
}());
