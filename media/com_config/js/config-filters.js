/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
const recursiveApplyChanges = id => {
  document.querySelectorAll(`#filter-config select[data-parent="${id}"]`).forEach(child => {
    recursiveApplyChanges(child.dataset.id);
    child.value = 'NONE';
  });
};
const applyChanges = event => {
  const currentElement = event.currentTarget;
  const currentFilter = currentElement.options[currentElement.selectedIndex].value;
  if (currentFilter === 'NONE') {
    const childs = document.querySelectorAll(`#filter-config select[data-parent="${currentElement.dataset.id}"]`);
    if (childs.length && window.confirm(Joomla.Text._('COM_CONFIG_TEXT_FILTERS_NOTE'))) {
      childs.forEach(child => {
        recursiveApplyChanges(child.dataset.id);
        child.value = 'NONE';
      });
    }
  }
};
document.querySelectorAll('#filter-config select').forEach(select => select.addEventListener('change', applyChanges));
