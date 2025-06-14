/**
 * @package     Joomla.Site
 * @subpackage  Templates.Cassiopeia
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  function initTemplate(event) {
    const target = event && event.target ? event.target : document;

    /**
     * Prevent clicks on buttons within a disabled fieldset
     */
    target.querySelectorAll('fieldset.btn-group').forEach((fieldset) => {
      if (fieldset.getAttribute('disabled') === true) {
        fieldset.style.pointerEvents = 'none';
        fieldset.querySelectorAll('.btn').forEach((btn) => btn.classList.add('disabled'));
      }
    });
  }

  document.addEventListener('DOMContentLoaded', (event) => {
    initTemplate(event);

    /**
     * Back to top
     */
    const backToTop = document.getElementById('back-top');

    function checkScrollPos() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    }

    if (backToTop) {
      checkScrollPos();

      window.addEventListener('scroll', checkScrollPos);

      backToTop.addEventListener('click', (ev) => {
        ev.preventDefault();
        window.scrollTo(0, 0);
      });
    }

    document.head.querySelectorAll('link[rel="lazy-stylesheet"]').forEach(($link) => {
      $link.rel = 'stylesheet';
    });
  });

  /**
   * Initialize when a part of the page was updated
   */
  document.addEventListener('joomla:updated', initTemplate);
})(Joomla, document);
