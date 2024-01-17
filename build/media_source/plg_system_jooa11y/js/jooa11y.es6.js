import { Sa11y, Lang } from 'sa11y/dist/js/sa11y.esm';
import Sa11yLangEn from 'sa11y/dist/js/lang/en';


if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

Lang.addI18n(Sa11yLangEn.strings);

const options = Joomla.getOptions('jooa11yOptions');

window.addEventListener('load', () => {
  // Instantiate
  const checker = new Sa11y(options);
});
