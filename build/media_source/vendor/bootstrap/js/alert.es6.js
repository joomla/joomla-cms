import Alert from '../../../../../node_modules/bootstrap/js/src/alert';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Alert = Alert;

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
      .map((el) => new window.bootstrap.Alert(el));
  });
}

export default Alert;
