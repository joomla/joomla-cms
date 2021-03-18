/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  var isChecked = function isChecked(element) {
    return element.checked;
  };

  var getTreeElements = function getTreeElements(element) {
    return element.querySelectorAll('input[type="checkbox"]');
  };

  var getTreeRoot = function getTreeRoot(element) {
    return element.parentElement.nextElementSibling;
  };

  var check = function check(element) {
    element.checked = true;
  };

  var uncheck = function uncheck(element) {
    element.checked = false;
  };

  var disable = function disable(element) {
    return element.setAttribute('disabled', 'disabled');
  };

  var enable = function enable(element) {
    return element.removeAttribute('disabled');
  };

  var toggleState = function toggleState(element, rootChecked) {
    if (rootChecked === true) {
      disable(element);
      check(element);
      return;
    }

    enable(element);
    uncheck(element);
  };

  var switchState = function switchState(_ref) {
    var target = _ref.target;
    var root = getTreeRoot(target);
    var selfChecked = isChecked(target);

    if (root) {
      getTreeElements(root).map(function (element) {
        return toggleState(element, selfChecked);
      });
    }
  };

  [].slice.call(document.querySelectorAll('.treeselect input[type="checkbox"]')).forEach(function (checkbox) {
    checkbox.addEventListener('click', switchState);
  });
})(document);