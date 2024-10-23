/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

document.querySelectorAll('ul.mod-menu_dropdown-metismenu').forEach((menu) => {
  // eslint-disable-next-line no-new, no-undef
  const mm = new MetisMenu(menu, {
    triggerElement: 'button.mm-toggler',
  }).on('shown.metisMenu', (event) => {
    window.addEventListener('click', function mmClick(e) {
      if (!event.target.contains(e.target)) {
        mm.addEventListener('hidden.metisMenu', () => {
          window.removeEventListener('click', mmClick);
        });
        mm.hide(event.detail.shownElement);
      }
    });
  });
});
