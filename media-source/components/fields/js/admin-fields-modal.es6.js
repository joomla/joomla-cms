/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/* eslint-disable no-alert, no-prototype-builtins */
((Joomla) => {
  'use strict';

  Joomla.fieldIns = (id, editor) => {
    /** Use the API, if editor supports it * */
    if (window.parent.Joomla
      && window.parent.Joomla.editors
      && window.parent.Joomla.editors.instances
      && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
      window.parent.Joomla.editors.instances[editor].replaceSelection(`{field ${id}}`);
    } else {
      window.parent.jInsertEditorText(`{field ${id}}`, editor);
    }

    if (window.parent.Joomla.currentModal) {
      Joomla.Modal.getCurrent.close();
    }
  };

  Joomla.fieldgroupIns = (id, editor) => {
    /** Use the API, if editor supports it * */
    if (window.parent.Joomla
      && window.parent.Joomla.editors
      && window.parent.Joomla.editors.instances
      && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
      window.parent.Joomla.editors.instances[editor].replaceSelection(`{fieldgroup ${id}}`);
    } else {
      window.parent.jInsertEditorText(`{fieldgroup ${id}}`, editor);
    }

    if (window.parent.Joomla.currentModal) {
      Joomla.Modal.getCurrent.close();
    }
  };
})(Joomla);
