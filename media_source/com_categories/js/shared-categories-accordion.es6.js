/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!Joomla || !Joomla.Text) {
  throw new Error('core.js was not properly initialised');
}

// Selectors used by this script
const buttonsSelector = '[id^=category-btn-]';

/**
 * Handle the category toggle button click event
 * @param event
 */
const handleCategoryToggleButtonClick = ({ currentTarget }) => {
  const button = currentTarget;
  const icon = button.querySelector('span');

  // Toggle icon class
  icon.classList.toggle('icon-plus');
  icon.classList.toggle('icon-minus');

  // Toggle aria label, aria-expanded
  const ariaLabel = button.getAttribute('aria-label');
  const ariaExpanded = button.getAttribute('aria-expanded');
  button.setAttribute(
    'aria-label',
    (
      ariaLabel === Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES') ? Joomla.Text._('JGLOBAL_COLLAPSE_CATEGORIES') : Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES')
    ),
  );
  button.setAttribute(
    'aria-expanded',
    (
      ariaExpanded === 'false' ? 'true' : 'false'
    ),
  );

  const { categoryId } = button.dataset;
  const target = document.getElementById(`category-${categoryId}`);
  target.toggleAttribute('hidden');
};

document.querySelectorAll(buttonsSelector).forEach((button) => button.addEventListener('click', handleCategoryToggleButtonClick));
