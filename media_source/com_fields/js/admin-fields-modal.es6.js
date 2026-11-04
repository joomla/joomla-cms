/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  'use strict';

  if (!window.parent.Joomla) {
    throw new Error('core.js was not properly initialised');
  }

  if (!Joomla) {
    window.Joomla = {};
  }

  Joomla.fieldIns = (id, editor) => {
    // Use a JoomlaExpectingPostMessage flag to be able to distinct legacy methods
    if (window.parent.JoomlaExpectingPostMessage) {
      return;
    }
    console.warn('Method Joomla.fieldIns() is deprecated. Use postMessage() instead.');

    window.parent.Joomla.editors.instances[editor].replaceSelection(`{field ${id}}`);

    if (window.parent.Joomla.Modal) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  };

  Joomla.fieldgroupIns = (id, editor) => {
    // Use a JoomlaExpectingPostMessage flag to be able to distinct legacy methods
    if (window.parent.JoomlaExpectingPostMessage) {
      return;
    }
    console.warn('Method Joomla.fieldgroupIns() is deprecated. Use postMessage() instead.');

    window.parent.Joomla.editors.instances[editor].replaceSelection(`{fieldgroup ${id}}`);

    if (window.parent.Joomla.Modal) {
      window.parent.Joomla.Modal.getCurrent().close();
    }
  };

  document.addEventListener('click', ({ target }) => {
    const button = target.closest('[data-field-insert], [data-fieldgroup-insert]');

    if (!button) {
      return;
    }

    const { editor } = Joomla.getOptions('xtd-fields', {});

    if (button.hasAttribute('data-field-insert')) {
      Joomla.fieldIns(button.dataset.fieldInsert, editor);
    } else {
      Joomla.fieldgroupIns(button.dataset.fieldgroupInsert, editor);
    }
  });
})(Joomla);
