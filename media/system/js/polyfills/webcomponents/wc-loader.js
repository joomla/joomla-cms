(function (window) {
'use strict';
/* Check if ES6 then apply the shim */
var check = function () {
	try {
		eval("var foo = (x)=>x+1");
	} catch (e) {
		return false;
	}
	return true;
};
var es6Shim = "(()=>{'use strict';if(!window.customElements)return;const a=window.HTMLElement,b=window.customElements.define,c=window.customElements.get,d=new Map,e=new Map;let f=!1,g=!1;window.HTMLElement=function(){if(!f){const j=d.get(this.constructor),k=c.call(window.customElements,j);g=!0;const l=new k;return l}f=!1;},window.HTMLElement.prototype=a.prototype;Object.defineProperty(window,'customElements',{value:window.customElements,configurable:!0,writable:!0}),Object.defineProperty(window.customElements,'define',{value:(j,k)=>{const l=k.prototype,m=class extends a{constructor(){super(),Object.setPrototypeOf(this,l),g||(f=!0,k.call(this)),g=!1;}},n=m.prototype;m.observedAttributes=k.observedAttributes,n.connectedCallback=l.connectedCallback,n.disconnectedCallback=l.disconnectedCallback,n.attributeChangedCallback=l.attributeChangedCallback,n.adoptedCallback=l.adoptedCallback,d.set(k,j),e.set(j,k),b.call(window.customElements,j,m);},configurable:!0,writable:!0}),Object.defineProperty(window.customElements,'get',{value:(j)=>e.get(j),configurable:!0,writable:!0});})()";
/* Trick so ES5 code won't throw with evergreen browsers */
if (check()) (new Function(es6Shim))();

/* Load webcomponents async */
var loadWC = function() {
	if (Joomla.getOptions && typeof Joomla.getOptions === "function") {
		var el, p, wc = Joomla.getOptions('webcomponents', {});
		for (p in wc) {
			if (wc.hasOwnProperty(p)) {
				if (wc[p].match(/.js/)) {
					el = document.createElement('script');
					el.src = wc[p];
				} else if (wc[p].match(/.html/)) {
					el = document.createElement('link');
					el.setAttribute('href', wc[p]);
					el.setAttribute('rel', 'import');
				}
				if (el) {
					document.head.appendChild(el);
				}
			}
		}
	}
};

window.Joomla = Joomla || {};
Joomla.WebComponents = Joomla.WebComponents || {};
var name = 'wc-loader.js', polyfills = [];

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
		loadWC();
	});
} else {
	var fire = function() {
		requestAnimationFrame(function() {
			Joomla.WebComponents.ready = true;
			document.dispatchEvent(new CustomEvent('WebComponentsReady', {bubbles: true}));
			loadWC();
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
