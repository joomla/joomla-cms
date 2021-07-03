/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
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
