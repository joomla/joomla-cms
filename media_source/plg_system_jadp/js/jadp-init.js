/**
 * @package     Joomla.Plugin
 * @subpackage  System.jadp
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JADP early preference initialisation.
 *
 * This file is intentionally small and loaded synchronously in the document head
 * to apply saved preferences before the page is displayed.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'joomla.jadp.preferences';
  const { DEFAULTS, isValidStoredPreferences } = window.JADPPreferences;

  let saved = null;

  try {
    saved = JSON.parse(window.localStorage.getItem(STORAGE_KEY));
  } catch (error) {
    // Storage may be unavailable or contain invalid JSON. Defaults are safe.
  }

  const preferences = isValidStoredPreferences(saved) ? { ...DEFAULTS, ...saved.preferences } : DEFAULTS;
  const root = document.documentElement;

  root.dataset.jadpTextSize = preferences.textSize;

  if (preferences.lineSpacing !== 'normal') {
    root.dataset.jadpLineSpacing = preferences.lineSpacing;
  }

  if (preferences.letterSpacing !== 'normal') {
    root.dataset.jadpLetterSpacing = preferences.letterSpacing;
  }

  if (preferences.wordSpacing !== 'normal') {
    root.dataset.jadpWordSpacing = preferences.wordSpacing;
  }

  if (preferences.highContrast) {
    root.dataset.jadpHighContrast = 'true';
  }

  if (preferences.grayscale) {
    root.dataset.jadpGrayscale = 'true';
  }

  if (preferences.underlineLinks) {
    root.dataset.jadpUnderlineLinks = 'true';
  }
}());
