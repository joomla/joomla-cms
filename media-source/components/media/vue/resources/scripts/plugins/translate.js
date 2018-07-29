/**
 * Translate plugin
 */

let Translate = {};

Translate.translate = function (key) {
	// Translate from Joomla text
	return Joomla.JText._(key, key);
}


Translate.sprintf = function (string, ...args) {
	string = this.translate(string);
	var i = 0;
	return string.replace(/%((%)|s|d)/g, function (m) {
		var val = args[i];

		if (m == '%d') {
			val = parseFloat(val);
			if (isNaN(val)) {
				val = 0;
			}
		}
		i++;
		return val;
	});
}

Translate.install = function (Vue, options) {
	Vue.mixin({
		methods: {
			translate: function (key) {
				return Translate.translate(key);
			}
		}
	})
}

export default Translate;
