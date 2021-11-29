window.addEventListener('load', () => {
  // Check if Jooa11y is loaded
  if (!Jooa11y) {
    return;
  }

  // Set translations
  // Jooa11y.Lang.addI18n(Jooa11yLangEn.strings);

  const options = Joomla.getOptions('jooa11yOptions');

  // Instantiate
  const checker = new Jooa11y.Jooa11y(options);
  checker.doInitialCheck();
});
