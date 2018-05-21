(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];descriptor.enumerable = descriptor.enumerable || false;descriptor.configurable = true;if ("value" in descriptor) descriptor.writable = true;Object.defineProperty(target, descriptor.key, descriptor);
    }
  }return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);if (staticProps) defineProperties(Constructor, staticProps);return Constructor;
  };
}();

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _possibleConstructorReturn(self, call) {
  if (!self) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }return call && ((typeof call === "undefined" ? "undefined" : _typeof(call)) === "object" || typeof call === "function") ? call : self;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function, not " + (typeof superClass === "undefined" ? "undefined" : _typeof(superClass)));
  }subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } });if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
}

var JoomlaPanelsElement = function (_HTMLElement) {
  _inherits(JoomlaPanelsElement, _HTMLElement);

  _createClass(JoomlaPanelsElement, [{
    key: 'recall',
    get: function get() {
      return this.getAttribute('recall');
    },
    set: function set(value) {
      return this.setAttribute('recall', value);
    }
  }, {
    key: 'view',
    get: function get() {
      return this.getAttribute('view');
    },
    set: function set(value) {
      this.setAttribute('view', value);
    }
  }, {
    key: 'orientation',
    get: function get() {
      return this.getAttribute('orientation') || 'horizontal';
    },
    set: function set(value) {
      this.setAttribute('orientation', value);
    }
  }, {
    key: 'responsive',
    get: function get() {
      return this.getAttribute('responsive');
    },
    set: function set(value) {
      this.setAttribute('responsive', value);
    }
  }, {
    key: 'collapseWidth',
    get: function get() {
      return this.getAttribute('collapse-width');
    },
    set: function set(value) {
      this.setAttribute('collapse-width', value);
    }

    /* Lifecycle, element created */

  }], [{
    key: 'observedAttributes',

    /* Attributes to monitor */
    get: function get() {
      return ['recall', 'orientation', 'view', 'responsive', 'collapse-width'];
    }
  }]);

  function JoomlaPanelsElement() {
    _classCallCheck(this, JoomlaPanelsElement);

    // Setup configuration
    var _this = _possibleConstructorReturn(this, (JoomlaPanelsElement.__proto__ || Object.getPrototypeOf(JoomlaPanelsElement)).call(this));

    _this.hasActive = false;
    _this.currentActive = '';
    _this.hasNested = false;
    _this.isNested = false;
    _this.tabs = [];
    _this.tabsLinks = [];
    _this.panels = [];
    _this.tabLinkHash = [];
    return _this;
  }

  /* Lifecycle, element appended to the DOM */

  _createClass(JoomlaPanelsElement, [{
    key: 'connectedCallback',
    value: function connectedCallback() {
      var _this2 = this;

      if (!this.orientation || this.orientation && ['horizontal', 'vertical'].indexOf(this.orientation) === -1) {
        this.setAttribute('orientation', 'horizontal');
      }

      this.view = this.getAttribute('view') || 'tabs';
      this.recall = this.recall || 'false';
      this.responsive = this.getAttribute('responsive') || 'false';
      this.collapseWidth = this.getAttribute('collapseWidth') || 0;

      // Get tab elements
      this.panels = [].slice.call(this.querySelectorAll('section'));

      // Sanity check
      if (!this.panels.length) {
        throw new Error('`Joomla-panels` require one ore more panels!');
      }

      // Is this nested
      if (this.findAncestorByTagNme(this, 'joomla-tab')) {
        this.isNested = true;
      }

      // Does it have child tab element
      if (this.querySelector('joomla-tab')) {
        this.hasNested = true;
      }

      // Use the sessionStorage state!
      if (this.recall) {
        var href = sessionStorage.getItem(this.getStorageKey());
        // Do not fail on 3.x tab state values hack
        if (href && !/@\[/.test(href)) {
          this.tabLinkHash.push(href);
        }
        this.setTabState();
      }

      // Create the navigation
      if (this.firstElementChild.tagName !== 'ul') {
        this.createNavigation();
      }

      // Add missing A11Y
      this.panels.forEach(function (tab) {
        tab.setAttribute('role', 'tabpanel');
        _this2.tabs.push('#tab-' + tab.id);
        if (tab.hasAttribute('active')) {
          _this2.hasActive = true;
          _this2.currentActive = tab.id;
          _this2.querySelector('#tab-' + tab.id).setAttribute('aria-selected', 'true');
          _this2.querySelector('#tab-' + tab.id).setAttribute('active', '');
          _this2.querySelector('#tab-' + tab.id).setAttribute('tabindex', '0');
        }
      });

      // Fallback if no active tab
      if (!this.hasActive) {
        this.tabsLinks[0].setAttribute('active', '');
        this.hasActive = true;
        this.currentActive = this.panels[0].id;
        this.tabsLinks[0].setAttribute('aria-selected', 'true');
        this.tabsLinks[0].setAttribute('tabindex', '0');
        this.tabsLinks[0].setAttribute('active', '');
        this.panels[0].setAttribute('active', '');
      }

      // Check if there is a hash in the URI
      if (window.location.href.match(/#tab-/)) {
        // this.activateUriHash();
      }

      if (this.view === 'accordion') {
        this.toAccordion.bind(this)();
      }

      if (this.responsive === 'true') {
        // Convert tabs to accordian and vice versa
        this.changeView.bind(this);

        // Add behavior for window size change
        window.addEventListener('resize', this.changeView.bind(this));
      }
    }

    /* Lifecycle, element removed from the DOM */

  }, {
    key: 'disconnectedCallback',
    value: function disconnectedCallback() {
      var self = this;
      var ulEl = this.querySelector('ul');
      var navigation = [].slice.call(ulEl.querySelectorAll('a'));

      navigation.forEach(function (link) {
        link.removeEventListener('click', self.activateTabFromLink, true);
      });

      ulEl.removeEventListener('keydown', self.keyBehaviour, true);
    }

    /* Method to create the tabs navigation */

  }, {
    key: 'createNavigation',
    value: function createNavigation() {
      var _this3 = this;

      var self = this;
      var nav = '';

      if (this.firstElementChild.tagName.toLowerCase() !== 'ul') {
        nav = document.createElement('ul');
      }

      nav.setAttribute('role', 'tablist');
      this.panels.forEach(function (panel) {
        if (!panel.id) {
          throw new Error('`joomla-panels` All panels require an ID');
        }

        if (panel.parentNode !== _this3) {
          return;
        }

        var active = panel.getAttribute('active') || false;
        var liElement = document.createElement('li');
        var aElement = document.createElement('a');

        liElement.setAttribute('role', 'presentation');
        aElement.setAttribute('role', 'tab');
        aElement.setAttribute('aria-controls', panel.id);
        aElement.setAttribute('aria-selected', active ? 'true' : 'false');
        aElement.setAttribute('tabindex', active ? '0' : '-1');
        aElement.setAttribute('href', '#' + panel.id);
        aElement.setAttribute('id', 'tab-' + panel.id);
        aElement.innerHTML = panel.getAttribute('name');

        if (active) {
          aElement.setAttribute('active', '');
        }

        aElement.addEventListener('click', self.activateTabFromLink.bind(self));
        _this3.tabsLinks.push(aElement);

        liElement.append(aElement);
        nav.append(liElement);

        panel.setAttribute('aria-labelledby', 'tab-' + panel.id);

        if (!active) {
          panel.setAttribute('aria-hidden', 'true');
        }
      });

      this.insertAdjacentElement('afterbegin', nav);

      // Keyboard access
      this.querySelector('ul').addEventListener('keydown', this.keyBehaviour.bind(this));
    }
  }, {
    key: 'hideCurrent',
    value: function hideCurrent() {
      // Unset the current active tab
      if (this.currentActive) {
        // Emit hide event
        var el = this.querySelector('a[aria-controls="' + this.currentActive + '"]');
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

    /** Activate Tab */

  }, {
    key: 'activateTabFromLink',
    value: function activateTabFromLink(e) {
      e.preventDefault();
      var currentTabLink = this.currentActive;

      if (this.hasActive) {
        this.hideCurrent();
      }

      // Set the selected tab as active
      // Emit show event
      this.dispatchCustomEvent('joomla.tab.show', e.target, this.querySelector('#tab-' + currentTabLink));
      e.target.setAttribute('active', '');
      e.target.setAttribute('aria-selected', 'true');
      e.target.setAttribute('tabindex', '0');
      this.querySelector(e.target.hash).setAttribute('active', '');
      this.querySelector(e.target.hash).removeAttribute('aria-hidden');
      this.currentActive = e.target.hash.substring(1);
      // Emit shown event
      this.dispatchCustomEvent('joomla.tab.shown', e.target, this.querySelector('#tab-' + currentTabLink));
      this.saveState('#tab-' + e.target.hash.substring(1));
    }
  }, {
    key: 'showTab',
    value: function showTab(tab) {
      var tabLink = document.querySelector('#tab-' + tab.id);
      tabLink.click();
    }
  }, {
    key: 'show',
    value: function show(ulLink) {
      ulLink.click();
    }
  }, {
    key: 'keyBehaviour',
    value: function keyBehaviour(e) {
      // collect tab targets, and their parents' prev/next (or first/last)
      var currentTab = this.querySelector('#tab-' + this.currentActive);

      var previousTabItem = currentTab.parentNode.previousElementSibling || currentTab.parentNode.parentNode.lastElementChild;
      var nextTabItem = currentTab.parentNode.nextElementSibling || currentTab.parentNode.parentNode.firstElementChild;

      // Don't catch key events when ⌘ or Alt modifier is present
      if (e.metaKey || e.altKey) {
        return;
      }

      if (this.tabs.indexOf('#' + document.activeElement.id) === -1) {
        return;
      }

      // catch left/right and up/down arrow key events
      switch (e.keyCode) {
        case 37:
        case 38:
          e.preventDefault();
          e.stopPropagation();
          previousTabItem.querySelector('a').click();
          previousTabItem.querySelector('a').focus();
          break;
        case 39:
        case 40:
          e.preventDefault();
          e.stopPropagation();
          nextTabItem.querySelector('a').click();
          nextTabItem.querySelector('a').focus();
          break;
        default:
          break;
      }
    }

    /* eslint-disable */

  }, {
    key: 'getStorageKey',
    value: function getStorageKey() {
      return window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').split('#')[0];
    }
    /* eslint-disable */

  }, {
    key: 'saveState',
    value: function saveState(value) {
      var storageKey = this.getStorageKey();
      sessionStorage.setItem(storageKey, value);
    }
  }, {
    key: 'setTabState',
    value: function setTabState() {
      var _this4 = this;

      var self = this;
      var tabs = this.tabsLinks;

      if (this.hasNested) {
        // Add possible parent tab to the aray for activation
        if (this.tabLinkHash.length && this.tabLinkHash[0] !== '') {
          var hash = this.tabLinkHash[0].substring(5);
          var element = this.querySelector('' + hash);

          // Add the parent tab to the array for activation
          if (element) {
            var currentTabSet = this.findAncestorByTagNme(element, 'joomla-tab');
            var parentTabSet = this.findAncestorByTagNme(currentTabSet, 'joomla-tab');

            if (parentTabSet) {
              var parentTab = this.findAncestorByTagNme(currentTabSet, 'section');
              if (parentTab) {
                this.tabLinkHash.push('#tab-' + parentTab.id);
              }
            }
          }
        }

        // Remove the cascaded tabs and activate the right tab
        tabs.forEach(function (tab) {
          if (_this4.tabLinkHash.length) {
            var theId = '#tab-' + tab.id;

            if (_this4.tabLinkHash.indexOf(theId) === -1) {
              tab.removeAttribute('active');
            } else {
              tab.setAttribute('active', '');
            }
          }

          if (tab.parentNode === self) {
            _this4.tabsLinks.push(tab);
          }
        });
      } else {
        // Activate the correct tab
        tabs.forEach(function (tab) {
          if (_this4.tabLinkHash.length) {
            var theId = '#tab-' + tab.hash;
            if (_this4.tabLinkHash.indexOf(theId) > -1) {
              tab.removeAttribute('active');
            } else {
              tab.setAttribute('active', '');
            }
          }
        });

        this.tabsLinks = tabs;
      }
    }
  }, {
    key: 'toTabs',
    value: function toTabs() {
      var self = this;
      // remove the cascaded tabs
      for (var i = 0, l = this.panels.length; i < l; ++i) {
        if (this.panels[i].parentNode.parentNode.parentNode === this) {
          this.tabsLinks.push(this.panels[i]);
        }
      }

      if (this.tabsLinks.length) {
        this.tabsLinks.forEach(function (panel) {
          self.appendChild(panel);
        });
      }
    }
  }, {
    key: 'toAccordion',
    value: function toAccordion() {
      var self = this;
      // remove the cascaded tabs
      // for (let i = 0, l = this.panels.length; i < l; ++i) {
      //   if (this.panels[i].parentNode === this) {
      //     this.tabsLinks.push(this.panels[i]);
      //   }
      // }

      if (this.panels.length) {
        this.panels.forEach(function (panel) {
          var link = self.querySelector('a[aria-controls="' + panel.id + '"]');
          // if (link.parentNode.parentNode === self.firstElementChild)
          link.parentNode.appendChild(panel);
        });
      }
    }

    /** Method to convert tabs to accordion and vice versa depending on screen size */

  }, {
    key: 'changeView',
    value: function changeView() {
      if (window.outerWidth > 920) {
        if (this.view === 'tabs') {
          return;
        }
        // convert to tabs
        this.toTabs.bind(this);
        this.view = 'tabs';
      } else {
        if (this.view === 'accordion') {
          return;
        }
        // convert to accordion
        this.toAccordion.bind(this);
        this.view = 'accordion';
      }
    }
  }, {
    key: 'activateUriHash',
    value: function activateUriHash() {
      var hash = window.location.href.match(/#\S[^&]*/);
      var element = this.querySelector(hash[0]);

      if (element) {
        // Activate any parent tabs (nested tables)
        var currentTabSet = this.findAncestorByTagNme(element, 'joomla-tab');
        var parentTabSet = this.findAncestorByTagNme(currentTabSet, 'joomla-tab');

        if (parentTabSet) {
          var parentTab = this.findAncestorByTagNme(currentTabSet, 'section');
          parentTabSet.showTab(parentTab);
          // Now activate the given tab
          this.show(element);
        } else {
          // Now activate the given tab
          this.showTab(element);
        }
      }
    }
    /* eslint-disable */

  }, {
    key: 'findAncestorByTagNme',
    value: function findAncestorByTagNme(el, tagName) {
      while ((el = el.parentElement) && el.nodeName.toLowerCase() !== tagName) {}
      return el;
    }
    /* eslint-enable */

    /* Method to dispatch events */

  }, {
    key: 'dispatchCustomEvent',
    value: function dispatchCustomEvent(eventName, element, related) {
      var OriginalCustomEvent = new CustomEvent(eventName, { bubbles: true, cancelable: true });
      if (related) {
        OriginalCustomEvent.relatedTarget = related;
      }

      element.dispatchEvent(OriginalCustomEvent);
      element.removeEventListener(eventName, element);
    }
  }]);

  return JoomlaPanelsElement;
}(HTMLElement);

customElements.define('joomla-panels', JoomlaPanelsElement);

},{}]},{},[1]);
