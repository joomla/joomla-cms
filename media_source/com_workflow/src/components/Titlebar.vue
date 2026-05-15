<template>
  <section
    class="d-flex flex-wrap align-items-center justify-content-between"
    aria-labelledby="workflow-main-title"
  >
    <div class="col-md-6 d-flex flex-column">
      <h1
        id="workflow-main-title"
        class="mb-2"
      >
        {{ translate(workflow?.title) }}
      </h1>
      <dl
        class="d-flex align-items-center flex-wrap mb-0"
        aria-label="Workflow Details"
      >
        <dt class="visually-hidden">
          {{ translate('COM_WORKFLOW_GRAPH_STATUS') }}
        </dt>
        <dd class="me-3 mb-1 d-flex mb-0">
          <span
            class="badge"
            :class="workflow.published ? 'bg-success' : 'bg-warning'"
            role="status"
            :aria-label="`${workflow.published ? translate('COM_WORKFLOW_GRAPH_ENABLED') : translate('COM_WORKFLOW_GRAPH_DISABLED')}`"
          >
            {{ workflow.published ? translate('COM_WORKFLOW_GRAPH_ENABLED') : translate('COM_WORKFLOW_GRAPH_DISABLED') }}
          </span>
        </dd>

        <dt class="visually-hidden">
          {{ translate('COM_WORKFLOW_GRAPH_STAGE_COUNT') }}
        </dt>
        <dd class="me-3 mb-1 d-flex mb-0">
          <span>
            {{ stagesCount }} {{ stagesCount === 1 ? translate('COM_WORKFLOW_GRAPH_STAGE') : translate('COM_WORKFLOW_GRAPH_STAGES') }}
          </span>
        </dd>

        <dt class="visually-hidden">
          {{ translate('COM_WORKFLOW_GRAPH_TRANSITION_COUNT') }}
        </dt>
        <dd class="me-3 mb-1 d-flex mb-0">
          <span>
            {{ transitionsCount }} {{ transitionsCount === 1 ? translate('COM_WORKFLOW_GRAPH_TRANSITION')
            : translate('COM_WORKFLOW_GRAPH_TRANSITIONS') }}
          </span>
        </dd>
      </dl>
    </div>

    <div
      id="save-status"
      class="mb-2 fw-bold"
      role="status"
      aria-live="polite"
      :class="{
        'text-warning': saveStatus?.value === 'unsaved',
      }"
      :aria-label="translate(saveStatus?.value === 'unsaved' ? 'COM_WORKFLOW_GRAPH_UNSAVED_CHANGES' : 'COM_WORKFLOW_GRAPH_UP_TO_DATE')"
    >
      {{
        saveStatus.value === 'unsaved'
          ? translate('COM_WORKFLOW_GRAPH_UNSAVED_CHANGES')
          : translate('COM_WORKFLOW_GRAPH_UP_TO_DATE')
      }}
    </div>
  </section>
</template>

<script>
export default {
  name: 'WorkflowTitlebar',
  props: {
    saveStatus: {
      type: String,
      default: 'upToDate',
    },
  },
  computed: {
    workflow() {
      return this.$store.getters.workflow || { title: 'COM_WORKFLOW_GRAPH_LOADING', published: false };
    },
    stagesCount() {
      return this.$store.getters.stages?.length || 0;
    },
    transitionsCount() {
      return this.$store.getters.transitions?.length || 0;
    },
  },
};
</script>
