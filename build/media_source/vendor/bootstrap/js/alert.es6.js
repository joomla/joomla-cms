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
  if (Joomla.Bootstrap.Instances.Alert.get(el) && el.dispose) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Alert.set(el, new Alert(el));
};

// Ensure vanilla mode, for consistency of the events
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

// Get the elements/configurations from the PHP
const alerts = Joomla.getOptions('bootstrap.alert');
// Initialise the elements
if (alerts && alerts.length) {
  alerts.forEach((selector) => {
    Array.from(document.querySelectorAll(selector))
      .map((el) => Joomla.Bootstrap.Initialise.Alert(el));
  });
}

export default Alert;
