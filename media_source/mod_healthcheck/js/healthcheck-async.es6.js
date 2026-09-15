/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Fills any Health Check quick-icon that carries a data-url by fetching its value from
 * com_ajax. When the response includes an item list, clicking the icon toggles that list
 * instead of navigating, so each matched item can be opened directly.
 */
((document) => {
  'use strict';

  const STATUS_CLASS = {
    success: 'success', warning: 'warning', error: 'danger', critical: 'danger', info: 'info',
  };
  const STATUS_FILTER = {
    success: 'healthy', warning: 'warning', error: 'critical', critical: 'critical', info: 'healthy',
  };

  const buildItemList = (link, items) => {
    const container = link.closest('li') || link.parentNode;
    const list = document.createElement('ul');
    list.className = 'healthcheck-itemlist list-unstyled';
    list.hidden = true;
    // Take a full-width row so the list sits below the icon (inside the quickicon box), not beside it.
    list.style.cssText = 'flex-basis:100%;width:100%;margin:.5rem 0 0;padding:0;text-align:start;';

    items.forEach((item) => {
      const row = document.createElement('li');

      if (item.link) {
        const anchor = document.createElement('a');
        anchor.href = item.link;
        anchor.textContent = item.title;
        row.appendChild(anchor);
      } else {
        // No edit screen (e.g. malware files): render the path as plain, easy-to-copy text.
        const code = document.createElement('code');
        code.textContent = item.title;
        row.appendChild(code);
      }

      list.appendChild(row);
    });

    // Let the icon box wrap so the list drops onto its own row underneath.
    container.style.flexWrap = 'wrap';
    container.appendChild(list);

    // Open the list on click rather than following the icon's own link. While open, the icon spans
    // every dashboard column (via .is-expanded) so the list gets the full width.
    link.addEventListener('click', (event) => {
      event.preventDefault();
      const show = list.hidden;
      list.hidden = !show;
      container.classList.toggle('is-expanded', show);
    });
  };

  const fillIcon = async (amountEl) => {
    const url = amountEl.getAttribute('data-url');

    if (!url) {
      return;
    }

    // Remove the attribute first so the icon is never fetched twice.
    amountEl.removeAttribute('data-url');

    const link = amountEl.closest('a');

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

      const amount = parseInt(data.amount, 10) || 0;
      const amount_wrapper = document.createElement('div');
      amount_wrapper.textContent = amount;
      amountEl.innerHTML = '';
      amountEl.appendChild(amount_wrapper);

      const status = data.status || 'success';

      if (link) {
        ['info', 'success', 'warning', 'danger'].forEach((c) => link.classList.remove(c));
        link.classList.add(STATUS_CLASS[status] || 'info');

        if (amount > 0 && Array.isArray(data.items) && data.items.length) {
          buildItemList(link, data.items);
        } else if (amount === 0) {
          // Nothing to drill into — make the icon non-interactive.
          link.classList.add('pe-none');
        }
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
