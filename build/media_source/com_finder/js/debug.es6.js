/**
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable no-alert
((Joomla, document) => {
  'use strict';

  if (!Joomla) {
    throw new Error('core.js was not properly initialised');
  }

  Joomla.finderIndexer = () => {
    const path = 'index.php?option=com_finder&task=indexer.debug&tmpl=component&format=json';
    const token = `&${document.getElementById('finder-indexer-token').getAttribute('name')}=1`;

    Joomla.debugIndexing = () => {
      const formEls = new URLSearchParams(Array.from(new FormData(document.getElementById('debug-form')))).toString();
      Joomla.request({
        url: `${path}${token}&${formEls}`,
        method: 'GET',
        data: '',
        perform: true,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        onSuccess: (response) => {
          const output = document.getElementById('indexer-output');
          try {
            const parsed = JSON.parse(response);
            output.innerHTML = Joomla.sanitizeHtml(parsed.rendered);
          } catch (e) {
            output.innerHTML = Joomla.sanitizeHtml(response);
          }
        },
        onError: (xhr) => {
          const output = document.getElementById('indexer-output');
          output.innerHTML = xhr.response;
        },
      });
    };
  };
})(Joomla, document);

// @todo use directly the Joomla.finderIndexer() instead of the Indexer()!!!
document.addEventListener('DOMContentLoaded', () => {
  window.Indexer = Joomla.finderIndexer();
});
