(function (window) {
	'use strict';

	window.Joomla = Joomla || {};
	Joomla.WebComponents = Joomla.WebComponents || {};
	var wc, name = 'wc-loader.js', polyfills = [];

	/* Get the webcomponents */
	if (Joomla.getOptions && typeof Joomla.getOptions === "function") {
		wc = Joomla.getOptions('webcomponents', {});
	}


	/* Check if ES6 then apply the shim */
	var checkES6 = function () {
		try {
			eval("var foo = (x)=>x+1");
		} catch (e) {
			return false;
		}
		return true;
	};

	/* Check if we need the full polyfill set */
	var checkWC = function (wc) {
		if (wc.hasOwnProperty('fullPolyfill') && wc['fullPolyfill'] === 'true') {
			return true;
		}
		return false;
	};

	/* Load webcomponents async */
	var loadWC = function (wc) {
		var el, p, es5;
		for (p in wc) {
			if (wc.hasOwnProperty(p) && p !== 'full') {
				if (wc[p].match(/\.js/)) {
					el = document.createElement('script');
					if (!checkES6()) {
						// Browser is not ES6!
						if (wc[p].match(/\.min\.js/g)) {
							es5 = wc[p].replace(/\.min\.js/g, '-es5.min.js')
						} else if (wc[p].match(/\.js/g)) {
							es5 = wc[p].replace(/\.js/g, '-es5.js')
						}
						el.src = es5;
					} else {
						el.src = wc[p];
					}
				} else if (wc[p].match(/\.html/)) {
					el = document.createElement('link');
					el.setAttribute('href', wc[p]);
					el.setAttribute('rel', 'import');
				}
				if (el) {
					document.head.appendChild(el);
				}
			}
		}
	};


	if (checkWC(wc)) {
		console.log('full', checkWC(wc))
		if (!('import' in document.createElement('link'))) {
			polyfills.push('hi');
		}
		if (!('attachShadow' in Element.prototype && 'getRootNode' in Element.prototype) || (window.ShadyDOM && window.ShadyDOM.force)) {
			polyfills.push('sd');
		}
		if (!window.customElements || window.customElements.forcePolyfill) {
			polyfills.push('ce');
		}
		if (!('content' in document.createElement('template')) || !window.Promise || !Array.from || !(document.createDocumentFragment().cloneNode() instanceof DocumentFragment)) {
			polyfills = ['lite'];
		}
	} else {
		console.log('CE_only', checkWC(wc))
		if (!window.customElements || window.customElements.forcePolyfill) {
			polyfills.push('ce');
		}
	}


	if (polyfills.length) {
		var script = document.querySelector('script[src*="' + name +'"]'),
		    newScript = document.createElement('script'),
		    replacement = 'webcomponents-' + polyfills.join('-') + '.min.js';
		newScript.src = script.src.replace(name, replacement);

		if (document.readyState === 'loading' && ('import' in document.createElement('link'))) {
			document.write(newScript.outerHTML);
		} else {
			document.head.appendChild(newScript);
		}
		document.addEventListener('WebComponentsReady', function () {
			loadWC(wc);
		});
	} else {
		var fire = function() {
			requestAnimationFrame(function() {
				Joomla.WebComponents.ready = true;
				document.dispatchEvent(new CustomEvent('WebComponentsReady', {bubbles: true}));
				loadWC(wc);
			});
		};

		if (document.readyState !== 'loading') {
			fire();
		} else {
			document.addEventListener('readystatechange', function wait() {
				fire();
				document.removeEventListener('readystatechange', wait);
			});
		}
	}
})(window);
