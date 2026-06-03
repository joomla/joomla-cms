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
   * @param id - The ID of the workflow
   * @returns {Promise<{workflow: Object, stages: Array, transitions: Array}>}
   */
  async loadWorkflow({ commit }, id) {
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
    } catch (error) {
      notifications.error(error?.response?.data?.message || error?.message || 'COM_WORKFLOW_GRAPH_ERROR_UNKNOWN');
    } finally {
      commit('SET_LOADING', false);
    }
  },

  /**
   * Delete a stage from the workflow.
   * @param commit 
   * @param dispatch
   * @param state
   * @param id - The ID of the stage to delete
   * @param workflowId - The ID of the workflow
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
        notifications.error('COM_WORKFLOW_GRAPH_ERROR_STAGE_DEFAULT_CANT_DELETED');
        return;
      }

      if (transitions.length > 0) {
        notifications.error('COM_WORKFLOW_GRAPH_ERROR_STAGE_HAS_TRANSITIONS');
        return;
      }

      const stageDelete = state.stages.find(
        (s) => s.id.toString() === id,
      ).published === -1;

      await workflowGraphApi.deleteStage(id, workflowId, stageDelete);
    } catch (error) {
      notifications.error(error?.response?.data?.message || error?.message || 'COM_WORKFLOW_GRAPH_TRASH_STAGE_FAILED');
    } finally {
      commit('SET_LOADING', false);
      await dispatch('loadWorkflow', workflowId);
    }
  },

  /**
   * Delete a transition from the workflow.
   * @param commit
   * @param dispatch
   * @param state
   * @param id - The ID of the transition to delete
   * @param workflowId - The ID of the workflow
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
      notifications.error(error?.response?.data?.message || error?.message || 'COM_WORKFLOW_GRAPH_TRASH_TRANSITION_FAILED');
    } finally {
      commit('SET_LOADING', false);
      await dispatch('loadWorkflow', workflowId);
    }
  },

  /**
   * Update the position of a stage in the workflow locally.
   * @param commit
   * @param id - The ID of the stage
   * @param x - The new x position of the stage
   * @param y - The new y position of the stage
   */
  updateStagePosition({ commit }, { id, x, y }) {
    commit('UPDATE_STAGE_POSITION', { id, x, y });
  },


  /**
   * Update the position of a stage in the workflow via API in database.
   * @param commit
   * @param state
   * @returns {Promise<boolean>}
   */
  async updateStagePositionAjax({ commit, state }) {
    const response = await workflowGraphApi.updateStagePosition(
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

    notifications.error('COM_WORKFLOW_GRAPH_UPDATE_STAGE_POSITION_FAILED');
    return false;
  },

  /**
   * Update the canvas viewport (zoom and pan) for the workflow graph.
   * @param commit
   * @param zoom - The zoom level
   * @param panX - The pan offset on the X axis
   * @param panY - The pan offset on the Y axis
   */
  updateCanvasViewport({ commit }, { zoom, panX, panY }) {
    commit('SET_CANVAS_VIEWPORT', { zoom, panX, panY });
  },
};
