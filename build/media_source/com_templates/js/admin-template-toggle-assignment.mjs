/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  Joomla.toggleAll = () => {
    const checkBoxes = [].slice.call(document.querySelectorAll('.chk-menulink'));
    checkBoxes.forEach((checkBox) => {
      checkBox.checked = !checkBox.checked;
    });
  };

  Joomla.toggleMenutype = (a) => {
    const checkBox = [].slice.call(document.getElementsByClassName(`menutype-${a}`));
    checkBox.forEach((element) => {
      element.checked = !element.checked;
    });
  };
})(Joomla);
