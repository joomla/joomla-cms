import Button from '../../../../../node_modules/bootstrap/js/src/button';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Button = Button;

// Ensure vanilla mode, for consistency of the events
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const buttons = Joomla.getOptions('bootstrap.button');
  // Initialise the elements
  if (buttons && buttons.length) {
    buttons.forEach((selector) => {
      Array.from(document.querySelectorAll(selector))
        .map((el) => new window.bootstrap.Button(el));
    });
  }
}

export default Button;
