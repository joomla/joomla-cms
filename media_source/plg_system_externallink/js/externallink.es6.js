/**
 * @package     Joomla.Plugin
 * @subpackage  System.externallink
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
  const messageKey = 'PLG_SYSTEM_EXTERNALLINK_NEW_WINDOW';
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

  const findLastVisibleTextNode = (root) => {
    const children = Array.from(root.childNodes);

    for (let index = children.length - 1; index >= 0; index--) {
      const child = children[index];

      if (child.nodeType === Node.COMMENT_NODE) {
        continue;
      }

      if (child.nodeType === Node.TEXT_NODE) {
        if (child.nodeValue.trim() !== '') {
          return child;
        }

        continue;
      }

      if (child.nodeType === Node.ELEMENT_NODE) {
        if (child.matches('.sr-only, .visually-hidden, [aria-hidden="true"]')) {
          continue;
        }

        const nested = findLastVisibleTextNode(child);

        if (nested) {
          return nested;
        }
      }
    }

    return null;
  };

  const wrapLastWord = (link, icon, hiddenText) => {
    const textNode = findLastVisibleTextNode(link);

    if (!textNode) {
      return false;
    }

    const match = textNode.nodeValue.match(/^([\s\S]*?)(\s*)(\S+)(\s*)$/);

    if (!match || match[3].length >= 30) {
      return false;
    }

    const [, before, separator, word, trailingWhitespace] = match;
    const wrapper = document.createElement('span');
    wrapper.className = noBreakClass;

    const parent = textNode.parentNode;
    const wordNode = document.createTextNode(word);
    const trailingNode = trailingWhitespace ? document.createTextNode(trailingWhitespace) : null;

    if (before || separator) {
      parent.insertBefore(document.createTextNode(before + separator), textNode);
    }

    parent.insertBefore(wrapper, textNode);
    wrapper.appendChild(wordNode);
    wrapper.appendChild(icon);
    wrapper.appendChild(hiddenText);

    if (trailingNode) {
      parent.insertBefore(trailingNode, textNode.nextSibling);
    }

    parent.removeChild(textNode);

    // Existing hidden descendants at the end of the link must remain hidden and in order.
    // Only move the wrapper to the link's end when doing so does not change inline nesting.
    if (parent === link) {
      let following = wrapper.nextSibling;
      let canMoveToEnd = true;

      while (following) {
        const next = following.nextSibling;

        if (following.nodeType === Node.TEXT_NODE && following.nodeValue.trim() === '') {
          following = next;
          continue;
        }

        if (following.nodeType === Node.COMMENT_NODE) {
          following = next;
          continue;
        }

        if (following.nodeType === Node.ELEMENT_NODE && following.matches('.sr-only, .visually-hidden')) {
          wrapper.appendChild(following);
          following = next;
          continue;
        }

        canMoveToEnd = false;
        break;
      }

      if (canMoveToEnd) {
        link.appendChild(wrapper);

        if (trailingNode) {
          link.appendChild(trailingNode);
        }
      }
    }

    return true;
  };

  const processLink = (link) => {
    if (link.classList.contains(excludeClass) || link.classList.contains(processedClass) || link.querySelector('.' + iconClass)) {
      return;
    }

    const icon = makeIcon();
    const hiddenText = document.createElement('span');
    hiddenText.className = hiddenClass;
    hiddenText.textContent = Joomla.Text._(messageKey);

    const wrapped = wrapLastWord(link, icon, hiddenText);

    if (!wrapped) {
      const wrapper = document.createElement('span');
      wrapper.className = noBreakClass;
      wrapper.appendChild(icon);
      wrapper.appendChild(hiddenText);
      link.appendChild(wrapper);
    }

    link.classList.add(processedClass);
  };

  const processLinks = (root = document) => {
    if (root.matches && root.matches(selector)) {
      processLink(root);
    }

    if (root.querySelectorAll) {
      root.querySelectorAll(selector + ':not(.' + excludeClass + ')').forEach(processLink);
    }
  };

  processLinks();

  // Handle content inserted after the initial page load without duplicating indicators.
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
