/**
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const initModUser = () => {
  const buttons = document.querySelectorAll('.mod_user-color-scheme');

  buttons.forEach((button) => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      const docEl = document.documentElement;
      const { colorScheme } = docEl.dataset;
      const newScheme = colorScheme !== 'dark' ? 'dark' : 'light';
      docEl.dataset.colorScheme = newScheme;
      // document.cookie = `colorScheme=${newScheme};`;
      document.dispatchEvent(new CustomEvent('joomla:color-scheme-change', { bubbles: true }));
    });
  });
};

initModUser();
