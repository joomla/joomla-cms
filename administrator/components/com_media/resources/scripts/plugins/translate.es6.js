/**
 * Translate plugin
 */

const Translate = {};

// eslint-disable-next-line func-names
Translate.translate = function (key) {
  // Translate from Joomla text
  return Joomla.JText._(key, key);
};

// eslint-disable-next-line func-names
Translate.sprintf = function (string, ...args) {
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
    // eslint-disable-next-line no-plusplus
    i++;
    return val;
  });
};

// eslint-disable-next-line func-names
Translate.install = function (Vue) {
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
