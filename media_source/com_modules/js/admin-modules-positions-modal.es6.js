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
          link.hidden = false;
          anyVisible = true;
        } else {
          link.hidden = true;
        }
      });

      // Hide groups where all items are hidden
      document.querySelectorAll('.positions-group').forEach((group) => {
        const visibleItems = group.querySelectorAll('.position-select-link:not([hidden])');

        if (visibleItems.length === 0) {
          group.hidden = true;
        } else {
          group.hidden = false;
          if (query) {
            group.open = true; // auto-expand groups with results when searching
          }
        }
      });

      // Show/hide no-results message - aria-live="polite" on the element announces it when shown
      if (noResults) {
        noResults.hidden = anyVisible;
      }
    });
  });
})();
