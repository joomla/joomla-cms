/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (Joomla) {
  'use strict';

  Joomla.setMenuType = function (type, tmpl) {
    if (tmpl !== '') {
      window.parent.Joomla.submitbutton('item.setType', type);
      window.parent.Joomla.Modal.getCurrent().close();
    } else {
      window.location = "index.php?option=com_menus&view=item&task=item.setType&layout=edit&type=".concat(type);
    }
  };
})(Joomla);