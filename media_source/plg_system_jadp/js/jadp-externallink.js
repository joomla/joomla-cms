/**
 * @package     Joomla.Plugin
 * @subpackage  System.jadp
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  const selector = 'a[target="_blank"]';
  const excludeClass = 'no-external-link-icon';
  const processedClass = 'plg-system-externallink-processed';
  const noBreakClass = 'plg-system-externallink-nobreak';
  const iconClass = 'plg-system-externallink-icon';
  const hiddenClass = 'visually-hidden';
  const messageKey = 'PLG_SYSTEM_JADP_EXTERNAL_LINK_NEW_WINDOW';
  const svgNamespace = 'http://www.w3.org/2000/svg';

  const makeIcon = () => {
    const wrapper = document.createElement('span');
    wrapper.className = iconClass;
    wrapper.setAttribute('aria-hidden', 'true');

    const svg = document.createElementNS(svgNamespace, 'svg');
    svg.setAttribute('viewBox', '0 0 16 16');
    svg.setAttribute('width', '0.9em');
    svg.setAttribute('height', '0.9em');
    svg.setAttribute('focusable', 'false');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.5');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');

    const windowPath = document.createElementNS(svgNamespace, 'path');
    windowPath.setAttribute('d', 'M9.5 2H14v4.5M14 2 7.5 8.5');
    svg.appendChild(windowPath);

    const boxPath = document.createElementNS(svgNamespace, 'path');
    boxPath.setAttribute('d', 'M12 9v4a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4');
    svg.appendChild(boxPath);

    wrapper.appendChild(svg);

    return wrapper;
  };

  const processLink = (link) => {
    if (
      link.classList.contains(excludeClass) ||
      link.querySelector('.' + iconClass)
    ) {
      return;
    }

    const icon = makeIcon();

    const hiddenText = document.createElement('span');
    hiddenText.className = hiddenClass;
    hiddenText.textContent = Joomla.Text._(messageKey);

    link.appendChild(icon);
    link.appendChild(hiddenText);
  };

  const processLinks = (root = document) => {
    if (root.matches && root.matches(selector)) {
      processLink(root);
    }

    if (root.querySelectorAll) {
      root.querySelectorAll(selector).forEach(processLink);
    }
  };

  processLinks();

  // Handle content inserted after the initial page load.
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE) {
          processLinks(node);
        }
      });
    });
  });

  observer.observe(document.documentElement, {
    childList: true,
    subtree: true,
  });
})();
