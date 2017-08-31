/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

Joomla = window.Joomla || {};

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		/** http://stackoverflow.com/questions/18663941/finding-closest-element-without-jquery */
		function closest(el, selector) {
			var matchesFn;

			// find vendor prefix
			['matches', 'msMatchesSelector'].some(function(fn) {
				if (typeof document.body[fn] == 'function') {
					matchesFn = fn;
					return true;
				}
				return false;
			})

			var parent;

			// traverse parents
			while (el) {
				parent = el.parentElement;
				if (parent && parent[matchesFn](selector)) {
					return parent;
				}
				el = parent;
			}

			return null;
		}

		var wrapper = document.getElementById('wrapper'),
		    sidebar = document.getElementById('sidebar-wrapper');

		// Set the initial state of the sidebar based on the localStorage value
		if (Joomla.localStorageEnabled()) {
			var sidebarState = localStorage.getItem('atum-sidebar');
			if (sidebarState === 'open' || sidebarState === null) {
				wrapper.classList.remove('closed');
				localStorage.setItem('atum-sidebar', 'open');
			} else {
				wrapper.classList.add('closed');
				localStorage.setItem('atum-sidebar', 'closed');
			}
		}

		// If the sidebar doesn't exist, for example, on edit views, then remove the "closed" class
		if (!sidebar)
		{
			wrapper.classList.remove('closed');
		}

		// Fix toolbar and footer width for edit views
		if (wrapper.classList.contains('wrapper0')) {
			if (document.querySelector('.subhead')) {
				document.querySelector('.subhead').style.left = 0;
			}

			if (document.getElementById('status')) {
				document.getElementById('status').style.marginLeft = 0;
			}
		}

		if (sidebar && !sidebar.getAttribute('data-hidden')) {
			/** Sidebar */
			var menuToggle = document.getElementById('menu-collapse'),
			    first      = sidebar.querySelectorAll('.collapse-level-1');

			// Apply 2nd level collapse
			for (var i = 0; i < first.length; i++) {
				var second = first[i].querySelectorAll('.collapse-level-1');
				for (var j = 0; j < second.length; j++) {
					if (second[j]) {
						second[j].classList.remove('collapse-level-1');
						second[j].classList.add('collapse-level-2');
					}
				}
			}

			var menuClose = function() {
				sidebar.querySelector('.collapse').classList.remove('in');
				sidebar.querySelector('.collapse-arrow').classList.add('collapsed');
			};

			// Toggle menu
			menuToggle.addEventListener('click', function(e) {
				wrapper.classList.toggle('closed');

				var listItems = document.querySelectorAll('.main-nav li');
				for (var i = 0; i < listItems.length; i++) {
				 	listItems[i].classList.remove('open');
				}

				var elem = document.querySelector('.child-open');
				if (elem) {
					elem.classList.remove('child-open');
				}
				
				// Save the sidebar state
				if (Joomla.localStorageEnabled()) {
					if (wrapper.classList.contains('closed')) {
						localStorage.setItem('atum-sidebar', 'closed');
					} else {
						localStorage.setItem('atum-sidebar', 'open');
					}
				}
			});
			

			/**
			 * Sidebar Nav
			 */
			var allLinks     = wrapper.querySelectorAll('a.no-dropdown, a.collapse-arrow'),
			    currentUrl   = window.location.href.toLowerCase(),
			    mainNav      = document.getElementById('menu'),
		 	    menuParents  = mainNav.querySelectorAll('li.parent > a'),
			    subMenuClose = mainNav.querySelectorAll('li.parent .close');

			// Set active class
			for (var i = 0; i < allLinks.length; i++) {
				if (currentUrl === allLinks[i].href) {
					allLinks[i].classList.add('active');
					// Auto Expand First Level
					if (!allLinks[i].parentNode.classList.contains('parent')) {
						mainNav.classList.add('child-open');
						var firstLevel = closest(allLinks[i], '.collapse-level-1');
    						if (firstLevel) firstLevel.parentNode.classList.add('open');
					}
				}
			}

			// If com_cpanel or com_media - close menu
			if (document.body.classList.contains('com_cpanel') || document.body.classList.contains('com_media')) {
			    var menuChildOpen = mainNav.querySelectorAll('.open');

				for (var i = 0; i < menuChildOpen.length; i++) {
					menuChildOpen[i].classList.remove('open');
				}
				mainNav.classList.remove('child-open');
			}
			
			// Child open toggle
			var openToggle = function() {
				var menuItem = this.parentNode;

				if (menuItem.classList.contains('open')) {
					menuItem.classList.remove('open');
					mainNav.classList.remove('child-open');
				}
				else {
					var siblings = menuItem.parentNode.children;
					for (var i = 0; i < siblings.length; i++) {
					 	siblings[i].classList.remove('open');
					}
					wrapper.classList.remove('closed');
					menuItem.classList.add('open');
					mainNav.classList.add('child-open');
				}
			};

			for (var i = 0; i < menuParents.length; i += 1) {
			 	menuParents[i].addEventListener('click', openToggle);
			 	menuParents[i].addEventListener('keyup', openToggle);
			}

			// Menu close 
			for(var i=0;i<subMenuClose.length;i++){
				subMenuClose[i].addEventListener('click', function(e) {
					var menuChildOpen = mainNav.querySelectorAll('.open');

					for (var i = 0; i < menuChildOpen.length; i++) {
						menuChildOpen[i].classList.remove('open');
					}
					mainNav.classList.remove('child-open');	
				});
			}

			/** Accessibility */
			var allLiEl = sidebar.querySelectorAll('ul[role="menubar"] li');
			for (var i = 0; i < allLiEl.length; i++) {
				// We care for enter and space
				allLiEl[i].addEventListener('keyup', function(e) { if (e.keyCode == 32 || e.keyCode == 13 ) e.target.querySelector('a').click(); });
			}

			// Set the height of the menu to prevent overlapping
			var setMenuHeight = function() {
				var height = document.getElementById('header').offsetHeight + document.getElementById('main-brand').offsetHeight;
				document.getElementById('menu').height = window.height - height ;
			};

			setMenuHeight();

			// Remove 'closed' class on resize
			window.addEventListener('resize', function() {
				setMenuHeight();
			});

			if (Joomla.localStorageEnabled()) {
				if (localStorage.getItem('adminMenuState') == 'true') {
					menuClose();
				}
			}

		} else {
			if (sidebar) {
				sidebar.style.display = 'none';
				sidebar.style.width = 0;
			}

			if (document.getElementsByClassName('wrapper').length) {
				document.getElementsByClassName('wrapper')[0].style.paddingLeft = '0';
			}
		}



		/**
		 * Turn radios into btn-group
		 */
		var container = document.querySelectorAll('.btn-group');
		for (var i = 0; i < container.length; i++) {
			var labels = container[i].querySelectorAll('label');
			for (var j = 0; j < labels.length; j++) {
				labels[j].classList.add('btn');
				if ((j % 2) == 1) {
					labels[j].classList.add('btn-outline-danger');
				} else {
					labels[j].classList.add('btn-outline-success');

				}
			}
		}

		var btnNotActive = document.querySelector('.btn-group label:not(.active)');
		if (btnNotActive) {
			btnNotActive.addEventListener('click', function(event) {
				var input = document.getElementById(event.target.getAttribute('for'));

				if (input.getAttribute('checked') !== 'checked') {
					var label = closest(event.target, '.btn-group').querySelector('label');
					label.classList.remove('active');
					label.classList.remove('btn-success');
					label.classList.remove('btn-danger');
					label.classList.remove('btn-primary');

					if (closest(label, '.btn-group').classList.contains('btn-group-reversed')) {
						if (!label.classList.contains('btn')) label.classList.add('btn');
						if (input.value === '') {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-primary');
						} else if (input.value === 0) {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-success');
						} else {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-danger');
						}
					} else {
						if (input.value === '') {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-primary');
						} else if (input.value === 0) {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-danger');
						} else {
							label.classList.add('active');
							label.classList.add('btn');
							label.classList.add('btn-outline-success');
						}
					}
					input.setAttribute('checked', true);
					//input.dispatchEvent('change');
				}
			});
		}

		var btsGrouped = document.querySelectorAll('.btn-group input[checked=checked]');
		for (var i = 0, l = btsGrouped.length; l>i; i++) {
			var self   = btsGrouped[i],
			    attrId = self.id,
			    label = document.querySelector('label[for=' + attrId + ']');
			if (self.parentNode.parentNode.classList.contains('btn-group-reversed')) {
				if (self.value === '') {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-primary');
				} else if (self.value === 0) {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-success');
				} else {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-danger');
				}
			} else {
				if (self.value === '') {
					label.classList.add('active');
					label.classList.add('btn-outline-primary');
				} else if (self.value === 0) {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-danger');
				} else {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-success');
				}
			}
		}

		/**
		 * Sticky Toolbar
		 */
		var navTop;
		var isFixed = false;

		processScrollInit();
		processScroll();

		document.addEventListener('resize', processScrollInit, false);
		document.addEventListener('scroll', processScroll);

		function processScrollInit() {
			var subhead = document.getElementById('subhead');

			if (subhead) {
				navTop = document.querySelector('.subhead').offsetHeight;

				if (document.getElementById('sidebar-wrapper') && document.getElementById('sidebar-wrapper').style.display === 'none') {
					subhead.style.left = 0;
				}

				// Only apply the scrollspy when the toolbar is not collapsed
				if (document.body.clientWidth > 480) {
					document.querySelector('.subhead-collapse').style.height = document.querySelector('.subhead').style.height;
					subhead.style.width = 'auto';
				}
			}
		}

		function processScroll() {
			var subhead = document.getElementById('subhead');

			if (subhead) {
				var scrollTop = (window.pageYOffset || subhead.scrollTop)  - (subhead.clientTop || 0);

				if (scrollTop >= navTop && !isFixed) {
					isFixed = true;
					subhead.classList.add('subhead-fixed');

					if (document.getElementById('sidebar-wrapper') && document.getElementById('sidebar-wrapper').style.display === 'none') {
						subhead.style.left = 0;
					}
				} else if (scrollTop <= navTop && isFixed) {
					isFixed = false;
					subhead.classList.remove('subhead-fixed');
				}
			}
		}
	});
})();
