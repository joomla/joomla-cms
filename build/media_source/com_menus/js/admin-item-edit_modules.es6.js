/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const options = Joomla.getOptions('menus-edit-modules');

if (options) {
  window.viewLevels = options.viewLevels;
  window.menuId = parseInt(options.itemId, 10);
}

const assigned1 = document.getElementById('jform_toggle_modules_assigned1');
const assigned0 = document.getElementById('jform_toggle_modules_assigned0');
const published1 = document.getElementById('jform_toggle_modules_published1');
const published0 = document.getElementById('jform_toggle_modules_published0');

if (assigned1) {
  assigned1.addEventListener('click', () => {
    const list = [].slice.call(document.querySelectorAll('tr.no'));

    list.forEach((item) => {
      item.classList.add('table-row');
      item.classList.remove('hidden');
    });
  });
}

if (assigned0) {
  assigned0.addEventListener('click', () => {
    const list = [].slice.call(document.querySelectorAll('tr.no'));

    list.forEach((item) => {
      item.classList.add('hidden');
      item.classList.remove('table-row');
    });
  });
}

if (published1) {
  published1.addEventListener('click', () => {
    const list = [].slice.call(document.querySelectorAll('.table tr.unpublished'));

    list.forEach((item) => {
      item.classList.add('table-row');
      item.classList.remove('hidden');
    });
  });
}

if (published0) {
  published0.addEventListener('click', () => {
    const list = [].slice.call(document.querySelectorAll('.table tr.unpublished'));

    list.forEach((item) => {
      item.classList.add('hidden');
      item.classList.remove('table-row');
    });
  });
}
