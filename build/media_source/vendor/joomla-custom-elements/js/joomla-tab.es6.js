// eslint-disable-next-line max-classes-per-file
// class JoomlaDropDownElement extends HTMLElement {
//   constructor() {
//     super();
//     this.button = document.createElement('button');
//   }
//   connectedCallback() {
//     /**
//       <button aria-haspopup="listbox" aria-label="Choose a tab:"> Neptunium </button>
//       <ul tabindex="-1" role="listbox" aria-label="Choose a tab:" class="hidden">
//         <li role="option"> Neptunium </li>
//         <li role="option"> Plutonium </li>
//       </ul>
//      **/
//   }
// }
// customElements.define('joomla-tab-content', JoomlaDropDownElement);

// eslint-disable-next-line max-classes-per-file
class JoomlaTabContentElement extends HTMLElement {}
customElements.define('joomla-tab-content', JoomlaTabContentElement);

class JoomlaTabsElement extends HTMLElement {
  /* Attributes to monitor */
  static get observedAttributes() { return ['recall', 'orientation']; }

  get recall() { return this.getAttribute('recall'); }

  get orientation() { return this.getAttribute('orientation'); }

  set orientation(value) { this.setAttribute('orientation', value); }

  static getStorageKey() {
    return window.location.pathname.replace(/&return=[a-zA-Z0-9%]+/, '');
  }

  /* Lifecycle, element created */
  constructor() {
    super();

    this.currentActive = '';
    this.hasActive = false;
    this.hasNested = false;
    this.isNested = false;
    this.tabs = [];
    this.tabElements = [];
  }

  /* Lifecycle, element appended to the DOM */
  connectedCallback() {
    if (!this.orientation || (!['horizontal', 'vertical'].includes(this.orientation))) {
      this.orientation = 'horizontal';
    }

    // Do we have SSR links?
    const linksContainer = this.firstElementChild;
    if (linksContainer && linksContainer.tagName === 'DIV'
      && linksContainer.hasAttribute('role')
      && linksContainer.getAttribute('role') === 'tablist') {
      [].slice.call(linksContainer.querySelectorAll('a')).map((link) => this.addInteractivity(link));
    } else {
      // eslint-disable-next-line max-len
      // <a href="#first-tab" id="first-tab-btn" role="tab" aria-controls="first-tab" aria-selected="true">First tab</a>
      this.linksContainer = document.createElement('div');
      this.linksContainer.setAttribute('role', 'tablist');
      if (this.hasAttribute('label')) {
        this.linksContainer.setAttribute('aria-label', this.getAttribute('label') || '');
      }

      this.tabElements = Array.from(this.children)
        .filter((element) => element.tagName.toLowerCase() === 'joomla-tab-content');

      console.log(this.tabElements);
      this.tabElements.forEach((tab) => {
        // eslint-disable-next-line max-len
        // <joomla-tab-content id="first-tab" role="tabpanel" aria-labelledby="first-tab-btn"></joomla-tab-content>

        this.tabs.push(`#${tab.id}`);

        tab.setAttribute('role', 'tabpanel');
        tab.setAttribute('aria-labelledby', `${tab.id}-btn`);
        const link = document.createElement('a');
        link.setAttribute('href', `#${tab.id}`);
        link.setAttribute('id', `#${tab.id}-btn`);
        link.setAttribute('role', 'tab');
        link.setAttribute('aria-controls', `${tab.id}`);

        if (tab.hasAttribute('active')) {
          this.hasActive = true;
          this.currentActive = tab.id;
          link.setAttribute('aria-selected', 'true');
          link.setAttribute('active', '');
          link.setAttribute('tabindex', '0');
        }

        link.innerText = tab.getAttribute('name');
        this.linksContainer.appendChild(link);
        // this.tabs.push({ [`${tab.id}`]: tab, [`${tab.id}-btn`]: link });
      });
    }

    this.insertAdjacentElement('afterbegin', this.linksContainer);

    this.tabElements.map((tab) => this.addInteractivity(tab));

    // Keyboard access
    this.addKeyListeners();

    if (this.closest('joomla-tabs')) {
      this.isNested = true;
    }

    if (this.querySelector('joomla-tabs')) {
      this.hasNested = true;
    }

    // // Use the sessionStorage state!
    // if (this.hasAttribute('recall')) {
    //   const href = sessionStorage.getItem(this.getStorageKey());
    //   if (href) {
    //     tabLinkHash.push(href);
    //   }
    // }
    //
    // if (this.hasNested) {
    //   // @todo use the recall attribute
    //   const href = sessionStorage.getItem(this.getStorageKey());
    //   if (href) {
    //     tabLinkHash.push(href);
    //   }
    //   // @todo end
    //
    //   // Add possible parent tab to the aray for activation
    //   if (tabLinkHash.length && tabLinkHash[0] !== '') {
    //     const hash = tabLinkHash[0].substring(5);
    //     const element = this.querySelector(`#${hash}`);
    //
    //     // Add the parent tab to the array for activation
    //     if (element) {
    //       const currentTabSet = element.closest('joomla-tab');
    //
    //       if (this.isNested) {
    //         const parentTab = currentTabSet.closest('section');
    //
    //         if (parentTab) {
    //           tabLinkHash.push(`#tab-${parentTab.id}`);
    //         }
    //       }
    //     }
    //   }
    //
    //   // remove the cascaded tabs and activate the right tab
    //   tabs.forEach((tab) => {
    //     if (tabLinkHash.length) {
    //       const theId = `#tab-${tab.id}`;
    //
    //       if (tabLinkHash.indexOf(theId) === -1) {
    //         tab.removeAttribute('active');
    //       } else {
    //         tab.setAttribute('active', '');
    //       }
    //     }
    //
    //     if (tab.parentNode === self) {
    //       tabsEl.push(tab);
    //     }
    //   });
    // } else {
    //   // Activate the correct tab
    //   tabs.forEach((tab) => {
    //     if (tabLinkHash.length) {
    //       const theId = `#tab-${tab.id}`;
    //       if (tabLinkHash.indexOf(theId) === -1) {
    //         tab.removeAttribute('active');
    //       } else {
    //         tab.setAttribute('active', '');
    //       }
    //     }
    //   });
    //
    //   tabsEl = tabs;
    // }
    //
    // Add missing role
    // this.tabElements.forEach((tab) => {
    //   tab.setAttribute('role', 'tabpanel');
    //   this.tabs.push(`#tab-${tab.id}`);
    //   if (tab.hasAttribute('active')) {
    //     this.hasActive = true;
    //     this.currentActive = tab.id;
    //     this.querySelector(`#tab-${tab.id}`).setAttribute('aria-selected', 'true');
    //     this.querySelector(`#tab-${tab.id}`).setAttribute('active', '');
    //     this.querySelector(`#tab-${tab.id}`).setAttribute('tabindex', '0');
    //   }
    // });
    //
    // // Fallback if no active tab
    // if (!this.hasActive) {
    //   tabsEl[0].setAttribute('active', '');
    //   tabsEl[0].removeAttribute('aria-hidden');
    //   this.hasActive = true;
    //   this.currentActive = tabsEl[0].id;
    //   this.querySelector(`#tab-${tabsEl[0].id}`).setAttribute('aria-selected', 'true');
    //   this.querySelector(`#tab-${tabsEl[0].id}`).setAttribute('tabindex', '0');
    //   this.querySelector(`#tab-${tabsEl[0].id}`).setAttribute('active', '');
    // }
    //
    // // Check if there is a hash in the URI
    // if (window.location.href.match(/#\S[^&]*/)) {
    //   const hash = window.location.href.match(/#\S[^&]*/);
    //   const element = this.querySelector(hash[0]);
    //
    //   if (element) {
    //     // Activate any parent tabs (nested tables)
    //     const currentTabSet = element.closest('joomla-tab');
    //
    //     if (this.isNested) {
    //       const parentTabSet = currentTabSet.closest('joomla-tab');
    //       const parentTab = currentTabSet.closest('section');
    //       parentTabSet.showTab(parentTab);
    //       // Now activate the given tab
    //       this.show(element);
    //     } else {
    //       // Now activate the given tab
    //       this.showTab(element);
    //     }
    //   }
    // }
  }

  /* Lifecycle, element removed from the DOM */
  disconnectedCallback() {
    this.tabs.forEach((obj) => {
      Object.keys(obj).forEach((key) => {
        obj[key].removeEventListener('click', this);
      });
    });

    this.linksContainer.removeEventListener('keydown', this);
  }

  addInteractivity(tab) {
    if (this.hasActive && this.currentActive) {
      // this.hideCurrent();
    }

    const linkEl = this.querySelector(`#${tab.id}-btn`);

    if (linkEl) {
      linkEl.setAttribute('aria-selected', 'true');
      tab.setAttribute('aria-selected', 'true');
      tab.setAttribute('active', 'true');
    }

    // this.currentActive = link;
    // <a href="#first-tab" id="first-tab-btn" role="tab" aria-controls="first-tab" aria-selected="true">First tab</a>
    // link.
  }
  // /* Method to create the tabs navigation */
  // createNavigation(tabs) {
  //   if (this.firstElementChild.nodeName.toLowerCase() === 'ul') {
  //     return;
  //   }
  //
  //   const nav = document.createElement('ul');
  //   nav.setAttribute('role', 'tablist');
  //
  //   /** Activate Tab */
  //   const activateTabFromLink = (e) => {
  //     e.preventDefault();
  //
  //     // Doing toggle for accordion
  //     const justHide = this.view === 'accordion' && e.target.hasAttribute('active');
  //
  //     if (this.hasActive) {
  //       this.hideCurrent();
  //
  //       if (justHide) {
  //         this.hasActive = false;
  //         return;
  //       }
  //     }
  //
  //     const currentTabLink = this.currentActive;
  //
  //     // Set the selected tab as active
  //     // Emit show event
  //     this.dispatchCustomEvent('joomla.tab.show', e.target, this.querySelector(`#tab-${currentTabLink}`));
  //     e.target.setAttribute('active', '');
  //     e.target.setAttribute('aria-selected', 'true');
  //     e.target.setAttribute('tabindex', '0');
  //     this.querySelector(e.target.hash).setAttribute('active', '');
  //     this.querySelector(e.target.hash).removeAttribute('aria-hidden');
  //     this.currentActive = e.target.hash.substring(1);
  //     // Emit shown event
  //     this.dispatchCustomEvent('joomla.tab.shown', e.target, this.querySelector(`#tab-${currentTabLink}`));
  //     this.saveState(`#tab-${e.target.hash.substring(1)}`);
  //     this.hasActive = true;
  //   };
  //
  //   tabs.forEach((tab) => {
  //     if (!tab.id) {
  //       return;
  //     }
  //
  //     const active = tab.hasAttribute('active');
  //     const liElement = document.createElement('li');
  //     const aElement = document.createElement('a');
  //
  //     liElement.setAttribute('role', 'presentation');
  //     aElement.setAttribute('role', 'tab');
  //     aElement.setAttribute('aria-controls', tab.id);
  //     aElement.setAttribute('aria-selected', active ? 'true' : 'false');
  //     aElement.setAttribute('tabindex', active ? '0' : '-1');
  //     aElement.setAttribute('href', `#${tab.id}`);
  //     aElement.setAttribute('id', `tab-${tab.id}`);
  //     aElement.innerHTML = tab.getAttribute('name');
  //
  //     if (active) {
  //       aElement.setAttribute('active', '');
  //     }
  //
  //     aElement.addEventListener('click', activateTabFromLink);
  //
  //     liElement.appendChild(aElement);
  //     nav.appendChild(liElement);
  //
  //     tab.setAttribute('aria-labelledby', `tab-${tab.id}`);
  //     if (!active) {
  //       tab.setAttribute('aria-hidden', 'true');
  //     }
  //   });
  //
  //   this.insertAdjacentElement('afterbegin', nav);
  //
  //   // Keyboard access
  //   this.addKeyListeners();
  // }

  hideCurrent() {
    // Unset the current active tab
    if (this.currentActive) {
      // Emit hide event
      const el = this.querySelector(`a[aria-controls="${this.currentActive}"]`);
      const tab = this.querySelector(`#${this.currentActive}`);
      if (tab) {
        this.dispatchCustomEvent('joomla.tab.hide', el, tab);
        el.removeAttribute('active');
        el.setAttribute('tabindex', '-1');
        tab.removeAttribute('active');
        tab.setAttribute('aria-hidden', 'true');
        el.removeAttribute('aria-selected');
        // Emit hidden event
        this.dispatchCustomEvent('joomla.tab.hidden', el, tab);
      }
    }
  }

  showTab(tab) {
    const tabLink = document.querySelector(`#${tab.id}`);
    tabLink.click();
  }

  show(ulLink) {
    ulLink.click();
  }

  addKeyListeners() {
    const keyBehaviour = (e) => {
      console.log(`#${this.currentActive}`)
      // collect tab targets, and their parents' prev/next (or first/last)
      const currentTab = this.querySelector(`#${this.currentActive}`);
      // const tablist = [].slice.call(this.querySelector('ul').querySelectorAll('a'));

      const previousTabItem = currentTab.previousElementSibling
        || currentTab.lastElementChild;
      const nextTabItem = currentTab.nextElementSibling
        || currentTab.firstElementChild;

      // don't catch key events when ⌘ or Alt modifier is present
      if (e.metaKey || e.altKey) {
        return;
      }

      if (this.tabs.indexOf(`#${document.activeElement.id}`) === -1) {
        return;
      }

      // catch left/right and up/down arrow key events
      switch (e.keyCode) {
        case 37:
        case 38:
          previousTabItem.querySelector('a').click();
          previousTabItem.querySelector('a').focus();
          e.preventDefault();
          break;
        case 39:
        case 40:
          nextTabItem.querySelector('a').click();
          nextTabItem.querySelector('a').focus();
          e.preventDefault();
          break;
        default:
          break;
      }
    };
    this.linksContainer.addEventListener('keyup', keyBehaviour);
  }

  saveState(value) {
    const storageKey = this.getStorageKey();
    sessionStorage.setItem(storageKey, value);
  }

  /* Method to dispatch events */
  dispatchCustomEvent(eventName, element, related) {
    const OriginalCustomEvent = new CustomEvent(eventName, { bubbles: true, cancelable: true });
    OriginalCustomEvent.relatedTarget = related;
    element.dispatchEvent(OriginalCustomEvent);
    element.removeEventListener(eventName, element);
  }
}

customElements.define('joomla-tab', JoomlaTabsElement);
