document.addEventListener('DOMContentLoaded', () => {
  const allMenus = document.querySelectorAll('ul.mod-menu_metismenu');

  allMenus.forEach((menu) => {
    // eslint-disable-next-line no-new, no-undef
    const mm = new MetisMenu(menu, {
      triggerElement: 'button.mm-toggler',
    }).on('shown.metisMenu', (event) => {
      window.addEventListener('click', function mmClick(e) {
        if (!event.target.contains(e.target)) {
          mm.hide(event.detail.shownElement);
          window.removeEventListener('click', mmClick);
        }
      });
    });
  });
});
