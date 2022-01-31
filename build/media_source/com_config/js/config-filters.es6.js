/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
const recursiveApplyChanges = (id) => {
  const childs = [].slice.call(document.querySelectorAll(`#filter-config select[data-parent="${id}"]`));
  childs.map((child) => {
    recursiveApplyChanges(child.dataset.id);
    child.value = 'NONE';
    return child;
  });
};

const applyChanges = (event) => {
  const currentElement = event.currentTarget;
  const currentFilter = currentElement.options[currentElement.selectedIndex].value;

  if (currentFilter === 'NONE') {
    const childs = [].slice.call(document.querySelectorAll(`#filter-config select[data-parent="${currentElement.dataset.id}"]`));
    if (childs.length && window.confirm(Joomla.Text._('COM_CONFIG_TEXT_FILTERS_NOTE'))) {
      childs.map((child) => {
        recursiveApplyChanges(child.dataset.id);
        child.value = 'NONE';
        return child;
      });
    }
  }
};

[].slice.call(document.querySelectorAll('#filter-config select')).map((select) => select.addEventListener('change', applyChanges));
