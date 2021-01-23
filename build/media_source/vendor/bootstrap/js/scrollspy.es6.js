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
  if (Joomla.Bootstrap.Instances.Scrollspy.get(el)) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Scrollspy.set(el, new Scrollspy(el, options));
};

const scrollspys = Joomla.getOptions('bootstrap.scrollspy');

// Force Vanilla mode!
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

if (scrollspys) {
  Object.keys(scrollspys).forEach((scrollspy) => {
    const opt = scrollspys[scrollspy];
    const options = {
      offset: opt.offset ? opt.offset : 10,
      method: opt.method ? opt.method : 'auto',
      target: opt.target ? opt.target : null,
    };

    Array.from(document.querySelector(scrollspy))
      .map((el) => Joomla.Bootstrap.Initialise.Scrollspy(el, options));
  });
}

export default Scrollspy;
