(function () {
	const css = `joomla-tab{display:flex;flex-direction:column}joomla-tab>ul{display:flex;background-color:#f5f5f5;border-color:#ccc #ccc currentcolor;border-image:none;border-radius:.25rem .25rem 0 0;border-style:solid solid none;border-width:1px 1px 0;box-shadow:0 1px #fff inset,0 2px 3px -3px rgba(0,0,0,.15),0 -4px 0 rgba(0,0,0,.05) inset,0 0 3px rgba(0,0,0,.04);margin:0;padding:0;list-style:outside none none;overflow-x:auto;overflow-y:hidden;white-space:nowrap}joomla-tab>ul a{display:block;color:#0d1321;padding:.75em 1em;position:relative;box-shadow:1px 0 0 rgba(0,0,0,.05);text-decoration:none}joomla-tab>ul a[active]{background-color:rgba(0,0,0,.03);background-image:linear-gradient(to bottom,transparent,rgba(0,0,0,.05) 100%);border-left:0 none;border-right:0 none;border-top-left-radius:0;border-top-right-radius:0;box-shadow:2px 0 1px -1px rgba(0,0,0,.08) inset,-2px 0 1px -1px rgba(0,0,0,.08) inset,0 1px 0 rgba(0,0,0,.02) inset}joomla-tab>ul a[active]:after{background-color:#006898;bottom:-1px;content:"";height:5px;left:0;opacity:.8;position:absolute;right:0}joomla-tab>section{display:none;background-color:#fefefe;border:1px solid #ccc;border-radius:0 0 .25rem .25rem;box-shadow:0 0 3px rgba(0,0,0,.04);padding:15px}joomla-tab>section[active]{display:block}joomla-tab[orientation=vertical]{flex-direction:row;align-items:flex-start}joomla-tab[orientation=vertical]>ul{flex-direction:column;min-width:30%;height:auto;border:1px solid #ccc;border-radius:.25rem;box-shadow:none;overflow:hidden}joomla-tab[orientation=vertical] li:last-of-type a{border-bottom:0}joomla-tab[orientation=vertical] a{display:block;color:#0d1321;padding:.75em 1em;position:relative;border-bottom:1px solid #ddd;box-shadow:none;text-decoration:none}joomla-tab[orientation=vertical] a[active]{border-left:0 none;border-right:0 none;background-color:#fff;background-image:none;box-shadow:none}joomla-tab[orientation=vertical] a[active]:after{left:-1px;width:5px;height:auto;top:0;bottom:0}joomla-tab[orientation=vertical]>section{border:0 none;box-shadow:none;padding:0 15px}`;
	if (!document.getElementById('joomla-tab-stylesheet')) {
		const style = document.createElement('style');
		style.id = 'joomla-tab-stylesheet';
		style.innerText = css;
		document.head.appendChild(style);
	}
})();

class TabElement extends HTMLElement {
	/* Attributes to monitor */
	static get observedAttributes() { return ['recall', 'orientation']; }
	get recall() { return this.getAttribute('recall'); }
	get orientation() { return this.getAttribute('orientation'); }
	set orientation(value) { this.setAttribute('orientation', value); }


	/* Lifecycle, element created */
	constructor() {
		super();

		this.hasActive = false;
		this.currentActive = '';
	}

	/* Lifecycle, element appended to the DOM */
	connectedCallback() {
		if (!this.orientation || (this.orientation && ['horizontal', 'vertical'].indexOf(this.orientation) === -1)) {
			this.orientation = 'horizontal';
		}

		// get tab elements
		const tabs = [].slice.call(this.querySelectorAll('section'));

		// Sanity check
		if (!tabs) {
			return;
		}

		let tabsEl = [];
		// remove the cascaded tabs
		for (let i = 0, l = tabs.length; i < l; ++i) {
			var child = tabs[i];
			if (child.parentNode === this) {
				tabsEl.push(child);
			}
		}

		// Create the navigation
		this.createNavigation(tabsEl)

		// Add missing role
		tabsEl.forEach((tab) => {
			tab.setAttribute('role', 'tabpanel')
			if (tab.hasAttribute('active')) {
				this.hasActive = true;
				this.currentActive = tab.id
				this.querySelector('#tab-' + tab.id).setAttribute('aria-selected', 'true');
				this.querySelector('#tab-' + tab.id).setAttribute('active', '');
				this.querySelector('#tab-' + tab.id).setAttribute('tabindex', '0');
				return;
			}
		});

		// Fallback if no active tab
		if (!this.hasActive) {
			tabsEl[0].setAttribute('active', '');
			this.hasActive = true;
			this.currentActive = tabsEl[0].id;
			this.querySelector('#tab-' + tabsEl[0].id).setAttribute('aria-selected', 'true');
			this.querySelector('#tab-' + tabsEl[0].id).setAttribute('tabindex', '0');
			this.querySelector('#tab-' + tabsEl[0].id).setAttribute('active', '');


		}

		// Keyboard access
		this.keyListeners(tabsEl)

		// Check if there is a hash in the URI
		// if (location.href.matches(/#\S[^\&]*/)) {
		// 	const hash = location.href.matches(/#\S[^\&]*/);
		// 	const element = this.querySelector(hash);
		// 	// Activate any parent tabs (nested tables)
		// 	// joomla-tabs > ul > li > a
		// 	let link = element.parentNode.parentNode;
		// 	while (link.parentNode.tagName.toLowerCase() === 'joomla-tabs') {
		// 			this.showTab(link.parentNode.parentNode);
		// 			link = this.querySelector('#tab-' + link.parentNode.parentNode.id);
		// 	}

		// 	// Activate the given tab
		// 	this.querySelector('#tab-' + hash.substring(1)).click()
		// }

		// Use the sessionStorage state!
		if (this.hasAttribute('recall')) {
			this.restoreState();
		}
	}

	/* Lifecycle, element removed from the DOM */
	disconnectedCallback() {
		const ulEl = this.querySelector('ul');
		const navigation = [].slice.call(ulEl.querySelectorAll('a'));

		navigation.forEach(function (link) {
			link.removeEventListener('click', this);
		});
		ulEl.removeEventListener('keydown', this);
	}

	/* Method to create the tabs navigation */
	createNavigation(tabs) {
		const nav = document.createElement('ul');
		nav.setAttribute('role', 'tablist');

		/** Activate Tab */
		let activateTabFromLink = (e) => {
			e.preventDefault();

			if (this.hasActive) {
				this.hideCurrent()
			}

			const currentTabLink = this.currentActive;

			// Set the selected tab as active
			// Emit show event
			this.dispatchCustomEvent('joomla.tab.show', e.target, this.querySelector('#tab-' + currentTabLink));
			e.target.setAttribute('active', '');
			e.target.setAttribute('aria-selected', 'true');
			e.target.setAttribute('tabindex', '0');
			this.querySelector(e.target.hash).setAttribute('active', '');
			this.querySelector(e.target.hash).removeAttribute('aria-hidden');
			this.currentActive = e.target.hash.substring(1)
			// Emit shown event
			this.dispatchCustomEvent('joomla.tab.shown', e.target, this.querySelector('#tab-' + currentTabLink));
		}

		tabs.forEach((tab) => {
			if (!tab.id) {
				return;
			}

			const active = tab.hasAttribute('active');
			const liElement = document.createElement('li');
			const aElement = document.createElement('a');

			liElement.setAttribute('role', 'presentation');
			aElement.setAttribute('role', 'tab');
			aElement.setAttribute('aria-controls', tab.id);
			aElement.setAttribute('aria-selected', active ? 'true' : 'false');
			aElement.setAttribute('tabindex', active ? '0' : '-1');
			aElement.setAttribute('href', '#' + tab.id);
			aElement.setAttribute('id', 'tab-' + tab.id);
			aElement.innerHTML = tab.getAttribute('name')

			if (active) {
				aElement.setAttribute('active', '');
			}

			aElement.addEventListener('click', activateTabFromLink);

			liElement.append(aElement);
			nav.append(liElement)

			// aElement.addEventListener('joomla.tab.show', function (e) { console.log('show', e) });
			// aElement.addEventListener('joomla.tab.shown', function (e) { console.log('shown', e) });
			// aElement.addEventListener('joomla.tab.hide', function (e) { console.log('hide', e) });
			// aElement.addEventListener('joomla.tab.hidden', function (e) { console.log('hidden', e) });

			tab.setAttribute('aria-labelledby', 'tab-' + tab.id);
			if (!active) {
				tab.setAttribute('aria-hidden', 'true');
			}
		});

		this.insertAdjacentElement('afterbegin', nav);
	}

	hideCurrent() {
		// Unset the current active tab
		if (this.currentActive) {
			// Emit hide event
			const el = this.querySelector('a[aria-controls="' + this.currentActive + '"]');
			this.dispatchCustomEvent('joomla.tab.hide', el, this.querySelector('#tab-' + this.currentActive));
			el.removeAttribute('active');
			el.setAttribute('tabindex', '-1');
			this.querySelector('#' + this.currentActive).removeAttribute('active');
			this.querySelector('#' + this.currentActive).setAttribute('aria-hidden', 'true');
			el.removeAttribute('aria-selected');
			// Emit hidden event
			this.dispatchCustomEvent('joomla.tab.hidden', el, this.querySelector('#tab-' + this.currentActive));
		}
	}

	showTab(tab) {
		const tabLink = querySelector('#tab-' + tab.id)
		tabLink.click();
	}

	show(ulLink) {
		ulLink.click();
	}

	keyListeners() {
		const keyBehaviour = (e) => {
			// collect tab targets, and their parents' prev/next (or first/last)
			let currentTab = this.querySelector('#tab-' + this.currentActive);
			let tablist = [].slice.call(this.querySelector('ul').querySelectorAll('a'));
			let previousTabItem = currentTab.parentNode.previousElementSibling || tablist[tablist.length - 1];
			let nextTabItem = currentTab.parentNode.nextElementSibling || tablist[0];

			// don't catch key events when ⌘ or Alt modifier is present
			if (e.metaKey || e.altKey) return;

			// catch left/right and up/down arrow key events
			switch (e.keyCode) {
				case 37:
				case 38:
					if (previousTabItem.tagName.toLowerCase() !== 'li') {
						previousTabItem.click();
						previousTabItem.focus();
					} else {
						previousTabItem.querySelector('a').click();
						previousTabItem.querySelector('a').focus();
					}

					e.preventDefault();
					break;
				case 39:
				case 40:
					if (nextTabItem.tagName.toLowerCase() === 'a') {
						nextTabItem.click();
						nextTabItem.focus();
					} else {
						nextTabItem.querySelector('a').click();
						nextTabItem.querySelector('a').focus();
					}

					e.preventDefault();
					break;
				default:
					break;
			}
		}
		this.querySelector('ul').addEventListener('keydown', keyBehaviour)
	}

	restoreState() {

	}

	/* Method to dispatch events */
	dispatchCustomEvent(eventName, element, related) {
		let OriginalCustomEvent = new CustomEvent(eventName);
		OriginalCustomEvent.relatedTarget = related;
		element.dispatchEvent(OriginalCustomEvent);
		element.removeEventListener(eventName, element);
	}
}

customElements.define('joomla-tab', TabElement);
