/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  /**
    * Javascript to insert the link
    * View element calls jSelectTour when a tour is clicked
    * jSelectTour creates the link tag, sends it to the editor,
    * and closes the select frame.
    * */
  window.jSelectTour = (id, title, alias) => {
    if (!Joomla.getOptions('xtd-guidedtour')) {
      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    }

    const { editor } = Joomla.getOptions('xtd-guidedtour');
    const tag = `<button type="button" class="btn btn-secondary button-start-guidedtour" data-id="${id}"  data-gt-alias="${alias}">`
      + '<span className="icon-map-signs" aria-hidden="true"></span>'
      + `${title}</button>`;
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

      if (functionName === 'jSelectTour') {
        // Used in xtd_contacts
        window[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-alias'));
      } else {
        // Used in com_menus
        window.parent[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-alias'));
      }

      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });
})();
