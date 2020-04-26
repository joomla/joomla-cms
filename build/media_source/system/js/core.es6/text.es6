/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Text related functions
 *
 * @since  4.0.0
 */
((window, Joomla) => {
  'use strict';

  /**
   * Custom behavior for JavaScript I18N in Joomla! 1.6
   *
   * @type {{}}
   *
   * Allows you to call Joomla.Text._() to get a translated JavaScript string
   * pushed in with Text::script() in Joomla.
   */
  Joomla.Text = {
    strings: {},

    /**
     * Translates a string into the current language.
     *
     * @param {String} key   The string to translate
     * @param {String} def   Default string
     *
     * @returns {String}
     */
    _: (key, def) => {
      let newKey = key;
      let newDef = def;
      // Check for new strings in the optionsStorage, and load them
      const newStrings = Joomla.getOptions('joomla.jtext');
      if (newStrings) {
        Joomla.Text.load(newStrings);

        // Clean up the optionsStorage from useless data
        Joomla.loadOptions({ 'joomla.jtext': null });
      }

      newDef = newDef === undefined ? '' : newDef;
      newKey = newKey.toUpperCase();

      return Joomla.Text.strings[newKey] !== undefined ? Joomla.Text.strings[newKey] : newDef;
    },

    /**
     * Load new strings in to Joomla.Text
     *
     * @param {Object} object  Object with new strings
     * @returns {Joomla.Text}
     */
    load: (object) => {
      [].slice.call(Object.keys(object)).forEach((key) => {
        Joomla.Text.strings[key.toUpperCase()] = object[key];
      });

      return Joomla.Text;
    },
  };

  /**
   * For B/C we still support Joomla.JText
   *
   * @type {{}}
   *
   * @deprecated 5.0
   */
  Joomla.JText = Joomla.Text;
})(window, Joomla);
