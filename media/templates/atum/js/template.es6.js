/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla, doc) => {
  'use strict';

  const storageEnabled = typeof Storage !== 'undefined';
  const mobile = window.matchMedia('(max-width: 992px)');
  const small = window.matchMedia('(max-width: 575.98px)');
  const smallLandscape = window.matchMedia('(max-width: 767.98px)');
  const tablet = window.matchMedia('(min-width: 576px) and (max-width:991.98px)');
  /**
   * Shrink or extend the logo, depending on sidebar
   *
   * @param {string} [change] is the sidebar 'open' or 'closed'
   *
   * @since   4.0.0
   */

  function changeLogo(change) {
    const logo = doc.querySelector('.logo');
    const isLogin = doc.querySelector('body.com_login');

    if (!logo || isLogin) {
      return;
    }

    const state = change || storageEnabled && localStorage.getItem('atum-sidebar');

    if (state === 'closed') {
      logo.classList.add('small');
    } else {
      logo.classList.remove('small');
    }
  }
  /**
   * toggle arrow icon between down and up depending on position of the nav header
   *
   * @param {string} [positionTop] set if the nav header positioned to the 'top' otherwise 'bottom'
   *
   * @since   4.0.0
   */


  function toggleArrowIcon(positionTop) {
    const navDropDownIcon = doc.querySelectorAll('.nav-item.dropdown span[class*="fa-angle-"]');
    const remIcon = positionTop ? 'fa-angle-up' : 'fa-angle-down';
    const addIcon = positionTop ? 'fa-angle-down' : 'fa-angle-up';

    if (!navDropDownIcon) {
      return;
    }

    navDropDownIcon.forEach(item => {
      item.classList.remove(remIcon);
      item.classList.add(addIcon);
    });
  }
  /**
   * adjust color of svg logos
   *
   * @since   4.0.0
   */


  function changeSVGLogoColor() {
    const logoImgs = document.querySelectorAll('.logo img');
    logoImgs.forEach(img => {
      const imgID = img.getAttribute('id');
      const imgClass = img.getAttribute('class');
      const imgURL = img.getAttribute('src'); // Check if we're manipulating a SVG file.

      if (imgURL.substr(imgURL.length - 4).toLowerCase() !== '.svg') {
        return;
      }

      Joomla.request({
        url: imgURL,
        method: 'GET',
        onSuccess: response => {
          // Get the SVG tag, ignore the rest
          const parsedImg = new DOMParser().parseFromString(response, 'image/svg+xml');
          const svg = parsedImg.getElementsByTagName('svg')[0]; // Add replaced image's ID to the new SVG

          if (imgID) {
            svg.setAttribute('id', imgID);
          } // Add replaced image's classes to the new SVG


          if (imgClass) {
            svg.setAttribute('class', `${imgClass} replaced-svg`);
          } // Remove any invalid XML tags as per http://validator.w3.org


          svg.removeAttribute('xmlns:a'); // Check if the viewport is set, if the viewport is not set the SVG wont't scale.

          if (!svg.hasAttribute('viewBox') && svg.hasAttribute('height') && svg.hasAttribute('width')) {
            svg.setAttribute('viewBox', `0 0 ${svg.getAttribute('height')} ${svg.getAttribute('width')}`);
          } // Replace image with new SVG


          img.parentElement.replaceChild(svg, img);
        }
      });
    });
  }
  /**
   * put elements that are too much in the header in a dropdown
   *
   * @since   4.0.0
   */


  function headerItemsInDropdown() {
    const headerWrapper = doc.querySelector('.header-items');
    const headerItems = doc.querySelectorAll('.header-items > .header-item');
    const headerWrapperWidth = headerWrapper.offsetWidth;
    let headerItemsWidth = 0;
    headerItems.forEach(item => {
      headerItemsWidth += item.offsetWidth;
    });

    if (headerItemsWidth > headerWrapperWidth) {
      if (!doc.querySelector('#header-more-items')) {
        const headerMoreItem = document.createElement('div');
        headerMoreItem.className = 'header-item header-item-more d-flex';
        headerMoreItem.id = 'header-more-items';
        const headerItemContent = document.createElement('div');
        headerItemContent.className = 'header-item-content header-more d-flex';
        const headerMoreBtn = document.createElement('button');
        headerMoreBtn.className = 'header-more-btn d-flex flex-column align-items-stretch';
        headerMoreBtn.setAttribute('type', 'button');
        headerMoreBtn.setAttribute('title', 'More Elements');
        const spanFa = document.createElement('span');
        spanFa.className = 'fas fa-ellipsis-h';
        spanFa.setAttribute('aria-hidden', 'true');
        const headerMoreMenu = document.createElement('div');
        headerMoreMenu.className = 'header-more-menu d-flex flex-wrap';
        headerMoreBtn.appendChild(spanFa);
        headerItemContent.appendChild(headerMoreBtn);
        headerMoreItem.appendChild(headerItemContent);
        headerMoreItem.appendChild(headerMoreMenu);
        headerWrapper.appendChild(headerMoreItem);
        headerMoreBtn.addEventListener('click', () => {
          headerMoreItem.classList.toggle('active');
        });
        headerItemsWidth += headerMoreItem.offsetWidth;
      }

      const headerMoreWrapper = headerWrapper.querySelector('#header-more-items .header-more-menu');
      const headerMoreItems = headerMoreWrapper.querySelectorAll('.header-item');
      headerItems.forEach(item => {
        if (headerItemsWidth > headerWrapperWidth && item.id !== 'header-more-items') {
          headerItemsWidth -= item.offsetWidth;

          if (!headerMoreItems) {
            headerMoreWrapper.appendChild(item);
          } else {
            headerMoreWrapper.insertBefore(item, headerMoreItems[0]);
          }
        }
      });
    } else if (headerItemsWidth < headerWrapperWidth && doc.querySelector('#header-more-items')) {
      const headerMore = headerWrapper.querySelector('#header-more-items');
      const headerMoreItems = headerMore.querySelectorAll('.header-item');
      headerMoreItems.forEach(item => {
        headerItemsWidth += item.offsetWidth;

        if (headerItemsWidth < headerWrapperWidth) {
          headerWrapper.insertBefore(item, doc.querySelector('.header-items > .header-item'));
        }
      });

      if (!headerMore.querySelectorAll('.header-item').length) {
        headerWrapper.removeChild(headerMore);
      }
    }
  }
  /**
   * Change appearance for mobile devices
   *
   * @since   4.0.0
   */


  function setMobile() {
    const menu = doc.querySelector('.sidebar-menu');
    const sidebarNav = doc.querySelector('.sidebar-nav');
    const subhead = doc.querySelector('.subhead');
    const wrapper = doc.querySelector('.wrapper');
    changeLogo('closed');

    if (small.matches) {
      toggleArrowIcon();

      if (menu) {
        wrapper.classList.remove('closed');
      }
    } else {
      toggleArrowIcon('top');
    }

    if (tablet.matches && menu) {
      wrapper.classList.add('closed');
    }

    if (smallLandscape.matches) {
      if (sidebarNav) sidebarNav.classList.add('mm-collapse');
      if (subhead) subhead.classList.add('mm-collapse');
    } else {
      if (sidebarNav) sidebarNav.classList.remove('mm-collapse');
      if (subhead) subhead.classList.remove('mm-collapse');
    }
  }
  /**
   * Change appearance for mobile devices
   *
   * @since   4.0.0
   */


  function setDesktop() {
    const sidebarWrapper = doc.querySelector('.sidebar-wrapper');

    if (!sidebarWrapper) {
      changeLogo('closed');
    } else {
      changeLogo();
    }

    toggleArrowIcon('top');
  }
  /**
   * React on resizing window
   *
   * @since   4.0.0
   */


  function reactToResize() {
    window.addEventListener('resize', () => {
      if (mobile.matches) {
        setMobile();
      } else {
        setDesktop();
      }

      headerItemsInDropdown();
    });
  }
  /**
   * Subhead gets white background when user scrolls down
   *
   * @since   4.0.0
   */


  function subheadScrolling() {
    const subhead = doc.querySelector('.subhead');

    if (subhead) {
      doc.addEventListener('scroll', () => {
        if (window.scrollY > 0) {
          subhead.classList.add('bg-white', 'shadow-sm');
        } else {
          subhead.classList.remove('bg-white', 'shadow-sm');
        }
      });
    }
  }

  doc.addEventListener('DOMContentLoaded', () => {
    changeSVGLogoColor();
    headerItemsInDropdown();
    reactToResize();
    subheadScrolling();

    if (mobile.matches) {
      setMobile();
    } else {
      setDesktop();

      if (!navigator.cookieEnabled) {
        Joomla.renderMessages({
          error: [Joomla.Text._('JGLOBAL_WARNCOOKIES')]
        }, undefined, false, 6000);
      }

      window.addEventListener('joomla:menu-toggle', ({
        detail
      }) => {
        changeLogo(detail);
      });
    }
  });
})(window.Joomla, document);