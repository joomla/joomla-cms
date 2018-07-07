/* Polyfill service v3.14.0
 * For detailed credits and licence information see https://github.com/financial-times/polyfill-service.
 * 
 * UA detected: ie/8.0.0
 * Features requested: Array.prototype.filter
 * 
 * - Array.prototype.filter, License: CC0 */

(function(undefined) {
if (!('filter' in Array.prototype)) {

// Array.prototype.filter
Array.prototype.filter = function filter(callback) {
	if (this === undefined || this === null) {
		throw new TypeError(this + ' is not an object');
	}

	if (!(callback instanceof Function)) {
		throw new TypeError(callback + ' is not a function');
	}

	var
	object = Object(this),
	scope = arguments[1],
	arraylike = object instanceof String ? object.split('') : object,
	length = Math.max(Math.min(arraylike.length, 9007199254740991), 0) || 0,
	index = -1,
	result = [],
	element;

	while (++index < length) {
		element = arraylike[index];

		if (index in arraylike && callback.call(scope, element, index, object)) {
			result.push(element);
		}
	}

	return result;
};

}

})
.call('object' === typeof window && window || 'object' === typeof self && self || 'object' === typeof global && global || {});
