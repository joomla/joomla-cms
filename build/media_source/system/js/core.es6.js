/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

import { sanitizeHtml } from 'bootstrap/js/src/util/sanitizer.js';

const ARIA_ATTRIBUTE_PATTERN = /^aria-[\w-]*$/i;
const DATA_ATTRIBUTE_PATTERN = /^data-[\w-]*$/i;

const DefaultAllowlist = {
  // Global attributes allowed on any supplied element below.
  '*': ['class', 'dir', 'id', 'lang', 'role', ARIA_ATTRIBUTE_PATTERN, DATA_ATTRIBUTE_PATTERN],
  a: ['target', 'href', 'title', 'rel'],
  area: [],
  b: [],
  br: [],
  col: [],
  code: [],
  div: [],
  em: [],
  hr: [],
  h1: [],
  h2: [],
  h3: [],
  h4: [],
  h5: [],
  h6: [],
  i: [],
  img: ['src', 'srcset', 'alt', 'title', 'width', 'height'],
  li: [],
  ol: [],
  p: [],
  pre: [],
  s: [],
  small: [],
  span: [],
  sub: [],
  sup: [],
  strong: [],
  u: [],
  ul: [],
  button: ['type'],
  input: [
    'accept', 'alt', 'autocomplete', 'autofocus', 'capture',
    'checked', 'dirname', 'disabled', 'height', 'list', 'max',
    'maxlength', 'min', 'minlength', 'multiple', 'type', 'name',
    'pattern', 'placeholder', 'readonly', 'required', 'size', 'src',
    'step', 'value', 'width', 'inputmode',
  ],
  select: ['name'],
  textarea: ['name'],
  option: ['value', 'selected'],
};

// Only define the Joomla namespace if not defined.
window.Joomla = window.Joomla || {};

// Only define editors if not defined
window.Joomla.editors = window.Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
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
   *   Joomla.Modal.getCurrent(); // Returns node element, eg: document.getElementById('exampleId')
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
  setCurrent: (element) => {
    window.Joomla.Modal.current = element;
  },
  getCurrent: () => window.Joomla.Modal.current,
};

((Joomla) => {
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

    [].slice.call(Object.keys(source)).forEach((key) => {
      newDestination[key] = source[key];
    });

    return destination;
  };

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
  Joomla.loadOptions = (options) => {
    // Load form the script container
    if (!options) {
      const elements = [].slice.call(document.querySelectorAll('.joomla-script-options.new'));
      let counter = 0;

      elements.forEach((element) => {
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
    }

    // Initial loading
    if (!Joomla.optionsStorage) {
      Joomla.optionsStorage = options || {};
    } else if (options) {
      // Merge with existing
      [].slice.call(Object.keys(options)).forEach((key) => {
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
      let newDef = def;
      // Check for new strings in the optionsStorage, and load them
      const newStrings = Joomla.getOptions('joomla.jtext');
      if (newStrings) {
        Joomla.Text.load(newStrings);

        // Clean up the optionsStorage from useless data
        Joomla.loadOptions({ 'joomla.jtext': null });
      }

      newDef = newDef === undefined ? newKey : newDef;
      newKey = newKey.toUpperCase();

      return Joomla.Text.strings[newKey] !== undefined ? Joomla.Text.strings[newKey] : newDef;
    },

    /**
     * Load new strings in to Joomla.Text
     *
     * @param {Object} object  Object with new strings
     * @returns {Joomla.Text}
     */
    load: (object) => {
      [].slice.call(Object.keys(object)).forEach((key) => {
        Joomla.Text.strings[key.toUpperCase()] = object[key];
      });

      return Joomla.Text;
    },
  };

  /**
   * For B/C we still support Joomla.JText
   *
   * @type {{}}
   *
   * @deprecated 5.0
   */
  Joomla.JText = Joomla.Text;

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
    }

    // Toggle HTML5 validation
    newForm.noValidate = !validate;

    if (!validate) {
      newForm.setAttribute('novalidate', '');
    } else if (newForm.hasAttribute('novalidate')) {
      newForm.removeAttribute('novalidate');
    }

    // Submit the form.
    // Create the input type="submit"
    const button = document.createElement('input');
    button.classList.add('hidden');
    button.type = 'submit';

    // Append it and click it
    newForm.appendChild(button).click();

    // If "submit" was prevented, make sure we don't get a build up of buttons
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

    elements.forEach((element) => {
      if (element.type === checkbox.type && element.id.indexOf(currentStab) === 0) {
        element.checked = checkbox.checked;
        state += element.checked ? 1 : 0;
      }
    });

    if (checkbox.form.boxchecked) {
      checkbox.form.boxchecked.value = state;
      checkbox.form.boxchecked.dispatchEvent(new CustomEvent('change', {
        bubbles: true,
        cancelable: true,
      }));
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

    newForm.boxchecked.value = isitchecked
      ? parseInt(newForm.boxchecked.value, 10) + 1
      : parseInt(newForm.boxchecked.value, 10) - 1;

    newForm.boxchecked.dispatchEvent(new CustomEvent('change', {
      bubbles: true,
      cancelable: true,
    }));

    // If we don't have a checkall-toggle, done.
    if (!newForm.elements['checkall-toggle']) {
      return;
    }

    // Toggle main toggle checkbox depending on checkbox selection
    let c = true;
    let i;
    let e;
    let n;

    // eslint-disable-next-line no-plusplus
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
    }

    // eslint-disable-next-line no-constant-condition
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

  /**
   * Method to replace all request tokens on the page with a new one.
   *
   * @param {String}  newToken  The token
   *
   * Used in Joomla Installation
   */
  Joomla.replaceTokens = (newToken) => {
    if (!/^[0-9A-F]{32}$/i.test(newToken)) {
      return;
    }

    const elements = [].slice.call(document.getElementsByTagName('input'));

    elements.forEach((element) => {
      if (element.type === 'hidden' && element.value === '1' && element.name.length === 32) {
        element.name = newToken;
      }
    });
  };

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
   *    promise: false        Whether return a Promise instance.
   *              When true then next options is ignored: perform, onSuccess, onError, onComplete
   *
   *    onBefore:  (xhr) => {}            // Callback on before the request
   *    onSuccess: (response, xhr) => {}, // Callback on the request success
   *    onError:   (xhr) => {},           // Callback on the request error
   *    onComplete: (xhr) => {},          // Callback on the request completed, with/without error
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
  Joomla.request = (options) => {
    // Prepare the options
    const newOptions = Joomla.extend({
      url: '',
      method: 'GET',
      data: null,
      perform: true,
      promise: false,
    }, options);

    // Setup XMLHttpRequest instance
    const createRequest = (onSuccess, onError) => {
      const xhr = new XMLHttpRequest();

      xhr.open(newOptions.method, newOptions.url, true);

      // Set the headers
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('X-Ajax-Engine', 'Joomla!');

      if (newOptions.method !== 'GET') {
        const token = Joomla.getOptions('csrf.token', '');

        if (token) {
          xhr.setRequestHeader('X-CSRF-Token', token);
        }

        if (typeof newOptions.data === 'string' && (!newOptions.headers || !newOptions.headers['Content-Type'])) {
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
      }

      // Custom headers
      if (newOptions.headers) {
        [].slice.call(Object.keys(newOptions.headers)).forEach((key) => {
          // Allow request without Content-Type
          // eslint-disable-next-line no-empty
          if (key === 'Content-Type' && newOptions.headers['Content-Type'] === 'false') {

          } else {
            xhr.setRequestHeader(key, newOptions.headers[key]);
          }
        });
      }

      xhr.onreadystatechange = () => {
        // Request not finished
        if (xhr.readyState !== 4) {
          return;
        }

        // Request finished and response is ready
        if (xhr.status === 200) {
          if (newOptions.promise) {
            // A Promise accepts only one argument
            onSuccess.call(window, xhr);
          } else {
            onSuccess.call(window, xhr.responseText, xhr);
          }
        } else {
          onError.call(window, xhr);
        }

        if (newOptions.onComplete && !newOptions.promise) {
          newOptions.onComplete.call(window, xhr);
        }
      };

      // Do request
      if (newOptions.perform) {
        if (newOptions.onBefore && newOptions.onBefore.call(window, xhr) === false) {
          // Request interrupted
          if (newOptions.promise) {
            onSuccess.call(window, xhr);
          }
          return xhr;
        }

        xhr.send(newOptions.data);
      }

      return xhr;
    };

    // Return a Promise
    if (newOptions.promise) {
      return new Promise((resolve, reject) => {
        newOptions.perform = true;
        createRequest(resolve, reject);
      });
    }

    // Return a Request
    try {
      return createRequest(newOptions.onSuccess || (() => {}), newOptions.onError || (() => {}));
    } catch (error) {
      // eslint-disable-next-line no-unused-expressions,no-console
      console.error(error);
      return false;
    }
  };

  let lastRequestPromise;

  /**
   * Joomla Request queue.
   *
   * A FIFO queue of requests to execute serially. Used to prevent simultaneous execution of
   * multiple requests against the server which could trigger its Denial of Service protection.
   *
   * @param {object} options Options for Joomla.request()
   * @returns {Promise}
   */
  Joomla.enqueueRequest = (options) => {
    if (!options.promise) {
      throw new Error('Joomla.enqueueRequest supports only Joomla.request as Promise');
    }
    if (!lastRequestPromise) {
      lastRequestPromise = Joomla.request(options);
    } else {
      lastRequestPromise = lastRequestPromise.then(() => Joomla.request(options));
    }
    return lastRequestPromise;
  };

  /**
   *
   * @param {string} unsafeHtml The html for sanitization
   * @param {object} allowList The list of HTMLElements with an array of allowed attributes
   * @param {function} sanitizeFn A custom sanitization function
   *
   * @return string
   */
  Joomla.sanitizeHtml = (unsafeHtml, allowList, sanitizeFn) => {
    const allowed = (allowList === undefined || allowList === null)
      ? DefaultAllowlist : { ...DefaultAllowlist, ...allowList };
    return sanitizeHtml(unsafeHtml, allowed, sanitizeFn);
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
      const buf = [];

      // Html entity encode.
      let encodedJson = xhr.responseText.trim();

      // eslint-disable-next-line no-plusplus
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
})(Joomla);
