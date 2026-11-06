import Toast from 'bootstrap/js/src/toast';

window.bootstrap = window.bootstrap || {};
window.bootstrap.Toast = Toast;

if (Joomla && Joomla.getOptions) {
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
        elements.map((el) => new window.bootstrap.Toast(el, options));
      }
    });
  }
}

export default Toast;
