/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


Joomla = window.Joomla || {};

(function(Joomla, document) {
	'use strict';

	new MetisMenu("#menu");

	var wrapper        = document.getElementById('wrapper');
	var sidebar        = document.getElementById('sidebar-wrapper');
	var menuToggleIcon = document.getElementById('menu-collapse-icon');
	var body           = document.body;

	// Set the initial state of the sidebar based on the localStorage value
	if (Joomla.localStorageEnabled()) {
		var sidebarState = localStorage.getItem('atum-sidebar');
		if (sidebarState === 'open' || sidebarState === null) {
			wrapper.classList.remove('closed');
			menuToggleIcon.classList.remove('fa-toggle-off');
			menuToggleIcon.classList.add('fa-toggle-on');
			localStorage.setItem('atum-sidebar', 'open');
		} else {
			wrapper.classList.add('closed');
			menuToggleIcon.classList.remove('fa-toggle-on');
			menuToggleIcon.classList.add('fa-toggle-off');
			localStorage.setItem('atum-sidebar', 'closed');
		}
	}

	// If the sidebar doesn't exist, for example, on edit views, then remove the "closed" class
	if (!sidebar) {
		wrapper.classList.remove('closed');
	}

	if (sidebar && !sidebar.getAttribute('data-hidden')) {
		// Sidebar
		var menuToggle = document.getElementById('menu-collapse');


		var menuClose = function() {
			sidebar.querySelector('.collapse').classList.remove('in');
			sidebar.querySelector('.collapse-arrow').classList.add('collapsed');
		};

		// Toggle menu
		menuToggle.addEventListener('click', function(e) {
			wrapper.classList.toggle('closed');
			menuToggleIcon.classList.toggle('fa-toggle-on');
			menuToggleIcon.classList.toggle('fa-toggle-off');

			// Save the sidebar state
			if (Joomla.localStorageEnabled()) {
				if (wrapper.classList.contains('closed')) {
					localStorage.setItem('atum-sidebar', 'closed');
				} else {
					localStorage.setItem('atum-sidebar', 'open');
				}
			}
		});


	} else {
		if (sidebar) {
			sidebar.style.display = 'none';
			sidebar.style.width = 0;
		}

		var wrapperClass = document.getElementsByClassName('wrapper');
		if (wrapperClass.length) {
			wrapperClass[0].style.paddingLeft = 0;
		}
	}

})(Joomla, document);
