/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  const disable = (element) => {
    if (element.getAttribute('disabled')) {
      console.log('HAS ATTRIBUTE');
      element.removeAttribute('disabled');
    } else {
      element.setAttribute('disabled', 'disabled');
    }
  }

  const getRootChildren = (element) => element ? element.querySelectorAll('input[type="checkbox"]') : undefined;

  const checkIfRoot = (element) =>  getRootChildren(element.parentElement.nextElementSibling);

  window.addEventListener('load', () => {
    const checkboxes = document.querySelectorAll('.treeselect input[type="checkbox"]');

    for (let checkbox of checkboxes) {
      checkbox.addEventListener('click', (event) => {
        const targetElement = event.target;

        if (Number(targetElement.value) === 1) {
          checkboxes
            .filter(checkbox => checkbox !== targetElement)
            .map(checkbox => disable(checkbox));
        }

        if (typeof checkIfRoot(targetElement) !== 'undefined') {
          for (let subCheckbox of checkIfRoot(targetElement)) {
            disable(subCheckbox);
          }
        } else {
          disable(targetElement);
        }
      })
    }
  })
})()




