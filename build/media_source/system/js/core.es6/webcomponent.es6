/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {
    'use strict';

    /**
     * Loads any needed polyfill for web components and async load any web components
     *
     * Parts of the WebComponents method belong to The Polymer Project Authors. License http://polymer.github.io/LICENSE.txt
     *
     * @since   4.0.0
     */
    Joomla.WebComponents = () => {
        const wc = Joomla.getOptions('webcomponents');

        // Return early
        if (!wc || !wc.length) {
            return;
        }

        let polyfillsLoaded = false;
        const whenLoadedFns = [];
        let allowUpgrades = false;
        let flushFn;

        const fireEvent = () => {
            window.WebComponents.ready = true;
            document.dispatchEvent(new CustomEvent('WebComponentsReady', {bubbles: true}));
            // eslint-disable-next-line no-use-before-define
            loadWC();
        };

        const batchCustomElements = () => {
            if (window.customElements && customElements.polyfillWrapFlushCallback) {
                customElements.polyfillWrapFlushCallback((flushCallback) => {
                    flushFn = flushCallback;
                    if (allowUpgrades) {
                        flushFn();
                    }
                });
            }
        };

        const asyncReady = () => {
            // eslint-disable-next-line no-use-before-define
            batchCustomElements();
            // eslint-disable-next-line no-use-before-define
            ready();
        };

        const ready = () => {
            // bootstrap <template> elements before custom elements
            if (window.HTMLTemplateElement && HTMLTemplateElement.bootstrap) {
                HTMLTemplateElement.bootstrap(window.document);
            }

            polyfillsLoaded = true;
            // eslint-disable-next-line no-use-before-define
            runWhenLoadedFns().then(fireEvent);
        };

        const runWhenLoadedFns = () => {
            allowUpgrades = false;
            const done = () => {
                allowUpgrades = true;
                whenLoadedFns.length = 0;
                // eslint-disable-next-line no-unused-expressions
                flushFn && flushFn();
            };
            return Promise.all(whenLoadedFns.map(fn => (fn instanceof Function ? fn() : fn))).then(() => {
                done();
            }).catch((err) => {
                // eslint-disable-next-line no-console
                console.error(err);
            });
        };

        window.WebComponents = window.WebComponents || {
            ready: false,
            _batchCustomElements: batchCustomElements,
            waitFor: (waitFn) => {
                if (!waitFn) {
                    return;
                }
                whenLoadedFns.push(waitFn);
                if (polyfillsLoaded) {
                    runWhenLoadedFns();
                }
            },
        };

        /* Check if ES6 then apply the shim */
        const checkES6 = () => {
            try {
                // eslint-disable-next-line no-new-func, no-new
                new Function('(a = 0) => a');
                return true;
            } catch (err) {
                return false;
            }
        };

        /* Load web components async */
        const loadWC = () => {
            if (wc && wc.length) {
                wc.forEach((component) => {
                    let el;
                    if (component.match(/\.js/g)) {
                        el = document.createElement('script');
                        if (!checkES6()) {
                            let es5;
                            // Browser is not ES6!
                            if (component.match(/\.min\.js/g)) {
                                es5 = component.replace(/\.min\.js/g, '-es5.min.js');
                            } else if (component.match(/\.js/g)) {
                                es5 = component.replace(/\.js/g, '-es5.js');
                            }
                            el.src = es5;
                        } else {
                            el.src = component;
                        }
                    }
                    if (el) {
                        document.head.appendChild(el);
                    }
                });
            }
        };

        // Get the core.js src attribute
        let name = 'core.min.js';
        let script = document.querySelector(`script[src*="${name}"]`);

        if (!script) {
            name = 'core.js';
            script = document.querySelector(`script[src*="${name}"]`);
        }

        if (!script) {
            throw new Error('core(.min).js is not registered correctly!');
        }

        // Feature detect which polyfill needs to be imported.
        let polyfills = [];
        if (!('attachShadow' in Element.prototype && 'getRootNode' in Element.prototype)
            || (window.ShadyDOM && window.ShadyDOM.force)) {
            polyfills.push('sd');
        }
        if (!window.customElements || window.customElements.forcePolyfill) {
            polyfills.push('ce');
        }

        const needsTemplate = (() => {
            // no real <template> because no `content` property (IE and older browsers)
            const t = document.createElement('template');
            if (!('content' in t)) {
                return true;
            }
            // broken doc fragment (older Edge)
            if (!(t.content.cloneNode() instanceof DocumentFragment)) {
                return true;
            }
            // broken <template> cloning (Edge up to at least version 17)
            const t2 = document.createElement('template');
            t2.content.appendChild(document.createElement('div'));
            t.content.appendChild(t2);
            const clone = t.cloneNode(true);
            return (clone.content.childNodes.length === 0
                || clone.content.firstChild.content.childNodes.length === 0);
        })();

        // NOTE: any browser that does not have template or ES6 features
        // must load the full suite of polyfills.
        if (!window.Promise || !Array.from || !window.URL || !window.Symbol || needsTemplate) {
            polyfills = ['sd-ce-pf'];
        }

        if (polyfills.length) {
            const newScript = document.createElement('script');
            // Load it from the right place.
            const replacement = `media/vendor/webcomponentsjs/js/webcomponents-${polyfills.join('-')}.min.js`;

            const mediaVersion = script.src.match(/\?.*/);
            const base = Joomla.getOptions('system.paths');

            if (!base) {
                throw new Error('core(.min).js is not registered correctly!');
            }

            newScript.src = base.rootFull + replacement + (mediaVersion ? mediaVersion[0] : '');

            // if readyState is 'loading', this script is synchronous
            if (document.readyState === 'loading') {
                // make sure custom elements are batched whenever parser gets to the injected script
                newScript.setAttribute('onload', 'window.WebComponents._batchCustomElements()');
                document.write(newScript.outerHTML);
                document.addEventListener('DOMContentLoaded', ready);
            } else {
                newScript.addEventListener('load', asyncReady);
                newScript.addEventListener('error', () => {
                    throw new Error(`Could not load polyfill bundle${base.rootFull + replacement}`);
                });
                document.head.appendChild(newScript);
            }
        } else {
            polyfillsLoaded = true;
            if (document.readyState === 'complete') {
                fireEvent();
            } else {
                // this script may come between DCL and load, so listen for both
                // and cancel load listener if DCL fires
                window.addEventListener('load', ready);
                window.addEventListener('DOMContentLoaded', () => {
                    window.removeEventListener('load', ready);
                    ready();
                });
            }
        }
    };
})(Joomla, document);

/**
 * Load any web components and any polyfills required
 */
document.addEventListener('DOMContentLoaded', Joomla.WebComponents);
