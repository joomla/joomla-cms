import workflowGraphApi from '../app/WorkflowGraphApi.es6.js';
import notifications from '../plugins/Notifications.es6';

/**
 * Vuex Actions for asynchronous operations and workflows
 * Handles logic and commits to mutations
 */
export default {
  /**
   * Load a workflow by its ID, including stages and transitions.
   * @param commit
   * @param dispatch
   * @param id
   * @returns {Promise<{workflow: Object, stages: Array, transitions: Array}>}
   */
  async loadWorkflow({ commit, dispatch }, id) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      // Load workflow, stages, and transitions in parallel
      const [workflowRes, stagesRes, transitionsRes] = await Promise.all([
        await workflowGraphApi.getWorkflow(id),
        await workflowGraphApi.getStages(id),
        await workflowGraphApi.getTransitions(id),
      ]);

      commit('SET_WORKFLOW_ID', id);
      commit('SET_WORKFLOW', workflowRes?.data);
      commit('SET_STAGES', stagesRes?.data);
      commit('SET_TRANSITIONS', transitionsRes?.data);

      dispatch('saveToHistory');
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || error.message || 'UNEXPECTED_ERROR');
    } finally {
      commit('SET_LOADING', false);
    }
  },

  /**
   * Delete a stage from the workflow.
   * @param commit
   * @param dispatch
   * @param state
   * @param id
   * @param workflowId
   * @returns {Promise<void>}
   */
  async deleteStage({ commit, dispatch, state }, { id, workflowId }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const transitions = state.transitions.filter(
        (t) => t.from_stage_id.toString() === id || t.to_stage_id.toString() === id,
      );

      if (
        state.stages.length <= 1
          || state.stages.find((s) => s.id.toString() === id).default
      ) {
        const errorMessage = 'COM_WORKFLOW_ERROR_STAGE_DEFAULT_CANT_DELETED';
        commit('SET_ERROR', errorMessage);
        notifications.error(errorMessage);
        return;
      }

      if (transitions.length > 0) {
        const errorMessage = 'COM_WORKFLOW_ERROR_STAGE_HAS_TRANSITIONS';
        commit('SET_ERROR', errorMessage);
        if (window.Joomla && window.Joomla.renderMessages) {
          window.Joomla.renderMessages({ error: [errorMessage] });
        }
        return;
      }

      const stageDelete = state.stages.find(
        (s) => s.id.toString() === id,
      ).published === -1;

      await workflowGraphApi.deleteStage(id, workflowId, stageDelete);
    } catch (error) {
      const errorMessage = error.message;
      commit('SET_ERROR', errorMessage);

      if (window.Joomla && window.Joomla.renderMessages) {
        window.Joomla.renderMessages({
          error: [errorMessage],
        });
      }
    } finally {
      commit('SET_LOADING', false);
      await dispatch('loadWorkflow', workflowId);
    }
  },

  /**
   * Delete a transition from the workflow.
   * @param commit
   * @param dispatch
   * @param id
   * @param workflowId
   * @param transitionDelete
   * @returns {Promise<void>}
   */
  async deleteTransition({ commit, dispatch, state }, { id, workflowId }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    try {
      const transitionDelete = state.transitions.find(
        (t) => t.id.toString() === id,
      ).published === -1;
      await workflowGraphApi.deleteTransition(id, workflowId, transitionDelete);
    } catch (error) {
      const errorMessage = error.message || 'COM_WORKFLOW_GRAPH_DELETE_TRANSITION_FAILED';
      commit('SET_ERROR', errorMessage);

      if (window.Joomla && window.Joomla.renderMessages) {
        window.Joomla.renderMessages({
          error: [errorMessage],
        });
      }
    } finally {
      commit('SET_LOADING', false);
      await dispatch('loadWorkflow', workflowId);
    }
  },

  /**
   * Update the position of a stage in the workflow locally.
   * @param commit
   * @param dispatch
   * @param id
   * @param x
   * @param y
   */
  updateStagePosition({ commit, dispatch }, { id, x, y }) {
    commit('UPDATE_STAGE_POSITION', { id, x, y });
    dispatch('saveToHistory');
  },

  updateStagePositionAjax({ commit, state }) {
    const response = workflowGraphApi.updateStagePosition(
      state.workflowId,
      state.stages.reduce((acc, stage) => {
        if (stage.position) {
          acc[stage.id] = {
            x: stage.position.x,
            y: stage.position.y,
          };
        }
        return acc;
      }, {}),
    );

    if (response) {
      commit('SET_ERROR', null);
      return true;
    }

    commit('SET_ERROR', 'COM_WORKFLOW_GRAPH_UPDATE_STAGE_POSITION_FAILED');
    return false;
  },

  /**
   * Update the canvas viewport (zoom and pan) for the workflow graph.
   * @param commit
   * @param zoom
   * @param panX
   * @param panY
   */
  updateCanvasViewport({ commit }, { zoom, panX, panY }) {
    commit('SET_CANVAS_VIEWPORT', { zoom, panX, panY });
  },

  /**
   * Save the current state of the workflow to history.
   * @param commit
   * @param state
   * @returns {Promise<void>}
   */
  saveToHistory({ commit, state }) {
    const snapshot = {
      stagePositions: state.stages.map((stage) => ({
        id: stage.id,
        position: stage.position,
      })),
    };
    commit('ADD_TO_HISTORY', snapshot);
  },

  /**
   * Undo the last action in the workflow.
   * @param commit
   * @returns {Promise<void>}
   */
  undo({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    commit('UNDO_REDO', -1);
    commit('SET_LOADING', false);
  },

  /**
   * Redo the last undone action in the workflow.
   * @param commit
   * @returns {Promise<void>}
   */
  redo({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);
    commit('UNDO_REDO', 1);
    commit('SET_LOADING', false);
  },
};
