/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
function toggleAll() {
  var checkBoxes = [].slice.call(document.querySelectorAll('.chk-menulink'));
  var value = checkBoxes[0].checked;
  checkBoxes.forEach(function (checkBox) {
    checkBox.checked = !value;
  });
}

function toggleMenutype(a) {
  var checkBox = [].slice.call(document.getElementsByClassName(a));
  var value = checkBox[0].checked;
  checkBox.forEach(function (element) {
    element.checked = !value;
  });
}
