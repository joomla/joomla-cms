import Mark from 'mark.js/src/vanilla';

// mark.js defaults
const defaultOptions = {
  exclude: [],
  separateWordSearch: true,
  accuracy: 'partially',
  diacritics: true,
  synonyms: {},
  iframes: false,
  iframesTimeout: 5000,
  acrossElements: true,
  caseSensitive: false,
  ignoreJoiners: false,
  wildcards: 'disabled',
  compatibility: false,
};

if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('highlight')) {
  const scriptOptions = Joomla.getOptions('highlight');
  scriptOptions.forEach((currentOpts) => {
    const options = { ...defaultOptions, ...currentOpts };

    // Continue only if the element exists
    if (!options.compatibility) {
      const element = document.querySelector(`.${options.class}`);

      if (element) {
        const instance = new Mark(element);

        // Loop through the terms
        options.highLight.forEach((term) => {
          instance.mark(term, options);
        });
      }
    } else {
      const start = document.querySelector(`#${options.start}`);
      const end = document.querySelector(`#${options.end}`);
      const parent = start.parentNode;
      const targetNodes = [];
      const allElems = Array.from(parent.childNodes);
      let startEl = false;
      let stopEl = false;

      // Remove all elements till start element
      allElems.forEach((element) => {
        if (!startEl || stopEl) {
          return;
        }
        if (element === start) {
          startEl = true;
          return;
        }
        if (element === end) {
          stopEl = true;
          return;
        }
        if (startEl && !stopEl) {
          targetNodes.push(element);
        }
      });

      targetNodes.forEach((node) => {
        const instance = new Mark(node);
        // Loop through the terms
        options.highLight.map((term) => instance.mark(term, options));
      });
    }
  });
}
