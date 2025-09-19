import Button from 'bootstrap/js/src/button';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Button = Button;

if (Joomla?.getOptions) {
  // Get the elements/configurations from the PHP
  const buttons = Joomla.getOptions('bootstrap.button');
  // Initialise the elements
  if (buttons?.length) {
    buttons.forEach((selector) => {
      document.querySelectorAll(selector).forEach((el) => {
        new window.bootstrap.Button(el);
      });
    });
  }
}

export default Button;
