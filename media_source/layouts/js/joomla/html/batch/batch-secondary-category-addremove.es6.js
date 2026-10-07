/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  const onSelect = () => {
    const batchSecondaryCategory = document.getElementById('batch-secondary-category-id');
    const batchSecondaryCategoryAddRemove = document.getElementById('batch-secondary-category-addremove');
    let batchSelector;

    const onChange = () => {
      if (!batchSelector.value || (batchSelector.value && parseInt(batchSelector.value, 10) === 0)) {
        batchSecondaryCategoryAddRemove.classList.add('hidden');
      } else {
        batchSecondaryCategoryAddRemove.classList.remove('hidden');
      }
    };

    if (batchSecondaryCategory) {
      batchSelector = batchSecondaryCategory;
    }

    if (batchSecondaryCategoryAddRemove) {
      batchSecondaryCategoryAddRemove.classList.add('hidden');
    }

    if (batchSecondaryCategoryAddRemove && batchSelector) {
      batchSelector.addEventListener('change', onChange);
      onChange();
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onSelect, true);
  };

  // Document loaded
  document.addEventListener('DOMContentLoaded', onSelect, true);

  // Joomla updated
  document.addEventListener('joomla:updated', onSelect, true);
})();
