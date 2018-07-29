/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', () => {
  const title = document.getElementById('jform_title');
  title.setAttribute('data-old-value', title.value);

  title.addEventListener('change', (event) => {
    const label = document.getElementById('jform_label');
    const changedTitle = event.currentTarget;

    if (changedTitle.getAttribute('data-old-value') === label.value) {
      label.value = changedTitle.value;
    }

    changedTitle.setAttribute('data-old-value', changedTitle.value);
  });
});
