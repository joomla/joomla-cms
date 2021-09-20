/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Toggles the display of inline help DIVs
 *
 * @param {String} toggleClass The class name of the DIVs to toggle display for
 */
Joomla.toggleInlineHelp = (toggleClass) => {
  document.querySelectorAll(`div.${toggleClass}`)
    .forEach((elDiv) => {
      elDiv.classList.toggle('d-none');
    });
};

document.querySelectorAll('.button-inlinehelp')
  .forEach((elToggler) => {
    const toggleClass = elToggler.dataset.class ?? 'hide-aware-inline-help';
    Joomla.toggleInlineHelp(toggleClass);
    elToggler.addEventListener('click', (event) => {
      event.preventDefault();
      Joomla.toggleInlineHelp(toggleClass);
    });
  });
