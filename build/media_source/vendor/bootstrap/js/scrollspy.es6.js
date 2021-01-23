import Scrollspy from '../../../../../node_modules/bootstrap/js/src/scrollspy';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Scrollspy = new WeakMap();

/**
 * Initialise the Scrollspy iteractivity
 *
 * @param {HTMLElement} el The element that will become a scrollspy
 * @param {object} options The options for this scrollspy
 */
Joomla.Bootstrap.Initialise.Scrollspy = (el, options) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Scrollspy.get(el) && el.dispose) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Scrollspy.set(el, new Scrollspy(el, options));
};

// Ensure vanilla mode, for consistency of the events
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

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
      elements.map((el) => Joomla.Bootstrap.Initialise.Scrollspy(el, options));
    }
  });
}

export default Scrollspy;
