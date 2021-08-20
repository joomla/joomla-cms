/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

   /**
    * Javascript to (1) Display the modal when user clicks on the
    * module edit button. The modal is initialized by the id
    * of the module found using data-module-id attribute of
    * the button. (2) Remove imported modules by id or by
    * position and (3) To delete the importOnSave cookie when
    * the module modal is clsoed.
    */

  document.addEventListener('DOMContentLoaded', () => {
    const baseLink = 'index.php?option=com_modules&client_id=0&task=module.edit&tmpl=component&view=module&layout=modal&id=';
    const modalBtnElements = [].slice.call(document.getElementsByClassName('module-edit-link'));
    const elements = [].slice.call(document.querySelectorAll('#moduleEditModal .modal-footer .btn'));
    const removeModBtnElements = [].slice.call(document.getElementsByClassName('module-remove-link'));
    const removePositionBtnElements = [].slice.call(document.getElementsByClassName('import-remove-link'));
    const elModuleModal = document.getElementById('jform_articletext_editors-xtd_module_modal');

    if (modalBtnElements.length) {
      modalBtnElements.forEach((linkElement) => {
        linkElement.addEventListener('click', (_ref) => {
          const { target } = _ref;
          const link = baseLink + target.getAttribute('data-module-id');
          const modal = document.getElementById('moduleEditModal');
          const body = modal.querySelector('.modal-body');
          const iFrame = document.createElement('iframe');
          iFrame.src = link;
          iFrame.setAttribute('class', 'class="iframe jviewport-height70"');
          body.innerHTML = '';
          body.appendChild(iFrame);
          modal.open();
        });
      });
    }

    if (elements.length) {
      elements.forEach((element) => {
        element.addEventListener('click', (_ref2) => {
          const { target } = _ref2;
          const dataTarget = target.getAttribute('data-bs-target');

          if (dataTarget) {
            const iframe = document.querySelector('#moduleEditModal iframe');
            const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
            iframeDocument.querySelector(dataTarget).click();
          }
        });
      });
    }

    if (removeModBtnElements.length) {
      removeModBtnElements.forEach((linkElement) => {
        linkElement.addEventListener('click', (_ref) => {
          const { target } = _ref;
          const moduleId = target.getAttribute('data-module-id');
          let editorText = Joomla.editors.instances.jform_articletext.getValue();
          editorText = editorText.replace(`{loadmoduleid ${moduleId}}`, '');
          Joomla.editors.instances.jform_articletext.setValue(editorText);
          document.querySelector('.button-apply.btn.btn-success').click();
        });
      });
    }

    if (removePositionBtnElements.length) {
      removePositionBtnElements.forEach((linkElement) => {
        linkElement.addEventListener('click', (_ref) => {
          const { target } = _ref;
          const importText = target.getAttribute('data-importText');
          let editorText = Joomla.editors.instances.jform_articletext.getValue();
          editorText = editorText.replace(importText, '');
          Joomla.editors.instances.jform_articletext.setValue(editorText);
          document.querySelector('.button-apply.btn.btn-success').click();
        });
      });
    }

    // Clears the importOnSave cookie on closing the modal.
    elModuleModal.addEventListener('hidden.bs.modal', () => {
      const currentTime = new Date();
      currentTime.setTime(currentTime.getTime());
      document.cookie = `com_modules_importOnSave=0;expires=${currentTime.toUTCString()}`;
      const iframeURL = document.querySelector('iframe[name="Module"]').contentWindow.location.href;
      const indexOfId = iframeURL.indexOf('&id=');
      if (indexOfId !== -1) {
        const modid = iframeURL.slice(indexOfId + 4, iframeURL.indexOf('&', indexOfId + 4));
        Joomla.editors.instances.jform_articletext.replaceSelection(`{loadmoduleid ${modid}}`);
      }
    });
  });
})();
