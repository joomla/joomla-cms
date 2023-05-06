/**
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
const url = new URL(`${Joomla.getOptions('system.paths').baseFull}index.php?option=com_ajax&group=quickicon&plugin=SnoozeEOS&format=json`)
const observer = new MutationObserver(onCreated);
observer.observe(document.querySelector('#system-message-container'), { attributes: false, childList: true, subtree: true });

function onCreated(mutationList, observer) {
  for(let mutation of mutationList) {
    const nodes = Array.from(mutation.addedNodes);

    if (!nodes.length) {
      return;
    }

    observer.disconnect();
    let alerts = nodes.filter(node => node.querySelector('.eosnotify-snooze-btn'));

    if (alerts.length) {
      alerts[0].querySelector('.eosnotify-snooze-btn').addEventListener('click', (_) => fetch(url, { headers: { 'X-CSRF-Token': Joomla.getOptions('csrf.token') || '' } })
        .then((response) => {
          if (response.ok) {
          alerts[0].closest('joomla-alert').close();
        }
      }));
    }
  }
};
