/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Option related functions
 *
 * @since  4.0.0
 */
((window, Joomla) => {
  'use strict';

  /**
   * Joomla options storage
   *
   * @type {{}}
   *
   * @since 3.7.0
   */
  Joomla.optionsStorage = Joomla.optionsStorage || null;

  /**
   * Get script(s) options
   *
   * @param  {String}  key  Name in Storage
   * @param  {mixed}   def  Default value if nothing found
   *
   * @return {mixed}
   *
   * @since 3.7.0
   */
  Joomla.getOptions = (key, def) => {
    // Load options if they not exists
    if (!Joomla.optionsStorage) {
      Joomla.loadOptions();
    }

    return Joomla.optionsStorage[key] !== undefined ? Joomla.optionsStorage[key] : def;
  };

  /**
   * Load new options from given options object or from Element
   *
   * @param  {Object|undefined}  options  The options object to load.
   * Eg {"com_foobar" : {"option1": 1, "option2": 2}}
   *
   * @since 3.7.0
   */
  Joomla.loadOptions = (options) => {
    // Load form the script container
    if (!options) {
      const elements = [].slice.call(document.querySelectorAll('.joomla-script-options.new'));
      let counter = 0;

      elements.forEach((element) => {
        const str = element.text || element.textContent;
        const option = JSON.parse(str);

        if (option) {
          Joomla.loadOptions(option);
          counter += 1;
        }

        element.className = element.className.replace(' new', ' loaded');
      });

      if (counter) {
        return;
      }
    }

    // Initial loading
    if (!Joomla.optionsStorage) {
      Joomla.optionsStorage = options || {};
    } else if (options) {
      // Merge with existing
      [].slice.call(Object.keys(options)).forEach((key) => {
        /**
         * If both existing and new options are objects, merge them with Joomla.extend().
         * But test for new option being null, as null is an object, but we want to allow
         * clearing of options with ...
         *
         * Joomla.loadOptions({'joomla.jtext': null});
         */
        if (options[key] !== null && typeof Joomla.optionsStorage[key] === 'object' && typeof options[key] === 'object') {
          Joomla.optionsStorage[key] = Joomla.extend(Joomla.optionsStorage[key], options[key]);
        } else {
          Joomla.optionsStorage[key] = options[key];
        }
      });
    }
  };
})(window, Joomla);
