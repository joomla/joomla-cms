/**
 * VueFlow Accessibility Fixer
 * Handles accessibility issues that cannot be fixed with CSS alone
 */

export class AccessibilityFixer {
  constructor() {
    this.observer = null;
    this.processedElements = new WeakSet();
  }

  /**
   * Initialize the accessibility fixer
   */
  init() {
    // Initial fix
    this.fixVueFlowAccessibility();

    // Set up mutation observer to handle dynamically added elements
    this.setupMutationObserver();

    // Fix elements when VueFlow updates
    this.setupVueFlowObserver();
  }

  /**
   * Fix all VueFlow accessibility issues
   */
  fixVueFlowAccessibility() {
    // Fix SVG elements
    this.fixSVGElements();

    // Fix tabbable groups
    this.fixTabbableGroups();

    // Fix graphics-document elements
    this.fixGraphicsDocuments();

    // Fix button accessible names
    this.fixButtonAccessibleNames();

    // Fix duplicate SVG element IDs
    this.fixDuplicateSVGIds();
  }

  /**
   * Hide all SVG elements from screen readers
   */
  fixSVGElements() {
    const svgSelectors = [
      '.vue-flow svg',
      '.vue-flow [role="graphics-document"]',
      '.vue-flow__background svg',
      '.vue-flow__minimap svg',
      '.vue-flow__edge svg',
      '.vue-flow__nodes svg',
      '.vue-flow__edges svg',
      'svg[role="graphics-document"]',
      'g[role="group"] svg',
      'g[role="group"] [role="graphics-document"]',
    ];

    svgSelectors.forEach((selector) => {
      const elements = document.querySelectorAll(selector);
      elements.forEach((element) => {
        if (!this.processedElements.has(element)) {
          this.hideSVGFromScreenReaders(element);
          this.processedElements.add(element);
        }
      });
    });
  }

  /**
   * Hide individual SVG element from screen readers
   */
  hideSVGFromScreenReaders(element) {
    // Only add aria-hidden to elements where it's valid
    if (!this.isInvalidForAriaHidden(element)) {
      element.setAttribute('aria-hidden', 'true');
    }
    
    // Only set role="presentation" on elements where it's valid
    if (!this.isInvalidForPresentationRole(element)) {
      element.setAttribute('role', 'presentation');
    }
    
    // Remove ARIA attributes that shouldn't be on decorative elements
    if (!this.isInvalidForAriaAttributes(element)) {
      element.removeAttribute('aria-label');
      element.removeAttribute('aria-labelledby');
      element.removeAttribute('aria-describedby');
    }

    // Also hide all children
    const children = element.querySelectorAll('*');
    children.forEach((child) => {
      // Only add aria-hidden to child elements where it's valid
      if (!this.isInvalidForAriaHidden(child)) {
        child.setAttribute('aria-hidden', 'true');
      }
      
      // Only set role="presentation" on child elements where it's valid
      if (!this.isInvalidForPresentationRole(child)) {
        child.setAttribute('role', 'presentation');
      }
      
      // Remove ARIA attributes from children where appropriate
      if (!this.isInvalidForAriaAttributes(child)) {
        child.removeAttribute('aria-label');
        child.removeAttribute('aria-labelledby');
        child.removeAttribute('aria-describedby');
      }
    });
  }
  
  /**
   * Check if role="presentation" is invalid for this element
   */
  isInvalidForPresentationRole(element) {
    const invalidTags = ['title', 'desc', 'metadata'];
    return invalidTags.includes(element.tagName?.toLowerCase());
  }

  /**
   * Check if aria-hidden is invalid for this element
   */
  isInvalidForAriaHidden(element) {
    // aria-hidden is invalid on title elements when they have role="none"
    const tagName = element.tagName?.toLowerCase();
    if (tagName === 'title' && element.getAttribute('role') === 'none') {
      return true;
    }
    // aria-hidden should not be used on title, desc, metadata elements in general
    const invalidTags = ['title', 'desc', 'metadata'];
    return invalidTags.includes(tagName);
  }

  /**
   * Check if ARIA attributes should be removed from this element
   */
  isInvalidForAriaAttributes(element) {
    // Don't remove ARIA attributes from elements that might legitimately use them
    const tagName = element.tagName?.toLowerCase();
    const protectedTags = ['title', 'desc', 'metadata'];
    return protectedTags.includes(tagName);
  }

  /**
   * Fix tabbable group elements
   */
  fixTabbableGroups() {
    const groups = document.querySelectorAll('.vue-flow [role="group"][tabindex], [role="group"][tabindex]');
    
    groups.forEach((group) => {
      if (!this.processedElements.has(group)) {
        // Remove tabindex from non-interactive groups
        const hasInteractiveChildren = group.querySelector('button, [role="button"], [role="menuitem"], input, select, textarea, a[href]');
        
        if (!hasInteractiveChildren) {
          group.removeAttribute('tabindex');
          group.style.pointerEvents = 'none';
        } else {
          // If it has interactive children, make the group non-focusable but keep children interactive
          group.removeAttribute('tabindex');
          group.style.userSelect = 'none';
          group.style.webkitUserSelect = 'none';
          group.style.mozUserSelect = 'none';
        }
        
        this.processedElements.add(group);
      }
    });
  }

  /**
   * Fix graphics-document elements
   */
  fixGraphicsDocuments() {
    const graphicsElements = document.querySelectorAll('[role="graphics-document"]');
    
    graphicsElements.forEach((element) => {
      if (!this.processedElements.has(element)) {
        this.hideSVGFromScreenReaders(element);
        this.processedElements.add(element);
      }
    });
  }

  /**
   * Fix button accessible names to match visible text
   */
  fixButtonAccessibleNames() {
    const buttons = document.querySelectorAll('.stage-node[role="button"], .edge-label[role="button"]');
    
    buttons.forEach((button) => {
      if (!this.processedElements.has(button)) {
        // Remove any conflicting aria-label that doesn't match visible text
        const currentLabel = button.getAttribute('aria-label');
        const visibleText = this.getVisibleText(button);
        
        if (currentLabel && visibleText && !visibleText.includes(currentLabel) && !currentLabel.includes(visibleText)) {
          // If aria-label doesn't match visible text, remove it to let the browser use visible text
          button.removeAttribute('aria-label');
        }
        
        this.processedElements.add(button);
      }
    });
  }

  /**
   * Fix duplicate SVG element IDs
   */
  fixDuplicateSVGIds() {
    const seenIds = new Set();
    const elementsWithIds = document.querySelectorAll('svg [id], .vue-flow [id]');
    
    elementsWithIds.forEach((element) => {
      const id = element.id;
      if (id && seenIds.has(id)) {
        // Generate a unique ID
        const uniqueId = this.generateUniqueId(id, seenIds);
        element.id = uniqueId;
        seenIds.add(uniqueId);
      } else if (id) {
        seenIds.add(id);
      }
    });
  }

  /**
   * Generate a unique ID based on the original ID
   */
  generateUniqueId(originalId, seenIds) {
    let counter = 1;
    let newId = `${originalId}-${counter}`;
    
    while (seenIds.has(newId)) {
      counter++;
      newId = `${originalId}-${counter}`;
    }
    
    return newId;
  }

  /**
   * Get the main visible text content of an element
   */
  getVisibleText(element) {
    // For stage nodes, get the title
    const titleElement = element.querySelector('.card-title');
    if (titleElement) {
      return titleElement.textContent.trim();
    }

    // For edge labels, get the header text
    const headerElement = element.querySelector('header .card-title');
    if (headerElement) {
      return headerElement.textContent.trim();
    }

    // Fallback to any text content
    return element.textContent.trim().split('\n')[0].trim();
  }

  /**
   * Setup mutation observer to handle dynamically added elements
   */
  setupMutationObserver() {
    this.observer = new MutationObserver((mutations) => {
      let shouldProcess = false;

      mutations.forEach((mutation) => {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE) {
              // Check if the added node is a VueFlow element or contains VueFlow elements
              if (node.matches && (
                node.matches('.vue-flow *') ||
                node.matches('svg') ||
                node.matches('[role="graphics-document"]') ||
                node.matches('[role="group"]') ||
                node.querySelector('.vue-flow *, svg, [role="graphics-document"], [role="group"]')
              )) {
                shouldProcess = true;
              }
            }
          });
        }
      });

      if (shouldProcess) {
        // Debounce the processing
        clearTimeout(this.processTimeout);
        this.processTimeout = setTimeout(() => {
          this.fixVueFlowAccessibility();
        }, 100);
      }
    });

    this.observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  /**
   * Setup VueFlow-specific observer for when the flow updates
   */
  setupVueFlowObserver() {
    // Also listen for VueFlow specific events if available
    const vueFlowElement = document.querySelector('.vue-flow');
    if (vueFlowElement) {
      // Set up additional observer for VueFlow container changes
      const vueFlowObserver = new MutationObserver(() => {
        clearTimeout(this.vueFlowTimeout);
        this.vueFlowTimeout = setTimeout(() => {
          this.fixVueFlowAccessibility();
        }, 200);
      });

      vueFlowObserver.observe(vueFlowElement, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['role', 'tabindex', 'aria-label', 'aria-hidden'],
      });
    }
  }

  /**
   * Cleanup the fixer
   */
  destroy() {
    if (this.observer) {
      this.observer.disconnect();
    }
    clearTimeout(this.processTimeout);
    clearTimeout(this.vueFlowTimeout);
  }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const fixer = new AccessibilityFixer();
    fixer.init();
    
    // Make it globally available for cleanup
    window.workflowAccessibilityFixer = fixer;
  });
} else {
  const fixer = new AccessibilityFixer();
  fixer.init();
  window.workflowAccessibilityFixer = fixer;
}

export default AccessibilityFixer;
