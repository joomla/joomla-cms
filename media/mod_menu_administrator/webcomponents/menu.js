class JoomlaAdminMenu extends HTMLElement {
	constructor() {
		super();

		/** Initialize some variables */
		this.wrapper = document.getElementById('wrapper');
		this.sidebar = document.getElementById('sidebar-wrapper');
		this.allLinks = '';
		this.currentUrl = '';
		this.mainNav = '';
		this.menuParents = '';
		this.subMenuClose = '';
		this.menuToggle = '';
		this.first = '';
	}

	connectedCallback() {
		if (!this.wrapper || !this.sidebar) {
			throw new Error('HTML markup error initialising menu')
		}

		/** Save context */
		const self = this;

		/** Set the initial state of the sidebar based on the localStorage value */
		if (Joomla.localStorageEnabled()) {
			const sidebarState = localStorage.getItem('atum-sidebar');
			if (sidebarState === 'open' || sidebarState === null) {
				this.wrapper.classList.remove('closed');
				localStorage.setItem('atum-sidebar', 'open');
			} else {
				this.wrapper.classList.add('closed');
				localStorage.setItem('atum-sidebar', 'closed');
			}
		}

		/** If the sidebar doesn't exist, for example, on edit views, then remove the "closed" class */
		if (!this.sidebar) {
			this.wrapper.classList.remove('closed');
		}

		/** Fix toolbar and footer width for edit views */
		if (this.wrapper.classList.contains('wrapper0')) {
			if (document.querySelector('.subhead')) {
				document.querySelector('.subhead').style.left = 0;
			}

			if (document.getElementById('status')) {
				document.getElementById('status').style.marginLeft = 0;
			}
		}

		/** Sidebar Nav */
		this.allLinks = [].slice.call(this.wrapper.querySelectorAll('a.no-dropdown, a.collapse-arrow'));
		this.currentUrl = window.location.href.toLowerCase();
		this.mainNav = document.getElementById('menu');
		this.menuParents = [].slice.call(this.mainNav.querySelectorAll('li.parent > a'));
		this.subMenuClose = [].slice.call(this.mainNav.querySelectorAll('li.parent .close'));

		/** Sidebar */
		this.menuToggle = document.getElementById('menu-collapse');
		this.first = [].slice.call(this.sidebar.querySelectorAll('.collapse-level-1'));

		/** Apply 2nd level collapse */
		this.first.forEach((item) => {
			const second = [].slice.call(item.querySelectorAll('.collapse-level-1'));

			second.forEach((itemEl) => {
				if (itemEl) {
					itemEl.classList.remove('collapse-level-1');
					itemEl.classList.add('collapse-level-2');
				}
			});
		});

		/** Toggle menu */
		this.menuToggle.addEventListener('click', (e) => {
			self.wrapper.classList.toggle('closed');

			const listItems = [].slice.call(document.querySelectorAll('.main-nav > li'));
			listItems.forEach((listItem) => {
				listItem.classList.remove('open');
			});

			const elem = document.querySelector('.child-open');
			if (elem) {
				elem.classList.remove('child-open');
			}

			// Save the sidebar state
			if (Joomla.localStorageEnabled()) {
				if (self.wrapper.classList.contains('closed')) {
					localStorage.setItem('atum-sidebar', 'closed');
				} else {
					localStorage.setItem('atum-sidebar', 'open');
				}
			}
		});

		/** Set active class */
		this.allLinks.forEach((link) => {
			if (self.currentUrl === link.href) {
				link.classList.add('active');
				/** Auto Expand First Level */
				if (!link.parentNode.classList.contains('parent')) {
					self.mainNav.classList.add('child-open');
					const firstLevel = self.closest(link, '.collapse-level-1');
					if (firstLevel) {
						firstLevel.parentNode.classList.add('open');
					}
				}
			}
		});

		/** If com_cpanel or com_media - close menu */
		if (document.body.classList.contains('com_cpanel') || document.body.classList.contains('com_media')) {
			const menuChildsOpen = [].slice.call(this.mainNav.querySelectorAll('.open'));

			menuChildsOpen.forEach((menuChildOpen) => {
				menuChildOpen.classList.remove('open');
			});
			this.mainNav.classList.remove('child-open');
		}

		this.menuParents.forEach((item) => {
			item.addEventListener('click', self.openToggle.bind(self));
		});

		/** Menu close */
		this.subMenuClose.forEach((subMenuCloseEl) => {
			subMenuCloseEl.addEventListener('click', () => {
				const menuChildsOpen = self.mainNav.querySelectorAll('.open');

				menuChildsOpen.forEach((menuChildOpen) => {
					menuChildOpen.classList.remove('open');
				});
				self.mainNav.classList.remove('child-open');
			});
		});

		this.setMenuHeight();

		/** Remove 'closed' class on resize */
		window.addEventListener('resize', self.setMenuHeight.bind(self));

		if (Joomla.localStorageEnabled()) {
			if (localStorage.getItem('adminMenuState') == 'true') {
				self.menuClose.bind(self);
			}
		}
	}

	/** http://stackoverflow.com/questions/18663941/finding-closest-element-without-jquery */
	closest(el, selector) {
		let matchesFn;

		/** find vendor prefix */
		['matches', 'msMatchesSelector'].some(function(fn) {
			if (typeof document.body[fn] == 'function') {
				matchesFn = fn;
				return true;
			}
			return false;
		})

		let parent;

		/** traverse parents */
		while (el) {
			parent = el.parentElement;
			if (parent && parent[matchesFn](selector)) {
				return parent;
			}
			el = parent;
		}

		return null;
	}

	/** Set the height of the menu to prevent overlapping */
	setMenuHeight() {
		const height = document.getElementById('header').offsetHeight + document.getElementById('main-brand').offsetHeight;
		document.getElementById('menu').height = window.height - height;
	}

	/** Child open toggle */
	openToggle(e) {
		const menuItem = this.findAncestor(e.target, 'li');

		if (menuItem.classList.contains('open')) {
			this.mainNav.classList.remove('child-open');
			menuItem.classList.remove('open');
		}
		else {
			const siblings = [].slice.call(menuItem.parentNode.children);
			siblings.forEach((sibling) => {
				sibling.classList.remove('open');
			});
			this.wrapper.classList.remove('closed');
			this.mainNav.classList.add('child-open');
			if (menuItem.parentNode.classList.contains('main-nav')) {
				menuItem.classList.add('open');
			}
		}
	}

	/** Closes the menu */
	menuClose() {
		this.sidebar.querySelector('.collapse').classList.remove('in');
		this.sidebar.querySelector('.collapse-arrow').classList.add('collapsed');
	}

	/** Find ancestor by tag name */
	findAncestor(el, tagName) {
		while ((el = el.parentElement) && el.nodeName.toLowerCase() !== tagName);
		return el;
	}
}

customElements.define('joomla-admin-menu', JoomlaAdminMenu);
