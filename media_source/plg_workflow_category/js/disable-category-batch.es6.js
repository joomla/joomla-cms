/**
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  const disableCategory = () => {
    const dropdown = document.getElementById('toolbar-status-group');
    if (!dropdown) {
      return;
    }

    const batchButton = document.getElementById('status-group-children-batch');
    if (!batchButton) {
      return;
    }

    batchButton.addEventListener('click', () => {
      const observer = new MutationObserver((mutations, obs) => {
        const categorySelector = document.getElementById('batch-category-id');
        if (categorySelector) {
          categorySelector.disabled = true;
          obs.disconnect();
        }
      });

      observer.observe(document.body, { childList: true, subtree: true });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    disableCategory();
  });
})(document);
