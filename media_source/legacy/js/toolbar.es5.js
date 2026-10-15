/**
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    /**
     * Fix the alignment of the Options and Help toolbar buttons
     */
    const toolbarOptions = document.getElementById('toolbar-options');
    const toolbarHelp = document.getElementById('toolbar-help');
    const toolbarInlineHelp = document.getElementById('toolbar-inlinehelp');

    // Handle Help buttons
    document.querySelectorAll('.js-toolbar-help-btn').forEach((button) => {
      button.addEventListener('click', (event) => {
        const btn = event.currentTarget;
        const winprops = `height=${parseInt(btn.dataset.height, 10)},width=${parseInt(btn.dataset.width, 10)},top=${(window.innerHeight - parseInt(btn.dataset.height, 10)) / 2},`
          + `left=${(window.innerWidth - parseInt(btn.dataset.width, 10)) / 2},scrollbars=${btn.dataset.width === 'true'},resizable`;

        window.open(btn.dataset.url, btn.dataset.tile, winprops).window.focus();
      });
    });

    if (toolbarInlineHelp) {
      toolbarInlineHelp.classList.add('ms-auto');
      return;
    }

    if (toolbarHelp && !toolbarOptions) {
      toolbarHelp.classList.add('ms-auto');
    }

    if (toolbarOptions && !toolbarHelp) {
      toolbarOptions.classList.add('ms-auto');
    }
  });
}(Joomla, document));
