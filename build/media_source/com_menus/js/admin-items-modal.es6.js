/**
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla, document) => {
  'use strict';

  /**
   * Javascript to insert the link
   * View element calls jSelectContact when a contact is clicked
   * jSelectContact creates the link tag, sends it to the editor,
   * and closes the select frame.
   */
  window.jSelectMenuItem = (id, title, uri, object, link, lang) => {
    // eslint-disable-next-line no-console
    console.warn('Method jSelectMenuItem() is deprecated. Use postMessage() instead.');
    let thislang = '';

    if (!Joomla.getOptions('xtd-menus')) {
      // Something went wrong!
      return;
    }

    // eslint-disable-next-line prefer-destructuring
    const editor = Joomla.getOptions('xtd-menus').editor;

    if (lang !== '') {
      thislang = '&lang=';
    }

    const tag = `<a href="${uri + thislang + lang}">${title}</a>`;

    // Insert the link in the editor
    if (window.parent.Joomla.editors.instances[editor].getSelection()) {
      window.parent.Joomla.editors.instances[editor].replaceSelection(`<a href="${uri + thislang + lang}">${window.parent.Joomla.editors.instances[editor].getSelection()}</a>`);
    } else {
      window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
    }

    // Close the modal
    if (window.parent.Joomla.Modal && window.parent.Joomla.Modal.getCurrent()) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  };

  // Get the elements
  document.querySelectorAll('.select-link').forEach((element) => {
    // Listen for click event
    element.addEventListener('click', (event) => {
      event.preventDefault();
      const functionName = event.target.getAttribute('data-function');

      if (functionName === 'jSelectMenuItem' && window[functionName]) {
        // Used in xtd_contacts
        window[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), event.target.getAttribute('data-uri'), null, null, event.target.getAttribute('data-language'));
      } else if (window.parent[functionName]) {
        // Used in com_menus
        window.parent[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);
      }

      // Close the modal
      if (window.parent.Joomla.Modal && window.parent.Joomla.Modal.getCurrent()) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });
})(Joomla, document);
