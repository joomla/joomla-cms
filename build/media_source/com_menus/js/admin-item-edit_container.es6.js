/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  const isChecked = (element) => element.checked;
  const getTreeElements = (element) => element.querySelectorAll('input[type="checkbox"]');
  const getTreeRoot = (element) => element.parentElement.nextElementSibling;

  const check = (element) => {
    element.checked = true;
  };

  const uncheck = (element) => {
    element.checked = false;
  };

  const disable = (element) => element.setAttribute('disabled', 'disabled');
  const enable = (element) => element.removeAttribute('disabled');

  const toggleState = (element, rootChecked) => {
    if (rootChecked === true) {
      disable(element);
      check(element);

      return;
    }

    enable(element);
    uncheck(element);
  };
  const switchState = ({ target }) => {
    const root = getTreeRoot(target);
    const selfChecked = isChecked(target);

    if (root) {
      getTreeElements(root).map((element) => toggleState(element, selfChecked));
    }
  };

  document.querySelectorAll('.treeselect input[type="checkbox"]').forEach((checkbox) => checkbox.addEventListener('click', switchState));
})(document);
