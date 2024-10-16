// eslint-disable-next-line import/no-unresolved
import { Sa11y, Lang } from 'sa11y';
// eslint-disable-next-line import/no-unresolved
import Sa11yLang from 'sa11y-lang';

Lang.addI18n(Sa11yLang.strings);

window.addEventListener('load', () => {
  // eslint-disable-next-line no-new
  new Sa11y(Joomla.getOptions('jooa11yOptions', {}));
});
