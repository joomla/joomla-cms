// Sometimes we may need to compute derived state based on store state,
// for example filtering through a list of items and counting them.

/**
 * Get the currently selected directory
 * @param state
 * @returns {*}
 */
export const getSelectedDirectory = (state) => {
    return state.directories.find(directory => (directory.path.startsWith(state.selectedDirectory)));
}

/**
 * Get the sudirectories of the currently selected directory
 * @param state
 * @param getters
 * @returns {Array|directories|{/}|computed.directories|*|Object}
 */
export const getSelectedDirectoryDirectories = (state, getters) => {
    return state.directories.filter(
        directory => (directory.directory && directory.directory.startsWith(state.selectedDirectory))
    );
}

/**
 * Get the files of the currently selected directory
 * @param state
 * @param getters
 * @returns {Array|files|{}|FileList|*}
 */
export const getSelectedDirectoryFiles = (state, getters) => {
    return state.files.filter(
        file => (file.directory && file.directory.startsWith(state.selectedDirectory))
    );
}
