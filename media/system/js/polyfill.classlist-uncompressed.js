/**
 * Polyfill service v3.13.0
 * For detailed credits and licence information see http://github.com/financial-times/polyfill-service.
 * 
 * Features requested: Element.prototype.classList
 * 
 * - Object.defineProperty, License: CC0 (required by "Element.prototype.classList")
 * - _DOMTokenList, License: CC0 (required by "Element.prototype.classList")
 * - Document, License: CC0 (required by "Element", "Element.prototype.classList")
 * - Element, License: CC0 (required by "Element.prototype.classList")
 * - Element.prototype.classList, License: CC0
 *
 * @build      https://cdn.polyfill.io/v2/polyfill.js?features=Element.prototype.classList&flags=always,gated
 * 
 * @copyright  Copyright (c) 2016 Financial Times
 * @license    MIT License, https://github.com/Financial-Times/polyfill-service/blob/master/LICENSE.md
 */

(function(undefined) {
if (!(// In IE8, defineProperty could only act on DOM elements, so full support
// for the feature requires the ability to set a property on an arbitrary object
'defineProperty' in Object && (function() {
	try {
		var a = {};
		Object.defineProperty(a, 'test', {value:42});
		return true;
	} catch(e) {
		return false
	}
}()))) {

// Object.defineProperty
(function (nativeDefineProperty) {

	var supportsAccessors = Object.prototype.hasOwnProperty('__defineGetter__');
	var ERR_ACCESSORS_NOT_SUPPORTED = 'Getters & setters cannot be defined on this javascript engine';
	var ERR_VALUE_ACCESSORS = 'A property cannot both have accessors and be writable or have a value';

	Object.defineProperty = function defineProperty(object, property, descriptor) {

		// Where native support exists, assume it
		if (nativeDefineProperty && (object === window || object === document || object === Element.prototype || object instanceof Element)) {
			return nativeDefineProperty(object, property, descriptor);
		}

		if (object === null || !(object instanceof Object || typeof object === 'object')) {
			throw new TypeError('Object must be an object (Object.defineProperty polyfill)');
		}

		if (!(descriptor instanceof Object)) {
			throw new TypeError('Descriptor must be an object (Object.defineProperty polyfill)');
		}

		var propertyString = String(property);
		var hasValueOrWritable = 'value' in descriptor || 'writable' in descriptor;
		var getterType = 'get' in descriptor && typeof descriptor.get;
		var setterType = 'set' in descriptor && typeof descriptor.set;

		// handle descriptor.get
		if (getterType) {
			if (getterType !== 'function') {
				throw new TypeError('Getter expected a function (Object.defineProperty polyfill)');
			}
			if (!supportsAccessors) {
				throw new TypeError(ERR_ACCESSORS_NOT_SUPPORTED);
			}
			if (hasValueOrWritable) {
				throw new TypeError(ERR_VALUE_ACCESSORS);
			}
			object.__defineGetter__(propertyString, descriptor.get);
		} else {
			object[propertyString] = descriptor.value;
		}

		// handle descriptor.set
		if (setterType) {
			if (setterType !== 'function') {
				throw new TypeError('Setter expected a function (Object.defineProperty polyfill)');
			}
			if (!supportsAccessors) {
				throw new TypeError(ERR_ACCESSORS_NOT_SUPPORTED);
			}
			if (hasValueOrWritable) {
				throw new TypeError(ERR_VALUE_ACCESSORS);
			}
			object.__defineSetter__(propertyString, descriptor.set);
		}

		// OK to define value unconditionally - if a getter has been specified as well, an error would be thrown above
		if ('value' in descriptor) {
			object[propertyString] = descriptor.value;
		}

		return object;
	};
}(Object.defineProperty));

}


// _DOMTokenList
var _DOMTokenList = (function () { // eslint-disable-line no-unused-vars

	function tokenize(token) {
		if (/^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/.test(token)) {
			return String(token);
		} else {
			throw new Error('InvalidCharacterError: DOM Exception 5');
		}
	}

	function toObject(self) {
		for (var index = -1, object = {}, element; element = self[++index];) {
			object[element] = true;
		}

		return object;
	}

	function fromObject(self, object) {
		var array = [], token;

		for (token in object) {
			if (object[token]) {
				array.push(token);
			}
		}

		[].splice.apply(self, [0, self.length].concat(array));
	}

	var DTL = function() {};

	DTL.prototype = {
		constructor: DTL,
		item: function item(index) {
			return this[parseFloat(index)] || null;
		},
		length: Array.prototype.length,
		toString: function toString() {
			return [].join.call(this, ' ');
		},

		add: function add() {
			for (var object = toObject(this), index = 0, token; index in arguments; ++index) {
				token = tokenize(arguments[index]);

				object[token] = true;
			}

			fromObject(this, object);
		},
		contains: function contains(token) {
			return token in toObject(this);
		},
		remove: function remove() {
			for (var object = toObject(this), index = 0, token; index in arguments; ++index) {
				token = tokenize(arguments[index]);

				object[token] = false;
			}

			fromObject(this, object);
		},
		toggle: function toggle(token) {
			var
			object = toObject(this),
			contains = 1 in arguments ? !arguments[1] : tokenize(token) in object;

			object[token] = !contains;

			fromObject(this, object);

			return !contains;
		}
	};

	return DTL;

}());
if (!("Document" in this)) {

// Document
if (this.HTMLDocument) { // IE8

	// HTMLDocument is an extension of Document.  If the browser has HTMLDocument but not Document, the former will suffice as an alias for the latter.
	this.Document = this.HTMLDocument;

} else {

	// Create an empty function to act as the missing constructor for the document object, attach the document object as its prototype.  The function needs to be anonymous else it is hoisted and causes the feature detect to prematurely pass, preventing the assignments below being made.
	this.Document = this.HTMLDocument = document.constructor = (new Function('return function Document() {}')());
	this.Document.prototype = document;
}

}

if (!('Element' in this && 'HTMLElement' in this)) {

// Element
(function () {

	// IE8
	if (window.Element && !window.HTMLElement) {
		window.HTMLElement = window.Element;
		return;
	}

	// create Element constructor
	window.Element = window.HTMLElement = new Function('return function Element() {}')();

	// generate sandboxed iframe
	var vbody = document.appendChild(document.createElement('body'));
	var frame = vbody.appendChild(document.createElement('iframe'));

	// use sandboxed iframe to replicate Element functionality
	var frameDocument = frame.contentWindow.document;
	var prototype = Element.prototype = frameDocument.appendChild(frameDocument.createElement('*'));
	var cache = {};

	// polyfill Element.prototype on an element
	var shiv = function (element, deep) {
		var
		childNodes = element.childNodes || [],
		index = -1,
		key, value, childNode;

		if (element.nodeType === 1 && element.constructor !== Element) {
			element.constructor = Element;

			for (key in cache) {
				value = cache[key];
				element[key] = value;
			}
		}

		while (childNode = deep && childNodes[++index]) {
			shiv(childNode, deep);
		}

		return element;
	};

	var elements = document.getElementsByTagName('*');
	var nativeCreateElement = document.createElement;
	var interval;
	var loopLimit = 100;

	prototype.attachEvent('onpropertychange', function (event) {
		var
		propertyName = event.propertyName,
		nonValue = !cache.hasOwnProperty(propertyName),
		newValue = prototype[propertyName],
		oldValue = cache[propertyName],
		index = -1,
		element;

		while (element = elements[++index]) {
			if (element.nodeType === 1) {
				if (nonValue || element[propertyName] === oldValue) {
					element[propertyName] = newValue;
				}
			}
		}

		cache[propertyName] = newValue;
	});

	prototype.constructor = Element;

	if (!prototype.hasAttribute) {
		// <Element>.hasAttribute
		prototype.hasAttribute = function hasAttribute(name) {
			return this.getAttribute(name) !== null;
		};
	}

	// Apply Element prototype to the pre-existing DOM as soon as the body element appears.
	function bodyCheck() {
		if (!(loopLimit--)) clearTimeout(interval);
		if (document.body && !document.body.prototype && /(complete|interactive)/.test(document.readyState)) {
			shiv(document, true);
			if (interval && document.body.prototype) clearTimeout(interval);
			return (!!document.body.prototype);
		}
		return false;
	}
	if (!bodyCheck(true)) {
		document.onreadystatechange = bodyCheck;
		interval = setInterval(bodyCheck, 25);
	}

	// Apply to any new elements created after load
	document.createElement = function createElement(nodeName) {
		var element = nativeCreateElement(String(nodeName).toLowerCase());
		return shiv(element);
	};

	// remove sandboxed iframe
	document.removeChild(vbody);
}());

}

if (!('document' in this && "classList" in document.documentElement)) {

// Element.prototype.classList
Object.defineProperty(Element.prototype, 'classList', {
	configurable: true,
	get: function () {

		function pull() {
			var className = (typeof element.className === "object" ? element.className.baseVal : element.className);
			[].splice.apply(classList, [0, classList.length].concat((className || '').replace(/^\s+|\s+$/g, '').split(/\s+/)));
		}

		function push() {
			if (element.attachEvent) {
				element.detachEvent('onpropertychange', pull);
			}

			if (typeof element.className === "object") {
				element.className.baseVal = original.toString.call(classList);
			} else {
				element.className = original.toString.call(classList);
			}

			if (element.attachEvent) {
				element.attachEvent('onpropertychange', pull);
			}
		}

		var element = this;
		var original = _DOMTokenList.prototype;
		var ClassList = function ClassList() {};
		var classList;

		ClassList.prototype = new _DOMTokenList;

		ClassList.prototype.item = function item(index) { // eslint-disable-line no-unused-vars
			return pull(), original.item.apply(classList, arguments);
		};

		ClassList.prototype.toString = function toString() {
			return pull(), original.toString.apply(classList, arguments);
		};

		ClassList.prototype.add = function add() {
			return pull(), original.add.apply(classList, arguments), push();
		};

		ClassList.prototype.contains = function contains(token) { // eslint-disable-line no-unused-vars
			return pull(), original.contains.apply(classList, arguments);
		};

		ClassList.prototype.remove = function remove() {
			return pull(), original.remove.apply(classList, arguments), push();
		};

		ClassList.prototype.toggle = function toggle(token) {
			return pull(), token = original.toggle.apply(classList, arguments), push(), token;
		};

		classList = new ClassList;

		if (element.attachEvent) {
			element.attachEvent('onpropertychange', pull);
		}

		return classList;
	}
});

}


})
.call('object' === typeof window && window || 'object' === typeof self && self || 'object' === typeof global && global || {});
