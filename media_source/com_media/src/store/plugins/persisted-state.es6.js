// The options for persisting state
const persistedStateOptions = {
  storage: window.sessionStorage,
  key: 'joomla.mediamanager',
  reducer: (state) => ({
    selectedDirectory: state.selectedDirectory,
    showInfoBar: state.showInfoBar,
    listView: state.listView,
    gridSize: state.gridSize,
    search: state.search,
    sortBy: state.sortBy,
    sortDirection: state.sortDirection,
  }),
};
export default persistedStateOptions;
