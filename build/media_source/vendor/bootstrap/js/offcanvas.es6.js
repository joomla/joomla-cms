import Offcanvas from '../../../../../node_modules/bootstrap/js/src/offcanvas';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Offcanvas = Offcanvas;

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const offcanvases = Joomla.getOptions('bootstrap.alert');
  // Initialise the elements
  if (offcanvases && offcanvases.length) {
    offcanvases.forEach((selector) => {
      Array.from(document.querySelectorAll(selector))
        .map((el) => new window.bootstrap.Offcanvas(el));
    });
  }
}

export default Offcanvas;
