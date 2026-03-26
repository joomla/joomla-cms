<template>
  <Panel
    :position="panelPosition"
    role="list"
    aria-labelledby="controls-panel-title"
    class="d-flex gap-2 p-2"
  >
    <span id="controls-panel-title" class="visually-hidden">{{ translate('COM_WORKFLOW_GRAPH_CONTROLS') }}</span>

    <button
      class="toolbar-button btn btn-primary d-flex align-items-center gap-1"
      :aria-label="translate('COM_WORKFLOW_GRAPH_ADD_STAGE')"
      :title="`${translate('COM_WORKFLOW_GRAPH_ADD_STAGE')} (Alt+N)`"
      @click="$emit('add-stage')"
      @keydown.enter.prevent="$emit('add-stage')"
      @keydown.space.prevent="$emit('add-stage')"
    >
      <span
        class="icon icon-plus"
        aria-hidden="true"
      />
      {{ translate('COM_WORKFLOW_GRAPH_ADD_STAGE') }}
    </button>

    <button
      class="toolbar-button btn btn-info d-flex align-items-center gap-1"
      :aria-label="translate('COM_WORKFLOW_GRAPH_ADD_TRANSITION')"
      :title="`${translate('COM_WORKFLOW_GRAPH_ADD_TRANSITION')} (Alt+M)`"
      @click="$emit('add-transition')"
      @keydown.enter.prevent="$emit('add-transition')"
      @keydown.space.prevent="$emit('add-transition')"
    >
      <span
        class="icon icon-plus"
        aria-hidden="true"
      />
      {{ translate('COM_WORKFLOW_GRAPH_ADD_TRANSITION') }}
    </button>
  </Panel>
</template>

<script>
import { Panel } from '@vue-flow/core';
import { isRTL } from '../../utils/utils.es6';

export default {
  name: 'ControlsPanel',
  components: { Panel },
  emits: ['add-stage', 'add-transition'],
  computed: {
    panelPosition() {
      return isRTL() ? 'top-right' : 'top-left';
    },
  },
};
</script>
