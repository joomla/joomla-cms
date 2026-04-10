import { Lang, Sa11y } from 'sa11y';
import Sa11yLang from 'sa11y-lang';

Lang.addI18n(Sa11yLang.strings);
window.addEventListener('load', () => {
  new Sa11y(Joomla.getOptions('jooa11yOptions', {}));
});
