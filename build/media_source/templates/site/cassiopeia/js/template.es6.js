/**
 * @package     Joomla.Site
 * @subpackage  Templates.Cassiopeia
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */
const initTemplate = (event = { target: document }) => {
  /**
   * Prevent clicks on buttons within a disabled fieldset
   */
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

/**
 * Initialize when a part of the page was updated
 */
document.addEventListener('joomla:updated', initTemplate);

/**
 * Back to top button
 */
const backToTopButton = document.getElementById('back-top');
if (backToTopButton) {
  const toggleBackToTopButton = () => {
    if (backToTopButton) {
      backToTopButton.classList.toggle('visible');
    }
  };

  const observer = new IntersectionObserver(toggleBackToTopButton);

  backToTopButton.addEventListener('click', (event) => {
    event.preventDefault();
    window.scrollTo(0, 0);
  });

  observer.observe(document.querySelector('.header.container-header'));
}

// Lazyloaded stylesheets
[].slice.call(document.head.querySelectorAll('link[rel="lazy-stylesheet"]'))
  .forEach(($link) => { $link.rel = 'stylesheet'; });
