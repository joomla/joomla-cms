/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(document => {
  const isChecked = element => element.checked;

  const getTreeElements = element => element.querySelectorAll('input[type="checkbox"]');

  const getTreeRoot = element => element.parentElement.nextElementSibling;

  const check = element => {
    element.checked = true;
  };

  const uncheck = element => {
    element.checked = false;
  };

  const disable = element => element.setAttribute('disabled', 'disabled');

  const enable = element => element.removeAttribute('disabled');

  const toggleState = (element, rootChecked) => {
    if (rootChecked === true) {
      disable(element);
      check(element);
      return;
    }

    enable(element);
    uncheck(element);
  };

  const switchState = ({
    target
  }) => {
    const root = getTreeRoot(target);
    const selfChecked = isChecked(target);

    if (root) {
      getTreeElements(root).map(element => toggleState(element, selfChecked));
    }
  };

  [].slice.call(document.querySelectorAll('.treeselect input[type="checkbox"]')).forEach(checkbox => {
    checkbox.addEventListener('click', switchState);
  });
})(document);