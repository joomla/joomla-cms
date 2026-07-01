/**
 * @copyright   (C) 2026 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {

    const categoryField = document.getElementById('filter_category_id');
    const matchWrapper = document.getElementById('filter_category_match');

    if (!categoryField || !matchWrapper) return;

    const matchContainer = matchWrapper.closest('div');
    if (!matchContainer) return;

    const toggleMatchVisibility = () => {
      const hasCategory = Array.from(categoryField.selectedOptions).some(option => option.value !== '');
      matchContainer.hidden = !hasCategory;
    };

    // Run on page load
    toggleMatchVisibility();

    // Run when category changes
    categoryField.addEventListener('change', () => {
      setTimeout(toggleMatchVisibility, 50);
    });
  });
})();
