/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const modals = document.getElementsByClassName('changelogModal');

    Array.from(modals).forEach((element) => {
      element.addEventListener('click', (modal) => {
        Joomla.loadChangelog(modal.target.dataset.jsExtensionid, modal.target.dataset.jsView);
      });
    });
  });

  /**
   * Load the changelog data
   *
   * @param extensionId The extension ID to load the changelog for
   * @param view The view the changelog is for,
   *             this is used to determine which version number to show
   *
   * @since   4.0.0
   */
  Joomla.loadChangelog = (extensionId, view) => {
    Joomla.request({
      url: `index.php?option=com_installer&task=manage.loadChangelog&eid=${extensionId}&source=${view}&format=json`,
      onSuccess: (response) => {
        const result = JSON.parse(response);
        document.querySelector(`#changelogModal${extensionId} .modal-body`).innerHTML = result.data;
      },
    });
  };
})(Joomla);
