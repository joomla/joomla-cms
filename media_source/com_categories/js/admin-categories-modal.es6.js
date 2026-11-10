/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Calls the function named in the "function" request variable on the parent window when a
// category is selected, for a parent that does not listen for postMessage().
// @todo: Remove in 7.0 together with the "function" request variable.
document.addEventListener('click', (event) => {
  const link = event.target.closest('.select-link');
  const { function: functionName } = Joomla.getOptions('categories-modal', {});

  if (!link || window.parent.JoomlaExpectingPostMessage || typeof window.parent[functionName] !== 'function') {
    return;
  }

  event.preventDefault();

  const { id, title, uri, language } = link.dataset;

  window.parent[functionName](id, title, null, uri, language, null);
});
