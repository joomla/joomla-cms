/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(document => {
  const batchMenu = document.getElementById('batch-menu-id');
  const batchCopyMove = document.getElementById('batch-copy-move');
  let batchSelector;
  const onChange = () => {
    if (batchSelector.value !== 0 || batchSelector.value !== '') {
      batchCopyMove.classList.remove('hidden');
    } else {
      batchCopyMove.classList.add('hidden');
    }
  };
  if (batchMenu) {
    batchSelector = batchMenu;
  }
  if (batchCopyMove) {
    batchSelector.addEventListener('change', onChange);
  }
})(document);
