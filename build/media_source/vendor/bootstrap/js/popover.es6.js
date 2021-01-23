import Popover from '../../../../../node_modules/bootstrap/js/src/popover';
import Tooltip from '../../../../../node_modules/bootstrap/js/src/tooltip';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Popover = new WeakMap();
Joomla.Bootstrap.Instances.Tooltip = new WeakMap();

/**
 * Initialise the Popover iteractivity
 *
 * @param {HTMLElement} el The element that will become an popover
 * @param {object} options The options for this popover
 */
Joomla.Bootstrap.Initialise.Popover = (el, options) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Popover.get(el) && el.dispose) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Popover.set(el, new Popover(el, options));
};

/**
 * Initialise the Tooltip iteractivity
 *
 * @param {HTMLElement} el The element that will become an tooltip
 * @param {object} options The options for this tooltip
 */
Joomla.Bootstrap.Initialise.Tooltip = (el, options) => {
  if (Joomla.Bootstrap.Instances.Tooltip.get(el) && el.dispose) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Tooltip.set(el, new Tooltip(el, options));
};

// Ensure vanilla mode, for consistency of the events
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

// Get the elements/configurations from the PHP
const tooltips = Joomla.getOptions('bootstrap.tooltip');
const popovers = Joomla.getOptions('bootstrap.popover');
// Initialise the elements
if (typeof popovers === 'object' && popovers !== null) {
  Object.keys(popovers).forEach((popover) => {
    const opt = popovers[popover];
    const options = {
      animation: opt.animation ? opt.animation : true,
      container: opt.container ? opt.container : false,
      content: opt.content ? opt.content : '',
      delay: opt.delay ? opt.delay : 0,
      html: opt.html ? opt.html : false,
      placement: opt.placement ? opt.placement : 'top',
      selector: opt.selector ? opt.selector : false,
      title: opt.title ? opt.title : '',
      trigger: opt.trigger ? opt.trigger : 'click',
      offset: opt.offset ? opt.offset : 0,
      fallbackPlacement: opt.fallbackPlacement ? opt.fallbackPlacement : 'flip',
      boundary: opt.boundary ? opt.boundary : 'scrollParent',
      customClass: opt.customClass ? opt.customClass : '',
      sanitize: opt.sanitize ? opt.sanitize : true,
      sanitizeFn: opt.sanitizeFn ? opt.sanitizeFn : null,
      popperConfig: opt.popperConfig ? opt.popperConfig : null,
    };

    if (opt.template) {
      options.template = opt.template;
    }
    if (opt.allowList) {
      options.allowList = opt.allowList;
    }

    const elements = Array.from(document.querySelectorAll(popover));
    if (elements.length) {
      elements.map((el) => Joomla.Bootstrap.Initialise.Popover(el, options));
    }
  });
}
// Initialise the elements
if (typeof tooltips === 'object' && tooltips !== null) {
  Object.keys(tooltips).forEach((tooltip) => {
    const opt = tooltips[tooltip];
    const options = {
      animation: opt.animation ? opt.animation : true,
      container: opt.container ? opt.container : false,
      delay: opt.delay ? opt.delay : 0,
      html: opt.html ? opt.html : false,
      selector: opt.selector ? opt.selector : false,
      trigger: opt.trigger ? opt.trigger : 'hover focus',
      fallbackPlacement: opt.fallbackPlacement ? opt.fallbackPlacement : null,
      boundary: opt.boundary ? opt.boundary : 'clippingParents',
      title: opt.title ? opt.title : '',
      customClass: opt.customClass ? opt.customClass : '',
      sanitize: opt.sanitize ? opt.sanitize : true,
      sanitizeFn: opt.sanitizeFn ? opt.sanitizeFn : null,
      popperConfig: opt.popperConfig ? opt.popperConfig : null,
    };

    if (opt.placement) {
      options.placement = opt.placement;
    }
    if (opt.template) {
      options.template = opt.template;
    }
    if (opt.allowList) {
      options.allowList = opt.allowList;
    }

    const elements = Array.from(document.querySelectorAll(tooltip));
    if (elements.length) {
      elements.map((el) => Joomla.Bootstrap.Initialise.Tooltip(el, options));
    }
  });
}

export { Tooltip, Popover };
