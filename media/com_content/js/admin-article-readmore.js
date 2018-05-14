/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  window.insertReadmore = function (editor) {
    if (!Joomla.getOptions('xtd-readmore')) {
      // Something went wrong!
      return false;
    }

    var content = void 0;
    var options = window.Joomla.getOptions('xtd-readmore');

    if (window.Joomla && window.Joomla.editors && window.Joomla.editors.instances && window.Joomla.editors.instances.hasOwnProperty.call(editor)) {
      content = window.Joomla.editors.instances[editor].getValue();
    } else {
      content = function content() {
        return options.editor;
      };
    }

    if (!content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
      Joomla.editors.instances[editor].replaceSelection('<hr id="system-readmore">');
    } else {
      // TODO replace with joomla-alert
      alert(options.exists);
      return false;
    }
    return true;
  };
})();
