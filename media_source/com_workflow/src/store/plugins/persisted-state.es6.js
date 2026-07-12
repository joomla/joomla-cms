/**
 * Vuex plugin for persisting selected store data to localStorage
 * Typically used for preserving UI state across reloads
 */
export default function createPersistedState({ key = 'vuex', paths = [] } = {}) {
  return (store) => {
    try {
      const stored = localStorage.getItem(key);
      if (stored) {
        const parsed = JSON.parse(stored);
        paths.forEach((path) => {
          if (parsed[path] !== undefined) {
            store.state[path] = parsed[path];
          }
        });
      }

      store.subscribe((mutation, state) => {
        const partial = {};
        paths.forEach((path) => {
          partial[path] = state[path];
        });
        localStorage.setItem(key, JSON.stringify(partial));
      });
    } catch (err) {
      if (window.Joomla && window.Joomla.renderMessages) {
        window.Joomla.renderMessages({
          error: [err],
        });
      }
    }
  };
}
