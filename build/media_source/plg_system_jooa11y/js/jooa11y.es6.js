window.addEventListener('load', () => {
  // Check if Jooa11y is loaded
  if (!Jooa11y) {
    return;
  }

  // Set translations
  Jooa11y.Lang.addI18n(Jooa11yLangEn.strings);

  // Instantiate
  const checker = new Jooa11y.Jooa11y(Jooa11yLangEn.options);
  checker.doInitialCheck();
});
