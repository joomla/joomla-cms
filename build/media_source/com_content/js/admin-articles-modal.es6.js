/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  // Use a JoomlaExpectingPostMessage flag to be able to distinct legacy methods
  if (window.parent.JoomlaExpectingPostMessage) {
    return;
  }

  /**
    * Javascript to insert the link
    * View element calls jSelectArticle when an article is clicked
    * jSelectArticle creates the link tag, sends it to the editor,
    * and closes the select frame.
    * */
  window.jSelectArticle = (id, title, catid, object, link, lang) => {
    // eslint-disable-next-line no-console
    console.warn('Method jSelectArticle() is deprecated. Use postMessage() instead.');

    if (!Joomla.getOptions('xtd-articles')) {
      return;
    }

    const { editor } = Joomla.getOptions('xtd-articles');
    const tag = `<a href="${link}"${lang !== '' ? ` hreflang="${lang}"` : ''}>${title}</a>`;
    window.parent.Joomla.editors.instances[editor].replaceSelection(tag);

    if (window.parent.Joomla.Modal && window.parent.Joomla.Modal.getCurrent()) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  };

  document.querySelectorAll('.select-link').forEach((element) => {
    // Listen for click event
    element.addEventListener('click', (event) => {
      event.preventDefault();
      const { target } = event;
      const functionName = target.getAttribute('data-function');

      if (functionName === 'jSelectArticle' && window[functionName]) {
        // Used in xtd_contacts
        window[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-cat-id'), null, target.getAttribute('data-uri'), target.getAttribute('data-language'));
      } else if (window.parent[functionName]) {
        // Used in com_menus
        window.parent[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-cat-id'), null, target.getAttribute('data-uri'), target.getAttribute('data-language'));
      }

      if (window.parent.Joomla.Modal && window.parent.Joomla.Modal.getCurrent()) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });
})();
