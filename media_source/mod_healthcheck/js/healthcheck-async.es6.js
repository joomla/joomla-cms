/**
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Fills any Health Check quick-icon that carries a data-url by fetching its value from
 * com_ajax. When the response includes an item list, clicking the icon toggles that list
 * instead of navigating, so each matched item can be opened directly. Items that carry a
 * `code` property open that code in a modal instead.
 */
((document) => {
  'use strict';

  const STATUS_CLASS = {
    success: 'success', warning: 'warning', error: 'danger', critical: 'danger', info: 'info',
  };
  const STATUS_FILTER = {
    success: 'healthy', warning: 'warning', error: 'critical', critical: 'critical', info: 'healthy',
  };

  let codeModal = null;
  let lastFocused = null;

  const hideCodeModal = () => {
    if (!codeModal || codeModal.hidden) {
      return;
    }

    codeModal.hidden = true;

    // Return focus to the item that opened the modal.
    if (lastFocused && typeof lastFocused.focus === 'function') {
      lastFocused.focus();
    }
  };

  // Lazily create a single reusable modal for showing an item's affected code.
  const ensureCodeModal = () => {
    if (codeModal) {
      return codeModal;
    }

    // Build the modal with DOM methods.
    const create = (tag, className) => {
      const element = document.createElement(tag);
      element.className = className;
      return element;
    };

    const overlay = create('div', 'healthcheck-code-modal');
    overlay.hidden = true;

    const backdrop = create('div', 'healthcheck-code-modal-backdrop');

    const dialog = create('div', 'healthcheck-code-modal-dialog');
    dialog.setAttribute('role', 'dialog');
    dialog.setAttribute('aria-modal', 'true');
    dialog.setAttribute('aria-labelledby', 'healthcheck-code-modal-title');

    const header = create('div', 'healthcheck-code-modal-header');

    const title = create('h3', 'healthcheck-code-modal-title');
    title.id = 'healthcheck-code-modal-title';

    const closeButton = create('button', 'btn-close');
    closeButton.type = 'button';
    closeButton.setAttribute('aria-label', Joomla.Text._('JCLOSE', 'Close'));

    const body = create('pre', 'healthcheck-code-modal-body');
    body.tabIndex = 0;
    body.appendChild(document.createElement('code'));

    header.append(title, closeButton);
    dialog.append(header, body);
    overlay.append(backdrop, dialog);

    closeButton.addEventListener('click', hideCodeModal);
    backdrop.addEventListener('click', hideCodeModal);

    overlay.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        hideCodeModal();
        return;
      }

      // Keep keyboard focus inside the dialog while it is open.
      if (event.key === 'Tab') {
        const focusable = [closeButton, body];
        const index = focusable.indexOf(document.activeElement);
        const next = event.shiftKey ? index - 1 : index + 1;
        event.preventDefault();
        focusable[(next + focusable.length) % focusable.length].focus();
      }
    });

    document.body.appendChild(overlay);
    codeModal = overlay;

    return overlay;
  };

  // Populate and open the code modal. Title and code are set via textContent, so never injected.
  const showCodeModal = (title, code) => {
    const overlay = ensureCodeModal();
    overlay.querySelector('.healthcheck-code-modal-title').textContent = title;
    overlay.querySelector('.healthcheck-code-modal-body code').textContent = code;
    lastFocused = document.activeElement;
    overlay.hidden = false;
    overlay.querySelector('.btn-close').focus();
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

      if (item.code) {
        // The item carries source code (e.g. a class redefinition): show it in a modal instead of a link.
        const anchor = document.createElement('a');
        anchor.href = '#';
        anchor.textContent = item.title;
        anchor.addEventListener('click', (event) => {
          event.preventDefault();
          showCodeModal(item.title, String(item.code));
        });
        row.appendChild(anchor);
      } else if (item.link) {
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

  // Replace the icon's amount with the given value, using textContent so it is never parsed as HTML.
  const setAmount = (amountEl, value) => {
    const amountWrapper = document.createElement('div');
    amountWrapper.textContent = value;
    amountEl.replaceChildren(amountWrapper);
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
        setAmount(amountEl, 0);
        return;
      }

      const amount = parseInt(data.amount, 10) || 0;
      setAmount(amountEl, amount);

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
      setAmount(amountEl, '!');
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
