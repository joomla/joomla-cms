import { createStore } from 'vuex';
import state from './state.es6.js';
import mutations from './mutations.es6.js';
import actions from './actions.es6.js';
import getters from './getters.es6.js';
import createPersistedState from './plugins/persisted-state.es6';

/**
 * Vuex Store for Workflow Graph
 * Handles state, mutations, actions, getters, and persistence of workflow graph data
 */
export default createStore({
  state,
  mutations,
  actions,
  getters,
  plugins: [
    createPersistedState({
      key: 'workflow-graph-state',
      paths: ['workflowId', 'stages', 'transitions'],
    }),
  ],
});
