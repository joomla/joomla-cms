import Carousel from 'bootstrap/js/src/carousel';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Carousel = Carousel;

if (Joomla && Joomla.getOptions) {
  // Get the elements configuration from PHP
  const carousels = Joomla.getOptions('bootstrap.carousel');

  if (typeof carousels === 'object' && carousels !== null) {
    Object.keys(carousels).forEach((carousel) => {
      const options = carousels[carousel];
      const elements = Array.from(document.querySelectorAll(carousel));
      if (elements.length) {
        elements.map((el) => new window.bootstrap.Carousel(el, options));
      }
    });
  }
}

export default Carousel;
