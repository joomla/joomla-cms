/**
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

const data = {
  option: 'com_ajax',
  group: 'quickicon',
  plugin: 'SnoozeEOS',
  format: 'json',
};

const observer = new MutationObserver(onCreated);
observer.observe(document.querySelector('#system-message-container'), { attributes: false, childList: true, subtree: true });

async function onCreated(mutationList, observer) {
  for(let mutation of mutationList) {
    if (!Array.from(mutation.addedNodes).length) return;
    let alerts = Array.from(mutation.addedNodes).filter(node => node.querySelector('.eosnotify-snooze-btn'));

    if (alerts.length) {
      observer.disconnect();
      alerts[0].querySelector('.eosnotify-snooze-btn').addEventListener('click', async (_) => {
        const response = await fetch(new URL(`${Joomla.getOptions('system.paths').baseFull}index.php`),
          {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'X-CSRF-Token': Joomla.getOptions('csrf.token') || '' }
        });

        if (response.ok) {
          alerts[0].closest('joomla-alert').close();
        }
      })
    }
  }
};

