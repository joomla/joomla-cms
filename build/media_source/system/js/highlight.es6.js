import Mark from 'mark.js/src/vanilla';

if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('js-highlight')) {
  const options = Joomla.getOptions('js-highlight');
  const markOptions = {
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
  };

  // Continue only if the element exists
  if (options.class && !options.compatibility) {
    const element = document.querySelector(`.${options.class}`);

    if (element) {
      const instance = new Mark(element);

      // Loop through the terms
      options.highLight.forEach((term) => {
        instance.mark(term, markOptions);
      });
    }
  } else {
    const start = document.querySelector(`#${options.start}`);
    const end = document.querySelector(`#${options.end}`);
    const parent = start.parentNode;
    const targetNodes = [];
    const allElems = Array.from(parent.childNodes);

    // Remove all elements till start element
    allElems.forEach((element) => {
      if (element !== start || element !== end) {
        targetNodes.push(element);
      }
    });

    targetNodes.forEach((node) => {
      const instance = new Mark(node);
      // Loop through the terms
      options.highLight.map((term) => instance.mark(term, markOptions));
    });
  }
}
