/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('jform_image').addEventListener('change', ({
    currentTarget
  }) => {
    const flagSelectedValue = currentTarget.value;
    const flagimage = document.getElementById('flag').querySelector('img');
    if (flagSelectedValue) {
      flagimage.setAttribute('src', `${Joomla.getOptions('system.paths').rootFull}/media/mod_languages/images/${flagSelectedValue}.gif`);
      flagimage.setAttribute('alt', flagSelectedValue);
    } else {
      flagimage.removeAttribute('src');
      flagimage.setAttribute('alt', '');
    }
  }, false);
});
