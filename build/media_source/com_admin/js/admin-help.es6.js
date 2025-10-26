/**
  * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
  * @license     GNU General Public License version 2 or later; see LICENSE.txt
  */

document.addEventListener("DOMContentLoaded", function (event) {
  new MetisMenu('#helpmenu', {
    toggle: true
  });

  const helpIndex = document.getElementById('helpmenu');
  if (helpIndex) {
    helpIndex.querySelectorAll('a').forEach(element =>
      element.addEventListener('click', () => {
        window.scroll(0, 0);
        if (element.classList.contains('has-arrow')) {
          // 🔸 Action for a link to a folder, where <a class="has-arrow">
          if (element.classList.contains('mm-collapsed')) {
            element.classList.remove('active');
          } else {
            element.classList.add('active');
          }
        } else {
          // Action for a link to an article.
          const id = element.dataset.id;
          if (id) {
            // First, reset all other links to default state
            helpIndex.querySelectorAll('a:not(has-arrow)').forEach(a => {
              if (a.dataset.id !== id) {
                a.classList.remove('active');
              } else {
                element.classList.add('active');
              }
            });
          }
          localStorage.setItem('helpIndex.lastClick', id);

          // In narrow screens, close the help menu after selecting an item.
          const btn = document.querySelector('button[data-bs-target="#help-index"]');
          const isVisible = !!(btn && btn.offsetParent !== null);
          if (isVisible) {
            document.querySelector(`nav#help-index`).classList.add('collapse');
            document.querySelector(`nav#help-index`).classList.remove('show');
          }
        }
      })
    );
  }

  // Async restore function
  function restoreMenu() {
    let lastClick = localStorage.getItem('helpIndex.lastClick');
    if (!lastClick) {
      lastClick = 'start-here';
    }
    
    const selectedLink = helpIndex.querySelector(`a[data-id="${lastClick}"]`);
    if (!selectedLink) return;

    // Collect parent list items top-down
    const lists = [];
    let parentLi = selectedLink.closest('li');
    while (parentLi && parentLi !== helpIndex) {
      const parentUl = parentLi.parentElement;
      const parentLiOfUl = parentUl.closest('li');
      if (parentLiOfUl) {
        const li = parentLiOfUl;
        if (li) lists.unshift(li);
      }
      parentLi = parentLiOfUl;
    }

    // Trigger clicks in sequence
    for (const li of lists) {
      li.classList.add('mm-active');
      li.querySelector('ul').classList.add('mm-show');
      li.querySelector('a').classList.add('active');
      li.querySelector('a').setAttribute('aria-expanded', true);
    }

    // Optional: highlight selected link
    selectedLink.classList.add('active');

    // Give the submenu a moment to fully render before clicking
    selectedLink.click();
  }

  // Run restore after MetisMenu setup delay
  restoreMenu();
});
