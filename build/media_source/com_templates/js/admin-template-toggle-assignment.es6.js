/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

function toggle_all() {
  const checkBoxes = [].slice.call(document.querySelectorAll(".chk-menulink"));
  const value = checkBoxes[0].checked;
  checkBoxes.forEach(checkBox => {
    checkBox.checked = !value;
  });
}

function toggle_menutype(a) {
  const checkBox = [].slice.call(document.getElementsByClassName(a));
  const value = checkBox[0].checked;
  checkBox.forEach(element => {
    element.checked = !value;
  });
}
