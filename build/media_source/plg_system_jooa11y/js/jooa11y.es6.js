// eslint-disable-next-line import/no-unresolved
import { Jooa11y, Lang } from '@joomla/joomla-a11y-checker/dist/js/joomla-a11y-checker.esm.js';

if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

const stringPrefix = 'PLG_SYSTEM_JOOA11Y_';

Lang.translate = (string) => Joomla.Text._(stringPrefix + string, string);

const options = Joomla.getOptions('jooa11yOptions');

window.addEventListener('load', () => {
  // Instantiate
  const checker = new Jooa11y(options);
  checker.doInitialCheck();
});
