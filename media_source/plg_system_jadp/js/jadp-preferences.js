/**
 * @package     Joomla.Plugin
 * @subpackage  System.jadp
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Shared JADP preference definitions and validation.
 *
 * This file is loaded synchronously before both early initialisation and
 * the preference engine so that they always use the same preference schema.
 */
(function () {
  'use strict';

  const DEFAULTS = Object.freeze({
    textSize: 'normal',
    lineSpacing: 'normal',
    letterSpacing: 'normal',
    wordSpacing: 'normal',
    highContrast: false,
    grayscale: false,
    underlineLinks: false,
  });

  const isValidPreferences = (preferences) => preferences
    && typeof preferences === 'object'
    && ['normal', 'large', 'extra-large'].includes(preferences.textSize)
    && ['normal', 'increased', 'maximum'].includes(preferences.lineSpacing)
    && ['normal', 'increased', 'maximum'].includes(preferences.letterSpacing)
    && ['normal', 'increased', 'maximum'].includes(preferences.wordSpacing)
    && typeof preferences.highContrast === 'boolean'
    && typeof preferences.grayscale === 'boolean'
    && typeof preferences.underlineLinks === 'boolean';

  const isValidStoredPreferences = (stored) => stored
    && stored.version === 1
    && isValidPreferences(stored.preferences);

  window.JADPPreferences = Object.freeze({
    DEFAULTS,
    isValidPreferences,
    isValidStoredPreferences,
  });
}());
