import Vue from 'vue/dist/vue.esm.browser.min.js';
import Vuex from 'vuex/dist/vuex.esm.browser.min.js';
import createPersistedState from 'vuex-persistedstate/dist/vuex-persistedstate.es.js';
import state from './state.es6';
import * as getters from './getters.es6';
import * as actions from './actions.es6';
import mutations from './mutations.es6';
import { persistedStateOptions } from './plugins/persisted-state.es6';

Vue.use(Vuex);

// A Vuex instance is created by combining the state, mutations, actions, and getters.
export default new Vuex.Store({
  state,
  getters,
  actions,
  mutations,
  plugins: [createPersistedState(persistedStateOptions)],
  strict: (process.env.NODE_ENV !== 'production'),
});
