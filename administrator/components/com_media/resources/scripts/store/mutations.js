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

            // Add the new directories to the directories array
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

            // Add the new files to the files array
            state.files.push(...newFiles);

            // Update the relation to the parent directory
            state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
                files: [...parentDirectory.files, ...newFileIds]
            }));
        }
    },

    /**
     * The upload success mutation
     * @param state
     * @param payload
     */
    [types.UPLOAD_SUCCESS]: (state, payload) => {
        const file = payload;
        const isNew = (!state.files.some(existing => (existing.path === file.path)));

        // TODO handle file_exists
        if (isNew) {
            const parentDirectory = state.directories.find((existing) => (existing.path === file.directory));
            const parentDirectoryIndex = state.directories.indexOf(parentDirectory);

            // Add the new file to the files array
            state.files.push(file);

            // Update the relation to the parent directory
            state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
                files: [...parentDirectory.files, file.path]
            }));
        }
    },

    /**
     * The create directory success mutation
     * @param state
     * @param payload
     */
    [types.CREATE_DIRECTORY_SUCCESS]: (state, payload) => {

        const directory = payload;
        const isNew = (!state.directories.some(existing => (existing.path === directory.path)));

        if (isNew) {
            const parentDirectory = state.directories.find((existing) => (existing.path === directory.directory));
            const parentDirectoryIndex = state.directories.indexOf(parentDirectory);

            // Add the new directory to the directory
            state.directories.push(directory);

            // Update the relation to the parent directory
            state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
                directories: [...parentDirectory.directories, directory.path]
            }));
        }
    },

    /**
     * The delete success mutation
     * @param state
     * @param payload
     */
    [types.DELETE_SUCCESS]: (state, payload) => {
        const item = payload;

        // Delete file
        if (item.type === 'file') {
            state.files.splice(state.files.findIndex(
                file => file.path === item.path
            ), 1);
        }

        // Delete dir
        if (item.type === 'dir') {
            state.directories.splice(state.directories.findIndex(
                directory => directory.path === item.path
            ), 1);
        }
    },

    /**
     * Select a browser item
     * @param state
     * @param payload the item
     */
    [types.SELECT_BROWSER_ITEM]: (state, payload) => {
        state.selectedItems.push(payload);
    },

    /**
     * Unselect a browser item
     * @param state
     * @param payload the item
     */
    [types.UNSELECT_BROWSER_ITEM]: (state, payload) => {
        const item = payload;
        state.selectedItems.splice(state.selectedItems.findIndex(
            selectedItem => selectedItem.path === item.path
        ), 1);
    },

    /**
     * Unselect all browser items
     * @param state
     * @param payload the item
     */
    [types.UNSELECT_ALL_BROWSER_ITEMS]: (state, payload) => {
        state.selectedItems = [];
    },

    /**
     * Show the create folder modal
     * @param state
     */
    [types.SHOW_CREATE_FOLDER_MODAL]: (state) => {
        state.showCreateFolderModal = true;
    },

    /**
     * Show the create folder modal
     * @param state
     */
    [types.HIDE_CREATE_FOLDER_MODAL]: (state) => {
        state.showCreateFolderModal = false;
    },
}
