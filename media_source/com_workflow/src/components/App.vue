<template>
  <main
    id="workflow-app"
    class="d-flex flex-column flex-grow-1 min-vh-80"
    aria-labelledby="workflow-main-title"
  >
    <header
      id="workflow-header"
      class="d-flex flex-column flex-shrink-0"
    >
      <WorkflowTitlebar
        :save-status="saveStatus"
      />
    </header>

    <div class="d-flex flex-grow-1 overflow-hidden">
      <section
        id="main-canvas"
        class="flex-grow-1 position-relative"
        aria-describedby="canvas-description"
      >
        <div
          id="canvas-description"
          class="visually-hidden"
        >
          {{ translate('COM_WORKFLOW_GRAPH_CANVAS_DESCRIPTION') }}
        </div>

        <WorkflowCanvas
          ref="canvas"
          :save-status="saveStatus"
          :set-save-status="setSaveStatus"
          @focus-request="handleCanvasFocus"
        />
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import WorkflowTitlebar from './Titlebar.vue';
import WorkflowCanvas from './canvas/WorkflowCanvas.vue';

const store = useStore();
const saveStatus = ref('upToDate');
function setSaveStatus(val) {
  saveStatus.value = val;
}
const canvas = ref(null);

function handleCanvasFocus() {
  canvas.value?.focus();
}

onMounted(() => {
  const { workflowId: idFromOpts = null } = Joomla.getOptions('com_workflow', {});
  const idFromURL = parseInt(new URL(window.location.href).searchParams.get('id'), 10);
  const currentWorkflowId = idFromOpts || idFromURL;

  if (currentWorkflowId !== null && !Number.isNaN(currentWorkflowId)) {
    store.dispatch('loadWorkflow', currentWorkflowId);
  } else {
    throw new Error('COM_WORKFLOW_GRAPH_ERROR_INVALID_ID');
  }
});
</script>

<script>
export default {
  name: 'WorkflowGraphApp',
};
</script>
