/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  window.insertReadmore = (editor) => {
    if (!Joomla.getOptions('xtd-readmore')) {
    // Something went wrong!
      return false;
    }

    let content;
    const options = window.Joomla.getOptions('xtd-readmore');

    if (window.Joomla && window.Joomla.editors && window.Joomla.editors.instances
    && window.Joomla.editors.instances.hasOwnProperty.call(editor)) {
      content = window.Joomla.editors.instances[editor].getValue();
    } else {
      content = () => options.editor;
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
