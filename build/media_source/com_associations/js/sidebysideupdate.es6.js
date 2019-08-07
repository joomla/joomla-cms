/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const referenceIframe = document.getElementById('reference-association');
    const targetIframe = document.getElementById('target-association');

    // Saving target, send the save action to the target iframe.
    Joomla.submitbutton = (task) => {
      // Using close button, normal joomla submit.
      if (task === 'association.cancel') {
        Joomla.submitform(task);
      } else if (task === 'defaultassoclang.update') {
        Joomla.submitform(task);
      } else {
        window.frames['target-association'].Joomla.submitbutton(`${document.getElementById('adminForm').getAttribute('data-associatedview')}, .apply`);
        document.getElementById('updateChild').click();
      }
    };

    // Attach behaviour to reference frame load event.
    referenceIframe.addEventListener('load', () => {
      const reference = referenceIframe.contentDocument;
      const referenceDiff = reference.querySelector('#diff');
      // Waiting until the reference has loaded before loading the target to avoid race conditions

      if (!referenceDiff) {
        // Disable language field.
        reference.querySelector('#jform_language').setAttribute('disabled', '');

        // Remove modal buttons on the reference
        reference.querySelector('#associations').querySelectorAll('.btn').forEach(e => e.parentNode.removeChild(e));
      }

      // Iframe load finished, hide Joomla loading layer.
      Joomla.loadingLayer('hide');
    });

    targetIframe.addEventListener('load', () => {
      if (targetIframe.getAttribute('src') !== '') {
        const target = targetIframe.contentDocument;

        // Update language field with the selected language and then disable it.
        target.querySelector('#jform_language').setAttribute('disabled', '');

        // Remove modal buttons on the reference
        target.querySelector('#associations').querySelectorAll('.btn').forEach(e => e.parentNode.removeChild(e));

        // Iframe load finished, hide Joomla loading layer.
        Joomla.loadingLayer('hide');
      }
    });
  });
})(Joomla, document);
