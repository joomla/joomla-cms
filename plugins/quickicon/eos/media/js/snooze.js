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

customElements.whenDefined('joomla-alert').then(() => {

    document.querySelectorAll('joomla-alert').forEach((alert) => {

        const btn = document.querySelector('.eosnotify-snooze-btn');

        if (!btn) return;

        btn.addEventListener('click', async (_) => {
            const response = await fetch(new URL(`${Joomla.getOptions('system.paths').baseFull}index.php`),
                {
                    method: 'POST',
                    body: JSON.stringify(data),
                    headers: { 'X-CSRF-Token': Joomla.getOptions('csrf.token') || '' }
                });

            if (response.ok) {
                alert.close();
            }
        })
    })
});

