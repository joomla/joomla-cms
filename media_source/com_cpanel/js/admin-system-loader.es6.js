/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, Joomla) => {
  'use strict';

  const init = () => {
    // Cleanup
    window.removeEventListener('load', init);

    // Get the elements
    const elements = document.querySelectorAll('.system-counter');

    if (elements.length) {
      elements.forEach((element) => {
        const badgeurl = element.getAttribute('data-url');

        if (badgeurl && Joomla && Joomla.request && typeof Joomla.request === 'function') {
          Joomla.enqueueRequest({
            url: badgeurl,
            method: 'POST',
            promise: true,
          }).then((xhr) => {
            const resp = xhr.responseText;
            let response;
            try {
              response = JSON.parse(resp);
            } catch (error) {
              throw new Error('Failed to parse JSON');
            }

            if (response.error || !response.success) {
              element.classList.remove('icon-spin', 'icon-spinner');
              element.classList.add('text-danger', 'icon-remove');
            } else if (response.data) {
              const elem = document.createElement('span');
              elem.classList.add('float-end', 'badge', 'bg-warning', 'text-dark');
              elem.innerHTML = Joomla.sanitizeHtml(response.data);

              element.parentNode.replaceChild(elem, element);
            } else {
              element.classList.remove('icon-spin', 'icon-spinner');
              element.classList.add('icon-check', 'text-success');
            }
          }).catch(() => {
            element.classList.remove('icon-spin', 'icon-spinner');
            element.classList.add('text-danger', 'icon-remove');
          });
        }
      });
    }
  };

  // Give some times to the layout and other scripts to settle their stuff
  window.addEventListener('load', () => {
    setTimeout(init, 300);
  });
})(document, Joomla);
