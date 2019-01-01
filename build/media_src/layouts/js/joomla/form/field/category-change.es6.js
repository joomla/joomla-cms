/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  const onBoot = () => {
    Joomla.loadingLayer('load');
    const id = Joomla.getOptions('category-change');
    const element = document.querySelector(`#${id}`);

    if (!element) {
      throw new Error('Category Id element not found');
    }

    if (element.getAttribute('data-cat-id') && element.value !== element.getAttribute('data-cat-id')) {
      element.value = element.getAttribute('data-cat-id');
    } else {
      // No custom fields
      element.setAttribute('data-cat-id', element.value);
    }

    window.Joomla.categoryHasChanged = (el) => {
      if (el.value === el.getAttribute('data-cat-id')) {
        return;
      }

      Joomla.loadingLayer('show');
      document.body.appendChild(document.createElement('joomla-core-loader'));

      // Custom Fields
      if (el.getAttribute('data-section')) {
        document.querySelector('input[name=task]').value = `${el.getAttribute('data-section')}.reload`;
      }

      element.form.submit();
    };

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(Joomla);
