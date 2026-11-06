/**
 * Announce a message via ARIA live region.
 * @param {HTMLElement} liveRegionElement
 * @param {string} message
 */
export function announce(liveRegionElement, message) {
  if (!liveRegionElement || !message) return;
  liveRegionElement.textContent = '';
  setTimeout(() => {
    liveRegionElement.textContent = message;
  }, 10);
}

/**
 * Focus a stage node by stageId.
 * @param {string|number} stageId - The ID of the stage to focus
 */
export function focusNode(stageId) {
  const el = document.querySelector(`.stage-node[data-stage-id='${stageId}']`);
  if (el) el.focus();
}

/**
 * Focus an edge label by transitionId.
 * @param {string|number} transitionId - The ID of the transition to focus
 */
export function focusEdge(transitionId) {
  const el = document.querySelector(`.edge-label[data-edge-id='${transitionId}']`);
  if (el) el.focus();
}

/**
 * Find and cycle focus among elements with a selector.
 * @param {string} selector - The selector for the elements to focus
 * @param {boolean} reverse - Whether to cycle focus in reverse order
 */
export function cycleFocus(selector, reverse = false) {
  const elements = Array.from(document.querySelectorAll(selector));
  if (!elements.length) return;
  const currentIndex = elements.indexOf(document.activeElement);
  let nextIndex;
  if (reverse) {
    nextIndex = currentIndex <= 0 ? elements.length - 1 : currentIndex - 1;
  } else {
    nextIndex = currentIndex >= elements.length - 1 ? 0 : currentIndex + 1;
  }
  elements[nextIndex].focus();
}

/**
 * Cycle between defined focus modes (e.g., stages → transitions → toolbar → actions).
 * @param {string[]} focusModes - Array of focus mode strings.
 * @param {Ref<string>} currentModeRef - Vue ref holding the current mode.
 * @param {HTMLElement} liveRegionElement - ARIA live region for screen reader feedback.
 */
export function cycleMode(focusModes, currentModeRef, liveRegionElement) {
  const currentIndex = focusModes.indexOf(currentModeRef.value);
  const nextIndex = (currentIndex + 1) % focusModes.length;
  currentModeRef.value = focusModes[nextIndex];
}

/**
 * Handle focus and keyboard events for dialog iframes.
 * This function sets focus to the first input or body of the iframe,
 * and adds an Escape key listener to close the dialog.
 *
 * @param {HTMLIFrameElement} iframe - The iframe element to handle.
 *
 */
function handleDialogIframeLoad(iframe) {
  try {
    iframe.focus();
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    if (iframeDoc) {
      const firstInput = iframeDoc.querySelector('input:not([type="hidden"]), select, textarea');
      if (firstInput) {
        firstInput.focus();
      } else {
        iframeDoc.body.focus();
      }

      iframeDoc.addEventListener('keydown', (e) => {
        if (e.code === 'Escape') {
          e.preventDefault();
          const parentDialog = document.querySelector('joomla-dialog dialog[open]');
          if (parentDialog && parentDialog.close) {
            parentDialog.close();
          }
        }
      });
    }
  } catch (error) {
    iframe.focus();
  }
}

/**
 * Handle dialog close event.
 * @param previouslyFocusedElement - The element that was focused before the dialog opened
 * @param store - The Vuex store instance
 */
function handleDialogClose(previouslyFocusedElement, store) {
  if (previouslyFocusedElement.value) {
    previouslyFocusedElement.value.focus();
    previouslyFocusedElement.value = null;
  }
  store.dispatch('loadWorkflow', store.getters.workflowId);
}

/**
 * Handle Escape keydown event on dialog.
 * @param e - The keyboard event
 */
function handleDialogKeydown(e) {
  if (e.code === 'Escape') {
    e.preventDefault();
    const dialog = e.currentTarget;
    if (dialog && dialog.close) {
      dialog.close();
    }
  }
}

/**
 * Setup focus handlers for dialog iframes.
 * This function will focus the dialog and handle iframe loading and closing.
 *
 * @param {Ref<HTMLElement>} previouslyFocusedElement - Ref to store the previously focused element.
 * @param {Object} store - Vuex store instance.
 */
export function setupDialogFocusHandlers(previouslyFocusedElement, store) {
  setTimeout(() => {
    const dialog = document.querySelector('joomla-dialog dialog[open]');
    if (dialog) {
      dialog.focus();
      const iframe = dialog.querySelector('iframe');
      if (iframe) {
        iframe.addEventListener('load', () => {
          handleDialogIframeLoad(iframe);
        });
      }

      dialog.addEventListener('close', () => {
        handleDialogClose(previouslyFocusedElement, store);
      });
      dialog.addEventListener('keydown', handleDialogKeydown);
    }
  }, 100);
}
