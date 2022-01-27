// The options for persisting state
// eslint-disable-next-line import/prefer-default-export
export const persistedStateOptions = {
  key: 'joomla.mediamanager',
  paths: [
    'selectedDirectory',
    'showInfoBar',
    'listView',
    'gridSize',
    'search',
  ],
  storage: window.sessionStorage,
};
