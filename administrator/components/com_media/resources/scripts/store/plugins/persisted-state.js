// The options for persisting state
export const persistedStateOptions = {
    key: 'joomla.mediamanager',
    paths: [
        'selectedDirectory',
        'showInfoBar',
        'listView',
        'gridSize',
    ],
    storage: window.sessionStorage,
};