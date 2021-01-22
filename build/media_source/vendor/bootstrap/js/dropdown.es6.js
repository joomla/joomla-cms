import Dropdown from '../../../../../node_modules/bootstrap/js/src/dropdown';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Dropdown = new WeakMap();

/**
 * Initialise the iteractivity
 *
 * @param {HTMLElement} el The element that will become an dropdown
 * @param {object} options The options for this dropdown
 */
Joomla.Bootstrap.Initialise.Dropdown = (el, options) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Dropdown.get(el)) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Dropdown.set(el, new Dropdown(el, options));
};

const dropdowns = Joomla.getOptions('bootstrap.dropdown');

// Force Vanilla mode!
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

if (dropdowns) {
  Object.keys(dropdowns).forEach((dropdown) => {
    const opt = dropdowns[dropdown];
    const options = {
      interval: opt.interval ? opt.interval : 5000,
      pause: opt.pause ? opt.pause : 'hover',
    };

    Array.from(document.querySelectorAll(dropdown))
      .map((el) => Joomla.Bootstrap.Initialise.Dropdown(el, options));
  });
}

export default Dropdown;
