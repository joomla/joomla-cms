import Dropdown from 'bootstrap/js/src/dropdown';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Dropdown = Dropdown;

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const dropdowns = Joomla.getOptions('bootstrap.dropdown');
  // Initialise the elements
  if (typeof dropdowns === 'object' && dropdowns !== null) {
    Object.keys(dropdowns).forEach((dropdown) => {
      const opt = dropdowns[dropdown];
      const options = {
        interval: opt.interval ? opt.interval : 5000,
        pause: opt.pause ? opt.pause : 'hover',
      };

      const elements = Array.from(document.querySelectorAll(dropdown));
      if (elements.length) {
        elements.map((el) => new window.bootstrap.Dropdown(el, options));
      }
    });
  }
}

export default Dropdown;
