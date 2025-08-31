import {
  announce, cycleFocus, cycleMode,
} from './focus-utils.es6';

/**
 * Attach global keyboard listeners for workflow canvas.
 * @param {Object} options
 * @param {Function} addStage
 * @param {Function} addTransition
 * @param {Function} editItem
 * @param {Function} deleteItem
 * @param {Function} toggleMode
 * @param {Function} undo
 * @param {Function} redo
 * @param {Function} clearSelection
 * @param {Function} zoomIn
 * @param {Function} zoomOut
 *
 * @param {Object} state - { selectedStage, selectedTransition, isTransitionMode, liveRegion }
 */
export function setupGlobalShortcuts({
  addStage, addTransition, editItem, deleteItem,
  undo, redo, updateSaveMessage, saveNodePosition,
  clearSelection, zoomIn, zoomOut, fitView,
  viewport, state, setSaveStatus, store,
}) {
  function isModifierPressed(e, key) {
    return (e.ctrlKey || e.metaKey) && [key.toLowerCase(), key.toUpperCase()].includes(e.key);
  }
  function handleKey(e) {
    const iframe = document.querySelector('joomla-dialog dialog[open]');
    if (iframe) {
      if (e.key === 'Escape') {
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
      if (!el) return;

      const moveBy = fast ? 20 : 5;
      if (!store) return;

      const stageIndex = store.getters.stages.findIndex((s) => s.id === parseInt(stageId, 10));
      if (stageIndex === -1) return;
      const currentPosition = store.getters.stages[stageIndex].position || { x: 0, y: 0 };
      if (!currentPosition) return;

      let newX = currentPosition.x;
      let newY = currentPosition.y;

      switch (direction) {
        case 'ArrowUp': newY -= moveBy; break;
        case 'ArrowDown': newY += moveBy; break;
        case 'ArrowLeft': newX -= moveBy; break;
        case 'ArrowRight': newX += moveBy; break;
        default: break;
      }

      store.dispatch('updateStagePosition', { id: stageId, x: newX, y: newY });
      setSaveStatus('unsaved');
      updateSaveMessage();
      saveNodePosition();
    }

    switch (true) {
      case e.altKey && ['n', 'N'].includes(e.key):
        e.preventDefault();
        addStage();
        announce(state.liveRegion, 'Add stage');
        break;

      case e.altKey && ['m', 'M'].includes(e.key):
        e.preventDefault();
        addTransition();
        announce(state.liveRegion, 'Add transition');
        break;

      case isModifierPressed(e, 'z'):
        e.preventDefault();
        undo();
        break;

      case isModifierPressed(e, 'y'):
        e.preventDefault();
        redo();
        break;

      case e.key === 'e' || e.key === 'E':
        e.preventDefault();
        editItem();
        break;

      case e.key === 'Delete' || e.key === 'Backspace':
        e.preventDefault();
        deleteItem();
        break;

      case e.key === 'Escape':
        e.preventDefault();
        clearSelection();
        break;

      case ['+', '='].includes(e.key):
        e.preventDefault();
        zoomIn();
        break;

      case ['-', '_'].includes(e.key):
        e.preventDefault();
        zoomOut();
        break;

      case ['f', 'F'].includes(e.key):
        e.preventDefault();
        fitView({ padding: 0.5, duration: 300 });
        break;

      case e.key === 'Tab': {
        e.preventDefault();
        cycleMode(['buttons', 'stages', 'transitions', 'toolbar', 'actions', 'links'], state.currentFocusMode, state.liveRegion);
        const tabSelector = groupSelectors[state.currentFocusMode.value];
        if (tabSelector) {
          const first = document.querySelector(tabSelector);
          if (first) first.focus();
        }
        break;
      }

      case ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key):
        e.preventDefault();
        if (state.selectedStage.value) {
          if (e.shiftKey) {
            moveNode(state.selectedStage.value.toString(), e.key, e.shiftKey);
          } else {
            const buttonSelector = `.stage-node[data-stage-id='${state.selectedStage.value}'] button[tabindex="0"]`;
            if (buttonSelector) {
              cycleFocus(buttonSelector, 0);
            }
          }
        } else if (state.selectedTransition.value) {
          const buttonSelector = `.edge-label[data-edge-id='${state.selectedTransition.value}'] button[tabindex="0"]`;
          if (buttonSelector) {
            cycleFocus(buttonSelector, 0);
          }
        } else if (e.shiftKey) {
          const panStep = 20;
          switch (e.key) {
            case 'ArrowUp': viewport.value.y += panStep; break;
            case 'ArrowDown': viewport.value.y -= panStep; break;
            case 'ArrowLeft': viewport.value.x += panStep; break;
            case 'ArrowRight': viewport.value.x -= panStep; break;
            default: break;
          }
        } else {
          const reverse = ['ArrowLeft', 'ArrowUp'].includes(e.key);
          const selector = groupSelectors[state.currentFocusMode.value];
          if (selector) {
            cycleFocus(selector, reverse);
          }
          break;
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
