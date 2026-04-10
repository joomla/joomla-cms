/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, Joomla) => {

  if (!Joomla || typeof Joomla.Text._ !== 'function') {
    throw new Error('core.js was not properly initialised');
  }
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('toolbar-load').addEventListener('click', () => {
      const toolbarLoad = document.getElementById('toolbar-load');
      if (!toolbarLoad.disabled) {
        const ids = document.querySelectorAll('input[id*="cb"]:checked');
        if (ids.length === 1) {
          // Add version item id to URL
          const url = `${toolbarLoad.childNodes[1].getAttribute('data-url')}&version_id=${ids[0].value}`;
          if (window.parent && url) {
            window.parent.location = url;
          }
        } else {
          Joomla.renderMessages({
            error: [Joomla.Text._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE_VERSION')]
          });
        }
      }
      return false;
    });
    document.getElementById('toolbar-preview').addEventListener('click', () => {
      const toolbarPreview = document.getElementById('toolbar-preview');
      if (!toolbarPreview.disabled) {
        const windowSizeArray = ['width=800, height=600, resizable=yes, scrollbars=yes'];
        const ids = document.querySelectorAll('input[id*="cb"]:checked');
        if (ids.length === 1) {
          // Add version item id to URL
          const url = `${toolbarPreview.childNodes[1].getAttribute('data-url')}&version_id=${ids[0].value}`;
          if (window.parent && url) {
            window.open(url, '', windowSizeArray.toString());
          }
        } else {
          Joomla.renderMessages({
            error: [Joomla.Text._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE_VERSION')]
          });
        }
      }
      return false;
    });
    document.getElementById('toolbar-compare').addEventListener('click', () => {
      const toolbarCompare = document.getElementById('toolbar-compare');
      if (!toolbarCompare.disabled) {
        const windowSizeArray = ['width=1000, height=600, resizable=yes, scrollbars=yes'];
        const ids = document.querySelectorAll('input[id*="cb"]:checked');
        if (ids.length === 0) {
          Joomla.renderMessages({
            error: [Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]
          });
        } else if (ids.length === 2) {
          // Add version item ids to URL
          const url = `${toolbarCompare.childNodes[1].getAttribute('data-url')}&id1=${ids[0].value}&id2=${ids[1].value}`;
          if (window.parent && url) {
            window.open(url, '', windowSizeArray.toString());
          }
        } else {
          Joomla.renderMessages({
            error: [Joomla.Text._('COM_CONTENTHISTORY_BUTTON_SELECT_TWO_VERSIONS')]
          });
        }
      }
      return false;
    });
  });
})(document, Joomla);
