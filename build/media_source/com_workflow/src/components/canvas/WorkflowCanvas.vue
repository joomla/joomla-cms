<template>
  <div
    class="w-100 h-100 position-relative"
    role="application"
    aria-label="Workflow Graph"
  >
    <VueFlow
      v-if="!loading && !error"
      class="workflow-canvas"
      max-zoom="2.5"
      min-zoom=".3"
      :edges="styledEdges"
      :nodes="positionedNodes"
      :node-types="nodeTypes"
      :edge-types="edgeTypes"
      :nodes-connectable="workflow?.canCreate"
      :elements-selectable="true"
      :snap-to-grid="true"
      :snap-grid="[40, 40]"
      :disable-keyboard-a11y="true"
      role="application"
      :aria-label="translate('COM_WORKFLOW_GRAPH_CANVAS_LABEL')"
      @connect="handleConnect"
      @pane-click="clearSelection"
      @edge-click="selectEdge"
      @node-drag-stop="handleNodeDragStop"
    >
      <Background
        pattern-color="var(--body-color)"
        :gap="16"
        :aria-label="translate('COM_WORKFLOW_GRAPH_BACKGROUND')"
        :title="translate('COM_WORKFLOW_GRAPH_BACKGROUND')"
      />

      <!-- Controls Section -->
      <section
        id="workflow-controls"
        aria-label="Canvas Controls"
        class="workflow-controls-section"
      >
        <span class="visually-hidden">{{ translate('COM_WORKFLOW_GRAPH_CONTROLS') }}</span>

        <!-- Minimap Toggle -->
        <button
          id="toggle-minimap"
          class="toolbar-button custom-controls-button position-absolute z-20 ps-2 pe-2"
          tabindex="0"
          :style="showMiniMap ? 'bottom: 130px; left: 175px;' : 'bottom: 10px; left: 10px;'"
          :title="showMiniMap ? translate('COM_WORKFLOW_GRAPH_MINIMAP_HIDE') : translate('COM_WORKFLOW_GRAPH_MINIMAP_SHOW')"
          :aria-pressed="showMiniMap ? 'true' : 'false'"
          @click="showMiniMap = !showMiniMap"
        >
          <span
            v-if="showMiniMap"
            class="fa fa-close"
            aria-hidden="true"
          />
          <span
            v-else
            class="icon icon-expand-2"
            aria-hidden="true"
          />
          <span class="visually-hidden">
            {{ showMiniMap ? translate('COM_WORKFLOW_GRAPH_MINIMAP_HIDE') : translate('COM_WORKFLOW_GRAPH_MINIMAP_SHOW') }}
          </span>
        </button>

        <MiniMap
          v-if="showMiniMap"
          class="z-10"
          position="bottom-left"
          pannable
          zoomable
          :aria-label="translate('COM_WORKFLOW_GRAPH_MINIMAP_LABEL')"
          :node-color="(node) => node.data?.stage?.color || '#0d6efd'"
          :mask-color="'rgba(255, 255, 255, .6)'"
        />

        <CustomControls
          :aria-label="translate('COM_WORKFLOW_GRAPH_CONTROLS')"
        />

        <ControlsPanel
          v-if="workflow?.canCreate"
          class="canvas-controls-panel"
          @add-stage="addStage"
          @add-transition="addTransition"
        />
      </section>

      <!-- Workflow Content Sections -->
      <section
        id="workflow-stages"
        class="visually-hidden"
      >
        <span>({{ positionedNodes.length }} {{ positionedNodes.length === 1 ? translate('COM_WORKFLOW_GRAPH_STAGE') :  translate('COM_WORKFLOW_GRAPH_STAGES') }})</span>
        <ul>
          <li
            v-for="node in positionedNodes"
            :key="`stage-${node.id}`"
            :id="`stage-list-${node.id}`"
          >
            {{ sprintf('COM_WORKFLOW_GRAPH_STAGE_REF', node.data.stage?.title) }} - 
            {{ node.data.stage.published ? sprintf('COM_WORKFLOW_GRAPH_STAGE_STATUS_ENABLED', node.data.stage?.title) : sprintf('COM_WORKFLOW_GRAPH_STAGE_STATUS_DISABLED', node.data.stage?.title) }}.
            <span v-if="node.data.stage.default">({{ translate('COM_WORKFLOW_GRAPH_STAGE_DEFAULT') }})</span>
            {{ node.data.stage?.description ? sprintf('COM_WORKFLOW_GRAPH_STAGE_DESCRIPTION', node.data.stage?.description) : '' }}
          </li>
        </ul>
      </section>

      <section
        id="workflow-transitions"
        class="visually-hidden"
      >
        <span>({{ styledEdges.length }} {{ styledEdges.length === 1 ? translate('COM_WORKFLOW_GRAPH_TRANSITION') :  translate('COM_WORKFLOW_GRAPH_TRANSITIONS') }})</span>
        <ul>
          <li
            v-for="edge in styledEdges"
            :key="`transition-${edge.id}`"
            :id="`transition-list-${edge.id}`"
          >
            {{ sprintf('COM_WORKFLOW_GRAPH_TRANSITION_REF', edge.data.title, stages.find((s) => s.id === parseInt(edge.source, 10))?.title, stages.find((s) => s.id === parseInt(edge.target, 10))?.title) }}
            {{ edge.data.published ? sprintf('COM_WORKFLOW_GRAPH_TRANSITION_STATUS_ENABLED', edge.data.title) : sprintf('COM_WORKFLOW_GRAPH_TRANSITION_STATUS_DISABLED', edge.data.title) }}
          </li>
        </ul>
      </section>
    </VueFlow>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="d-flex justify-content-center align-items-center h-100"
      role="status"
      aria-live="polite"
    >
      <div class="spinner-border" role="status">
        <span class="visually-hidden">{{ translate('COM_WORKFLOW_GRAPH_LOADING') }}</span>
      </div>
      <span class="ms-2">{{ translate('COM_WORKFLOW_GRAPH_LOADING') }}</span>
    </div>

    <!-- Accessibility Live Region -->
    <div
      ref="liveRegion"
      aria-live="polite"
      role="status"
      class="visually-hidden"
      aria-atomic="true"
    />
  </div>
</template>

<script>
import {
  ref, computed, onMounted, onUnmounted, watch,
} from 'vue';
import { useStore } from 'vuex';
// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';
import { VueFlow, useVueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { MiniMap } from '@vue-flow/minimap';
import stageNode from '../nodes/StageNode.vue';
import CustomEdge from '../edges/CustomEdge.vue';
import CustomControls from './CustomControls.vue';
import ControlsPanel from './ControlsPanel.vue';
import { announce, setupDialogFocusHandlers } from '../../utils/focus-utils.es6.js';
import { generatePositionedNodes, createSpecialNode } from '../../utils/positioning.es6.js';
import { generateStyledEdges } from '../../utils/edges.es6.js';
import { setupGlobalShortcuts } from '../../utils/keyboard-manager.es6.js';
import { debounce } from '../../utils/utils.es6';
import AccessibilityFixer from '../../utils/accessibility-fixer.es6.js';
import notifications from '../../plugins/Notifications.es6.js';

export default {
  name: 'WorkflowCanvas',
  components: {
    VueFlow,
    Background,
    MiniMap,
    CustomControls,
    ControlsPanel,
  },
  props: {
    nodeTypes: {
      type: Object,
      default: () => ({ stage: stageNode }),
    },
    edgeTypes: {
      type: Object,
      default: () => ({ custom: CustomEdge }),
    },
    saveStatus: { type: Object, required: true },
    setSaveStatus: { type: Function, required: true },
  },
  setup(props, { emit }) {
    const store = useStore();
    const {
      fitView, zoomIn, zoomOut, viewport, setViewport, onViewportChange,
    } = useVueFlow();

    const isTransitionMode = ref(false);
    const selectedStage = ref(null);
    const selectedTransition = ref(null);
    const liveRegion = ref(null);
    const saveStatus = ref('upToDate');
    const currentFocusMode = ref('links');
    const previouslyFocusedElement = ref(null);
    const accessibilityFixer = ref(null);

    const showMiniMap = ref(true);
    const workflow = computed(() => store.getters.workflow || {});
    const stages = computed(() => store.getters.stages || []);
    const transitions = computed(() => store.getters.transitions || []);
    const loading = computed(() => store.getters.loading);
    const error = computed(() => store.getters.error);
    const workflowId = computed(() => store.getters.workflowId);

    function translate(key) {
      return Joomla.Text._(key);
    }

    function sprintf(key, ...args) {
      const base = Joomla.Text._(key);
      let i = 0;
      return base.replace(/%((%)|s|d)/g, (m) => {
        let val = args[i];

        if (m === '%d') {
          val = parseFloat(val);
          if (Number.isNaN(val)) {
            val = 0;
          }
        }
        i += 1;
        return val;
      });
    }

    function openModal(type, id = null, params = {}) {
      previouslyFocusedElement.value = document.activeElement;
      const extension = Joomla.getOptions('com_workflow', {})?.extension || '';
      const baseUrl = `index.php?option=com_workflow&view=${type}&workflow_id=${workflowId.value}&extension=${extension}&layout=modal&tmpl=component`;
      const baseUrlwithId = id ? `${baseUrl}&id=${id}` : baseUrl;
      const extraQuery = Object.entries(params)
        .map(([key, val]) => `${encodeURIComponent(key)}=${encodeURIComponent(val)}`)
        .join('&');
      const src = extraQuery
        ? `${baseUrlwithId}&${extraQuery}`
        : baseUrlwithId;

      const textHeader = id
        ? translate(`COM_WORKFLOW_GRAPH_EDIT_${type.toUpperCase()}`)
        : translate(`COM_WORKFLOW_GRAPH_ADD_${type.toUpperCase()}`);

      const dialog = new JoomlaDialog({
        popupType: 'iframe',
        textHeader,
        src,
      });

      const msgListener = (message) => {
        try {
          if (message.source === (dialog.getBodyContent().contentWindow || window)) {
            const type = message.data && message.data.messageType;
            if (type === 'joomla:content-select') {
              dialog.close();
            }
          }
        } catch (e) {
          // Ignore cross-origin errors
        }
      };
      window.addEventListener('message', msgListener);

      dialog.addEventListener('joomla-dialog:close', () => {
        window.removeEventListener('message', msgListener);
      });

      dialog.show();
      setupDialogFocusHandlers(previouslyFocusedElement, store);
    }

    function canEdit(id, type = 'stage') {
      if (type === 'stage') {
        const stage = stages.value.find((s) => s.id === parseInt(id, 10));
        return stage?.permissions?.edit;
      }
      if (type === 'transition') {
        const transition = transitions.value.find((t) => t.id === parseInt(id, 10));
        return transition?.permissions?.edit;
      }
      return false;
    }

    function canDelete(id, type = 'stage') {
      if (type === 'stage') {
        const stage = stages.value.find((s) => s.id === parseInt(id, 10));
        return stage?.permissions?.delete;
      }
      if (type === 'transition') {
        const transition = transitions.value.find((t) => t.id === parseInt(id, 10));
        return transition?.permissions?.delete;
      }
      return false;
    }

    function selectStage(id) {
      isTransitionMode.value = false;
      selectedStage.value = parseInt(id, 10);
      selectedTransition.value = null;
      announce(liveRegion.value, sprintf('COM_WORKFLOW_GRAPH_STAGE_SELECTED', stages?.value?.find(s => s.id === parseInt(id, 10))?.title || id));
    }

    function selectTransition(id) {
      isTransitionMode.value = true;
      selectedTransition.value = parseInt(id, 10);
      selectedStage.value = null;
      announce(liveRegion.value, sprintf('COM_WORKFLOW_GRAPH_TRANSITION_SELECTED', transitions?.value?.find(t => t.id === parseInt(id, 10))?.title || id));
    }

    function editStage(id) {
      if (!canEdit(id, 'stage')) {
        return;
      }
      openModal('stage', id);
    }

    function editTransition(id) {
      if (!canEdit(id, 'transition')) {
        return;
      }
      openModal('transition', id);
    }

    function clearSelection() {
      selectedStage.value = null;
      selectedTransition.value = null;
      isTransitionMode.value = false;
      announce(liveRegion.value, translate('COM_WORKFLOW_GRAPH_SELECTION_CLEARED'));
    }

    function deleteStage(id) {
      if (!canDelete(id, 'stage')) {
        return;
      }
      store.dispatch('deleteStage', { id, workflowId: workflowId.value });
      selectedStage.value = null;
    }

    function deleteTransition(id) {
      if (!canDelete(id, 'transition')) {
        return;
      }
      store.dispatch('deleteTransition', { id, workflowId: workflowId.value });
      selectedTransition.value = null;
    }

    function handleDeleteConfirm(type, id) {
      if (type === 'stage') deleteStage(id.toString());
      else deleteTransition(id.toString());
    }

    function showDeleteModal(type, id) {
      if ((!canDelete(id, 'stage') && type === 'stage') || (!canDelete(id, 'transition') && type === 'transition')) {
        return;
      }
      const title = translate(type === 'stage'
        ? 'COM_WORKFLOW_GRAPH_TRASH_STAGE'
        : 'COM_WORKFLOW_GRAPH_TRASH_TRANSITION');

      const message = translate(type === 'stage'
        ? sprintf('COM_WORKFLOW_GRAPH_TRASH_STAGE_CONFIRM', stages?.value?.find(s => s.id === parseInt(id, 10))?.title || id)
        : sprintf('COM_WORKFLOW_GRAPH_TRASH_TRANSITION_CONFIRM', transitions?.value?.find(t => t.id === parseInt(id, 10))?.title || id));

      JoomlaDialog.confirm(message, title).then((result) => {
        if (result) {
          handleDeleteConfirm(type, id);
        }
      });
    }

    function addStage() {
      if (!workflow?.value?.canCreate) {
        return;
      }
      openModal('stage');
      announce(liveRegion.value, translate('COM_WORKFLOW_GRAPH_ADD_STAGE_DIALOG_OPENED'));
    }

    function addTransition() {
      if (!workflow?.value?.canCreate) {
        return;
      }
      openModal('transition');
      announce(liveRegion.value, translate('COM_WORKFLOW_GRAPH_ADD_TRANSITION_DIALOG_OPENED'));
    }

    function handleConnect({ source, target }) {
      if (!workflow?.value?.canCreate) {
        return;
      }
      if (source && target) {
        openModal('transition', null, { from_stage_id: source, to_stage_id: target });
        announce(liveRegion.value, sprintf('COM_WORKFLOW_GRAPH_CREATING_TRANSITION', (stages?.value?.find(s => s.id === parseInt(source, 10))?.title || source), (stages?.value?.find(s => s.id === parseInt(target, 10))?.title || target)));
      }
    }

    function selectEdge({ edge }) {
      selectTransition(edge?.id);
    }

    function updateSaveMessage() {
      const el = document.getElementById('save-status');
      if (!el) return;
      if (saveStatus.value === 'unsaved') {
        el.classList.add('text-warning');
        el.textContent = translate('COM_WORKFLOW_GRAPH_UNSAVED_CHANGES');
      } else {
        el.classList.remove('text-warning');
        el.textContent = translate('COM_WORKFLOW_GRAPH_UP_TO_DATE');
      }
    }

    const saveNodePosition = debounce(async () => {
      const response = await store.dispatch('updateStagePositionAjax');
      if (response) {
        saveStatus.value = 'upToDate';
        updateSaveMessage();
        announce(liveRegion.value, translate('COM_WORKFLOW_GRAPH_STAGE_POSITIONS_UPDATED'));
      } else {
        notifications.error(translate('COM_WORKFLOW_GRAPH_ERROR_FAILED_TO_UPDATE_STAGE_POSITIONS'));
      }
    }, 3000);

    async function handleNodeDragStop({ node }) {
      if (!node || !node.id || node.id === 'from_any') return;
      const position = store.getters.stages.find((s) => s.id === parseInt(node.id, 10))?.position;
      const nodePosition = node.computedPosition || position || { x: 0, y: 0 };
      const { x, y } = nodePosition;
      saveStatus.value = 'unsaved';
      updateSaveMessage();
      await store.dispatch('updateStagePosition', { id: node.id, x, y });
      saveNodePosition();
    }

    const positionedNodes = computed(() => {
      const nodes = generatePositionedNodes(stages.value);
      const special = createSpecialNode('from_any', { x: 600, y: -200 }, 'purple', 'From Any', selectStage, false);
      return [...nodes.map((n) => ({
        ...n,
        data: {
          ...n.data,
          isSelected: selectedStage.value === parseInt(n.id, 10),
          onSelect: () => selectStage(n.id),
          onEscape: () => clearSelection(),
          onEdit: () => editStage(n.id),
          onDelete: () => showDeleteModal('stage', n.id),
        },
      })), special];
    });

    const styledEdges = computed(() => generateStyledEdges(transitions.value, {
      selectedId: selectedTransition.value,
    }).map((edge) => ({
      ...edge,
      data: {
        ...edge.data,
        onSelect: () => selectTransition(edge.id),
        onEscape: () => clearSelection(),
        onDelete: () => showDeleteModal('transition', edge.id),
        onEdit: () => editTransition(edge.id),
      },
    })));
    onMounted(() => {
      // Initialize accessibility fixer
      accessibilityFixer.value = new AccessibilityFixer();
      accessibilityFixer.value.init();

      const detach = setupGlobalShortcuts({
        addStage,
        addTransition,
        editItem: () => {
          if (selectedStage.value) editStage(selectedStage.value);
          else if (selectedTransition.value) editTransition(selectedTransition.value);
        },
        deleteItem: () => {
          if (selectedStage.value) showDeleteModal('stage', selectedStage.value);
          else if (selectedTransition.value) showDeleteModal('transition', selectedTransition.value);
        },
        clearSelection,
        zoomIn,
        zoomOut,
        fitView,
        viewport,
        state: {
          selectedStage,
          selectedTransition,
          isTransitionMode,
          currentFocusMode,
          liveRegion: liveRegion.value,
        },
        setSaveStatus: (val) => { saveStatus.value = val; },
        updateSaveMessage,
        saveNodePosition,
        store,
      });
      onUnmounted(() => {
        detach();
        if (accessibilityFixer.value) {
          accessibilityFixer.value.destroy();
        }
      });
    });

    let isRestoringViewport = false;
    watch([loading, error], () => {
      setTimeout(() => {
        if (loading.value || error.value) return;
        const { panX, panY, zoom } = store.getters.canvas ?? {};

        if (
          typeof panX === 'number' && !Number.isNaN(panX)
          && typeof panY === 'number' && !Number.isNaN(panY)
          && typeof zoom === 'number' && !Number.isNaN(zoom)
        ) {
          isRestoringViewport = true;
          Promise.resolve()
            .then(() => setViewport({ x: panX, y: panY, zoom }))
            .finally(() => {
              isRestoringViewport = false;
            });
        } else {
          fitView({ padding: 0.4, duration: 300 });
        }
      }, 0);
    }, { immediate: true });

    onViewportChange(({ x, y, zoom }) => {
      if (isRestoringViewport) {
        return;
      }

      if ([x, y, zoom].some((v) => typeof v !== 'number' || Number.isNaN(v))) {
        return;
      }
      store.dispatch('updateCanvasViewport', { panX: x, panY: y, zoom });
    });

    return {
      loading,
      error,
      showMiniMap,
      workflow,
      stages,
      positionedNodes,
      styledEdges,
      liveRegion,
      handleConnect,
      selectEdge,
      handleDeleteConfirm,
      addStage,
      addTransition,
      clearSelection,
      handleNodeDragStop,
    };
  },
};
</script>
