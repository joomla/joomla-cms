/**
 * Translate plugin
 */

const Translate = {};

Translate.translate = (key) => {
  // Translate from Joomla text
  window.Joomla.JText._(key, key);
};

Translate.sprintf = (string, ...args) => {
  // eslint-disable-next-line no-param-reassign
  string = this.translate(string);
  let i = 0;
  return string.replace(/%((%)|s|d)/g, (m) => {
    let val = args[i];

    if (m === '%d') {
      val = parseFloat(val);
      // eslint-disable-next-line no-restricted-globals
      if (isNaN(val)) {
        val = 0;
      }
    }
    i += 1;
    return val;
  });
};

Translate.install = (Vue) => {
  Vue.mixin({
    methods: {
      translate(key) {
        return Translate.translate(key);
      },
      sprintf(key, ...args) {
        return Translate.sprintf(key, args);
      },
    },
  });
};

export default Translate;
