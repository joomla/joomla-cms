/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Calls the function named in the "function" request variable on the parent window when a
// news feed is selected, for a parent that does not listen for postMessage().
// @todo: Remove in 7.0 together with the "function" request variable.
document.addEventListener('click', (event) => {
  const button = event.target.closest('.select-link');
  const { function: functionName } = Joomla.getOptions('newsfeeds-modal', {});

  if (!button || window.parent.JoomlaExpectingPostMessage || typeof window.parent[functionName] !== 'function') {
    return;
  }

  const {
    id, title, catId, uri, language,
  } = button.dataset;

  window.parent[functionName](id, title, catId, null, uri, language, null);
});
