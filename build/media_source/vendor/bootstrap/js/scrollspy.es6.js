import Scrollspy from 'bootstrap/js/src/scrollspy';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Scrollspy = Scrollspy;

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const scrollspys = Joomla.getOptions('bootstrap.scrollspy');
  // Initialise the elements
  if (typeof scrollspys === 'object' && scrollspys !== null) {
    Object.keys(scrollspys).forEach((scrollspy) => {
      const opt = scrollspys[scrollspy];
      const options = {
        offset: opt.offset ? opt.offset : 10,
        method: opt.method ? opt.method : 'auto',
      };

      if (opt.target) {
        options.target = opt.target;
      }

      const elements = Array.from(document.querySelectorAll(scrollspy));
      if (elements.length) {
        elements.map((el) => new window.bootstrap.Scrollspy(el, options));
      }
    });
  }
}

export default Scrollspy;
