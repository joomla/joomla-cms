/**
 * Joomla! Custom events
 *
 * @since  4.0.0
 */
((window, Joomla) => {
  'use strict';

  if (Joomla.Event) {
    return;
  }

  Joomla.Event = {};

  /**
   * Dispatch custom event.
   *
   * An event name convention:
   *     The event name has at least two part, separated ":", eg `foo:bar`.
   *     Where the first part is an "event supporter",
   *     and second part is the event name which happened.
   *     Which is allow us to avoid possible collisions with another scripts
   *     and native DOM events.
   *     Joomla! CMS standard events should start from `joomla:`.
   *
   * Joomla! events:
   *     `joomla:updated`  Dispatch it over the changed container,
   *      example after the content was updated via ajax
   *     `joomla:removed`  The container was removed
   *
   * @param {HTMLElement|string}  element  DOM element, the event target. Or the event name,
   * then the target will be a Window
   * @param {String|Object}       name     The event name, or an optional parameters in case
   * when "element" is an event name
   * @param {Object}              params   An optional parameters. Allow to send a custom data
   * through the event.
   *
   * @example
   *
   *   Joomla.Event.dispatch(myElement, 'joomla:updated', {for: 'bar', foo2: 'bar2'});
   *   // Will dispatch event to myElement
   *   or:
   *   Joomla.Event.dispatch('joomla:updated', {for: 'bar', foo2: 'bar2'});
   *   // Will dispatch event to Window
   *
   * @since   4.0.0
   */
  Joomla.Event.dispatch = (element, name, params) => {
    let newElement = element;
    let newName = name;
    let newParams = params;
    if (typeof element === 'string') {
      newParams = name;
      newName = element;
      newElement = window;
    }
    newParams = newParams || {};

    newElement.dispatchEvent(new CustomEvent(newName, {
      detail: newParams,
      bubbles: true,
      cancelable: true,
    }));
  };

  /**
   * Once listener. Add EventListener to the Element and auto-remove it
   * after the event was dispatched.
   *
   * @param {HTMLElement}  element   DOM element
   * @param {String}       name      The event name
   * @param {Function}     callback  The event callback
   *
   * @since   4.0.0
   */
  Joomla.Event.listenOnce = (element, name, callback) => {
    const onceCallback = (event) => {
      element.removeEventListener(name, onceCallback);
      return callback.call(element, event);
    };

    element.addEventListener(name, onceCallback);
  };
})(window, Joomla);
