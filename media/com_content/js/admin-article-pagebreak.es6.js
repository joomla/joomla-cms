/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  window.insertPagebreak = editor => {
    // Get the pagebreak title
    let title = document.getElementById('title').value;

    if (!window.parent.Joomla.getOptions('xtd-pagebreak')) {
      // Something went wrong!
      window.parent.Joomla.Modal.getCurrent().close();
      return false;
    } // Get the pagebreak toc alias -- not inserting for now don't know which attribute to use..


    let alt = document.getElementById('alt').value;
    title = title !== '' ? `title="${title}"` : '';
    alt = alt !== '' ? `alt="${alt}"` : '';
    const tag = `<hr class="system-pagebreak" ${title} ${alt}>`;
    window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
    window.parent.Joomla.Modal.getCurrent().close();
    return false;
  };
})();