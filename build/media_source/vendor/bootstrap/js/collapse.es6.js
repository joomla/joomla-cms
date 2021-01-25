import Collapse from '../../../../../node_modules/bootstrap/js/src/collapse';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Collapse = new WeakMap();

/**
 * Initialise the Collapse iteractivity
 *
 * @param {HTMLElement} el The element that will become an collapse
 * @param {object} options The options for this collapse
 */
Joomla.Bootstrap.Initialise.Collapse = (el, options) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Collapse.get(el) && el.dispose) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Collapse.set(el, new Collapse(el, options));
};

// Ensure vanilla mode, for consistency of the events
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

// Get the elements/configurations from the PHP
const collapses = { ...Joomla.getOptions('bootstrap.collapse'), ...Joomla.getOptions('bootstrap.accordion') };
// Initialise the elements
if (typeof collapses === 'object' && collapses !== null) {
  Object.keys(collapses).forEach((collapse) => {
    const opt = collapses[collapse];
    const options = {
      toggle: opt.toggle ? opt.toggle : true,
    };

    if (opt.parent) {
      options.parent = opt.parent;
    }

    const elements = Array.from(document.querySelectorAll(collapse));
    if (elements.length) {
      elements.map((el) => Joomla.Bootstrap.Initialise.Collapse(el, options));
    }
  });
}

export default Collapse;
