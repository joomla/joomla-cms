/*!
* metismenujs - v1.2.0
* MetisMenu with Vanilla-JS
* https://github.com/onokumus/metismenujs#readme
*
* Made by Osman Nuri Okumus <onokumus@gmail.com> (https://github.com/onokumus)
* Under MIT License
*/
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    (global = global || self, global.MetisMenu = factory());
}(this, (function () { 'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation. All rights reserved.
    Licensed under the Apache License, Version 2.0 (the "License"); you may not use
    this file except in compliance with the License. You may obtain a copy of the
    License at http://www.apache.org/licenses/LICENSE-2.0

    THIS CODE IS PROVIDED ON AN *AS IS* BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    KIND, EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION ANY IMPLIED
    WARRANTIES OR CONDITIONS OF TITLE, FITNESS FOR A PARTICULAR PURPOSE,
    MERCHANTABLITY OR NON-INFRINGEMENT.

    See the Apache Version 2.0 License for specific language governing permissions
    and limitations under the License.
    ***************************************************************************** */

    var __assign = function() {
        __assign = Object.assign || function __assign(t) {
            for (var s, i = 1, n = arguments.length; i < n; i++) {
                s = arguments[i];
                for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
            }
            return t;
        };
        return __assign.apply(this, arguments);
    };

    var Default = {
        parentTrigger: 'li',
        subMenu: 'ul',
        toggle: true,
        triggerElement: 'a',
    };
    var ClassName = {
        ACTIVE: 'mm-active',
        COLLAPSE: 'mm-collapse',
        COLLAPSED: 'mm-collapsed',
        COLLAPSING: 'mm-collapsing',
        METIS: 'metismenu',
        SHOW: 'mm-show',
    };

    function matches(element, selector) {
        var nativeMatches = element.matches
            || element.webkitMatchesSelector
            || element.msMatchesSelector;
        return nativeMatches.call(element, selector);
    }
    function closest(element, selector) {
        if (element.closest) {
            return element.closest(selector);
        }
        var el = element;
        while (el) {
            if (matches(el, selector)) {
                return el;
            }
            el = el.parentElement;
        }
        return null;
    }

    var MetisMenu = /** @class */ (function () {
        /**
         * Creates an instance of MetisMenu.
         *
         * @constructor
         * @param {Element | string} element
         * @param {IMMOptions} [options]
         * @memberof MetisMenu
         */
        function MetisMenu(element, options) {
            this.element = MetisMenu.isElement(element) ? element : document.querySelector(element);
            this.config = __assign(__assign({}, Default), options);
            this.disposed = false;
            this.triggerArr = [];
            this.init();
        }
        MetisMenu.attach = function (el, opt) {
            return new MetisMenu(el, opt);
        };
        MetisMenu.prototype.init = function () {
            var _this = this;
            var METIS = ClassName.METIS, ACTIVE = ClassName.ACTIVE, COLLAPSE = ClassName.COLLAPSE;
            this.element.classList.add(METIS);
            [].slice.call(this.element.querySelectorAll(this.config.subMenu)).forEach(function (ul) {
                ul.classList.add(COLLAPSE);
                var li = closest(ul, _this.config.parentTrigger);
                if (li === null || li === void 0 ? void 0 : li.classList.contains(ACTIVE)) {
                    _this.show(ul);
                }
                else {
                    _this.hide(ul);
                }
                var a = li === null || li === void 0 ? void 0 : li.querySelector(_this.config.triggerElement);
                if ((a === null || a === void 0 ? void 0 : a.getAttribute('aria-disabled')) === 'true') {
                    return;
                }
                a === null || a === void 0 ? void 0 : a.setAttribute('aria-expanded', 'false');
                a === null || a === void 0 ? void 0 : a.addEventListener('click', _this.clickEvent.bind(_this));
                _this.triggerArr.push(a);
            });
        };
        MetisMenu.prototype.clickEvent = function (evt) {
            if (!this.disposed) {
                var target = evt === null || evt === void 0 ? void 0 : evt.currentTarget;
                if (target && target.tagName === 'A') {
                    evt.preventDefault();
                }
                var li = closest(target, this.config.parentTrigger);
                var ul = li === null || li === void 0 ? void 0 : li.querySelector(this.config.subMenu);
                this.toggle(ul);
            }
        };
        MetisMenu.prototype.update = function () {
            this.disposed = false;
            this.init();
        };
        MetisMenu.prototype.dispose = function () {
            var _this = this;
            this.triggerArr.forEach(function (arr) {
                arr.removeEventListener('click', _this.clickEvent.bind(_this));
            });
            this.disposed = true;
        };
        MetisMenu.prototype.on = function (evtType, handler, options) {
            this.element.addEventListener(evtType, handler, options);
            return this;
        };
        MetisMenu.prototype.off = function (evtType, handler, options) {
            this.element.removeEventListener(evtType, handler, options);
            return this;
        };
        MetisMenu.prototype.emit = function (evtType, evtData, shouldBubble) {
            if (shouldBubble === void 0) { shouldBubble = false; }
            var evt;
            if (typeof CustomEvent === 'function') {
                evt = new CustomEvent(evtType, {
                    bubbles: shouldBubble,
                    detail: evtData,
                });
            }
            else {
                evt = document.createEvent('CustomEvent');
                evt.initCustomEvent(evtType, shouldBubble, false, evtData);
            }
            this.element.dispatchEvent(evt);
            return this;
        };
        MetisMenu.prototype.toggle = function (ul) {
            var li = closest(ul, this.config.parentTrigger);
            if (li === null || li === void 0 ? void 0 : li.classList.contains(ClassName.ACTIVE)) {
                this.hide(ul);
            }
            else {
                this.show(ul);
            }
        };
        MetisMenu.prototype.show = function (el) {
            var _this = this;
            var _a;
            var ul = el;
            var ACTIVE = ClassName.ACTIVE, COLLAPSE = ClassName.COLLAPSE, COLLAPSED = ClassName.COLLAPSED, COLLAPSING = ClassName.COLLAPSING, SHOW = ClassName.SHOW;
            if (this.isTransitioning || ul.classList.contains(COLLAPSING)) {
                return;
            }
            var complete = function () {
                ul.classList.remove(COLLAPSING);
                ul.style.height = '';
                ul.removeEventListener('transitionend', complete);
                _this.setTransitioning(false);
                _this.emit('shown.metisMenu', {
                    shownElement: ul,
                });
            };
            var li = closest(ul, this.config.parentTrigger);
            li === null || li === void 0 ? void 0 : li.classList.add(ACTIVE);
            var a = li === null || li === void 0 ? void 0 : li.querySelector(this.config.triggerElement);
            a === null || a === void 0 ? void 0 : a.setAttribute('aria-expanded', 'true');
            a === null || a === void 0 ? void 0 : a.classList.remove(COLLAPSED);
            ul.style.height = '0px';
            ul.classList.remove(COLLAPSE);
            ul.classList.remove(SHOW);
            ul.classList.add(COLLAPSING);
            var eleParentSiblins = [].slice
                .call((_a = li === null || li === void 0 ? void 0 : li.parentNode) === null || _a === void 0 ? void 0 : _a.children)
                .filter(function (c) { return c !== li; });
            if (this.config.toggle && eleParentSiblins.length > 0) {
                eleParentSiblins.forEach(function (sibli) {
                    var sibUl = sibli.querySelector(_this.config.subMenu);
                    if (sibUl) {
                        _this.hide(sibUl);
                    }
                });
            }
            this.setTransitioning(true);
            ul.classList.add(COLLAPSE);
            ul.classList.add(SHOW);
            ul.style.height = ul.scrollHeight + "px";
            this.emit('show.metisMenu', {
                showElement: ul,
            });
            ul.addEventListener('transitionend', complete);
        };
        MetisMenu.prototype.hide = function (el) {
            var _this = this;
            var ACTIVE = ClassName.ACTIVE, COLLAPSE = ClassName.COLLAPSE, COLLAPSED = ClassName.COLLAPSED, COLLAPSING = ClassName.COLLAPSING, SHOW = ClassName.SHOW;
            var ul = el;
            if (this.isTransitioning || !ul.classList.contains(SHOW)) {
                return;
            }
            this.emit('hide.metisMenu', {
                hideElement: ul,
            });
            var li = closest(ul, this.config.parentTrigger);
            li === null || li === void 0 ? void 0 : li.classList.remove(ACTIVE);
            var complete = function () {
                ul.classList.remove(COLLAPSING);
                ul.classList.add(COLLAPSE);
                ul.style.height = '';
                ul.removeEventListener('transitionend', complete);
                _this.setTransitioning(false);
                _this.emit('hidden.metisMenu', {
                    hiddenElement: ul,
                });
            };
            ul.style.height = ul.getBoundingClientRect().height + "px";
            ul.style.height = ul.offsetHeight + "px";
            ul.classList.add(COLLAPSING);
            ul.classList.remove(COLLAPSE);
            ul.classList.remove(SHOW);
            this.setTransitioning(true);
            ul.addEventListener('transitionend', complete);
            ul.style.height = '0px';
            var a = li === null || li === void 0 ? void 0 : li.querySelector(this.config.triggerElement);
            a === null || a === void 0 ? void 0 : a.setAttribute('aria-expanded', 'false');
            a === null || a === void 0 ? void 0 : a.classList.add(COLLAPSED);
        };
        MetisMenu.prototype.setTransitioning = function (isTransitioning) {
            this.isTransitioning = isTransitioning;
        };
        MetisMenu.isElement = function (element) {
            return Boolean(element.classList);
        };
        return MetisMenu;
    }());

    return MetisMenu;

})));
//# sourceMappingURL=metismenujs.js.map
