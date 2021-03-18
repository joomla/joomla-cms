/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';
  /**
    * Javascript to insert the link
    * View element calls jSelectContact when a contact is clicked
    * jSelectContact creates the link tag, sends it to the editor,
    * and closes the select frame.
    */

  window.jSelectContact = function (id, title, catid, object, link, lang) {
    var hreflang = '';

    if (!Joomla.getOptions('xtd-contacts')) {
      // Something went wrong
      window.parent.Joomla.Modal.getCurrent().close();
      return false;
    }

    var _Joomla$getOptions = Joomla.getOptions('xtd-contacts'),
        editor = _Joomla$getOptions.editor;

    if (lang !== '') {
      hreflang = "hreflang = \"".concat(lang, "\"");
    }

    var tag = "<a ".concat(hreflang, "  href=\"").concat(link, "\">").concat(title, "</a>");
    window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
    window.parent.Joomla.Modal.getCurrent().close();
    return true;
  };

  document.addEventListener('DOMContentLoaded', function () {
    // Get the elements
    var elements = document.querySelectorAll('.select-link');

    for (var i = 0, l = elements.length; l > i; i += 1) {
      // Listen for click event
      elements[i].addEventListener('click', function (event) {
        event.preventDefault();
        var functionName = event.target.getAttribute('data-function');

        if (functionName === 'jSelectContact') {
          // Used in xtd_contacts
          window[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);
        } else {
          // Used in com_menus
          window.parent[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);
        }

        if (window.parent.Joomla.Modal) {
          window.parent.Joomla.Modal.getCurrent().close();
        }
      });
    }
  });
})();