/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('The Joomla API wasn\'t initialized properly');
}

Joomla.toggleAll = () => [].slice.call(document.querySelectorAll('.chk-menulink'))
  .forEach((box) => {
    box.checked = !box.checked;
  });

Joomla.toggleMenutype = (a) => [].slice.call(document.querySelectorAll(`.menutype-${a}`))
  .forEach((box) => {
    box.checked = !box.checked;
  });
