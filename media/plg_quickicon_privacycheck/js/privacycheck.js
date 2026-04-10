/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(document => {

  const checkPrivacy = () => {
    const variables = Joomla.getOptions('js-privacy-check');
    const ajaxUrl = variables.plg_quickicon_privacycheck_ajax_url;
    const url = variables.plg_quickicon_privacycheck_url;
    const text = variables.plg_quickicon_privacycheck_text;
    const quickicon = document.getElementById('plg_quickicon_privacycheck');
    const link = quickicon.querySelector('span.j-links-link');

    /**
     * DO NOT use fetch() for QuickIcon requests. They must be queued.
     *
     * @see https://github.com/joomla/joomla-cms/issues/38001
     */
    Joomla.enqueueRequest({
      url: ajaxUrl,
      method: 'GET',
      promise: true
    }).then(xhr => {
      const response = xhr.responseText;
      const request = JSON.parse(response);
      if (request.data.number_urgent_requests) {
        // Quickicon on dashboard shows message
        const countBadge = document.createElement('span');
        countBadge.classList.add('badge', 'text-dark', 'bg-light');
        countBadge.textContent = request.data.number_urgent_requests;
        link.textContent = `${text.REQUESTFOUND} `;
        link.appendChild(countBadge);

        // Quickicon becomes red
        quickicon.classList.add('danger');

        // Span in alert
        const countSpan = document.createElement('span');
        countSpan.classList.add('label', 'label-important');
        countSpan.textContent = `${text.REQUESTFOUND_MESSAGE.replace('%s', request.data.number_urgent_requests)} `;

        // Button in alert to 'view requests'
        const requestButton = document.createElement('button');
        requestButton.classList.add('btn', 'btn-primary', 'btn-sm');
        requestButton.setAttribute('onclick', `document.location='${url}'`);
        requestButton.textContent = text.REQUESTFOUND_BUTTON;
        const div = document.createElement('div');
        div.classList.add('alert', 'alert-error', 'alert-joomlaupdate');
        div.appendChild(countSpan);
        div.appendChild(requestButton);

        // Add elements to container for alert messages
        const container = document.querySelector('#system-message-container');
        container.insertBefore(div, container.firstChild);
      } else {
        quickicon.classList.add('success');
        link.textContent = text.NOREQUEST;
      }
    }).catch(() => {
      quickicon.classList.add('danger');
      link.textContent = text.ERROR;
    });
  };

  // Give some times to the layout and other scripts to settle their stuff
  window.addEventListener('load', () => setTimeout(checkPrivacy, 360));
})(document);
