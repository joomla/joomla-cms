/**
 * @package     Joomla.Plugin
 * @subpackage  System.jadp
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JADP preference state and persistence.
 *
 * Vanilla ES6 only. This file deliberately has no external dependencies.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'joomla.jadp.preferences';

  const { DEFAULTS, isValidPreferences, isValidStoredPreferences } = window.JADPPreferences;

  const readPreferences = () => {
    try {
      const stored = JSON.parse(window.localStorage.getItem(STORAGE_KEY));

      if (isValidStoredPreferences(stored)) {
        return { ...DEFAULTS, ...stored.preferences };
      }
    } catch (error) {
      // localStorage can be disabled, unavailable or contain invalid JSON.
    }

    return { ...DEFAULTS };
  };

  let preferences = readPreferences();
  const listeners = new Set();

  const apply = () => {
    const root = document.documentElement;

    root.dataset.jadpTextSize = preferences.textSize;

    const attributes = {
      jadpLineSpacing: preferences.lineSpacing,
      jadpLetterSpacing: preferences.letterSpacing,
      jadpWordSpacing: preferences.wordSpacing,
    };

    Object.entries(attributes).forEach(([name, value]) => {
      if (value === 'normal') {
        delete root.dataset[name];
      } else {
        root.dataset[name] = value;
      }
    });

    [
      ['jadpHighContrast', preferences.highContrast],
      ['jadpGrayscale', preferences.grayscale],
      ['jadpUnderlineLinks', preferences.underlineLinks],
    ].forEach(([name, enabled]) => {
      if (enabled) {
        root.dataset[name] = 'true';
      } else {
        delete root.dataset[name];
      }
    });
  };

  const persist = () => {
    try {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify({
        version: 1,
        preferences,
      }));
    } catch (error) {
      // Preferences still work in memory for this page view.
    }
  };

  const notify = () => {
    listeners.forEach((listener) => listener({ ...preferences }));
  };

  const set = (name, value) => {
    if (!Object.prototype.hasOwnProperty.call(DEFAULTS, name)) {
      return;
    }

    const candidate = { ...preferences, [name]: value };

    if (!isValidPreferences(candidate)) {
      return;
    }

    preferences = candidate;
    apply();
    persist();
    notify();
  };

  const reset = () => {
    preferences = { ...DEFAULTS };
    apply();

    try {
      window.localStorage.removeItem(STORAGE_KEY);
    } catch (error) {
      // Reset still applies to the current page.
    }

    notify();
  };

  const subscribe = (listener) => {
    listeners.add(listener);
    listener({ ...preferences });

    return () => listeners.delete(listener);
  };

  window.addEventListener('storage', (event) => {
    if (event.key !== STORAGE_KEY && event.key !== null) {
      return;
    }

    preferences = readPreferences();
    apply();
    notify();
  });

  apply();

  window.JADP = Object.freeze({
    getPreferences: () => ({ ...preferences }),
    set,
    reset,
    subscribe,
  });
}());
