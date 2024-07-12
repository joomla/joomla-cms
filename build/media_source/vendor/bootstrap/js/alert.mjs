import Alert from 'bootstrap/js/src/alert';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Alert = Alert;

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const alerts = Joomla.getOptions('bootstrap.alert');
  // Initialise the elements
  if (alerts && alerts.length) {
    alerts.forEach((selector) => {
      Array.from(document.querySelectorAll(selector))
        .map((el) => new window.bootstrap.Alert(el));
    });
  }
}

export default Alert;
