/**
 * @license
 * Copyright (c) 2018 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at http://polymer.github.io/PATENTS.txt
 *
 * LICENSE.txt from http://polymer.github.io/LICENSE.txt
 *
 * Copyright (c) 2014 The Polymer Authors. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 * * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @note This file has been modified by the Joomla! Project
 *       and no longer reflects the original work of its author.
 */

((Joomla, document) => {
  'use strict';

  /**
   * Basic flow of the loader process
   *
   * There are 4 flows the loader can take when booting up
   *
   * - Synchronous script, no polyfills needed
   *   - wait for `DOMContentLoaded`
   *   - fire WCR event, as there could not be any callbacks passed to `waitFor`
   *
   * - Synchronous script, polyfills needed
   *   - document.write the polyfill bundle
   *   - wait on the `load` event of the bundle to batch Custom Element upgrades
   *   - wait for `DOMContentLoaded`
   *   - run callbacks passed to `waitFor`
   *   - fire WCR event
   *
   * - Asynchronous script, no polyfills needed
   *   - wait for `DOMContentLoaded`
   *   - run callbacks passed to `waitFor`
   *   - fire WCR event
   *
   * - Asynchronous script, polyfills needed
   *   - Append the polyfill bundle script
   *   - wait for `load` event of the bundle
   *   - batch Custom Element Upgrades
   *   - run callbacks pass to `waitFor`
   *   - fire WCR event
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
      document.dispatchEvent(new CustomEvent('WebComponentsReady', { bubbles: true }));
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
      return Promise.all(whenLoadedFns.map((fn) => (fn instanceof Function ? fn() : fn)))
        .then(() => {
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
          throw new Error(`Could not load polyfill bundle ${base.rootFull + replacement}`);
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
