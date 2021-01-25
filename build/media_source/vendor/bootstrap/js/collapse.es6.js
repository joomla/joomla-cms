import Collapse from '../../../../../node_modules/bootstrap/js/src/collapse';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Collapse = Collapse;

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
      elements.map((el) => new window.bootstrap.Collapse(el, options));
    }
  });
}

export default Collapse;
