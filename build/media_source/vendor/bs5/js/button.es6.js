import Data from './dom/data.js';
import EventHandler from './dom/event-handler.js';

/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.0.0-beta1): util/index.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

const getjQuery = () => {
  const { jQuery } = window;

  if (jQuery && !document.body.hasAttribute('data-bs-no-jquery')) {
    return jQuery
  }

  return null
};

const onDOMContentLoaded = callback => {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', callback);
  } else {
    callback();
  }
};

const isRTL = document.documentElement.dir === 'rtl';

/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.0.0-beta1): base-component.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

/**
 * ------------------------------------------------------------------------
 * Constants
 * ------------------------------------------------------------------------
 */

const VERSION = '5.0.0-beta1';

class BaseComponent {
  constructor(element) {
    if (!element) {
      return
    }

    this._element = element;
    Data.setData(element, this.constructor.DATA_KEY, this);
  }

  dispose() {
    Data.removeData(this._element, this.constructor.DATA_KEY);
    this._element = null;
  }

  /** Static */

  static getInstance(element) {
    return Data.getData(element, this.DATA_KEY)
  }

  static get VERSION() {
    return VERSION
  }
}

/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.0.0-beta1): button.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

/**
 * ------------------------------------------------------------------------
 * Constants
 * ------------------------------------------------------------------------
 */

const NAME = 'button';
const DATA_KEY = 'bs.button';
const EVENT_KEY = `.${DATA_KEY}`;
const DATA_API_KEY = '.data-api';

const CLASS_NAME_ACTIVE = 'active';

const SELECTOR_DATA_TOGGLE = '[data-bs-toggle="button"]';

const EVENT_CLICK_DATA_API = `click${EVENT_KEY}${DATA_API_KEY}`;

/**
 * ------------------------------------------------------------------------
 * Class Definition
 * ------------------------------------------------------------------------
 */

class Button extends BaseComponent {
  // Getters

  static get DATA_KEY() {
    return DATA_KEY
  }

  // Public

  toggle() {
    // Toggle class and sync the `aria-pressed` attribute with the return value of the `.toggle()` method
    this._element.setAttribute('aria-pressed', this._element.classList.toggle(CLASS_NAME_ACTIVE));
  }

  // Static

  static jQueryInterface(config) {
    return this.each(function () {
      let data = Data.getData(this, DATA_KEY);

      if (!data) {
        data = new Button(this);
      }

      if (config === 'toggle') {
        data[config]();
      }
    })
  }
}

/**
 * ------------------------------------------------------------------------
 * Data Api implementation
 * ------------------------------------------------------------------------
 */

EventHandler.on(document, EVENT_CLICK_DATA_API, SELECTOR_DATA_TOGGLE, event => {
  event.preventDefault();

  const button = event.target.closest(SELECTOR_DATA_TOGGLE);

  let data = Data.getData(button, DATA_KEY);
  if (!data) {
    data = new Button(button);
  }

  data.toggle();
});

/**
 * ------------------------------------------------------------------------
 * jQuery
 * ------------------------------------------------------------------------
 * add .Button to jQuery only if jQuery is present
 */

onDOMContentLoaded(() => {
  const $ = getjQuery();
  /* istanbul ignore if */
  if ($) {
    const JQUERY_NO_CONFLICT = $.fn[NAME];
    $.fn[NAME] = Button.jQueryInterface;
    $.fn[NAME].Constructor = Button;

    $.fn[NAME].noConflict = () => {
      $.fn[NAME] = JQUERY_NO_CONFLICT;
      return Button.jQueryInterface
    };
  }
});

window.Joomla = window.Joomla || {};
window.Joomla.Bootstrap = {};
window.Joomla.Bootstrap.Button = Button;

export default Button;
