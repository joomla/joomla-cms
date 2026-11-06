/**
 * Translate plugin
 */

const Translate = {
  // Translate from Joomla text
  translate: (key) => Joomla.Text._(key, key),
  sprintf: (string, ...args) => {
    const newString = Translate.translate(string);
    let i = 0;
    return newString.replace(/%((%)|s|d)/g, (m) => {
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
