/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, Joomla) => {
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('toolbar-load').addEventListener('click', () => {
      const ids = document.querySelectorAll('input[id*="cb"]:checked');
      if (ids.length === 1) {
        // Add version item id to URL
        const url = `${document.getElementById('toolbar-load').getAttribute('data-url')}&version_id=${ids[0].value}`;
        if (window.parent && url) {
          window.parent.location = url;
        }
      } else {
        alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE'));
      }
      return false;
    });

    document.getElementById('toolbar-preview').addEventListener('click', () => {
      const windowSizeArray = ['width=800, height=600, resizable=yes, scrollbars=yes'];
      const ids = document.querySelectorAll('input[id*="cb"]:checked');
      if (ids.length === 1) {
        // Add version item id to URL
        const url = `${document.getElementById('toolbar-preview').getAttribute('data-url')}&version_id=${ids[0].value}`;
        if (window.parent && url) {
          window.open(url, '', windowSizeArray.toString());
        }
      } else {
        alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE'));
      }
      return false;
    });

    document.getElementById('toolbar-compare').addEventListener('click', () => {
      const windowSizeArray = ['width=1000, height=600, resizable=yes, scrollbars=yes'];
      const ids = document.querySelectorAll('input[id*="cb"]:checked');
      if (ids.length === 0) {
        alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
      } else if (ids.length === 2) {
        // Add version item ids to URL
        const url = `${document.getElementById('toolbar-compare').getAttribute('data-url')}&id1=${ids[0].value}&id2=${ids[1].value}`;
        if (window.parent && url) {
          window.open(url, '', windowSizeArray.toString());
        }
      } else {
        alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_TWO'));
      }
      return false;
    });
  });
})(document, Joomla);
