/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  /**
    * Javascript to insert the link
    * View element calls jSelectArticle when an article is clicked
    * jSelectArticle creates the link tag, sends it to the editor,
    * and closes the select frame.
    * */
  window.jSelectArticle = (id, title, catid, object, link, lang) => {
    if (!Joomla.getOptions('xtd-articles')) {
      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    }

    const { editor } = Joomla.getOptions('xtd-articles');
    const tag = `<a href="${link}"${lang !== '' ? ` hreflang="${lang}"` : ''}>${title}</a>`;
    window.parent.Joomla.editors.instances[editor].replaceSelection(tag);

    if (window.parent.Joomla.Modal) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  };

  document.querySelectorAll('.select-link').forEach((element) => {
    // Listen for click event
    element.addEventListener('click', (event) => {
      event.preventDefault();
      const { target } = event;
      const functionName = target.getAttribute('data-function');

      if (functionName === 'jSelectArticle') {
        // Used in xtd_contacts
        window[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-cat-id'), null, target.getAttribute('data-uri'), target.getAttribute('data-language'));
      } else {
        // Used in com_menus
        window.parent[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-cat-id'), null, target.getAttribute('data-uri'), target.getAttribute('data-language'));
      }

      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });
})();
