/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

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
  setCurrent: (element) => {
    window.Joomla.current = element;
  },
  getCurrent: () => window.Joomla.current,
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
})(Joomla);
