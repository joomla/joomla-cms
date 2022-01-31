import Carousel from 'bootstrap/js/src/carousel';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Carousel = Carousel;

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const carousels = Joomla.getOptions('bootstrap.carousel');
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
        elements.map((el) => new window.bootstrap.Carousel(el, options));
      }
    });
  }
}

export default Carousel;
