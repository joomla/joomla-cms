/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  const onSelect = () => {
    const batchTag = document.getElementById('batch-tag-id');
    const batchTagAddRemove = document.getElementById('batch-tag-addremove');
    let batchSelector;

    const onChange = () => {
      if (!batchSelector.value
          || (batchSelector.value && parseInt(batchSelector.value, 10) === 0)) {
        batchTagAddRemove.classList.add('hidden');
      } else {
        batchTagAddRemove.classList.remove('hidden');
      }
    };

    if (batchTag) {
      batchSelector = batchTag;
    }

    if (batchTagAddRemove) {
      batchTagAddRemove.classList.add('hidden');
    }

    if (batchTagAddRemove) {
      batchSelector.addEventListener('change', onChange);
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onSelect, true);
  };

  // Document loaded
  document.addEventListener('DOMContentLoaded', onSelect, true);

  // Joomla updated
  document.addEventListener('joomla:updated', onSelect, true);
})();
