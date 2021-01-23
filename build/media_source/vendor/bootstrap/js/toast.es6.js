import Toast from '../../../../../node_modules/bootstrap/js/src/toast';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Toast = new WeakMap();

/**
 * Initialise the iteractivity
 *
 * @param {HTMLElement} el The element that will become an toast
 * @param {object} options The options for this toast
 */
Joomla.Bootstrap.Initialise.Toast = (el, options) => {
  if (!(el instanceof Element)) {
    return;
  }
  if (Joomla.Bootstrap.Instances.Toast.get(el) && el.dispose) {
    el.dispose();
  }
  Joomla.Bootstrap.Instances.Toast.set(el, new Toast(el, options));
};

// Ensure vanilla mode, for consistency of the events
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

// Get the elements/configurations from the PHP
const toasts = Joomla.getOptions('bootstrap.toast');
// Initialise the elements
if (typeof toasts === 'object' && toasts !== null) {
  Object.keys(toasts).forEach((toast) => {
    const opt = toasts[toast];
    const options = {
      animation: opt.animation ? opt.animation : true,
      autohide: opt.autohide ? opt.autohide : true,
      delay: opt.delay ? opt.delay : 5000,
    };

    const elements = Array.from(document.querySelectorAll(toast));
    if (elements.length) {
      elements.map((el) => Joomla.Bootstrap.Initialise.Toast(el, options));
    }
  });
}

export default Toast;
