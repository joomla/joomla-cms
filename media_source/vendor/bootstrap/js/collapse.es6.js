import Collapse from 'bootstrap/js/src/collapse';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Collapse = Collapse;

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const collapses = { ...Joomla.getOptions('bootstrap.collapse'), ...Joomla.getOptions('bootstrap.accordion') };
  // Initialise the elements
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
