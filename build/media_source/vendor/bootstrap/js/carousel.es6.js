import Carousel from '../../../../../node_modules/bootstrap/js/src/carousel';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Carousel = new WeakMap();

/**
 * Initialise the Carousel iteractivity
 *
 * @param {HTMLElement} el The element that will become an Carousel
 * @param {object} options The options for this carousel
 */
Joomla.Bootstrap.Initialise.Carousel = (el, options) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Carousel.get(el) && el.dispose) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Carousel.set(el, new Carousel(el, options));
};

// Get the elements/configurations from the PHP
const carousels = Joomla.getOptions('bootstrap.carousel');

// Force Vanilla mode!
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}
// Initialise the elements
if (typeof carousels === 'object' && carousels !== null) {
  Object.keys(carousels).forEach((carousel) => {
    const opt = carousels[carousel];
    const options = {
      interval: opt.interval ? opt.interval : 5000,
      keyboard: opt.keyboard ? opt.keyboard : true,
      pause: opt.pause ? opt.pause : 'hover',
      slide: opt.slide ? opt.slide : false,
      wrap: opt.wrap ? opt.wrap : true,
      touch: opt.touch ? opt.touch : true,
    };

    const elements = Array.from(document.querySelectorAll(carousel));
    if (elements.length) {
      elements.map((el) => Joomla.Bootstrap.Initialise.Carousel(el, options));
    }
  });
}

export default Carousel;
