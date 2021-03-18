/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (Joomla) {
  'use strict';

  if (!window.parent.Joomla) {
    throw new Error('core.js was not properly initialised');
  }

  if (!Joomla) {
    window.Joomla = {};
  }

  Joomla.fieldIns = function (id, editor) {
    window.parent.Joomla.editors.instances[editor].replaceSelection("{field ".concat(id, "}"));

    if (window.parent.Joomla.Modal) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  };

  Joomla.fieldgroupIns = function (id, editor) {
    window.parent.Joomla.editors.instances[editor].replaceSelection("{fieldgroup ".concat(id, "}"));

    if (window.parent.Joomla.Modal) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  };
})(Joomla);