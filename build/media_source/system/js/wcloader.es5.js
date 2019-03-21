/**
 * Web component polyfills loader, based on: https://github.com/webcomponents/webcomponentsjs/blob/master/webcomponents-loader.js
 * and https://github.com/open-wc/open-wc/blob/master/packages/polyfills-loader/polyfills-loader.js
 */
(function () {
	function asyncLaodScripts() {
		Joomla.WebComponents();
	}

	document.addEventListener('WebComponentsReady', asyncLaodScripts);

	function needsTemplatePolyfill() {
		/** no real <template> because no `content` property (IE and older browsers) */
		var template = document.createElement('template');
		if (!('content' in template)) {
			return true;
		}
		/** broken doc fragment (older Edge) */
		if (!(template.content.cloneNode() instanceof DocumentFragment)) {
			return true;
		}
		/** broken <template> cloning (Edge up to at least version 17) */
		var template2 = document.createElement('template');
		template2.content.appendChild(document.createElement('div'));
		template.content.appendChild(template2);
		var clone = template.cloneNode(true);
		return (
			clone.content.childNodes.length === 0 ||
			clone.content.firstChild.content.childNodes.length === 0
		);
	}

	/**
	 * Loads web component polyfills
	 */
	function loadPolyfills() {
		var needsCustomElements = !window.customElements || window.customElements.forcePolyfill;
		var needsShadowDom =
			!('attachShadow' in Element.prototype) ||
			!('getRootNode' in Element.prototype) ||
			(window.ShadyDOM && window.ShadyDOM.force);

		/** Return early for the browsers that support Web Components */
		if (!needsCustomElements && !needsShadowDom) {
			document.dispatchEvent(new CustomEvent('WebComponentsReady'));
			return;
		}

		var needsTemplate = needsTemplatePolyfill();
		var script = document.createElement('script');

		if (needsTemplate ||
			!window.Array.from ||
			!('Promise' in window) ||
			!('Symbol' in window) ||
			!('URLSearchParams' in window)) {
			/** template is a watermark for requiring all polyfills (IE11 and Edge) */
			script.src = '{{nevergreen}}';
			script.setAttribute('nomodule', '');
		} else if (needsShadowDom || needsCustomElements) {
			/**
			 * only chrome 53 supports shadow dom but not custom elements. this is an older browser, there is no need
			 * for complicating the setup here. there is no harm in loading the polyfills there
			 */
			script.src = '{{evergreen}}';
			script.setAttribute('type', 'module');
		}

		document.head.appendChild(script);
	}

	loadPolyfills();
})();
