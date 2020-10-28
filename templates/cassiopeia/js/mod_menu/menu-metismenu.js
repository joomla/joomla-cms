document.addEventListener("DOMContentLoaded", function(event) {
	const allMenus = document.querySelectorAll('ul.mod-menu_metismenu');

	allMenus.forEach(menu => {
		// eslint-disable-next-line no-new, no-undef
		new MetisMenu(menu, {
			triggerElement: 'button.mm-toggler'
		});
	});
});
