import Button from '../../../../../node_modules/bootstrap/js/src/button';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Button = new WeakMap();

/**
 * Initialise the Button iteractivity
 *
 * @param {HTMLElement} el The element that will become an Button
 */
Joomla.Bootstrap.Initialise.Button = (el) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Button.get(el)) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Button.set(el, new Button(el));
};

const buttons = Joomla.getOptions('bootstrap.button');

// Force Vanilla mode!
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

if (buttons && buttons.length) {
  buttons.forEach((selector) => {
    Array.from(document.querySelectorAll(selector))
      .map((el) => Joomla.Bootstrap.Initialise.Button(el));
  });
}

export default Button;
