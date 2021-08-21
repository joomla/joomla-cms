/**
 * @package     Joomla.Site
 * @subpackage  Templates.Cassiopeia
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

// Prevent clicks on buttons within a disabled fieldset
const initTemplate = (event = { target: document }) => {
  [].slice.call(event.target.querySelectorAll('fieldset.btn-group'))
    .forEach((field) => {
      if (field.hasAttribute('disabled') === true) {
        field.style.pointerEvents = 'none';
        [].slice.call(field.querySelectorAll('.btn'))
          .forEach((button) => {
            button.classList.add('disabled');
          });
      }
    });
};

// Execute on load
initTemplate();
// Execute when a part of the page was updated
document.addEventListener('joomla:updated', initTemplate);

// Back to top button
const backToTopButton = document.getElementById('back-top');
if (backToTopButton) {
  backToTopButton.addEventListener('click', (event) => {
    event.preventDefault();
    window.scrollTo(0, 0);
  });

  if ('IntersectionObserver' in window) {
    (new IntersectionObserver(() => { backToTopButton.classList.toggle('visible'); }))
    .observe(document.querySelector('.header.container-header'));
  } else {
    // Remove once 2018 browsers are the minimum supported on Joomla
    window.addEventListener('scroll', () => {
      const method = document.body.scrollTop > 20 || document.documentElement.scrollTop > 20 ? 'add' : 'remove';
      backToTopButton.classList[method]('visible');
    }, { passive: true });
  }
}

// Lazyloaded stylesheets
[].slice.call(document.head.querySelectorAll('link[rel="lazy-stylesheet"]'))
  .forEach(($link) => { $link.rel = 'stylesheet'; });
