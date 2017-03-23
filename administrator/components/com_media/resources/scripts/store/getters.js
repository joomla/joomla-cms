// Sometimes we may need to compute derived state based on store state,
// for example filtering through a list of items and counting them.

/**
 * Get the currently selected directory
 * @param state
 * @returns {*}
 */
export const getSelectedDirectory = (state) => {
    return state.directories.find(directory => (directory.path === state.selectedDirectory));
}

/**
 * Get the sudirectories of the currently selected directory
 * @param state
 * @param getters
 * @returns {Array|directories|{/}|computed.directories|*|Object}
 */
export const getSelectedDirectoryDirectories = (state, getters) => {
    return getters.getSelectedDirectory.directories
        .map(directoryPath => state.directories.find(directory => (directory.path === directoryPath)));
}

/**
 * Get the files of the currently selected directory
 * @param state
 * @param getters
 * @returns {Array|files|{}|FileList|*}
 */
export const getSelectedDirectoryFiles= (state, getters) => {
    return getters.getSelectedDirectory.files
        .map(filePath => state.files.find(file => (file.path === filePath)));
}