/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Fills any Health Check quick-icon that carries a data-url by fetching its value from
 * com_ajax, so heavy checks can be loaded after the dashboard has rendered.
 */
((document) => {
  'use strict';

  const STATUS_CLASS = {
    success: 'success', warning: 'warning', error: 'danger', critical: 'danger', info: 'info',
  };
  const STATUS_FILTER = {
    success: 'healthy', warning: 'warning', error: 'critical', critical: 'critical', info: 'healthy',
  };

  const fillIcon = async (amountEl) => {
    const url = amountEl.getAttribute('data-url');

    if (!url) {
      return;
    }

    // Remove the attribute first so the icon is never fetched twice.
    amountEl.removeAttribute('data-url');

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const json = await response.json();
      const payload = json && json.data ? json.data : null;
      const data = Array.isArray(payload) ? payload[0] : payload;

      if (!data || typeof data.amount === 'undefined') {
        amountEl.innerHTML = '<div>0</div>';
        return;
      }

      amountEl.innerHTML = `<div>${parseInt(data.amount, 10) || 0}</div>`;

      const status = data.status || 'success';
      const link = amountEl.closest('a');

      if (link) {
        ['info', 'success', 'warning', 'danger'].forEach((c) => link.classList.remove(c));
        link.classList.add(STATUS_CLASS[status] || 'info');
      }

      const wrapper = amountEl.closest('[data-healthcheck-status]');

      if (wrapper) {
        wrapper.setAttribute('data-healthcheck-status', STATUS_FILTER[status] || 'healthy');
      }
    } catch (e) {
      amountEl.innerHTML = '<div>!</div>';
    }
  };

  const init = () => {
    document.querySelectorAll('.health-checks [data-url]').forEach(fillIcon);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(document);
