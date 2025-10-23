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
    helpIndex.querySelectorAll('a:not(.has-arrow)').forEach(element => element.addEventListener('click', () => {
      window.scroll(0, 0);
      // Save clicked link data-id
      const id = element.dataset.id;
      if (id) {
        localStorage.setItem('helpIndex.lastClick', id);
      }
    }));
  }

  // Helper function: wait for given milliseconds
  const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

  // Async restore function
  async function restoreMenu() {
    let lastClick = localStorage.getItem('helpIndex.lastClick');
    if (!lastClick) {
      lastClick = 'start-here';
    }

    const selectedLink = helpIndex.querySelector(`a[data-id="${lastClick}"]`);
    if (!selectedLink) return;

    // Collect parent anchors top-down
    const parentAnchors = [];
    let parentLi = selectedLink.closest('li');
    while (parentLi && parentLi !== helpIndex) {
      const parentUl = parentLi.parentElement;
      const parentLiOfUl = parentUl.closest('li');
      if (parentLiOfUl) {
        const anchor = parentLiOfUl.querySelector('a.has-arrow');
        if (anchor) parentAnchors.unshift(anchor);
      }
      parentLi = parentLiOfUl;
    }

    // Trigger clicks in sequence with a delay
    for (const anchor of parentAnchors) {
      anchor.click();
      await wait(400); // adjust delay as needed (150–300ms)
    }

    // Optional: highlight selected link
    selectedLink.classList.add('active');

    // Ensure it's visible and then "click" it
    //selectedLink.scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Give the submenu a moment to fully render before clicking
    await wait(100);
    selectedLink.click();
  }

  // Run restore after MetisMenu setup delay
  setTimeout(() => {
    restoreMenu();
  }, 100);
});
