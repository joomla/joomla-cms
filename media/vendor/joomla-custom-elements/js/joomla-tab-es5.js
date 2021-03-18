(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

function _typeof2(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof2 = function _typeof2(obj) { return typeof obj; }; } else { _typeof2 = function _typeof2(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof2(obj); }

function _typeof(obj) {
  if (typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol") {
    _typeof = function _typeof(obj) {
      return _typeof2(obj);
    };
  } else {
    _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : _typeof2(obj);
    };
  }

  return _typeof(obj);
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  }

  return _assertThisInitialized(self);
}

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) _setPrototypeOf(subClass, superClass);
}

function _wrapNativeSuper(Class) {
  var _cache = typeof Map === "function" ? new Map() : undefined;

  _wrapNativeSuper = function _wrapNativeSuper(Class) {
    if (Class === null || !_isNativeFunction(Class)) return Class;

    if (typeof Class !== "function") {
      throw new TypeError("Super expression must either be null or a function");
    }

    if (typeof _cache !== "undefined") {
      if (_cache.has(Class)) return _cache.get(Class);

      _cache.set(Class, Wrapper);
    }

    function Wrapper() {
      return _construct(Class, arguments, _getPrototypeOf(this).constructor);
    }

    Wrapper.prototype = Object.create(Class.prototype, {
      constructor: {
        value: Wrapper,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    return _setPrototypeOf(Wrapper, Class);
  };

  return _wrapNativeSuper(Class);
}

function isNativeReflectConstruct() {
  if (typeof Reflect === "undefined" || !Reflect.construct) return false;
  if (Reflect.construct.sham) return false;
  if (typeof Proxy === "function") return true;

  try {
    Date.prototype.toString.call(Reflect.construct(Date, [], function () {}));
    return true;
  } catch (e) {
    return false;
  }
}

function _construct(Parent, args, Class) {
  if (isNativeReflectConstruct()) {
    _construct = Reflect.construct;
  } else {
    _construct = function _construct(Parent, args, Class) {
      var a = [null];
      a.push.apply(a, args);
      var Constructor = Function.bind.apply(Parent, a);
      var instance = new Constructor();
      if (Class) _setPrototypeOf(instance, Class.prototype);
      return instance;
    };
  }

  return _construct.apply(null, arguments);
}

function _isNativeFunction(fn) {
  return Function.toString.call(fn).indexOf("[native code]") !== -1;
}

function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

function _getPrototypeOf(o) {
  _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

(function () {
  customElements.define('joomla-tab',
  /*#__PURE__*/
  function (_HTMLElement) {
    _inherits(_class, _HTMLElement);

    _createClass(_class, [{
      key: "recall",
      get: function get() {
        return this.getAttribute('recall');
      }
    }, {
      key: "view",
      get: function get() {
        return this.getAttribute('view');
      },
      set: function set(value) {
        this.setAttribute('view', value);
      }
    }, {
      key: "orientation",
      get: function get() {
        return this.getAttribute('orientation');
      },
      set: function set(value) {
        this.setAttribute('orientation', value);
      }
      /* Lifecycle, element created */

    }], [{
      key: "observedAttributes",

      /* Attributes to monitor */
      get: function get() {
        return ['recall', 'orientation', 'view'];
      }
    }]);

    function _class() {
      var _this;

      _classCallCheck(this, _class);

      _this = _possibleConstructorReturn(this, _getPrototypeOf(_class).call(this));
      _this.hasActive = false;
      _this.currentActive = '';
      _this.hasNested = false;
      _this.isNested = false;
      _this.tabs = [];
      return _this;
    }
    /* Lifecycle, element appended to the DOM */


    _createClass(_class, [{
      key: "connectedCallback",
      value: function connectedCallback() {
        var _this2 = this;

        if (!this.orientation || this.orientation && ['horizontal', 'vertical'].indexOf(this.orientation) === -1) {
          this.orientation = 'horizontal';
        } // get tab elements


        var self = this;
        var tabs = [].slice.call(this.querySelectorAll('section'));
        var tabsEl = [];
        var tabLinkHash = []; // Sanity check

        if (!tabs) {
          return;
        }

        if (this.parentNode.closest('joomla-tab')) {
          this.isNested = true;
        }

        if (this.querySelector('joomla-tab')) {
          this.hasNested = true;
        } // Use the sessionStorage state!


        if (this.hasAttribute('recall')) {
          var href = sessionStorage.getItem(this.getStorageKey());

          if (href) {
            tabLinkHash.push(href);
          }
        }

        if (this.hasNested) {
          // @todo use the recall attribute
          var _href = sessionStorage.getItem(this.getStorageKey());

          if (_href) {
            tabLinkHash.push(_href);
          } // @todo end
          // Add possible parent tab to the aray for activation


          if (tabLinkHash.length && tabLinkHash[0] !== '') {
            var hash = tabLinkHash[0].substring(5);
            var element = this.querySelector("#".concat(hash)); // Add the parent tab to the array for activation

            if (element) {
              var currentTabSet = element.closest('joomla-tab');

              if (this.isNested) {
                var parentTab = currentTabSet.closest('section');

                if (parentTab) {
                  tabLinkHash.push("#tab-".concat(parentTab.id));
                }
              }
            }
          } // remove the cascaded tabs and activate the right tab


          tabs.forEach(function (tab) {
            if (tabLinkHash.length) {
              var theId = "#tab-".concat(tab.id);

              if (tabLinkHash.indexOf(theId) === -1) {
                tab.removeAttribute('active');
              } else {
                tab.setAttribute('active', '');
              }
            }

            if (tab.parentNode === self) {
              tabsEl.push(tab);
            }
          });
        } else {
          // Activate the correct tab
          tabs.forEach(function (tab) {
            if (tabLinkHash.length) {
              var theId = "#tab-".concat(tab.id);

              if (tabLinkHash.indexOf(theId) === -1) {
                tab.removeAttribute('active');
              } else {
                tab.setAttribute('active', '');
              }
            }
          });
          tabsEl = tabs;
        } // Create the navigation


        if (this.view !== 'accordion') {
          this.createNavigation(tabsEl);
        } // Add missing role


        tabsEl.forEach(function (tab) {
          tab.setAttribute('role', 'tabpanel');

          _this2.tabs.push("#tab-".concat(tab.id));

          if (tab.hasAttribute('active')) {
            _this2.hasActive = true;
            _this2.currentActive = tab.id;

            _this2.querySelector("#tab-".concat(tab.id)).setAttribute('aria-selected', 'true');

            _this2.querySelector("#tab-".concat(tab.id)).setAttribute('active', '');

            _this2.querySelector("#tab-".concat(tab.id)).setAttribute('tabindex', '0');
          }
        }); // Fallback if no active tab

        if (!this.hasActive) {
          tabsEl[0].setAttribute('active', '');
          tabsEl[0].removeAttribute('aria-hidden');
          this.hasActive = true;
          this.currentActive = tabsEl[0].id;
          this.querySelector("#tab-".concat(tabsEl[0].id)).setAttribute('aria-selected', 'true');
          this.querySelector("#tab-".concat(tabsEl[0].id)).setAttribute('tabindex', '0');
          this.querySelector("#tab-".concat(tabsEl[0].id)).setAttribute('active', '');
        } // Check if there is a hash in the URI


        if (window.location.href.match(/#\S[^&]*/)) {
          var _hash = window.location.href.match(/#\S[^&]*/);

          var _element = this.querySelector(_hash[0]);

          if (_element) {
            // Activate any parent tabs (nested tables)
            var _currentTabSet = _element.closest('joomla-tab');

            if (this.isNested) {
              var parentTabSet = _currentTabSet.closest('joomla-tab');

              var _parentTab = _currentTabSet.closest('section');

              parentTabSet.showTab(_parentTab); // Now activate the given tab

              this.show(_element);
            } else {
              // Now activate the given tab
              this.showTab(_element);
            }
          }
        } // Convert tabs to accordian


        self.checkView(self);
        window.addEventListener('resize', function () {
          self.checkView(self);
        });
      }
      /* Lifecycle, element removed from the DOM */

    }, {
      key: "disconnectedCallback",
      value: function disconnectedCallback() {
        var _this3 = this;

        var ulEl = this.querySelector('ul');
        var navigation = [].slice.call(ulEl.querySelectorAll('a'));
        navigation.forEach(function (link) {
          link.removeEventListener('click', _this3);
        });
        ulEl.removeEventListener('keydown', this);
      }
      /* Method to create the tabs navigation */

    }, {
      key: "createNavigation",
      value: function createNavigation(tabs) {
        var _this4 = this;

        if (this.firstElementChild.nodeName.toLowerCase() === 'ul') {
          return;
        }

        var nav = document.createElement('ul');
        nav.setAttribute('role', 'tablist');
        /** Activate Tab */

        var activateTabFromLink = function activateTabFromLink(e) {
          e.preventDefault();

          if (_this4.hasActive) {
            _this4.hideCurrent();
          }

          var currentTabLink = _this4.currentActive; // Set the selected tab as active
          // Emit show event

          _this4.dispatchCustomEvent('joomla.tab.show', e.target, _this4.querySelector("#tab-".concat(currentTabLink)));

          e.target.setAttribute('active', '');
          e.target.setAttribute('aria-selected', 'true');
          e.target.setAttribute('tabindex', '0');

          _this4.querySelector(e.target.hash).setAttribute('active', '');

          _this4.querySelector(e.target.hash).removeAttribute('aria-hidden');

          _this4.currentActive = e.target.hash.substring(1); // Emit shown event

          _this4.dispatchCustomEvent('joomla.tab.shown', e.target, _this4.querySelector("#tab-".concat(currentTabLink)));

          _this4.saveState("#tab-".concat(e.target.hash.substring(1)));
        };

        tabs.forEach(function (tab) {
          if (!tab.id) {
            return;
          }

          var active = tab.hasAttribute('active');
          var liElement = document.createElement('li');
          var aElement = document.createElement('a');
          liElement.setAttribute('role', 'presentation');
          aElement.setAttribute('role', 'tab');
          aElement.setAttribute('aria-controls', tab.id);
          aElement.setAttribute('aria-selected', active ? 'true' : 'false');
          aElement.setAttribute('tabindex', active ? '0' : '-1');
          aElement.setAttribute('href', "#".concat(tab.id));
          aElement.setAttribute('id', "tab-".concat(tab.id));
          aElement.innerHTML = tab.getAttribute('name');

          if (active) {
            aElement.setAttribute('active', '');
          }

          aElement.addEventListener('click', activateTabFromLink);
          liElement.appendChild(aElement);
          nav.appendChild(liElement);
          tab.setAttribute('aria-labelledby', "tab-".concat(tab.id));

          if (!active) {
            tab.setAttribute('aria-hidden', 'true');
          }
        });
        this.insertAdjacentElement('afterbegin', nav); // Keyboard access

        this.addKeyListeners();
      }
    }, {
      key: "hideCurrent",
      value: function hideCurrent() {
        // Unset the current active tab
        if (this.currentActive) {
          // Emit hide event
          var el = this.querySelector("a[aria-controls=\"".concat(this.currentActive, "\"]"));
          this.dispatchCustomEvent('joomla.tab.hide', el, this.querySelector("#tab-".concat(this.currentActive)));
          el.removeAttribute('active');
          el.setAttribute('tabindex', '-1');
          this.querySelector("#".concat(this.currentActive)).removeAttribute('active');
          this.querySelector("#".concat(this.currentActive)).setAttribute('aria-hidden', 'true');
          el.removeAttribute('aria-selected'); // Emit hidden event

          this.dispatchCustomEvent('joomla.tab.hidden', el, this.querySelector("#tab-".concat(this.currentActive)));
        }
      }
    }, {
      key: "showTab",
      value: function showTab(tab) {
        var tabLink = document.querySelector("#tab-".concat(tab.id));
        tabLink.click();
      }
    }, {
      key: "show",
      value: function show(ulLink) {
        ulLink.click();
      }
    }, {
      key: "addKeyListeners",
      value: function addKeyListeners() {
        var _this5 = this;

        var keyBehaviour = function keyBehaviour(e) {
          // collect tab targets, and their parents' prev/next (or first/last)
          var currentTab = _this5.querySelector("#tab-".concat(_this5.currentActive)); // const tablist = [].slice.call(this.querySelector('ul').querySelectorAll('a'));


          var previousTabItem = currentTab.parentNode.previousElementSibling || currentTab.parentNode.parentNode.lastElementChild;
          var nextTabItem = currentTab.parentNode.nextElementSibling || currentTab.parentNode.parentNode.firstElementChild; // don't catch key events when ⌘ or Alt modifier is present

          if (e.metaKey || e.altKey) {
            return;
          }

          if (_this5.tabs.indexOf("#".concat(document.activeElement.id)) === -1) {
            return;
          } // catch left/right and up/down arrow key events


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

        this.querySelector('ul').addEventListener('keyup', keyBehaviour);
      }
    }, {
      key: "getStorageKey",
      value: function getStorageKey() {
        return window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').split('#')[0];
      }
    }, {
      key: "saveState",
      value: function saveState(value) {
        var storageKey = this.getStorageKey();
        sessionStorage.setItem(storageKey, value);
      }
      /** Method to convert tabs to accordion and vice versa depending on screen size */

    }, {
      key: "checkView",
      value: function checkView(element) {
        var el = element;
        var nav = el.querySelector('ul');
        var tabsEl = [];

        if (document.body.getBoundingClientRect().width > 920) {
          if (this.view === 'tabs') {
            return;
          }

          el.view = 'tabs'; // convert to tabs

          var panels = [].slice.call(nav.querySelectorAll('section')); // remove the cascaded tabs

          for (var i = 0, l = panels.length; i < l; ++i) {
            if (panels[i].parentNode.parentNode.parentNode === el) {
              tabsEl.push(panels[i]);
            }
          }

          if (tabsEl.length) {
            tabsEl.forEach(function (panel) {
              el.appendChild(panel);
            });
          }
        } else {
          if (this.view === 'accordion') {
            return;
          }

          el.view = 'accordion'; // convert to accordion

          var _panels = [].slice.call(el.querySelectorAll('section')); // remove the cascaded tabs


          for (var _i = 0, _l = _panels.length; _i < _l; ++_i) {
            if (_panels[_i].parentNode === el) {
              tabsEl.push(_panels[_i]);
            }
          }

          if (tabsEl.length) {
            tabsEl.forEach(function (panel) {
              var link = el.querySelector("a[aria-controls=\"".concat(panel.id, "\"]"));

              if (link.parentNode.parentNode === el.firstElementChild) {
                link.parentNode.appendChild(panel);
              }
            });
          }
        }
      }
      /* Method to dispatch events */

    }, {
      key: "dispatchCustomEvent",
      value: function dispatchCustomEvent(eventName, element, related) {
        var OriginalCustomEvent = new CustomEvent(eventName, {
          bubbles: true,
          cancelable: true
        });
        OriginalCustomEvent.relatedTarget = related;
        element.dispatchEvent(OriginalCustomEvent);
        element.removeEventListener(eventName, element);
      }
    }]);

    return _class;
  }(_wrapNativeSuper(HTMLElement)));
})();

},{}]},{},[1]);
