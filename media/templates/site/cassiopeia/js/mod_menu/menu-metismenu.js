/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

document.querySelectorAll('ul.mod-menu_dropdown-metismenu').forEach(menu => {
  const mm = new MetisMenu(menu, {
    triggerElement: 'button.mm-toggler'
  }).on('shown.metisMenu', event => {
    function mmClick(e) {
      if (!event.target.contains(e.target)) {
        mm.on('hidden.metisMenu', () => {
          window.removeEventListener('click', mmClick);
        });
        mm.hide(event.detail.shownElement);
      }
    }
    window.addEventListener('click', mmClick);
  });
});
