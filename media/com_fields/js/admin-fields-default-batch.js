/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
const setUp = container => {
  const batchSelector = container.getElementById('batch-group-id');
  const batchCopyMove = container.getElementById('batch-copy-move');
  if (!container || !batchCopyMove) {
    return;
  }
  batchCopyMove.classList.add('hidden');
  batchSelector.addEventListener('change', () => {
    if (batchSelector.value === 'nogroup' || batchSelector.value !== '') {
      batchCopyMove.classList.remove('hidden');
    } else {
      batchCopyMove.classList.add('hidden');
    }
  }, false);
};
setUp(document);
document.addEventListener('joomla:loaded', ({
  target
}) => setUp(target));
