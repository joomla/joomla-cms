/**
 * Translate plugin
 */

const Translate = {
  // Translate from Joomla text
  translate: (key) => Joomla.Text._(key, key),
  sprintf: (string, ...args) => {
    // eslint-disable-next-line no-param-reassign
    string = Translate.translate(string);
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
  },
  install: (Vue) => Vue.mixin({
    methods: {
      translate(key) {
        return Translate.translate(key);
      },
      sprintf(key, ...args) {
        return Translate.sprintf(key, args);
      },
    },
  }),
};

export default Translate;
