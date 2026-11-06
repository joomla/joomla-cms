/**
 * Reactive base state for the workflow graph
 * Includes workflow ID, workflow, stages, loading, transitions, history, and canvas viewport
 */
export default {
  workflowId: null,
  workflow: null,
  stages: [],
  transitions: [],
  loading: false,
  error: null,
  canvas: {
    zoom: null,
    panX: null,
    panY: null,
  },
};
