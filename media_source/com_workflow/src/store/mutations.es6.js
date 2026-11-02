/**
 * Vuex Mutations for synchronously modifying workflow state
 */
export default {
  SET_WORKFLOW_ID(state, id) {
    state.workflowId = id;
  },
  SET_WORKFLOW(state, workflow) {
    state.workflow = workflow;
  },
  SET_STAGES(state, stages) {
    state.stages = stages.map((stage, idx) => ({
      ...stage,
      position: {
        x: typeof stage?.position?.x === 'number' && !Number.isNaN(stage.position.x)
          ? stage.position.x
          : 100 + (idx % 4) * 400,
        y: typeof stage?.position?.y === 'number' && !Number.isNaN(stage.position.y)
          ? stage.position.y
          : 100 + Math.floor(idx / 4) * 300,
      },
    }));
  },
  SET_TRANSITIONS(state, transitions) {
    state.transitions = transitions;
  },
  SET_LOADING(state, loading) {
    state.loading = loading;
  },
  SET_ERROR(state, error) {
    state.error = error;
  },
  UPDATE_STAGE_POSITION(state, { id, x, y }) {
    state.stages = state.stages.map((stage) => {
      if (stage.id.toString() === id) {
        return {
          ...stage,
          position: {
            x,
            y,
          },
        };
      }
      return stage;
    });
  },
  SET_CANVAS_VIEWPORT(state, { zoom, panX, panY }) {
    state.canvas.zoom = zoom;
    state.canvas.panX = panX;
    state.canvas.panY = panY;
  },
};
