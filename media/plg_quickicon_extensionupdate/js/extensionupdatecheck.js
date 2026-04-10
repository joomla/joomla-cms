/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {

  // Ajax call to get the update status of the installed extensions
  const fetchUpdate = () => {
    if (Joomla.getOptions('js-extensions-update')) {
      const options = Joomla.getOptions('js-extensions-update');
      const update = (type, text) => {
        const link = document.getElementById('plg_quickicon_extensionupdate');
        if (link) {
          link.classList.add(type);
        }
        link.querySelectorAll('span.j-links-link').forEach(span => {
          span.innerHTML = Joomla.sanitizeHtml(text);
        });
      };

      /**
       * DO NOT use fetch() for QuickIcon requests. They must be queued.
       *
       * @see https://github.com/joomla/joomla-cms/issues/38001
       */
      Joomla.enqueueRequest({
        url: options.ajaxUrl,
        method: 'GET',
        promise: true
      }).then(xhr => {
        const response = xhr.responseText;
        const updateInfoList = JSON.parse(response);
        if (Array.isArray(updateInfoList)) {
          if (updateInfoList.length === 0) {
            // No updates
            update('success', Joomla.Text._('PLG_QUICKICON_EXTENSIONUPDATE_UPTODATE'));
          } else {
            update('danger', Joomla.Text._('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND').replace('%s', `<span class="badge text-dark bg-light">${updateInfoList.length}</span>`));
          }
        } else {
          // An error occurred
          update('danger', Joomla.Text._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR'));
        }
      }).catch(() => {
        // An error occurred
        update('danger', Joomla.Text._('PLG_QUICKICON_EXTENSIONUPDATE_ERROR'));
      });
    }
  };

  // Give some times to the layout and other scripts to settle their stuff
  window.addEventListener('load', () => setTimeout(fetchUpdate, 330));
})();
