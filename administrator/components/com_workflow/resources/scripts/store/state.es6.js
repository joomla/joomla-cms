/**
 * Reactive base state for the workflow graph
 * Includes workflow ID, stages, transitions, history, and canvas viewport
 */
export default {
  workflowId: null,
  workflow: null,
  stages: [],
  transitions: [],
  loading: false,
  error: null,
  history: [],
  historyIndex: -1,
  canvas: {
    zoom: null,
    panX: null,
    panY: null,
  },
};
