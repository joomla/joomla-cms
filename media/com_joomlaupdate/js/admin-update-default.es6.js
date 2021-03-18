/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};
document.addEventListener('DOMContentLoaded', () => {
  const JoomlaUpdateOptions = Joomla.getOptions('joomlaupdate');
  window.joomlaupdate_password = JoomlaUpdateOptions.password;
  window.joomlaupdate_totalsize = JoomlaUpdateOptions.totalsize;
  window.joomlaupdate_ajax_url = JoomlaUpdateOptions.ajax_url;
  window.joomlaupdate_return_url = JoomlaUpdateOptions.return_url;
  window.pingExtract();
});