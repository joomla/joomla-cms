import { createStore } from 'vuex';
import VuexPersistence from 'vuex-persist';
import state from './state.es6';
import * as getters from './getters.es6';
import * as actions from './actions.es6';
import mutations from './mutations.es6';
import persistedStateOptions from './plugins/persisted-state.es6.js';
// A Vuex instance is created by combining the state, mutations, actions, and getters.
export default createStore({
  state,
  getters,
  actions,
  mutations,
  plugins: [new VuexPersistence(persistedStateOptions).plugin],
  strict: (process.env.NODE_ENV !== 'production'),
});
