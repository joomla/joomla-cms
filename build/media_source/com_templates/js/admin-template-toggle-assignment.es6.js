/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  Joomla.toggleAll = () => {
    const checkBoxes = [].slice.call(document.querySelectorAll('.chk-menulink'));
    const value = checkBoxes[0].checked;
    checkBoxes.forEach((checkBox) => {
      checkBox.checked = !value;
    });
  };

  Joomla.toggleMenutype = (a) => {
    const checkBox = [].slice.call(document.getElementsByClassName(`menutype-${a}`));
    const value = checkBox[0].checked;
    checkBox.forEach((element) => {
      element.checked = !value;
    });
  };
})(Joomla);
