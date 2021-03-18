/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(Joomla => {
  Joomla.toggleAll = () => {
    const checkBoxes = [].slice.call(document.querySelectorAll('.chk-menulink'));
    checkBoxes.forEach(checkBox => {
      checkBox.checked = !checkBox.checked;
    });
  };

  Joomla.toggleMenutype = a => {
    const checkBox = [].slice.call(document.getElementsByClassName(`menutype-${a}`));
    checkBox.forEach(element => {
      element.checked = !element.checked;
    });
  };
})(Joomla);