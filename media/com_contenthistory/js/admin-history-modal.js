/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document, Joomla) {
  'use strict';

  if (!Joomla || typeof Joomla.JText._ !== 'function') {
    throw new Error('core.js was not properly initialised');
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('toolbar-load').addEventListener('click', function () {
      var ids = document.querySelectorAll('input[id*="cb"]:checked');

      if (ids.length === 1) {
        // Add version item id to URL
        var url = "".concat(document.getElementById('toolbar-load').getAttribute('data-url'), "&version_id=").concat(ids[0].value);

        if (window.parent && url) {
          window.parent.location = url;
        }
      } else {
        // @todo use the CE Modal here
        alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE'));
      }

      return false;
    });
    document.getElementById('toolbar-preview').addEventListener('click', function () {
      var windowSizeArray = ['width=800, height=600, resizable=yes, scrollbars=yes'];
      var ids = document.querySelectorAll('input[id*="cb"]:checked');

      if (ids.length === 1) {
        // Add version item id to URL
        var url = "".concat(document.getElementById('toolbar-preview').getAttribute('data-url'), "&version_id=").concat(ids[0].value);

        if (window.parent && url) {
          window.open(url, '', windowSizeArray.toString());
        }
      } else {
        // @todo use the CE Modal here
        alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE'));
      }

      return false;
    });
    document.getElementById('toolbar-compare').addEventListener('click', function () {
      var windowSizeArray = ['width=1000, height=600, resizable=yes, scrollbars=yes'];
      var ids = document.querySelectorAll('input[id*="cb"]:checked');

      if (ids.length === 0) {
        // @todo use the CE Modal here
        alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
      } else if (ids.length === 2) {
        // Add version item ids to URL
        var url = "".concat(document.getElementById('toolbar-compare').getAttribute('data-url'), "&id1=").concat(ids[0].value, "&id2=").concat(ids[1].value);

        if (window.parent && url) {
          window.open(url, '', windowSizeArray.toString());
        }
      } else {
        // @todo use the CE Modal here
        alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_TWO'));
      }

      return false;
    });
  });
})(document, Joomla);