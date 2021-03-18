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
    * View element calls jSelectArticle when an article is clicked
    * jSelectArticle creates the link tag, sends it to the editor,
    * and closes the select frame.
    * */

  window.jSelectArticle = function (id, title, catid, object, link, lang) {
    var hreflang = '';

    if (!Joomla.getOptions('xtd-articles')) {
      // Something went wrong!
      // @TODO Close the modal
      return false;
    }

    var _Joomla$getOptions = Joomla.getOptions('xtd-articles'),
        editor = _Joomla$getOptions.editor;

    if (lang !== '') {
      hreflang = "hreflang=\"".concat(lang, "\"");
    }

    var tag = "<a ".concat(hreflang, " href=\"").concat(link, "\">").concat(title, "</a>");
    window.parent.Joomla.editors.instances[editor].replaceSelection(tag);

    if (window.parent.Joomla.Modal) {
      window.parent.Joomla.Modal.getCurrent().close();
    }

    return true;
  };

  document.addEventListener('DOMContentLoaded', function () {
    // Get the elements
    var elements = document.querySelectorAll('.select-link');

    for (var i = 0, l = elements.length; l > i; i += 1) {
      // Listen for click event
      elements[i].addEventListener('click', function (event) {
        event.preventDefault();
        var target = event.target;
        var functionName = target.getAttribute('data-function');

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
    }
  });
})();