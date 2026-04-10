/**
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla.submitbutton = task => {

  if (task === 'actionlogs.exportLogs') {
    Joomla.submitform(task, document.getElementById('exportForm'));
    return;
  }
  if (task === 'actionlogs.exportSelectedLogs') {
    // Get id of selected action logs item and pass it to export form hidden input
    const cids = [];
    document.querySelectorAll("input[name='cid[]']:checked").forEach(element => cids.push(element.value));
    document.exportForm.cids.value = cids.join(',');
    Joomla.submitform(task, document.getElementById('exportForm'));
    return;
  }
  Joomla.submitform(task);
};
