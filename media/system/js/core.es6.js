/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Only define the Joomla namespace if not defined.
window.Joomla = window.Joomla || {}; // Only define editors if not defined

window.Joomla.editors = window.Joomla.editors || {}; // An object to hold each editor instance on page, only define if not defined.

window.Joomla.editors.instances = window.Joomla.editors.instances || {
  /**
   * *****************************************************************
   * All Editors MUST register, per instance, the following callbacks:
   * *****************************************************************
   *
   * getValue         Type  Function  Should return the complete data from the editor
   *                                  Example: () => { return this.element.value; }
   * setValue         Type  Function  Should replace the complete data of the editor
   *                                  Example: (text) => { return this.element.value = text; }
   * getSelection     Type  Function  Should return the selected text from the editor
   *                                  Example: function () { return this.selectedText; }
   * disable          Type  Function  Toggles the editor into disabled mode. When the editor is
   *                                  active then everything should be usable. When inactive the
   *                                  editor should be unusable AND disabled for form validation
   *                                  Example: (bool) => { return this.disable = value; }
   * replaceSelection Type  Function  Should replace the selected text of the editor
   *                                  If nothing selected, will insert the data at the cursor
   *                                  Example:
   *                                  (text) => {
   *                                    return insertAtCursor(this.element, text);
   *                                    }
   *
   * USAGE (assuming that jform_articletext is the textarea id)
   * {
   * To get the current editor value:
   *  Joomla.editors.instances['jform_articletext'].getValue();
   * To set the current editor value:
   *  Joomla.editors.instances['jform_articletext'].setValue('Joomla! rocks');
   * To replace(selection) or insert a value at  the current editor cursor (replaces the J3
   * jInsertEditorText API):
   *  replaceSelection:
   *  Joomla.editors.instances['jform_articletext'].replaceSelection('Joomla! rocks')
   * }
   *
   * *********************************************************
   * ANY INTERACTION WITH THE EDITORS SHOULD USE THE ABOVE API
   * *********************************************************
   */
};
window.Joomla.Modal = window.Joomla.Modal || {
  /**
   * *****************************************************************
   * Modals should implement
   * *****************************************************************
   *
   * getCurrent  Type  Function  Should return the modal element
   * setCurrent  Type  Function  Should set the modal element
   * current     Type  {node}    The modal element
   *
   * USAGE (assuming that exampleId is the modal id)
   * To get the current modal element:
   *   Joomla.Modal.current; // Returns node element, eg: document.getElementById('exampleId')
   * To set the current modal element:
   *   Joomla.Modal.setCurrent(document.getElementById('exampleId'));
   *
   * *************************************************************
   * Joomla's UI modal uses `element.close();` to close the modal
   * and `element.open();` to open the modal
   * If you are using another modal make sure the same
   * functionality is bound to the modal element
   * @see media/legacy/bootstrap.init.js
   * *************************************************************
   */
  current: '',
  setCurrent: element => {
    window.Joomla.current = element;
  },
  getCurrent: () => window.Joomla.current
};

(Joomla => {
  'use strict';
  /**
   * Method to Extend Objects
   *
   * @param  {Object}  destination
   * @param  {Object}  source
   *
   * @return Object
   */

  Joomla.extend = (destination, source) => {
    let newDestination = destination;
    /**
     * Technically null is an object, but trying to treat the destination as one in this
     * context will error out.
     * So emulate jQuery.extend(), and treat a destination null as an empty object.
     */

    if (destination === null) {
      newDestination = {};
    }

    [].slice.call(Object.keys(source)).forEach(key => {
      newDestination[key] = source[key];
    });
    return destination;
  };
})(Joomla);
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Ajax related functions
 *
 * @since  4.0.0
 */


((window, Joomla) => {
  'use strict';
  /**
   * Method to perform AJAX request
   *
   * @param {Object} options   Request options:
   * {
   *    url:     'index.php', Request URL
   *    method:  'GET',       Request method GET (default), POST
   *    data:    null,        Data to be sent, see
   *                https://developer.mozilla.org/docs/Web/API/XMLHttpRequest/send
   *    perform: true,        Perform the request immediately
   *              or return XMLHttpRequest instance and perform it later
   *    headers: null,        Object of custom headers, eg {'X-Foo': 'Bar', 'X-Bar': 'Foo'}
   *
   *    onBefore:  (xhr) => {}            // Callback on before the request
   *    onSuccess: (response, xhr) => {}, // Callback on the request success
   *    onError:   (xhr) => {},           // Callback on the request error
   * }
   *
   * @return XMLHttpRequest|Boolean
   *
   * @example
   *
   *   Joomla.request({
   *    url: 'index.php?option=com_example&view=example',
   *    onSuccess: (response, xhr) => {
   *     JSON.parse(response);
   *    }
   *   })
   *
   * @see    https://developer.mozilla.org/docs/Web/API/XMLHttpRequest
   */

  Joomla.request = options => {
    let xhr; // Prepare the options

    const newOptions = Joomla.extend({
      url: '',
      method: 'GET',
      data: null,
      perform: true
    }, options); // Set up XMLHttpRequest instance

    try {
      xhr = new XMLHttpRequest();
      xhr.open(newOptions.method, newOptions.url, true); // Set the headers

      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('X-Ajax-Engine', 'Joomla!');

      if (newOptions.method !== 'GET') {
        const token = Joomla.getOptions('csrf.token', '');

        if (token) {
          xhr.setRequestHeader('X-CSRF-Token', token);
        }

        if (!newOptions.headers || !newOptions.headers['Content-Type']) {
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
      } // Custom headers


      if (newOptions.headers) {
        [].slice.call(Object.keys(newOptions.headers)).forEach(key => {
          // Allow request without Content-Type
          // eslint-disable-next-line no-empty
          if (key === 'Content-Type' && newOptions.headers['Content-Type'] === 'false') {} else {
            xhr.setRequestHeader(key, newOptions.headers[key]);
          }
        });
      }

      xhr.onreadystatechange = () => {
        // Request not finished
        if (xhr.readyState !== 4) {
          return;
        } // Request finished and response is ready


        if (xhr.status === 200) {
          if (newOptions.onSuccess) {
            newOptions.onSuccess.call(window, xhr.responseText, xhr);
          }
        } else if (newOptions.onError) {
          newOptions.onError.call(window, xhr);
        }
      }; // Do request


      if (newOptions.perform) {
        if (newOptions.onBefore && newOptions.onBefore.call(window, xhr) === false) {
          // Request interrupted
          return xhr;
        }

        xhr.send(newOptions.data);
      }
    } catch (error) {
      // eslint-disable-next-line no-unused-expressions,no-console
      window.console ? console.log(error) : null;
      return false;
    }

    return xhr;
  };
})(window, Joomla);
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable max-len

/**
 * Patch Custom Events
 * https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/CustomEvent
 */


(() => {
  if (typeof window.CustomEvent === 'function') {
    return false;
  }

  const CustomEvent = (event, params) => {
    const evt = document.createEvent('CustomEvent');
    const newParams = params || {
      bubbles: false,
      cancelable: false,
      detail: undefined
    };
    evt.initCustomEvent(event, newParams.bubbles, newParams.cancelable, newParams.detail);
    return evt;
  };

  CustomEvent.prototype = window.Event.prototype;
  window.CustomEvent = CustomEvent;
  return true;
})();
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
      cancelable: true
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
    const onceCallback = event => {
      element.removeEventListener(name, onceCallback);
      return callback.call(element, event);
    };

    element.addEventListener(name, onceCallback);
  };
})(window, Joomla);
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! form related functions
 *
 * @since  4.0.0
 */


((window, Joomla) => {
  'use strict';
  /**
   * Generic submit form
   *
   * @param  {String}  task      The given task
   * @param  {node}    form      The form element
   * @param  {bool}    validate  The form element
   *
   * @returns  {void}
   */

  Joomla.submitform = (task, form, validate) => {
    let newForm = form;
    const newTask = task;

    if (!newForm) {
      newForm = document.getElementById('adminForm');
    }

    if (newTask) {
      newForm.task.value = newTask;
    } // Toggle HTML5 validation


    newForm.noValidate = !validate;

    if (!validate) {
      newForm.setAttribute('novalidate', '');
    } else if (newForm.hasAttribute('novalidate')) {
      newForm.removeAttribute('novalidate');
    } // Submit the form.
    // Create the input type="submit"


    const button = document.createElement('input');
    button.classList.add('hidden');
    button.type = 'submit'; // Append it and click it

    newForm.appendChild(button).click(); // If "submit" was prevented, make sure we don't get a build up of buttons

    newForm.removeChild(button);
  };
  /**
   * Default function. Can be overridden by the component to add custom logic
   *
   * @param  {String}  task            The given task
   * @param  {String}  formSelector    The form selector eg '#adminForm'
   * @param  {bool}    validate        The form element
   *
   * @returns {void}
   */


  Joomla.submitbutton = (task, formSelector, validate) => {
    let form = document.querySelector(formSelector || 'form.form-validate');
    let newValidate = validate;

    if (typeof formSelector === 'string' && form === null) {
      form = document.querySelector(`#${formSelector}`);
    }

    if (form) {
      if (newValidate === undefined || newValidate === null) {
        const pressbutton = task.split('.');
        let cancelTask = form.getAttribute('data-cancel-task');

        if (!cancelTask) {
          cancelTask = `${pressbutton[0]}.cancel`;
        }

        newValidate = task !== cancelTask;
      }

      if (!newValidate || document.formvalidator.isValid(form)) {
        Joomla.submitform(task, form);
      }
    } else {
      Joomla.submitform(task);
    }
  };
  /**
   * USED IN: all list forms.
   *
   * Toggles the check state of a group of boxes
   *
   * Checkboxes must have an id attribute in the form cb0, cb1...
   *
   * @param {mixed}  checkbox The number of box to 'check', for a checkbox element
   * @param {string} stub     An alternative field name
   *
   * @return {boolean}
   */


  Joomla.checkAll = (checkbox, stub) => {
    if (!checkbox.form) {
      return false;
    }

    const currentStab = stub || 'cb';
    const elements = [].slice.call(checkbox.form.elements);
    let state = 0;
    elements.forEach(element => {
      if (element.type === checkbox.type && element.id.indexOf(currentStab) === 0) {
        element.checked = checkbox.checked;
        state += element.checked ? 1 : 0;
      }
    });

    if (checkbox.form.boxchecked) {
      checkbox.form.boxchecked.value = state;
      Joomla.Event.dispatch(checkbox.form.boxchecked, 'change');
    }

    return true;
  };
  /**
   * USED IN: administrator/components/com_cache/views/cache/tmpl/default.php
   * administrator/components/com_installer/views/discover/tmpl/default_item.php
   * administrator/components/com_installer/views/update/tmpl/default_item.php
   * administrator/components/com_languages/helpers/html/languages.php
   * libraries/joomla/html/html/grid.php
   *
   * @param  {boolean}  isitchecked  Flag for checked
   * @param  {node}     form         The form
   *
   * @return  {void}
   */


  Joomla.isChecked = (isitchecked, form) => {
    let newForm = form;

    if (typeof newForm === 'undefined') {
      newForm = document.getElementById('adminForm');
    } else if (typeof form === 'string') {
      newForm = document.getElementById(form);
    }

    newForm.boxchecked.value = isitchecked ? parseInt(newForm.boxchecked.value, 10) + 1 : parseInt(newForm.boxchecked.value, 10) - 1;
    Joomla.Event.dispatch(newForm.boxchecked, 'change'); // If we don't have a checkall-toggle, done.

    if (!newForm.elements['checkall-toggle']) {
      return;
    } // Toggle main toggle checkbox depending on checkbox selection


    let c = true;
    let i;
    let e;
    let n; // eslint-disable-next-line no-plusplus

    for (i = 0, n = newForm.elements.length; i < n; i++) {
      e = newForm.elements[i];

      if (e.type === 'checkbox' && e.name !== 'checkall-toggle' && !e.checked) {
        c = false;
        break;
      }
    }

    newForm.elements['checkall-toggle'].checked = c;
  };
  /**
   * USED IN: libraries/joomla/html/html/grid.php
   * In other words, on any reorderable table
   *
   * @param  {string}  order  The order value
   * @param  {string}  dir    The direction
   * @param  {string}  task   The task
   * @param  {node}    form   The form
   *
   * return  {void}
   */


  Joomla.tableOrdering = (order, dir, task, form) => {
    let newForm = form;

    if (typeof newForm === 'undefined') {
      newForm = document.getElementById('adminForm');
    } else if (typeof form === 'string') {
      newForm = document.getElementById(form);
    }

    newForm.filter_order.value = order;
    newForm.filter_order_Dir.value = dir;
    Joomla.submitform(task, newForm);
  };
  /**
   * USED IN: all over :)
   *
   * @param  {string}  id    The id
   * @param  {string}  task  The task
   * @param  {string}  form  The optional form
   *
   * @return {boolean}
   */


  Joomla.listItemTask = (id, task, form = null) => {
    let newForm = form;

    if (form !== null) {
      newForm = document.getElementById(form);
    } else {
      newForm = document.adminForm;
    }

    const cb = newForm[id];
    let i = 0;
    let cbx;

    if (!cb) {
      return false;
    } // eslint-disable-next-line no-constant-condition


    while (true) {
      cbx = newForm[`cb${i}`];

      if (!cbx) {
        break;
      }

      cbx.checked = false;
      i += 1;
    }

    cb.checked = true;
    newForm.boxchecked.value = 1;
    Joomla.submitform(task, newForm);
    return false;
  };
})(window, Joomla);
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Message related functions
 *
 * @since  4.0.0
 */


((window, Joomla) => {
  'use strict';
  /**
   * Render messages send via JSON
   * Used by some javascripts such as validate.js
   *
   * @param   {object}  messages JavaScript object containing the messages to render.
   *          Example:
   *          const messages = {
   *              "message": ["This will be a green message", "So will this"],
   *              "error": ["This will be a red message", "So will this"],
   *              "info": ["This will be a blue message", "So will this"],
   *              "notice": ["This will be same as info message", "So will this"],
   *              "warning": ["This will be a orange message", "So will this"],
   *              "my_custom_type": ["This will be same as info message", "So will this"]
   *          };
   * @param  {string} selector The selector of the container where the message will be rendered
   * @param  {bool}   keepOld  If we shall discard old messages
   * @param  {int}    timeout  The milliseconds before the message self destruct
   * @return  void
   */

  Joomla.renderMessages = (messages, selector, keepOld, timeout) => {
    let messageContainer;
    let typeMessages;
    let messagesBox;
    let title;
    let titleWrapper;
    let messageWrapper;
    let alertClass;

    if (typeof selector === 'undefined' || selector && selector === '#system-message-container') {
      messageContainer = document.getElementById('system-message-container');
    } else {
      messageContainer = document.querySelector(selector);
    }

    if (typeof keepOld === 'undefined' || keepOld && keepOld === false) {
      Joomla.removeMessages(messageContainer);
    }

    [].slice.call(Object.keys(messages)).forEach(type => {
      // Array of messages of this type
      typeMessages = messages[type];
      messagesBox = document.createElement('joomla-alert');

      if (['notice', 'message', 'error', 'warning'].indexOf(type) > -1) {
        alertClass = type === 'notice' ? 'info' : type;
        alertClass = type === 'message' ? 'success' : alertClass;
        alertClass = type === 'error' ? 'danger' : alertClass;
        alertClass = type === 'warning' ? 'warning' : alertClass;
      } else {
        alertClass = 'info';
      }

      messagesBox.setAttribute('type', alertClass);
      messagesBox.setAttribute('dismiss', 'true');

      if (timeout && parseInt(timeout, 10) > 0) {
        messagesBox.setAttribute('autodismiss', timeout);
      } // Title


      title = Joomla.Text._(type); // Skip titles with untranslated strings

      if (typeof title !== 'undefined') {
        titleWrapper = document.createElement('span');
        titleWrapper.className = 'alert-heading';
        titleWrapper.innerHTML = Joomla.Text._(type) ? Joomla.Text._(type) : type;
        messagesBox.appendChild(titleWrapper);
      } // Add messages to the message box


      typeMessages.forEach(typeMessage => {
        messageWrapper = document.createElement('div');
        messageWrapper.innerHTML = typeMessage;
        messagesBox.appendChild(messageWrapper);
      });
      messageContainer.appendChild(messagesBox);
    });
  };
  /**
   * Remove messages
   *
   * @param  {element} container The element of the container of the message
   * to be removed
   *
   * @return  {void}
   */


  Joomla.removeMessages = container => {
    let messageContainer;

    if (container) {
      messageContainer = container;
    } else {
      messageContainer = document.getElementById('system-message-container');
    }

    const alerts = [].slice.call(messageContainer.querySelectorAll('joomla-alert'));

    if (alerts.length) {
      alerts.forEach(alert => {
        alert.close();
      });
    }
  };
  /**
   * Treat AJAX errors.
   * Used by some javascripts such as sendtestmail.js and permissions.js
   *
   * @param   {object}  xhr         XHR object.
   * @param   {string}  textStatus  Type of error that occurred.
   * @param   {string}  error       Textual portion of the HTTP status.
   *
   * @return  {object}  JavaScript object containing the system error message.
   *
   * @since  3.6.0
   */


  Joomla.ajaxErrorsMessages = (xhr, textStatus) => {
    const msg = {};

    if (textStatus === 'parsererror') {
      // For jQuery jqXHR
      const buf = []; // Html entity encode.

      let encodedJson = xhr.responseText.trim(); // eslint-disable-next-line no-plusplus

      for (let i = encodedJson.length - 1; i >= 0; i--) {
        buf.unshift(['&#', encodedJson[i].charCodeAt(), ';'].join(''));
      }

      encodedJson = buf.join('');
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_PARSE').replace('%s', encodedJson)];
    } else if (textStatus === 'nocontent') {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_NO_CONTENT')];
    } else if (textStatus === 'timeout') {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_TIMEOUT')];
    } else if (textStatus === 'abort') {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT')];
    } else if (xhr.responseJSON && xhr.responseJSON.message) {
      // For vanilla XHR
      msg.error = [`${Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status)} <em>${xhr.responseJSON.message}</em>`];
    } else if (xhr.statusText) {
      msg.error = [`${Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status)} <em>${xhr.statusText}</em>`];
    } else {
      msg.error = [Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status)];
    }

    return msg;
  };
})(window, Joomla);
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Option related functions
 *
 * @since  4.0.0
 */


((window, Joomla) => {
  'use strict';
  /**
   * Joomla options storage
   *
   * @type {{}}
   *
   * @since 3.7.0
   */

  Joomla.optionsStorage = Joomla.optionsStorage || null;
  /**
   * Get script(s) options
   *
   * @param  {String}  key  Name in Storage
   * @param  {mixed}   def  Default value if nothing found
   *
   * @return {mixed}
   *
   * @since 3.7.0
   */

  Joomla.getOptions = (key, def) => {
    // Load options if they not exists
    if (!Joomla.optionsStorage) {
      Joomla.loadOptions();
    }

    return Joomla.optionsStorage[key] !== undefined ? Joomla.optionsStorage[key] : def;
  };
  /**
   * Load new options from given options object or from Element
   *
   * @param  {Object|undefined}  options  The options object to load.
   * Eg {"com_foobar" : {"option1": 1, "option2": 2}}
   *
   * @since 3.7.0
   */


  Joomla.loadOptions = options => {
    // Load form the script container
    if (!options) {
      const elements = [].slice.call(document.querySelectorAll('.joomla-script-options.new'));
      let counter = 0;
      elements.forEach(element => {
        const str = element.text || element.textContent;
        const option = JSON.parse(str);

        if (option) {
          Joomla.loadOptions(option);
          counter += 1;
        }

        element.className = element.className.replace(' new', ' loaded');
      });

      if (counter) {
        return;
      }
    } // Initial loading


    if (!Joomla.optionsStorage) {
      Joomla.optionsStorage = options || {};
    } else if (options) {
      // Merge with existing
      [].slice.call(Object.keys(options)).forEach(key => {
        /**
         * If both existing and new options are objects, merge them with Joomla.extend().
         * But test for new option being null, as null is an object, but we want to allow
         * clearing of options with ...
         *
         * Joomla.loadOptions({'joomla.jtext': null});
         */
        if (options[key] !== null && typeof Joomla.optionsStorage[key] === 'object' && typeof options[key] === 'object') {
          Joomla.optionsStorage[key] = Joomla.extend(Joomla.optionsStorage[key], options[key]);
        } else {
          Joomla.optionsStorage[key] = options[key];
        }
      });
    }
  };
})(window, Joomla);
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Text related functions
 *
 * @since  4.0.0
 */


((window, Joomla) => {
  'use strict';
  /**
   * Custom behavior for JavaScript I18N in Joomla! 1.6
   *
   * @type {{}}
   *
   * Allows you to call Joomla.Text._() to get a translated JavaScript string
   * pushed in with Text::script() in Joomla.
   */

  Joomla.Text = {
    strings: {},

    /**
     * Translates a string into the current language.
     *
     * @param {String} key   The string to translate
     * @param {String} def   Default string
     *
     * @returns {String}
     */
    _: (key, def) => {
      let newKey = key;
      let newDef = def; // Check for new strings in the optionsStorage, and load them

      const newStrings = Joomla.getOptions('joomla.jtext');

      if (newStrings) {
        Joomla.Text.load(newStrings); // Clean up the optionsStorage from useless data

        Joomla.loadOptions({
          'joomla.jtext': null
        });
      }

      newDef = newDef === undefined ? '' : newDef;
      newKey = newKey.toUpperCase();
      return Joomla.Text.strings[newKey] !== undefined ? Joomla.Text.strings[newKey] : newDef;
    },

    /**
     * Load new strings in to Joomla.Text
     *
     * @param {Object} object  Object with new strings
     * @returns {Joomla.Text}
     */
    load: object => {
      [].slice.call(Object.keys(object)).forEach(key => {
        Joomla.Text.strings[key.toUpperCase()] = object[key];
      });
      return Joomla.Text;
    }
  };
  /**
   * For B/C we still support Joomla.JText
   *
   * @type {{}}
   *
   * @deprecated 5.0
   */

  Joomla.JText = Joomla.Text;
})(window, Joomla);
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla! Text related functions
 *
 * @since  4.0.0
 */


((window, Joomla) => {
  'use strict';
  /**
   * Method to replace all request tokens on the page with a new one.
   *
   * @param {String}  newToken  The token
   *
   * Used in Joomla Installation
   */

  Joomla.replaceTokens = newToken => {
    if (!/^[0-9A-F]{32}$/i.test(newToken)) {
      return;
    }

    const elements = [].slice.call(document.getElementsByTagName('input'));
    elements.forEach(element => {
      if (element.type === 'hidden' && element.value === '1' && element.name.length === 32) {
        element.name = newToken;
      }
    });
  };
})(window, Joomla);
/**
 * @license
 * Copyright (c) 2018 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at http://polymer.github.io/PATENTS.txt
 *
 * LICENSE.txt from http://polymer.github.io/LICENSE.txt
 *
 * Copyright (c) 2014 The Polymer Authors. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 * * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @note This file has been modified by the Joomla! Project
 *       and no longer reflects the original work of its author.
 */


((Joomla, document) => {
  'use strict';
  /**
   * Basic flow of the loader process
   *
   * There are 4 flows the loader can take when booting up
   *
   * - Synchronous script, no polyfills needed
   *   - wait for `DOMContentLoaded`
   *   - fire WCR event, as there could not be any callbacks passed to `waitFor`
   *
   * - Synchronous script, polyfills needed
   *   - document.write the polyfill bundle
   *   - wait on the `load` event of the bundle to batch Custom Element upgrades
   *   - wait for `DOMContentLoaded`
   *   - run callbacks passed to `waitFor`
   *   - fire WCR event
   *
   * - Asynchronous script, no polyfills needed
   *   - wait for `DOMContentLoaded`
   *   - run callbacks passed to `waitFor`
   *   - fire WCR event
   *
   * - Asynchronous script, polyfills needed
   *   - Append the polyfill bundle script
   *   - wait for `load` event of the bundle
   *   - batch Custom Element Upgrades
   *   - run callbacks pass to `waitFor`
   *   - fire WCR event
   *
   * @since   4.0.0
   */

  Joomla.WebComponents = () => {
    const wc = Joomla.getOptions('webcomponents'); // Return early

    if (!wc || !wc.length) {
      return;
    }

    let polyfillsLoaded = false;
    const whenLoadedFns = [];
    let allowUpgrades = false;
    let flushFn;

    const fireEvent = () => {
      window.WebComponents.ready = true;
      document.dispatchEvent(new CustomEvent('WebComponentsReady', {
        bubbles: true
      })); // eslint-disable-next-line no-use-before-define

      loadWC();
    };

    const batchCustomElements = () => {
      if (window.customElements && customElements.polyfillWrapFlushCallback) {
        customElements.polyfillWrapFlushCallback(flushCallback => {
          flushFn = flushCallback;

          if (allowUpgrades) {
            flushFn();
          }
        });
      }
    };

    const asyncReady = () => {
      // eslint-disable-next-line no-use-before-define
      batchCustomElements(); // eslint-disable-next-line no-use-before-define

      ready();
    };

    const ready = () => {
      // bootstrap <template> elements before custom elements
      if (window.HTMLTemplateElement && HTMLTemplateElement.bootstrap) {
        HTMLTemplateElement.bootstrap(window.document);
      }

      polyfillsLoaded = true; // eslint-disable-next-line no-use-before-define

      runWhenLoadedFns().then(fireEvent);
    };

    const runWhenLoadedFns = () => {
      allowUpgrades = false;

      const done = () => {
        allowUpgrades = true;
        whenLoadedFns.length = 0; // eslint-disable-next-line no-unused-expressions

        flushFn && flushFn();
      };

      return Promise.all(whenLoadedFns.map(fn => fn instanceof Function ? fn() : fn)).then(() => {
        done();
      }).catch(err => {
        // eslint-disable-next-line no-console
        console.error(err);
      });
    };

    window.WebComponents = window.WebComponents || {
      ready: false,
      _batchCustomElements: batchCustomElements,
      waitFor: waitFn => {
        if (!waitFn) {
          return;
        }

        whenLoadedFns.push(waitFn);

        if (polyfillsLoaded) {
          runWhenLoadedFns();
        }
      }
    };
    /* Check if ES6 then apply the shim */

    const checkES6 = () => {
      try {
        // eslint-disable-next-line no-new-func, no-new
        new Function('(a = 0) => a');
        return true;
      } catch (err) {
        return false;
      }
    };
    /* Load web components async */


    const loadWC = () => {
      if (wc && wc.length) {
        wc.forEach(component => {
          let el;

          if (component.match(/\.js/g)) {
            el = document.createElement('script');

            if (!checkES6()) {
              let es5; // Browser is not ES6!

              if (component.match(/\.min\.js/g)) {
                es5 = component.replace(/\.min\.js/g, '-es5.min.js');
              } else if (component.match(/\.js/g)) {
                es5 = component.replace(/\.js/g, '-es5.js');
              }

              el.src = es5;
            } else {
              el.src = component;
            }
          }

          if (el) {
            document.head.appendChild(el);
          }
        });
      }
    }; // Get the core.js src attribute


    let name = 'core.min.js';
    let script = document.querySelector(`script[src*="${name}"]`);

    if (!script) {
      name = 'core.js';
      script = document.querySelector(`script[src*="${name}"]`);
    }

    if (!script) {
      throw new Error('core(.min).js is not registered correctly!');
    } // Feature detect which polyfill needs to be imported.


    let polyfills = [];

    if (!('attachShadow' in Element.prototype && 'getRootNode' in Element.prototype) || window.ShadyDOM && window.ShadyDOM.force) {
      polyfills.push('sd');
    }

    if (!window.customElements || window.customElements.forcePolyfill) {
      polyfills.push('ce');
    }

    const needsTemplate = (() => {
      // no real <template> because no `content` property (IE and older browsers)
      const t = document.createElement('template');

      if (!('content' in t)) {
        return true;
      } // broken doc fragment (older Edge)


      if (!(t.content.cloneNode() instanceof DocumentFragment)) {
        return true;
      } // broken <template> cloning (Edge up to at least version 17)


      const t2 = document.createElement('template');
      t2.content.appendChild(document.createElement('div'));
      t.content.appendChild(t2);
      const clone = t.cloneNode(true);
      return clone.content.childNodes.length === 0 || clone.content.firstChild.content.childNodes.length === 0;
    })(); // NOTE: any browser that does not have template or ES6 features
    // must load the full suite of polyfills.


    if (!window.Promise || !Array.from || !window.URL || !window.Symbol || needsTemplate) {
      polyfills = ['sd-ce-pf'];
    }

    if (polyfills.length) {
      const newScript = document.createElement('script'); // Load it from the right place.

      const replacement = `media/vendor/webcomponentsjs/js/webcomponents-${polyfills.join('-')}.min.js`;
      const mediaVersion = script.src.match(/\?.*/);
      const base = Joomla.getOptions('system.paths');

      if (!base) {
        throw new Error('core(.min).js is not registered correctly!');
      }

      newScript.src = base.rootFull + replacement + (mediaVersion ? mediaVersion[0] : ''); // if readyState is 'loading', this script is synchronous

      if (document.readyState === 'loading') {
        // make sure custom elements are batched whenever parser gets to the injected script
        newScript.setAttribute('onload', 'window.WebComponents._batchCustomElements()');
        document.write(newScript.outerHTML);
        document.addEventListener('DOMContentLoaded', ready);
      } else {
        newScript.addEventListener('load', asyncReady);
        newScript.addEventListener('error', () => {
          throw new Error(`Could not load polyfill bundle ${base.rootFull + replacement}`);
        });
        document.head.appendChild(newScript);
      }
    } else {
      polyfillsLoaded = true;

      if (document.readyState === 'complete') {
        fireEvent();
      } else {
        // this script may come between DCL and load, so listen for both
        // and cancel load listener if DCL fires
        window.addEventListener('load', ready);
        window.addEventListener('DOMContentLoaded', () => {
          window.removeEventListener('load', ready);
          ready();
        });
      }
    }
  };
})(Joomla, document);
/**
 * Load any web components and any polyfills required
 */


document.addEventListener('DOMContentLoaded', Joomla.WebComponents);