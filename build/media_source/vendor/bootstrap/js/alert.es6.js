import Alert from '../../../../../node_modules/bootstrap/js/src/alert';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Alert = new WeakMap();

/**
 * Initialise the Alert iteractivity
 *
 * @param {HTMLElement} el The element that will become an Alert
 */
Joomla.Bootstrap.Initialise.Alert = (el) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Alert.get(el)) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Alert.set(el, new Alert(el));
};

const alerts = Joomla.getOptions('bootstrap.alert');

// Force Vanilla mode!
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

if (alerts && alerts.length) {
  alerts.forEach((selector) => {
    Array.from(document.querySelectorAll(selector))
      .map((el) => Joomla.Bootstrap.Initialise.Alert(el));
  });
}

export default Alert;
