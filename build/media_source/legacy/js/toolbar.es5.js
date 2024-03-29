/**
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';

  /**
   * USED IN: libraries/joomla/html/toolbar/button/help.php
   *
   * Pops up a new window in the middle of the screen
   *
   * @param {string}  mypage  The URL for the redirect
   * @param {string}  myname  The name of the page
   * @param {int}     w       The width of the new window
   * @param {int}     h       The height of the new window
   * @param {string}  scroll  The vertical/horizontal scroll bars
   *
   * @since 4.0.0
   *
   * @deprecated  4.3 will be removed in 6.0
   *             Will be removed without replacement. Use browser native call instead
   */
  Joomla.popupWindow = function (mypage, myname, w, h, scroll) {
    const winl = (screen.width - w) / 2;
    const wint = (screen.height - h) / 2;
    const winprops = `height=${h},width=${w},top=${wint},left=${winl},scrollbars=${scroll},resizable`;

    window.open(mypage, myname, winprops).window.focus();
  };

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
