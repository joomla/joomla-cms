import Modal from 'bootstrap/js/src/modal';

Joomla = Joomla || {};
Joomla.Modal = Joomla.Modal || {};
window.bootstrap = window.bootstrap || {};
window.bootstrap.Modal = Modal;

const allowed = {
  iframe: ['src', 'name', 'width', 'height'],
};

Joomla.initialiseModal = (modal, options) => {
  if (!(modal instanceof Element)) {
    return;
  }

  // eslint-disable-next-line no-new
  new window.bootstrap.Modal(modal, options);

  // Comply with the Joomla API - Bound element.open/close
  modal.open = () => { window.bootstrap.Modal.getInstance(modal).show(modal); };
  modal.close = () => { window.bootstrap.Modal.getInstance(modal).hide(); };

  // Do some Joomla specific changes
  modal.addEventListener('show.bs.modal', () => {
    // Comply with the Joomla API - Set the current Modal ID
    Joomla.Modal.setCurrent(modal);

    if (modal.dataset.url) {
      const modalBody = modal.querySelector('.modal-body');
      const iframe = modalBody.querySelector('iframe');

      if (iframe) {
        const addData = modal.querySelector('joomla-field-mediamore');

        if (addData) {
          addData.parentNode.removeChild(addData);
        }

        iframe.parentNode.removeChild(iframe);
      }

      // @todo merge https://github.com/joomla/joomla-cms/pull/20788
      // Hacks because com_associations and field modals use pure javascript in the url!
      if (modal.dataset.iframe.indexOf('document.getElementById') > 0) {
        const iframeTextArr = modal.dataset.iframe.split('+');
        const idFieldArr = iframeTextArr[1].split('"');
        let el;

        idFieldArr[0] = idFieldArr[0].replace(/&quot;/g, '"');

        if (!document.getElementById(idFieldArr[1])) {
          // eslint-disable-next-line no-new-func
          const fn = new Function(`return ${idFieldArr[0]}`); // This is UNSAFE!!!!
          el = fn.call(null);
        } else {
          el = document.getElementById(idFieldArr[1]).value;
        }

        modalBody.insertAdjacentHTML('afterbegin', Joomla.sanitizeHtml(`${iframeTextArr[0]}${el}${iframeTextArr[2]}`, allowed));
      } else {
        modalBody.insertAdjacentHTML('afterbegin', Joomla.sanitizeHtml(modal.dataset.iframe, allowed));
      }
    }
  });

  modal.addEventListener('shown.bs.modal', () => {
    const modalBody = modal.querySelector('.modal-body');
    const modalHeader = modal.querySelector('.modal-header');
    const modalFooter = modal.querySelector('.modal-footer');
    let modalHeaderHeight = 0;
    let modalFooterHeight = 0;
    let maxModalBodyHeight = 0;
    let modalBodyPadding = 0;
    let modalBodyHeightOuter = 0;

    if (modalBody) {
      if (modalHeader) {
        const modalHeaderRects = modalHeader.getBoundingClientRect();
        modalHeaderHeight = modalHeaderRects.height;
        modalBodyHeightOuter = modalBody.offsetHeight;
      }
      if (modalFooter) {
        modalFooterHeight = parseFloat(getComputedStyle(modalFooter, null).height.replace('px', ''));
      }

      const modalBodyHeight = parseFloat(getComputedStyle(modalBody, null).height.replace('px', ''));
      const padding = modalBody.offsetTop;
      const maxModalHeight = parseFloat(getComputedStyle(document.body, null).height.replace('px', '')) - (padding * 2);
      modalBodyPadding = modalBodyHeightOuter - modalBodyHeight;
      maxModalBodyHeight = maxModalHeight - (modalHeaderHeight + modalFooterHeight + modalBodyPadding);
    }

    if (modal.dataset.url) {
      const iframeEl = modal.querySelector('iframe');
      const iframeHeight = parseFloat(getComputedStyle(iframeEl, null).height.replace('px', ''));
      if (iframeHeight > maxModalBodyHeight) {
        modalBody.style.maxHeight = maxModalBodyHeight;
        modalBody.style.overflowY = 'auto';
        iframeEl.style.maxHeight = maxModalBodyHeight - modalBodyPadding;
      }
    }
  });

  modal.addEventListener('hide.bs.modal', () => {
    const modalBody = modal.querySelector('.modal-body');
    modalBody.style.maxHeight = 'initial';
  });

  modal.addEventListener('hidden.bs.modal', () => {
    // Comply with the Joomla API - Remove the current Modal ID
    Joomla.Modal.setCurrent('');
  });
};

/**
 * Method to invoke a click on button inside an iframe
 *
 * @param   {object}  options  Object with the css selector for the parent element of an iframe
 *                             and the selector of the button in the iframe that will be clicked
 *                             { iframeSelector: '', buttonSelector: '' }
 * @returns {boolean}
 *
 * @since   4.0.0
 */
Joomla.iframeButtonClick = (options) => {
  if (!options.iframeSelector || !options.buttonSelector) {
    throw new Error('Selector is missing');
  }

  // Backward compatibility for older buttons
  const old2newBtn = {
    '#closeBtn': '#closeBtn, #toolbar-cancel>button',
    '#saveBtn': '#saveBtn, #toolbar-save>button',
    '#applyBtn': '#applyBtn, #toolbar-apply>button',
  };

  const iframe = document.querySelector(`${options.iframeSelector} iframe`);
  if (iframe) {
    const selector = old2newBtn[options.buttonSelector] ? old2newBtn[options.buttonSelector] : options.buttonSelector;
    const button = iframe.contentWindow.document.querySelector(selector);
    if (button) {
      button.click();
    }
  }
};

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const modals = Joomla.getOptions('bootstrap.modal');
  // Initialise the elements
  if (typeof modals === 'object' && modals !== null) {
    Object.keys(modals).forEach((modal) => {
      const opt = modals[modal];
      const options = {
        backdrop: opt.backdrop ? opt.backdrop : true,
        keyboard: opt.keyboard ? opt.keyboard : true,
        focus: opt.focus ? opt.focus : true,
      };

      document.querySelectorAll(modal).forEach((modalEl) => Joomla.initialiseModal(modalEl, options));
    });
  }
}

export default Modal;
