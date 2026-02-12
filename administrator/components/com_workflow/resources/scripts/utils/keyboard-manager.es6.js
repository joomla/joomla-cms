import {
  announce, cycleFocus, cycleMode,
} from './focus-utils.es6';

/**
 * Attach global keyboard listeners for workflow canvas.
 * @param {Object} options
 * @param {Function} addStage - Function to add a new stage
 * @param {Function} addTransition - Function to add a new transition
 * @param {Function} editItem - Function to edit an item
 * @param {Function} deleteItem - Function to delete an item
 * @param {Function} setSaveStatus - Function to set the save status of positions
 * @param {Function} updateSaveMessage - Function to update the save message
 * @param {Function} saveNodePosition - Function to save the node position
 * @param {Function} clearSelection - Function to clear the selection
 * @param {Function} zoomIn - Function to zoom in
 * @param {Function} zoomOut - Function to zoom out
 * @param {Function} fitView - Function to fit the view
 * @param {Ref<Object>} viewport - Ref to the viewport object
 * @param {Object} state - { selectedStage, selectedTransition, isTransitionMode, liveRegion }
 * @param {Object} store - Vuex store instance
 */
export function setupGlobalShortcuts({
  addStage, addTransition, editItem, deleteItem,
  setSaveStatus, updateSaveMessage, saveNodePosition,
  clearSelection, zoomIn, zoomOut, fitView,
  viewport, state, store,
}) {
  function handleKey(e) {
    const iframe = document.querySelector('joomla-dialog dialog[open]');
    if (iframe) {
      if (e.code === 'Escape') {
        e.preventDefault();
        iframe.close();
        return;
      }
      return;
    }

    const groupSelectors = {
      buttons: 'button, button:not([tabindex="-1"])',
      stages: '.stage-node',
      transitions: '.edge-label',
      toolbar: '.toolbar-button',
      actions: '.action-button',
      links: 'a[href], a[href]:not([tabindex="-1"])',
    };

    function moveNode(stageId, direction, fast = false) {
      const el = document.querySelector(`.stage-node[data-stage-id='${stageId}']`);
      if (!el || !store) return;

      const moveBy = fast ? 20 : 5;

      const stageIndex = store.getters.stages.findIndex((s) => s.id === parseInt(stageId, 10));
      if (stageIndex === -1) return;
      const currentPosition = store.getters.stages[stageIndex].position || { x: 0, y: 0 };
      if (!currentPosition) return;

      let { x, y } = currentPosition;

      switch (direction) {
        case 'ArrowUp': y -= moveBy; break;
        case 'ArrowDown': y += moveBy; break;
        case 'ArrowLeft': x -= moveBy; break;
        case 'ArrowRight': x += moveBy; break;
        default: return;
      }

      store.dispatch('updateStagePosition', { id: stageId, x, y });
      setSaveStatus('unsaved');
      updateSaveMessage();
      saveNodePosition();
    }

    switch (true) {
      /* ---------- Add Stage / Transition ---------- */
      case e.altKey && e.code === 'KeyN':
        e.preventDefault();
        addStage();
        announce(state.liveRegion, 'Add stage');
        break;

      case e.altKey && e.code === 'KeyM':
        e.preventDefault();
        addTransition();
        announce(state.liveRegion, 'Add transition');
        break;

      /* ---------- Edit / Delete ---------- */
      case e.code === 'KeyE':
        e.preventDefault();
        editItem();
        break;

      case e.code === 'Delete' || e.code === 'Backspace':
        e.preventDefault();
        deleteItem();
        break;

      case e.code === 'Escape':
        e.preventDefault();
        clearSelection();
        break;

      /* ---------- Zoom / View ---------- */
      case e.code === 'Equal': // + / =
        e.preventDefault();
        zoomIn();
        break;

      case e.code === 'Minus': // - / _
        e.preventDefault();
        zoomOut();
        break;

      case e.code === 'KeyF':
        e.preventDefault();
        fitView({ padding: 0.5, duration: 300 });
        break;

      /* ---------- Focus Mode Cycling ---------- */
      case e.code === 'Tab': {
        e.preventDefault();
        cycleMode(['buttons', 'stages', 'transitions', 'toolbar', 'actions', 'links'], state.currentFocusMode, state.liveRegion);
        const tabSelector = groupSelectors[state.currentFocusMode.value];
        if (tabSelector) {
          const first = document.querySelector(tabSelector);
          if (first) first.focus();
        }
        break;
      }

      /* ---------- Arrow Navigation ---------- */
      case ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.code):
        e.preventDefault();
        if (state.selectedStage.value) {
          if (e.shiftKey) {
            moveNode(state.selectedStage.value.toString(), e.code, true);
          } else {
            const buttonSelector = `.stage-node[data-stage-id='${state.selectedStage.value}'] button[tabindex="0"]`;
            if (buttonSelector) cycleFocus(buttonSelector, 0);
          }
        } else if (state.selectedTransition.value) {
          const buttonSelector = `.edge-label[data-edge-id='${state.selectedTransition.value}'] button[tabindex="0"]`;
          if (buttonSelector) cycleFocus(buttonSelector, 0);
        } else if (e.shiftKey) {
          const panStep = 20;
          switch (e.code) {
            case 'ArrowUp': viewport.value.y += panStep; break;
            case 'ArrowDown': viewport.value.y -= panStep; break;
            case 'ArrowLeft': viewport.value.x += panStep; break;
            case 'ArrowRight': viewport.value.x -= panStep; break;
            default: break;
          }
        } else {
          const reverse = ['ArrowLeft', 'ArrowUp'].includes(e.code);
          const selector = groupSelectors[state.currentFocusMode.value];
          if (selector) cycleFocus(selector, reverse);
        }
        break;

      default:
        break;
    }
  }

  document.addEventListener('keydown', handleKey);

  return () => {
    document.removeEventListener('keydown', handleKey);
  };
}
