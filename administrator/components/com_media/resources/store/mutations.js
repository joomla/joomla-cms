import * as types from "./mutation-types";

// The only way to actually change state in a store is by committing a mutation.
// Mutations are very similar to events: each mutation has a string type and a handler.
// The handler function is where we perform actual state modifications, and it will receive the state as the first argument.

export default {

    /**
     * The load content success mutation
     * @param state
     * @param payload
     */
    [types.SELECT_DIRECTORY]: (state, payload) => {
        state.selectedDirectory = payload;
    },

    /**
     * The load content success mutation
     * @param state
     * @param payload
     */
    [types.LOAD_CONTENTS_SUCCESS]: (state, payload) => {
        const newDirectories = payload.directories
            .filter(directory => (!state.directories.some(existing => (existing.path === directory.path))));
        const newFiles = payload.files
            .filter(file => (!state.files.some(existing => (existing.path === file.path))));

        // Merge the directories
        if (newDirectories.length > 0) {
            const newDirectoryIds = newDirectories.map(directory => directory.path);
            const parentDirectory = state.directories.find((directory) => (directory.path === newDirectories[0].directory));
            const parentDirectoryIndex = state.directories.indexOf(parentDirectory);

            // Add the new directories to the directories
            state.directories.push(...newDirectories);

            // Update the relation to the parent directory
            state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
                directories: [...parentDirectory.directories, ...newDirectoryIds]
            }));
        }

        // Merge the files
        if (newFiles.length > 0) {
            const newFileIds = newFiles.map(file => file.path);
            const parentDirectory = state.directories.find((directory) => (directory.path === newFiles[0].directory));
            const parentDirectoryIndex = state.directories.indexOf(parentDirectory);

            // Add the new files to the files
            state.files.push(...newFiles);

            // Update the relation to the parent directory
            state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
                files: [...parentDirectory.files, ...newFileIds]
            }));
        }
    }
}
