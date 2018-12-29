/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Only define the Joomla namespace if not defined.
Joomla = window.Joomla || {};

/**
 * Sets the HTML of the container-collapse element
 */
Joomla.setcollapse = (url, name, height) => {
  if (!document.getElementById(`collapse-${name}`)) {
    document.getElementById('container-collapse').innerHTML = `<div class="collapse fade" id="collapse-${name}"><iframe class="iframe" src="${url}" height="${height}" width="100%"></iframe></div>`;
  }
};
