<template>
  <div
    ref="controlsContainer"
    class="custom-controls position-absolute z-10 p-1"
    role="list"
    aria-labelledby="canvas-controls-title"
  >
    <span id="canvas-controls-title" class="visually-hidden">{{ translate('COM_WORKFLOW_GRAPH_CANVAS_VIEW_CONTROLS') }}</span>

    <ul class="d-flex flex-column gap-1 list-unstyled mb-0 px-0">
      <li>
        <button
          class="toolbar-button custom-controls-button"
          :title="translate('COM_WORKFLOW_GRAPH_ZOOM_IN')"
          @click="zoomIn"
          @keydown.enter="zoomIn"
          @keydown.space.prevent="zoomIn"
        >
          <span class="icon icon-plus" aria-hidden="true" />
          <span class="visually-hidden">{{ translate('COM_WORKFLOW_GRAPH_ZOOM_IN') }}</span>
        </button>
      </li>
      <li>
        <button
          class="toolbar-button custom-controls-button"
          :title="translate('COM_WORKFLOW_GRAPH_ZOOM_OUT')"
          @click="zoomOut"
          @keydown.enter="zoomOut"
          @keydown.space.prevent="zoomOut"
        >
          <span class="icon icon-minus" aria-hidden="true" />
          <span class="visually-hidden">{{ translate('COM_WORKFLOW_GRAPH_ZOOM_OUT') }}</span>
        </button>
      </li>
      <li>
        <button
          class="toolbar-button custom-controls-button"
          :title="translate('COM_WORKFLOW_GRAPH_FIT_VIEW')"
          @click="customFitView"
          @keydown.enter="customFitView"
          @keydown.space.prevent="customFitView"
        >
          <span class="icon icon-expand" aria-hidden="true" />
          <span class="visually-hidden">{{ translate('COM_WORKFLOW_GRAPH_FIT_VIEW') }}</span>
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useVueFlow } from '@vue-flow/core';

export default {
  name: 'CustomControls',
  setup() {
    const { zoomIn, zoomOut, fitView } = useVueFlow();
    function customFitView() {
      fitView({
        padding: 0.5,
        duration: 300,
      });
    }
    const controlsContainer = ref(null);

    return {
      zoomIn,
      zoomOut,
      controlsContainer,
      customFitView,
    };
  },
};
</script>
