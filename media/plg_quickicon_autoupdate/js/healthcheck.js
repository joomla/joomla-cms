/**
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (Joomla && Joomla.getOptions('js-auto-update')) {
  const update = (type, text) => {
    const link = document.getElementById('plg_quickicon_autoupdate');
    if (link) {
      link.classList.add(type);
    }
    link.querySelectorAll('span.j-links-link').forEach(span => {
      span.innerHTML = Joomla.sanitizeHtml(text);
    });
  };
  const checkHealthStatus = () => {
    const options = Joomla.getOptions('js-auto-update');

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
      const healthStatus = JSON.parse(response);
      if (healthStatus) {
        // Not active
        if (!healthStatus.active) {
          update('warning', Joomla.Text._('PLG_QUICKICON_AUTOUPDATE_DISABLED'));
        } else if (healthStatus.healthy === -1) {
          update('danger', Joomla.Text._('PLG_QUICKICON_AUTOUPDATE_OUTDATED'));
        } else if (healthStatus.healthy === 0) {
          update('warning', Joomla.Text._('PLG_QUICKICON_AUTOUPDATE_UNAVAILABLE'));
        } else {
          update('success', Joomla.Text._('PLG_QUICKICON_AUTOUPDATE_OK'));
        }
      } else {
        // An error occurred
        update('danger', Joomla.Text._('PLG_QUICKICON_AUTOUPDATE_ERROR'));
      }
    }).catch(() => {
      // An error occurred
      update('danger', Joomla.Text._('PLG_QUICKICON_AUTOUPDATE_ERROR'));
    });
  };

  // Give some times to the layout and other scripts to settle their stuff
  window.addEventListener('load', () => setTimeout(checkHealthStatus, 300));
}
