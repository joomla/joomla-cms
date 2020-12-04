/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Add a keyboard event listener to the Select a Module Type search element.
 *
 * IMPORTANT! This script is meant to be loaded deferred. This means that a. it's non-blocking
 * (the browser can load it whenever) and b. it doesn't need an on DOMContentLoaded event handler
 * because the browser is guaranteed to execute it only after the DOM content has loaded, the
 * whole point of it being deferred.
 *
 * The search box has a keyboard handler that fires every time you press a keyboard button or send
 * a keypress with a touch / virtual keyboard. We then iterate all module type cards and check if
 * the plain text (HTML stripped out) representation of the module title or description partially
 * matches the text you entered in the search box. If it doesn't we add a Bootstrap class to hide
 * the module.
 *
 * This way we limit the displayed modules only to those searched.
 *
 * This feature follows progressive enhancement. The search box is hidden by default and only
 * displayed when this JavaScript here executes. Furthermore, session storage is only used if it
 * is available in the browser. That's a bit of a pain but makes sure things won't break in older
 * browsers.
 *
 * Furthermore and to facilitate the user experience we auto-focus the search element which has a
 * suitable title so that non-sighted users are not startled. This way we address both UX concerns
 * and accessibility.
 *
 * Finally, the search string is saved into session storage on the assumption that the user is
 * probably going to be creating multiple instances of the same module, one after another, as is
 * typical when building a new Joomla! site.
 */
((document) => {
  'use strict';

  // Make sure the element exists i.e. a template override has not removed it.
  const elSearch = document.getElementById('comModulesSelectSearch');
  const elSearchContainer = document.getElementById('comModulesSelectSearchContainer');

  if (!elSearch || !elSearchContainer) {
    return;
  }

  // Add the keyboard event listener which performs the live search in the cards
  elSearch.addEventListener('keyup', (event) => {
    /** @type {KeyboardEvent} event */
    const partialSearch = event.target.value;
    const elCards = document.querySelectorAll('.comModulesSelectCard');

    // Save the search string into session storage
    if (typeof (sessionStorage) !== 'undefined') {
      sessionStorage.setItem('Joomla.com_modules.new.search', partialSearch);
    }

    // Iterate all the module cards
    elCards.forEach((card) => {
      const cardHeaderList = card.querySelectorAll('.card-header');
      const cardBodyList = card.querySelectorAll('.card-body');
      const title = cardHeaderList.length ? cardHeaderList[0].textContent : '';
      const description = cardBodyList.length ? cardBodyList[0].textContent : '';

      // First remove the classes which hide the module cards
      card.classList.remove('d-none');

      // An empty search string means that we should show everything
      if (partialSearch === '') {
        return;
      }

      // If the module title and description don’t match add a class to hide it.
      if (!title.toLowerCase().includes(partialSearch.toLowerCase())
        && !description.toLowerCase().includes(partialSearch.toLowerCase())) {
        card.classList.add('d-none');
      }
    });
  });

  // For reasons of progressive enhancement the search box is hidden by default.
  elSearchContainer.classList.remove('d-none');

  // Focus the just show element
  elSearch.focus();

  try {
    if (typeof (sessionStorage) !== 'undefined') {
      // Load the search string from session storage
      elSearch.value = sessionStorage.getItem('Joomla.com_modules.new.search') || '';

      // Trigger the keyboard handler event manually to initiate the search
      elSearch.dispatchEvent(new KeyboardEvent('keyup'));
    }
  } catch (e) {
    // This is probably Internet Explorer which doesn't support the KeyboardEvent constructor :(
  }
})(document);
