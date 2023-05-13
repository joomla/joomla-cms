/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.eos
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

const url = new URL(`${Joomla.getOptions('system.paths').baseFull}index.php?option=com_ajax&group=quickicon&plugin=eos&format=json`);

async function onMutatedMessagesContainer(mutationList, observer) {
  // eslint-disable-next-line no-restricted-syntax
  for (const mutation of mutationList) {
    const nodes = Array.from(mutation.addedNodes);
    if (!nodes.length) {
      return;
    }

    const alerts = nodes.filter((node) => node.querySelector('.eosnotify-snooze-btn'));
    if (!alerts.length) {
      return;
    }

    observer.disconnect();
    alerts[0].querySelector('.eosnotify-snooze-btn').addEventListener('click', () => fetch(url, { headers: { 'X-CSRF-Token': Joomla.getOptions('csrf.token') || '' } })
      .then((response) => {
        if (response.ok) {
          alerts[0].closest('joomla-alert').close();
        }
      }));
  }
}

const observer = new MutationObserver(onMutatedMessagesContainer);
observer.observe(document.querySelector('#system-message-container'), { attributes: false, childList: true, subtree: true });
