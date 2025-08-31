/**
 * Vuex Getters for accessing state in components
 * Provides reusable computed-like access to store data
 */
export default {
  workflowId: (state) => state.workflowId,
  workflow: (state) => state.workflow,
  stages: (state) => state.stages,
  transitions: (state) => state.transitions,
  loading: (state) => state.loading,
  error: (state) => state.error,
  canUndo: (state) => state.historyIndex > 0,
  canRedo: (state) => state.historyIndex < state.history.length - 1,
  canvas: (state) => state.canvas,
};
