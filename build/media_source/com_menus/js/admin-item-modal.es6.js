/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  Joomla.setMenuType = (type, tmpl) => {
    if (tmpl !== '') {
      window.parent.Joomla.submitbutton('item.setType', type);
      window.parent.Joomla.Modal.getCurrent().close();
    } else {
      window.location = `index.php?option=com_menus&view=item&task=item.setType&layout=edit&type=${type}`;
    }
  };
})(Joomla);
