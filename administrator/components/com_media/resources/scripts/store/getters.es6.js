// Sometimes we may need to compute derived state based on store state,
// for example filtering through a list of items and counting them.
/**
 * Get the currently selected directory
 * @param state
 * @returns {*}
 */
export const getSelectedDirectory = (state) => state.directories
  .find((directory) => (directory.path === state.selectedDirectory));

/**
 * Get the sudirectories of the currently selected directory
 * @param state
 *
 * @returns {Array|directories|{/}|computed.directories|*|Object}
 */
export const getSelectedDirectoryDirectories = (state) => state.directories
  .filter((directory) => (directory.directory === state.selectedDirectory));

/**
 * Get the files of the currently selected directory
 * @param state
 *
 * @returns {Array|files|{}|FileList|*}
 */
export const getSelectedDirectoryFiles = (state) => state.files
  .filter((file) => (file.directory === state.selectedDirectory));

/**
 * Whether or not all items of the current directory are selected
 * @param state
 * @param getters
 * @returns Array
 */
export const getSelectedDirectoryContents = (state, getters) => [
  ...getters.getSelectedDirectoryDirectories,
  ...getters.getSelectedDirectoryFiles,
];
