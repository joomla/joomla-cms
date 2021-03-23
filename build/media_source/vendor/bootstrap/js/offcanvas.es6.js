import Offcanvas from '../../../../../node_modules/bootstrap/js/src/offcanvas';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Offcanvas = Offcanvas;

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const offcanvases = Joomla.getOptions('bootstrap.offcanvas');
  // Initialise the elements
  if (typeof offcanvases === 'object' && offcanvases !== null) {
    Object.keys(offcanvases)
      .forEach((offcanvas) => {
        const opt = offcanvases[offcanvas];
        const options = {
          interval: opt.interval ? opt.interval : 5000,
          pause: opt.pause ? opt.pause : 'hover',
        };

        const elements = Array.from(document.querySelectorAll(offcanvas));
        if (elements.length) {
          elements.map((el) => new window.bootstrap.Offcanvas(el, options));
        }
      });
  }
}

export default Offcanvas;
