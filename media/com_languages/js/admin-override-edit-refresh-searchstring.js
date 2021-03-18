/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('jform_searchstring').addEventListener('focus', function (_ref) {
    var srcElement = _ref.srcElement;

    if (!Joomla.overrider.states.refreshed) {
      var expired = document.getElementById('overrider-spinner').getAttribute('data-search-string-expired');

      if (expired) {
        Joomla.overrider.refreshCache();
        Joomla.overrider.states.refreshed = true;
      }
    }

    srcElement.classList.remove('invalid');
  }, false);
  document.getElementById('more-results-button').addEventListener('click', function () {
    Joomla.overrider.searchStrings(Joomla.overrider.states.more);
  }, false);
});