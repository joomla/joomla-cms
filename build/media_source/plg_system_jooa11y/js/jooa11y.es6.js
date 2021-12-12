import { Jooa11y, Lang } from 'joomla-a11y-checker/dist/js/joomla-a11y-checker.esm.js';

if (!Joomla) {
  throw new Error('Joomla API is not initaiated properly!');
}

const options = Joomla.getOptions('jooa11yOptions');

window.addEventListener('load', () => {
  /**
   * Set translations:
   * Lang.addI18n(Jooa11yLangEn.strings);
   */

  // Instantiate
  const checker = new Jooa11y(options);
  checker.doInitialCheck();
});
