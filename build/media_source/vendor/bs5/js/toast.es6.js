import Data from './dom/data.js';
import EventHandler from './dom/event-handler.js';
import Manipulator from './dom/manipulator.js';

/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.0.0-beta1): util/index.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */
const MILLISECONDS_MULTIPLIER = 1000;
const TRANSITION_END = 'transitionend';

// Shoutout AngusCroll (https://goo.gl/pxwQGp)
const toType = obj => {
  if (obj === null || obj === undefined) {
    return `${obj}`
  }

  return {}.toString.call(obj).match(/\s([a-z]+)/i)[1].toLowerCase()
};

const getTransitionDurationFromElement = element => {
  if (!element) {
    return 0
  }

  // Get transition-duration of the element
  let { transitionDuration, transitionDelay } = window.getComputedStyle(element);

  const floatTransitionDuration = Number.parseFloat(transitionDuration);
  const floatTransitionDelay = Number.parseFloat(transitionDelay);

  // Return 0 if element or transition duration is not found
  if (!floatTransitionDuration && !floatTransitionDelay) {
    return 0
  }

  // If multiple durations are defined, take the first
  transitionDuration = transitionDuration.split(',')[0];
  transitionDelay = transitionDelay.split(',')[0];

  return (Number.parseFloat(transitionDuration) + Number.parseFloat(transitionDelay)) * MILLISECONDS_MULTIPLIER
};

const triggerTransitionEnd = element => {
  element.dispatchEvent(new Event(TRANSITION_END));
};

const isElement = obj => (obj[0] || obj).nodeType;

const emulateTransitionEnd = (element, duration) => {
  let called = false;
  const durationPadding = 5;
  const emulatedDuration = duration + durationPadding;

  function listener() {
    called = true;
    element.removeEventListener(TRANSITION_END, listener);
  }

  element.addEventListener(TRANSITION_END, listener);
  setTimeout(() => {
    if (!called) {
      triggerTransitionEnd(element);
    }
  }, emulatedDuration);
};

const typeCheckConfig = (componentName, config, configTypes) => {
  Object.keys(configTypes).forEach(property => {
    const expectedTypes = configTypes[property];
    const value = config[property];
    const valueType = value && isElement(value) ?
      'element' :
      toType(value);

    if (!new RegExp(expectedTypes).test(valueType)) {
      throw new Error(
        `${componentName.toUpperCase()}: ` +
        `Option "${property}" provided type "${valueType}" ` +
        `but expected type "${expectedTypes}".`)
    }
  });
};

const reflow = element => element.offsetHeight;

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
 * Bootstrap (v5.0.0-beta1): toast.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

/**
 * ------------------------------------------------------------------------
 * Constants
 * ------------------------------------------------------------------------
 */

const NAME = 'toast';
const DATA_KEY = 'bs.toast';
const EVENT_KEY = `.${DATA_KEY}`;

const EVENT_CLICK_DISMISS = `click.dismiss${EVENT_KEY}`;
const EVENT_HIDE = `hide${EVENT_KEY}`;
const EVENT_HIDDEN = `hidden${EVENT_KEY}`;
const EVENT_SHOW = `show${EVENT_KEY}`;
const EVENT_SHOWN = `shown${EVENT_KEY}`;

const CLASS_NAME_FADE = 'fade';
const CLASS_NAME_HIDE = 'hide';
const CLASS_NAME_SHOW = 'show';
const CLASS_NAME_SHOWING = 'showing';

const DefaultType = {
  animation: 'boolean',
  autohide: 'boolean',
  delay: 'number'
};

const Default = {
  animation: true,
  autohide: true,
  delay: 5000
};

const SELECTOR_DATA_DISMISS = '[data-bs-dismiss="toast"]';

/**
 * ------------------------------------------------------------------------
 * Class Definition
 * ------------------------------------------------------------------------
 */

class Toast extends BaseComponent {
  constructor(element, config) {
    super(element);

    this._config = this._getConfig(config);
    this._timeout = null;
    this._setListeners();
  }

  // Getters

  static get DefaultType() {
    return DefaultType
  }

  static get Default() {
    return Default
  }

  static get DATA_KEY() {
    return DATA_KEY
  }

  // Public

  show() {
    const showEvent = EventHandler.trigger(this._element, EVENT_SHOW);

    if (showEvent.defaultPrevented) {
      return
    }

    this._clearTimeout();

    if (this._config.animation) {
      this._element.classList.add(CLASS_NAME_FADE);
    }

    const complete = () => {
      this._element.classList.remove(CLASS_NAME_SHOWING);
      this._element.classList.add(CLASS_NAME_SHOW);

      EventHandler.trigger(this._element, EVENT_SHOWN);

      if (this._config.autohide) {
        this._timeout = setTimeout(() => {
          this.hide();
        }, this._config.delay);
      }
    };

    this._element.classList.remove(CLASS_NAME_HIDE);
    reflow(this._element);
    this._element.classList.add(CLASS_NAME_SHOWING);
    if (this._config.animation) {
      const transitionDuration = getTransitionDurationFromElement(this._element);

      EventHandler.one(this._element, TRANSITION_END, complete);
      emulateTransitionEnd(this._element, transitionDuration);
    } else {
      complete();
    }
  }

  hide() {
    if (!this._element.classList.contains(CLASS_NAME_SHOW)) {
      return
    }

    const hideEvent = EventHandler.trigger(this._element, EVENT_HIDE);

    if (hideEvent.defaultPrevented) {
      return
    }

    const complete = () => {
      this._element.classList.add(CLASS_NAME_HIDE);
      EventHandler.trigger(this._element, EVENT_HIDDEN);
    };

    this._element.classList.remove(CLASS_NAME_SHOW);
    if (this._config.animation) {
      const transitionDuration = getTransitionDurationFromElement(this._element);

      EventHandler.one(this._element, TRANSITION_END, complete);
      emulateTransitionEnd(this._element, transitionDuration);
    } else {
      complete();
    }
  }

  dispose() {
    this._clearTimeout();

    if (this._element.classList.contains(CLASS_NAME_SHOW)) {
      this._element.classList.remove(CLASS_NAME_SHOW);
    }

    EventHandler.off(this._element, EVENT_CLICK_DISMISS);

    super.dispose();
    this._config = null;
  }

  // Private

  _getConfig(config) {
    config = {
      ...Default,
      ...Manipulator.getDataAttributes(this._element),
      ...(typeof config === 'object' && config ? config : {})
    };

    typeCheckConfig(NAME, config, this.constructor.DefaultType);

    return config
  }

  _setListeners() {
    EventHandler.on(this._element, EVENT_CLICK_DISMISS, SELECTOR_DATA_DISMISS, () => this.hide());
  }

  _clearTimeout() {
    clearTimeout(this._timeout);
    this._timeout = null;
  }

  // Static

  static jQueryInterface(config) {
    return this.each(function () {
      let data = Data.getData(this, DATA_KEY);
      const _config = typeof config === 'object' && config;

      if (!data) {
        data = new Toast(this, _config);
      }

      if (typeof config === 'string') {
        if (typeof data[config] === 'undefined') {
          throw new TypeError(`No method named "${config}"`)
        }

        data[config](this);
      }
    })
  }
}

/**
 * ------------------------------------------------------------------------
 * jQuery
 * ------------------------------------------------------------------------
 * add .Toast to jQuery only if jQuery is present
 */

onDOMContentLoaded(() => {
  const $ = getjQuery();
  /* istanbul ignore if */
  if ($) {
    const JQUERY_NO_CONFLICT = $.fn[NAME];
    $.fn[NAME] = Toast.jQueryInterface;
    $.fn[NAME].Constructor = Toast;
    $.fn[NAME].noConflict = () => {
      $.fn[NAME] = JQUERY_NO_CONFLICT;
      return Toast.jQueryInterface
    };
  }
});

window.Joomla = window.Joomla || {};
window.Joomla.Bootstrap = {};
window.Joomla.Bootstrap.Toast = Toast;

export default Toast;
