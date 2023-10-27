/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
window.Joomla = window.Joomla || {};

Joomla.setMenuType = (type, tmpl) => {
  // eslint-disable-next-line no-console
  console.warn('Method Joomla.setMenuType() is deprecated. Use "modal-content-select" asset and elements with [data-content-select] attribute.');

  if (tmpl !== '') {
    window.parent.Joomla.submitbutton('item.setType', type);

    if (window.parent.Joomla.Modal && window.parent.Joomla.Modal.getCurrent()) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  } else {
    window.location = `index.php?option=com_menus&view=item&task=item.setType&layout=edit&type=${type}`;
  }
};
