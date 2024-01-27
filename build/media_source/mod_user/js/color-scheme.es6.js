/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * Every quickicon with an ajax request url loads data and set them into the counter element
 * Also the data name is set as singular or plural.
 * A SR-only text is added
 * The class pulse gets 'warning', 'success' or 'error', depending on the retrieved data.
 */
if (!Joomla) {
    throw new Error('Joomla API was not properly initialized');
}

const initModUser = () => {
    const buttons = [].slice.call(document.querySelectorAll('.mod_user-colorScheme'));

    console.log(window.document);

    buttons.forEach(button => {
        button.addEventListener('click', e => {
            e.preventDefault();

            const docEl = document.documentElement
            const colorScheme = docEl.dataset.colorScheme;

            const newScheme = colorScheme !== 'dark' ? 'dark' : 'light';

            docEl.dataset.colorScheme = newScheme;
            docEl.dataset.bsTheme = newScheme;

            const expires = new Date();
            expires.setTime(expires.getTime() + 31536000000);
            document.cookie = `colorScheme=${newScheme}; expires=${expires.toUTCString()};`;
            document.dispatchEvent(new CustomEvent('joomla:color-scheme-change', { bubbles: true }));

        });
    });

};

document.addEventListener('DOMContentLoaded', initModUser);