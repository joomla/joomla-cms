<template>
  <div
    ref="controlsContainer"
    class="custom-controls z-10"
    role="group"
    aria-labelledby="canvas-controls-title"
  >
    <h2 id="canvas-controls-title" class="visually-hidden">Canvas View Controls</h2>

    <ul class="d-flex flex-column gap-1 list-unstyled mb-0" role="group">
      <li>
        <button
          class="toolbar-button custom-controls-button"
          tabindex="0"
          type="button"
          aria-label="Zoom in"
          title="Zoom in (+ key)"
          @click="zoomIn"
          @keydown.enter="zoomIn"
          @keydown.space.prevent="zoomIn"
        >
          <span class="icon icon-plus" aria-hidden="true" />
          <span class="visually-hidden">Zoom In</span>
        </button>
      </li>
      <li>
        <button
          class="toolbar-button custom-controls-button"
          tabindex="0"
          type="button"
          aria-label="Zoom out"
          title="Zoom out (- key)"
          @click="zoomOut"
          @keydown.enter="zoomOut"
          @keydown.space.prevent="zoomOut"
        >
          <span class="icon icon-minus" aria-hidden="true" />
          <span class="visually-hidden">Zoom Out</span>
        </button>
      </li>
      <li>
        <button
          class="toolbar-button custom-controls-button"
          tabindex="0"
          type="button"
          aria-label="Fit view"
          title="Fit view (F key)"
          @click="customFitView"
          @keydown.enter="customFitView"
          @keydown.space.prevent="customFitView"
        >
          <span class="icon icon-expand" aria-hidden="true" />
          <span class="visually-hidden">Fit View</span>
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
</script>clear
