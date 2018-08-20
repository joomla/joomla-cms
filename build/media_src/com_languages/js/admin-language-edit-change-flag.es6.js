/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('jform_image').addEventListener('change', (event) => {
    const flagSelectedValue = event.currentTarget.value;
    const flagimage = document.getElementById('flag').querySelector('img');
    const src = `${Joomla.getOptions('system.paths').rootFull}/media/mod_languages/images/${flagSelectedValue}.gif`;

    if (flagSelectedValue) {
      flagimage.setAttribute('src', src);
      flagimage.setAttribute('alt', flagSelectedValue);
    } else {
      flagimage.removeAttribute('src');
      flagimage.setAttribute('alt', '');
    }
  }, false);
});
