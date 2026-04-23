/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Client-side search/filter for the positions modal dialog
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('comModulesPositionsSearch');
    const noResults = document.getElementById('comModulesPositionsNoResults');

    if (!searchInput) {
      return;
    }

    // Auto-focus the search input
    searchInput.focus();

    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim().toLowerCase();
      const links = document.querySelectorAll('.position-select-link');
      let anyVisible = false;

      links.forEach((link) => {
        const id = (link.dataset.id || '').toLowerCase();
        const text = (link.textContent || '').trim().toLowerCase();
        const matches = !query || id.includes(query) || text.includes(query);

        if (matches) {
          link.classList.remove('d-none');
          anyVisible = true;
        } else {
          link.classList.add('d-none');
        }
      });

      // Hide groups where all items are hidden
      document.querySelectorAll('.positions-group').forEach((group) => {
        const visibleItems = group.querySelectorAll('.position-select-link:not(.d-none)');

        if (visibleItems.length === 0) {
          group.classList.add('d-none');
        } else {
          group.classList.remove('d-none');
          if (query) {
            group.open = true; // auto-expand groups with results when searching
          }
        }
      });

      // Show/hide no-results message
      if (noResults) {
        if (anyVisible) {
          noResults.setAttribute('hidden', '');
        } else {
          noResults.removeAttribute('hidden');
        }
      }
    });
  });
})();
