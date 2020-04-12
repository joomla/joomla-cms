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
   * Method to replace all request tokens on the page with a new one.
   *
   * @param {String}  newToken  The token
   *
   * Used in Joomla Installation
   */
  Joomla.replaceTokens = (newToken) => {
    if (!/^[0-9A-F]{32}$/i.test(newToken)) {
      return;
    }

    const elements = [].slice.call(document.getElementsByTagName('input'));

    elements.forEach((element) => {
      if (element.type === 'hidden' && element.value === '1' && element.name.length === 32) {
        element.name = newToken;
      }
    });
  };
})(window, Joomla);
