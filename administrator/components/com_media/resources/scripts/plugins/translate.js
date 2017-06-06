/**
 * Translate plugin
 */

let Translate = {};

Translate.install = function (Vue, options) {
	Vue.mixin({
		methods: {
			translate: function (key) {
				 return Joomla.JText._(key, key);
			}
		}
	})
}

export default Translate;
