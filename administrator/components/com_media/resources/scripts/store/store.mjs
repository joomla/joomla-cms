import { createStore } from 'vuex';
import VuexPersistence from 'vuex-persist';
import state from './state.mjs';
import * as getters from './getters.mjs';
import * as actions from './actions.mjs';
import mutations from './mutations.mjs';
import persistedStateOptions from './plugins/persisted-state.mjs';
// A Vuex instance is created by combining the state, mutations, actions, and getters.
export default createStore({
  state,
  getters,
  actions,
  mutations,
  plugins: [new VuexPersistence(persistedStateOptions).plugin],
  strict: (process.env.NODE_ENV !== 'production'),
});
