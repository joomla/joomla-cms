/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('jform_searchstring').addEventListener('focus', (event) => {
    if (!Joomla.overrider.states.refreshed) {
      const expired = document.getElementById('overrider-spinner').getAttribute('data-search-string-expired');
      if (expired) {
        Joomla.overrider.refreshCache();
        Joomla.overrider.states.refreshed = true;
      }
    }
    event.currentTarget.classList.remove('invalid');
  }, false);

  document.getElementById('more-results-button').addEventListener('click', () => {
    Joomla.overrider.searchStrings(Joomla.overrider.states.more);
  }, false);
});

