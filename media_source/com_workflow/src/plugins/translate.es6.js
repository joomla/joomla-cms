/**
 * Joomla Translation Plugin Wrapper
 * Provides global `translate` and `sprintf` methods to all Vue components
 */
const Translate = {
  /**
   * Translate a Joomla key
   * Falls back to key if translation is missing
   * @param {string} key
   * @returns {string}
   */
  translate: (key) => Joomla.Text._(key, key),

  /**
   * Format string using Joomla `sprintf`
   * @param {string} string
   * @param {...*} args
   * @returns {string}
   */
  sprintf: (string, ...args) => {
    const base = Translate.translate(string);
    let i = 0;
    return base.replace(/%((%)|s|d)/g, (m) => {
      let val = args[i];

      if (m === '%d') {
        val = parseFloat(val);
        if (Number.isNaN(val)) {
          val = 0;
        }
      }
      i += 1;
      return val;
    });
  },

  /**
   * Vue plugin install method
   * Adds $translate and $sprintf globally
   * @param {App} Vue
   */
  install: (Vue) => Vue.mixin({
    methods: {
      translate(key) {
        return Translate.translate(key);
      },
      sprintf(key, ...args) {
        return Translate.sprintf(key, ...args);
      },
    },
  }),
};

export default Translate;
